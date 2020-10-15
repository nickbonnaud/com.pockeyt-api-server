<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelpTicketReplyTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_create_a_help_ticket_reply() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => 'Help Ticket Reply Message'
    ];

    $response = $this->json('POST', '/api/customer/help-reply', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_send_correct_help_reply_data() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->customerHeaders($helpTicket->customer);

    $formData = [
      'ticket_identifier' => 111,
      'message' => ''
    ];

    $response = $this->json('POST', '/api/customer/help-reply', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_only_create_reply_to_help_ticket_they_own() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->customerHeaders($helpTicket->customer);

    $formData = [
      'ticket_identifier' => factory(\App\Models\Customer\HelpTicket::class)->create()->identifier,
      'message' => 'Help Ticket Reply Message'
    ];

    $response = $this->json('POST', '/api/customer/help-reply', $formData)->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_customer_can_create_a_help_ticket_reply() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->customerHeaders($helpTicket->customer);
    $message = 'Help Ticket Reply Message';
    
    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => $message
    ];

    $response = $this->json('POST', '/api/customer/help-reply', $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'message' => $message,
      'read' => false,
      'from_customer' => true
    ]);

    $this->assertSame($message, $response->data->message);
    $this->assertTrue($response->data->from_customer);
  }

  public function test_auth_customer_adding_reply_touches_owning_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $helpTicketUpdatedAt = $helpTicket->updated_at;
    $this->customerHeaders($helpTicket->customer);
    $message = 'Help Ticket Reply Message';
    
    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => $message
    ];
    sleep(1);
    $response = $this->json('POST', '/api/customer/help-reply', $formData)->getData();
    $this->assertNotEquals($helpTicketUpdatedAt, $helpTicket->fresh()->updated_at);
  }

  public function test_an_unauth_customer_cannot_mark_replies_as_read() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => false, 'read' => false]);
    $this->assertEquals(2, $helpTicket->replies()->where('read', false)->count());

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/customer/help-reply/{$helpTicket->identifier}", $formData)->assertStatus(401);
    $this->assertSame('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_only_mark_replies_as_read_for_tickets_they_own() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => false, 'read' => false]);
    $wrongHelpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $this->customerHeaders($helpTicket->customer);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/customer/help-reply/{$wrongHelpTicket->identifier}", $formData)->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_customer_only_updates_replies_for_referenced_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => false, 'read' => false]);
    $wrongHelpTicket = factory(\App\Models\Customer\HelpTicket::class)->create(['customer_id' => $helpTicket->customer_id]);
    $wrongReplies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $wrongHelpTicket->id, 'from_customer' => false, 'read' => false]);

    $this->customerHeaders($helpTicket->customer);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/customer/help-reply/{$wrongHelpTicket->identifier}", $formData);
    $this->assertEquals(2, $helpTicket->replies()->where('read', false)->count());
    $this->assertEquals(0, $wrongHelpTicket->replies()->where('read', false)->count());
  }

  public function test_an_auth_customer_can_update_their_replies_for_a_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => false, 'read' => false]);

    $this->assertEquals(2, $helpTicket->replies()->where('read', false)->count());
    $this->customerHeaders($helpTicket->customer);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/customer/help-reply/{$helpTicket->identifier}", $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[0]->id . "",
      'read' => "1"
    ]);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[1]->id . "",
      'read' => '1'
    ]);

    $this->assertEquals(0, $helpTicket->replies()->where('read', false)->count());
    $this->assertEquals(2, $helpTicket->replies()->where('read', true)->count());
  }
}
