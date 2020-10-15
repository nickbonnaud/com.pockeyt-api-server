<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelpTicketReplyTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_admin_cannot_create_a_help_ticket_reply() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => 'Help Ticket Reply Message'
    ];

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_admin_must_send_correct_help_reply_data() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->adminHeaders($helpTicket->admin);

    $formData = [
      'ticket_identifier' => 'cnji32',
      'message' => 33
    ];

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->assertStatus(422);
    $this->assertEquals('The given data was invalid.', ($response->getData())->message);
  }

  public function test_an_auth_admin_can_only_add_reply_to_help_ticket_they_own() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->adminHeaders($helpTicket->admin);

    $formData = [
      'ticket_identifier' => factory(\App\Models\Customer\HelpTicket::class)->create()->identifier,
      'message' => 'Help Ticket Reply Message'
    ];

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_admin_can_create_a_help_ticket_reply() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->adminHeaders($helpTicket->admin);
    $message = 'Help Ticket Reply Message';
    
    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => $message
    ];

    $this->assertDatabaseMissing('help_ticket_replies', [
      'id' => $helpTicket->id,
    ]);

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $helpTicket->id,
      'message' => $message,
      'read' => false,
      'from_customer' => false
    ]);

    $this->assertSame($message, $response->data->message);
    $this->assertFalse($response->data->from_customer);
  }

  public function test_auth_admin_adding_reply_touches_owning_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $helpTicketUpdatedAt = $helpTicket->updated_at;
    $this->adminHeaders($helpTicket->admin);

    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => 'Help Ticket Reply Message'
    ];

    sleep(1);

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->getData();
    $this->assertNotEquals($helpTicketUpdatedAt, $helpTicket->fresh()->updated_at);
  }

  public function test_an_unauth_admin_cannot_mark_replies_as_read() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 4)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => true, 'read' => false]);
    $this->assertEquals(4, $helpTicket->replies()->where('read', false)->count());

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "api/admin/help-reply/{$helpTicket->identifier}", $formData)->assertStatus(401);
    $this->assertSame('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_admin_can_only_mark_replies_as_read_for_tickets_they_own() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 4)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => true, 'read' => false]);

    $wrongHelpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $wrongHelpTicket->id, 'from_customer' => true, 'read' => false]);

    $this->adminHeaders($helpTicket->admin);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "api/admin/help-reply/{$wrongHelpTicket->identifier}", $formData)->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_admin_only_marks_as_read_replies_on_referenced_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 4)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => true, 'read' => false]);

    $wrongHelpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $wrongHelpTicket->id, 'from_customer' => true, 'read' => false]);

    $this->adminHeaders($helpTicket->admin);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "api/admin/help-reply/{$helpTicket->identifier}", $formData)->getData();
    $this->assertEquals(2, $wrongHelpTicket->replies()->where('read', false)->count());
    $this->assertEquals(0, $helpTicket->replies()->where('read', false)->count());
  }

  public function test_an_auth_admin_can_mark_replies_as_read_for_help_ticket() {
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => true, 'read' => false]);

    $this->adminHeaders($helpTicket->admin);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[0]->id . "",
      'read' => "0"
    ]);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[1]->id . "",
      'read' => "0"
    ]);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/admin/help-reply/{$helpTicket->identifier}", $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[0]->id . "",
      'read' => "1"
    ]);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[1]->id . "",
      'read' => "1"
    ]);

    $this->assertTrue($response->data->replies[0]->read);
    $this->assertTrue($response->data->replies[1]->read);
  }

  public function test_an_auth_master_admin_can_create_a_help_ticket_reply() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => \App\Models\Admin\Role::where('name', 'master' )->first()->id]);
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $this->adminHeaders($masterAdmin);
    $message = 'Help Ticket Reply Message';
    
    $formData = [
      'ticket_identifier' => $helpTicket->identifier,
      'message' => $message
    ];

    $this->assertDatabaseMissing('help_ticket_replies', [
      'id' => $helpTicket->id,
    ]);

    $response = $this->json('POST', '/api/admin/help-reply', $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $helpTicket->id,
      'message' => $message,
      'read' => false,
      'from_customer' => false
    ]);

    $this->assertSame($message, $response->data->message);
    $this->assertFalse($response->data->from_customer);
  }

  public function test_an_auth_master_admin_can_mark_replies_as_read_for_help_ticket() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => \App\Models\Admin\Role::where('name', 'master' )->first()->id]);
    $helpTicket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $replies = factory(\App\Models\Customer\HelpTicketReply::class, 2)->create(['help_ticket_id' => $helpTicket->id, 'from_customer' => true, 'read' => false]);

    $this->adminHeaders($masterAdmin);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[0]->id . "",
      'read' => "0"
    ]);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[1]->id . "",
      'read' => "0"
    ]);

    $formData = [
      'read' => true
    ];

    $response = $this->json('PATCH', "/api/admin/help-reply/{$helpTicket->identifier}", $formData)->getData();

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[0]->id . "",
      'read' => "1"
    ]);

    $this->assertDatabaseHas('help_ticket_replies', [
      'id' => $replies[1]->id . "",
      'read' => "1"
    ]);

    $this->assertTrue($response->data->replies[0]->read);
    $this->assertTrue($response->data->replies[1]->read);
  }
}