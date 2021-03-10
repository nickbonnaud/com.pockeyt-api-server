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
    ];

    $response = $this->send('', 'post', '/api/business/reply', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_must_send_correct_reply_data() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $token = $this->createBusinessToken($message->business);

    $formData = [
      'message_identifier' => 'not a uuid',
      'body' => 'b',
    ];

    $response = $this->send($token, 'post', '/api/business/reply', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_create_a_reply() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $token = $this->createBusinessToken($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
    ];

    $latestReply = $message->latest_reply;
    $response = $this->send($token, 'post', '/api/business/reply', $formData)->getData();

    $this->assertDatabaseHas('business_message_replies', [
      'body' => $formData['body'],
      'sent_by_business' => true
    ]);

    $this->assertNotSame($latestReply, $message->fresh()->latest_reply);
    $this->assertEquals($formData['body'], $response->data->body);
    $this->assertNotNull($message->replies);
  }

  public function test_an_auth_business_can_only_create_reply_for_their_message() {
    $message = factory(\App\Models\Business\BusinessMessage::class)->create();
    $token = $this->createBusinessToken($message->business);

    $formData = [
      'message_identifier' => "091d62e0-1871-11ea-87a1-e3a681034c76",
      'body' => 'body of reply',
    ];

    $response = $this->send($token, 'post', '/api/business/reply', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_adding_a_reply_updates_updates_the_owning_message_timestamp() {
    $createdDate = (Carbon::now())->subDay();
    $message = factory(\App\Models\Business\BusinessMessage::class)->create(['created_at' => $createdDate, 'updated_at' => $createdDate]);
    $token = $this->createBusinessToken($message->business);

    $formData = [
      'message_identifier' => $message->identifier,
      'body' => 'body of reply',
    ];

    $this->assertEquals($message->fresh()->updated_at->toDateTimeString(), $createdDate->toDateTimeString());
    $response = $this->send($token, 'post', '/api/business/reply', $formData)->getData();
    $this->assertNotEquals($message->fresh()->updated_at->toDateTimeString(), $createdDate->toDateTimeString());
    $this->assertEquals($message->fresh()->updated_at->toJson(), $response->data->created_at);
  }
}
