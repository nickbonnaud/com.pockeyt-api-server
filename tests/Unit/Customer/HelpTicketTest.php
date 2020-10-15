<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelpTicketTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_help_ticket_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $helpTicket->customer);
  }

  public function test_a_customer_can_have_many_help_messages() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$helpTicket = factory(\App\Models\Customer\HelpTicket::class, 4)->create(['customer_id' => $customer->id]);
  	$this->assertequals(4, $customer->helpTickets->count());
  }

  public function test_a_help_ticket_belongs_to_an_admin() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create(['admin_id' => $admin->id]);
    $this->assertInstanceOf('App\Models\Admin\Admin', $helpTicket->admin);
  }

  public function test_an_admin_can_have_many_help_tickets() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class, 7)->create(['admin_id' => $admin->id]);
    $this->assertequals(7, $admin->helpTickets->count());
  }

  public function test_a_help_ticket_generates_an_identifier() {
  	$helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
  	$this->assertNotNull($helpTicket->identifier);
  }
}
