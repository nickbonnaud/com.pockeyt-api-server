<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpTicketReplyTest extends TestCase{
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_help_ticket_reply_belongs_to_a_help_ticket() {
  	$helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
  	$helpTicketReply = factory(\App\Models\Customer\HelpTicketReply::class)->create(['help_ticket_id' => $helpTicket->id]);
  	$this->assertInstanceOf('App\Models\Customer\HelpTicket', $helpTicketReply->helpTicket);
  }

  public function test_a_help_ticket_can_have_many_replies() {
  	$helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
  	$helpTicketReply = factory(\App\Models\Customer\HelpTicketReply::class, 6)->create(['help_ticket_id' => $helpTicket->id]);
  	$this->assertEquals(6, $helpTicket->replies->count());
  }
}
