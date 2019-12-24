<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessMessageTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_business_message_belongs_to_a_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$message = factory(\App\Models\Business\BusinessMessage::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $message->business);
  }

  public function test_a_business_has_many_messages() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$messages = factory(\App\Models\Business\BusinessMessage::class, 5)->create(['business_id' => $business->id]);

  	$this->assertInstanceOf('App\Models\Business\BusinessMessage', $business->messages->first());
  	$this->assertequals(5, $business->messages->count());
  }

  public function test_a_business_message_generates_an_identifier() {
  	$message = factory(\App\Models\Business\BusinessMessage::class)->create();
  	$this->assertNotNull($message->identifier);
  }
}
