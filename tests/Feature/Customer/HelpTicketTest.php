<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelpTicketTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_their_help_tickets() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $response = $this->json('GET', '/api/customer/help')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_their_help_tickets() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $helpTickets = factory(\App\Models\Customer\HelpTicket::class, 8)->create(['customer_id' => $customer->id]);
    $this->customerHeaders($customer);
    $response = $this->json('GET', '/api/customer/help')->getData();
    $this->assertEquals(8, $response->meta->total);
  }

  public function test_an_auth_customer_can_fecth_help_tickets_by_resolved() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 8)->create(['customer_id' => $customer->id, 'resolved' => false]);
    factory(\App\Models\Customer\HelpTicket::class, 3)->create(['customer_id' => $customer->id, 'resolved' => true]);
    $this->customerHeaders($customer);

    $response = $this->json('GET', '/api/customer/help?resolved=false')->getData();
    $this->assertEquals(8, $response->meta->total);

    $response = $this->json('GET', '/api/customer/help?resolved=true')->getData();
    $this->assertEquals(3, $response->meta->total);
  }

  public function test_an_unauth_customer_cannot_create_a_help_ticket() {
    factory(\App\Models\Admin\Admin::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $formData = [
      'subject' => 'Help Ticket Subject',
      'message' => 'Help Ticket Message Body with some more money'
    ];

    $response = $this->json('POST', '/api/customer/help', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_send_correct_message_data() {
    factory(\App\Models\Admin\Admin::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->customerHeaders($customer);

    $formData = [
      'subject' => '',
      'message' => 'z'
    ];
    $response = $this->json('POST', '/api/customer/help', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_create_a_help_ticket() {
    factory(\App\Models\Admin\Admin::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->customerHeaders($customer);

    $subject = 'Help Ticket Subject';
    $message = 'Help Ticket Message Body with some more money';

    $formData = [
      'subject' => $subject,
      'message' => $message
    ];

    $response = $this->json('POST', '/api/customer/help', $formData)->getData();
    $this->assertDatabaseHas('help_tickets', [
      'subject' => $subject,
      'message' => $message,
      'read' => false
    ]);

    $this->assertSame($subject, $response->data->subject);
    $this->assertSame($message, $response->data->message);
  }

  public function test_creating_a_help_ticket_assigns_admin_with_least_help_tickets() {
    $adminOne = factory(\App\Models\Admin\Admin::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 6)->create(['admin_id' => $adminOne->id]);

    $adminTwo = factory(\App\Models\Admin\Admin::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 1)->create(['admin_id' => $adminTwo->id]);

    $adminThree = factory(\App\Models\Admin\Admin::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 4)->create(['admin_id' => $adminThree->id]);

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->customerHeaders($customer);

    $subject = 'Help Ticket Subject';
    $message = 'Help Ticket Message Body with some more money';

    $formData = [
      'subject' => $subject,
      'message' => $message
    ];

    $response = $this->json('POST', '/api/customer/help', $formData)->getData();
    $this->assertEquals($adminTwo->id, \App\Models\Customer\HelpTicket::where('identifier', $response->data->identifier)->first()->admin_id);
  }

  public function test_an_unauth_customer_cannot_delete_their_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $response = $this->json('DELETE', "/api/customer/help/{$helpTicket->identifier}")->assertStatus(401);
    $this->assertSame('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_own_help_ticket_to_delete() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $wrongCustomer = factory(\App\Models\Customer\Customer::class)->create();
    $this->customerHeaders($wrongCustomer);
    $response = $this->json('DELETE', "/api/customer/help/{$helpTicket->identifier}")->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_customer_can_delete_their_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->customerHeaders($helpTicket->customer);
    $response = $this->json('DELETE', "/api/customer/help/{$helpTicket->identifier}")->assertStatus(200);
    $this->assertSame(true, ($response->getData())->data->deleted);
  } 
}
