<?php

namespace Tests\Feature\Business;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessMessageTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_create_a_message() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $formData = [
      'title' => "Message Title",
      'body' => "Message Body",
      'sent_by_business' => true
    ];

    $response = $this->json('POST', '/api/business/message', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_must_send_correct_message_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $this->businessHeaders($business);

    $formData = [
      'title' => "w",
      'body' => "wdw",
      'sent_by_business' => "not bool"
    ];

    $response = $this->json('POST', '/api/business/message', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_create_a_message() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $this->businessHeaders($business);

    $formData = [
      'title' => "Message Title",
      'body' => "Message Body",
      'sent_by_business' => true
    ];

    $response = $this->json('POST', '/api/business/message', $formData)->getData();
    $this->assertDatabaseHas('business_messages', [
      'title' => "Message Title",
      'body' => "Message Body",
      'sent_by_business' => true
    ]);
    $this->assertEquals($formData['title'], $response->data->title);
    $this->assertEquals($formData['body'], $response->data->body);
  }

  public function test_an_unauth_business_cannot_update_a_message() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['sent_by_business' => false]);

    $formData = [
      'read' => true,
    ];

    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_only_update_read_attribute_on_message() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['sent_by_business' => false]);
    $this->businessHeaders($message->business);

    $formData = [
      'title' => 'new title',
      'body' => 'new body',
      'read' => true,
    ];

    $this->assertDatabaseMissing('business_messages', ['identifier' => $message->identifier, 'read' => true]);

    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->getData();
    $this->assertNotEquals('new title', $response->data->title);
    $this->assertNotEquals('new body', $response->data->body);
    $this->assertEquals(true, $response->data->read);
    
    $this->assertDatabaseHas('business_messages', ['identifier' => $message->identifier, 'read' => true]);
  }

  public function test_an_auth_business_cannot_update_message_after_read() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['sent_by_business' => false, 'read' => true]);
    $this->businessHeaders($message->business);

    $formData = [
      'read' => false,
    ];

    $this->assertDatabaseHas('business_messages', ['identifier' => $message->identifier, 'read' => true]);
    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->getData();
    $this->assertNotEquals(false, $response->data->read);
    $this->assertDatabaseHas('business_messages', ['identifier' => $message->identifier, 'read' => true]);
  }

  public function test_an_unauth_business_cannot_retrieve_messages() {
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\BusinessMessage::class, 8)->create(['business_id' => $business->id]);
    $response = $this->json('GET', '/api/business/message')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_retrieve_their_messages() {
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\BusinessMessage::class, 8)->create(['business_id' => $business->id]);
    $this->businessHeaders($business);

    $response = $this->json('GET', '/api/business/message')->getData();
    $this->assertEquals(8, count($response->data));
  }

  public function test_a_business_message_includes_all_replies() {
    $numReplies = 12;

    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $replies = factory(\App\Models\Business\BusinessMessageReply::class, $numReplies)->create(['business_message_id' => $message->id]);
    $this->businessHeaders($message->business);

    $response = $this->json('GET', '/api/business/message')->getData();
    $this->assertEquals($numReplies, count($response->data[0]->replies));
  }

  public function test_retrieving_business_messages_orders_by_updated_at() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $latestMessage = factory(\App\Models\Business\BusinessMessage::class)->create(['business_id' => $business->id]);
    $earliestMessage = factory(\App\Models\Business\BusinessMessage::class)->create(['business_id' => $business->id, 'updated_at' => (Carbon::now())->subDays(5)]);
    $midMessage = factory(\App\Models\Business\BusinessMessage::class)->create(['business_id' => $business->id, 'updated_at' => (Carbon::now())->subDays(2)]);

    $this->businessHeaders($business);

    $response = $this->json('GET', '/api/business/message')->getData();
    $this->assertEquals($latestMessage->identifier, $response->data[0]->identifier);
    $this->assertEquals($midMessage->identifier, $response->data[1]->identifier);
    $this->assertEquals($earliestMessage->identifier, $response->data[2]->identifier);
  }

  public function test_an_auth_business_can_update_unread_reply_to_false() {
    $numReplies = 3;

    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $replies = factory(\App\Models\Business\BusinessMessageReply::class, $numReplies)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $this->businessHeaders($message->business);

    $formData = [
      'unread_reply' => false
    ];

    
    $this->assertTrue($message->fresh()->unread_reply);
    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->getData();

    $this->assertFalse($message->fresh()->unread_reply);
  }

  public function test_a_business_updating_unread_reply_to_false_marks_all_replies_as_read() {
    $numReplies = 3;

    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $replies = factory(\App\Models\Business\BusinessMessageReply::class, $numReplies)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $this->businessHeaders($message->business);

    $formData = [
      'unread_reply' => false
    ];

    
    $this->assertDatabaseMissing('business_message_replies', ['read' => true]);
    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->getData();
    $this->assertDatabaseHas('business_message_replies', ['read' => true]);
    $this->assertEquals($message->replies()->where('read', true)->count(), $numReplies);
  }

  public function test_a_business_only_marks_replies_as_read_if_not_sent_by_business() {
    $numReplies = 3;
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $replies = factory(\App\Models\Business\BusinessMessageReply::class, $numReplies)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);

    factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => true]);
    $this->businessHeaders($message->business);

    $formData = [
      'unread_reply' => false
    ];

    
    $this->assertEquals(4, $message->replies()->where('read', false)->count());
    $response = $this->json('PATCH', "/api/business/message/{$message->identifier}", $formData)->getData();
    $this->assertEquals(1, $message->replies()->where('read', false)->count());
  }
}
