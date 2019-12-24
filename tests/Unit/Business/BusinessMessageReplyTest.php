<?php

namespace Tests\Unit\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BusinessMessageReplyTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_business_message_reply_belongs_to_a_message() {
  	$message = factory(\App\Models\Business\BusinessMessage::class)->create();
  	$reply = factory(\App\Models\Business\BusinessMessageReply::class)->create(['business_message_id' => $message->id]);
  	$this->assertInstanceOf('App\Models\Business\BusinessMessage', $reply->message);
  }

  public function test_a_business_message_has_many_replies() {
  	$message = factory(\App\Models\Business\BusinessMessage::class)->create();
  	$reply = factory(\App\Models\Business\BusinessMessageReply::class, 4)->create(['business_message_id' => $message->id]);

  	$this->assertInstanceOf('App\Models\Business\BusinessMessageReply', $message->replies->first());
  	$this->assertequals(4, $message->replies->count());
  }

  public function test_a_business_message_reply_generates_a_identifier() {
  	$reply = factory(\App\Models\Business\BusinessMessageReply::class)->create();
  	$this->assertNotNull($reply->identifier);
  }
}
