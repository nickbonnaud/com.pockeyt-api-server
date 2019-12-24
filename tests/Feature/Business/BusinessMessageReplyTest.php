<?php

namespace Tests\Feature\Business;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BusinessMessageReplyTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_create_a_reply() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
      'sent_by_business' => true
    ];

    $response = $this->json('POST', '/api/business/reply', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_must_send_correct_reply_data() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => 'not a uuid',
      'body' => 'b',
      'sent_by_business' => 'not boolean'
    ];

    $response = $this->json('POST', '/api/business/reply', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_create_a_reply() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
      'sent_by_business' => true
    ];

    $response = $this->json('POST', '/api/business/reply', $formData)->getData();

    $this->assertDatabaseHas('business_message_replies', [
      'body' => $formData['body'],
      'sent_by_business' => $formData['sent_by_business']
    ]);

    $this->assertEquals($formData['body'], $response->data->body);
    $this->assertNotNull($message->replies);
  }

  public function test_an_auth_business_can_only_create_reply_for_their_message() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $header = $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => "091d62e0-1871-11ea-87a1-e3a681034c76",
      'body' => 'body of reply',
      'sent_by_business' => true
    ];

    $response = $this->json('POST', '/api/business/reply', $formData, $header)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_unauth_business_cannot_update_a_reply() {
    $reply = factory(\App\Models\Business\BusinessMessageReply::class)->create();

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/business/reply/{$reply->identifier}", $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_only_update_read_attribute_on_reply() {
    $reply = factory(\App\Models\Business\BusinessMessageReply::class)->create(['read' => false]);
    $this->businessHeaders($reply->message->business);

    $formData = [
      'body' => 'new body',
      'read' => true
    ];

    $this->assertDatabaseMissing('business_message_replies', ['identifier' => $reply->identifier, 'read' => true]);

    $response = $this->json('PATCH', "/api/business/reply/{$reply->identifier}", $formData)->getData();

    $this->assertNotEquals($formData['body'], $response->data->body);
    $this->assertEquals(true, $response->data->read);

    $this->assertDatabaseHas('business_message_replies', ['identifier' => $reply->identifier, 'read' => true]);
  }

  public function test_an_auth_business_cannot_update_reply_after_it_has_been_read() {
    $reply = factory(\App\Models\Business\BusinessMessageReply::class)->create(['read' => true]);
    $this->businessHeaders($reply->message->business);

    $formData = [
      'body' => 'new body',
      'read' => false
    ];

    $this->assertDatabaseHas('business_message_replies', ['identifier' => $reply->identifier, 'read' => true]);

    $response = $this->json('PATCH', "/api/business/reply/{$reply->identifier}", $formData)->getData();

    $this->assertNotEquals($formData['read'], $response->data->read);
    $this->assertDatabaseHas('business_message_replies', ['identifier' => $reply->identifier, 'read' => true]);
  }

  public function test_adding_a_reply_updates_updates_the_owning_message_timestamp() {
    $createdDate = (Carbon::now())->subDay();
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['created_at' => $createdDate, 'updated_at' => $createdDate]);
    $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
      'sent_by_business' => false
    ];

    $this->assertEquals($message->fresh()->updated_at->toDateTimeString(), $createdDate->toDateTimeString());
    $response = $this->json('POST', '/api/business/reply', $formData)->getData();
    $this->assertNotEquals($message->fresh()->updated_at->toDateTimeString(), $createdDate->toDateTimeString());
    $this->assertEquals($message->fresh()->updated_at->toDateTimeString(), $response->data->created_at);
  }

  public function test_adding_a_reply_to_a_business_message_not_from_business_sets_unread_reply_to_true() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
      'sent_by_business' => false
    ];

    $this->assertFalse($message->fresh()->unread_reply);
    $response = $this->json('POST', '/api/business/reply', $formData)->getData();
    $this->assertTrue($message->fresh()->unread_reply);
  }

  public function test_adding_a_reply_to_a_business_message_from_business_does_not_set_unread_reply_to_true() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $this->businessHeaders($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
      'sent_by_business' => true
    ];

    $this->assertFalse($message->fresh()->unread_reply);
    $response = $this->json('POST', '/api/business/reply', $formData)->getData();
    $this->assertFalse($message->fresh()->unread_reply);
  }

  public function test_marking_reply_as_read_by_business_sets_unread_reply_to_false_if_reply_not_from_business() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $reply = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $this->businessHeaders($reply->message->business);

    $formData = [
      'read' => true
    ];

    $this->assertTrue($message->fresh()->unread_reply);
    $response = $this->json('PATCH', "/api/business/reply/{$reply->identifier}", $formData)->getData();
    $this->assertFalse($message->fresh()->unread_reply);
  }

  public function test_marking_reply_as_read_by_business_does_not_set_unread_to_false_if_other_unread_replies() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $reply = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $this->businessHeaders($reply->message->business);

    $formData = [
      'read' => true
    ];

    $this->assertTrue($message->fresh()->unread_reply);
    $response = $this->json('PATCH', "/api/business/reply/{$reply->identifier}", $formData)->getData();
    $this->assertTrue($message->fresh()->unread_reply);
  }

  public function test_marking_reply_as_read_does_not_set_unread_to_false_if_sent_by_business() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $replyToBusiness = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $replyFromBusiness = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => true]);
    $this->businessHeaders($replyFromBusiness->message->business);

    $formData = [
      'read' => true
    ];

    $this->assertTrue($message->fresh()->unread_reply);
    $response = $this->json('PATCH', "/api/business/reply/{$replyFromBusiness->identifier}", $formData)->getData();
    $this->assertTrue($message->fresh()->unread_reply);
  }

  public function test_marking_reply_as_read_sets_unread_to_false_if_not_sent_by_business() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['unread_reply' => true]);
    $replyToBusiness = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => false]);
    $replyFromBusiness = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id, 'read' => false, 'sent_by_business' => true]);
    $this->businessHeaders($replyToBusiness->message->business);

    $formData = [
      'read' => true
    ];

    $this->assertTrue($message->fresh()->unread_reply);
    $response = $this->json('PATCH', "/api/business/reply/{$replyToBusiness->identifier}", $formData)->getData();
    $this->assertFalse($message->fresh()->unread_reply);
  }
}
