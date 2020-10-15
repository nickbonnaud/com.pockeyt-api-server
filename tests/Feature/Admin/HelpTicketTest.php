<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HelpTicketTest extends TestCase {
   use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_admin_cannot_view_their_help_tickets() {
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $response = $this->json('GET', "/api/admin/help")->assertStatus(401);

    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_admin_can_fetch_their_help_tickets() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $ticket = factory(\App\Models\Customer\HelpTicket::class, 5)->create(['admin_id' => $admin->id]);
    factory(\App\Models\Customer\HelpTicket::class, 3)->create();

    $this->adminHeaders($admin);
    $response = $this->json('GET', "/api/admin/help")->getData();
    $this->assertEquals(5, $response->meta->total);
  }

  public function test_an_auth_admin_can_fetch_help_ticket_by_resolved() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 4)->create(['admin_id' => $admin->id, 'resolved' => false]);
    factory(\App\Models\Customer\HelpTicket::class, 6)->create(['admin_id' => $admin->id, 'resolved' => true]);
    $this->adminHeaders($admin);

    $response = $this->json('GET', "/api/admin/help?resolved=false")->getData();
    $this->assertEquals(4, $response->meta->total);

    $response = $this->json('GET', "/api/admin/help?resolved=true")->getData();
    $this->assertEquals(6, $response->meta->total);
  }

  public function test_an_unauth_admin_cannot_mark_help_ticket_as_read() {
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $formData = ['read' => true];

    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}")->assertStatus(401);

    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_admin_can_only_mark_help_tickets_they_own_as_read() {
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create();
    $formData = ['read' => true];

    $this->adminHeaders(factory(\App\Models\Admin\Admin::class)->create());

    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}", $formData)->assertStatus(403);
    $this->assertSame('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_admin_only_marks_help_tickets_as_read_referenced_in_request() {
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create();
    factory(\App\Models\Customer\HelpTicket::class, 4)->create(['admin_id' => $ticket->admin_id]);

    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'read' => false
    ]);

    $formData = ['read' => true];

    $this->adminHeaders($ticket->admin);
    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}", $formData)->getData();

    $this->assertSame(true, $response->data->read);
    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'read' => true
    ]);
    $this->assertEquals(4, \App\Models\Customer\HelpTicket::where('read', false)->count());
  }

  public function test_an_auth_admin_can_mark_help_tickets_as_resolved() {
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create(['read' =>true]);
    factory(\App\Models\Customer\HelpTicket::class, 4)->create(['admin_id' => $ticket->admin_id]);

    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'resolved' => false
    ]);

    $formData = ['resolved' => true];
    $this->adminHeaders($ticket->admin);
    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}", $formData)->getData();

    $this->assertSame(true, $response->data->resolved);
    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'resolved' => true
    ]);
  }

  public function test_an_auth_master_admin_can_fetch_all_help_tickets() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => \App\Models\Admin\Role::where('name', 'master' )->first()->id]);
    factory(\App\Models\Customer\HelpTicket::class, 8)->create();

    $this->adminHeaders($masterAdmin);
    $response = $this->json('GET', "/api/admin/help")->getData();
    $this->assertEquals(8, $response->meta->total);
  }

  public function test_an_auth_master_admin_can_mark_ticket_as_read() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => \App\Models\Admin\Role::where('name', 'master' )->first()->id]);
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create();

    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'read' => false
    ]);

    $formData = ['read' => true];

    $this->adminHeaders($masterAdmin);
    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}", $formData)->getData();

    $this->assertSame(true, $response->data->read);
    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'read' => true
    ]);
  }

  public function test_an_auth_master_admin_can_mark_help_tickets_as_resolved() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => \App\Models\Admin\Role::where('name', 'master' )->first()->id]);
    $ticket = factory(\App\Models\Customer\HelpTicket::class)->create(['read' =>true]);

    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'resolved' => false
    ]);

    $formData = ['resolved' => true];

    $this->adminHeaders($masterAdmin);
    $response = $this->json('PATCH', "/api/admin/help/{$ticket->identifier}", $formData)->getData();

    $this->assertSame(true, $response->data->resolved);
    $this->assertDatabaseHas('help_tickets', [
      'identifier' => $ticket->identifier,
      'resolved' => true
    ]);
  }
}
