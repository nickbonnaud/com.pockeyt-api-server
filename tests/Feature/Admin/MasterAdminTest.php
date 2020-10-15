<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class MasterAdminTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_master_admin_cannot_approve_lower_level_admins() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => Admin\Role::where('name', 'master')->first()->id]);
    $helpAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);

    $formData = [
      'identifier' => $helpAdmin->identifier,
      'approved' => true
    ];

    $response = $this->json('PATCH', '/api/admin/master/admin', $formData)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_master_admin_must_provide_correct_data() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => Admin\Role::where('name', 'master')->first()->id]);
    $this->adminHeaders($masterAdmin);

    $helpAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);

    $formData = [
      'identifier' => $helpAdmin->identifier . '38fbc',
      'approved' => ''
    ];

    $response = $this->json('PATCH', '/api/admin/master/admin', $formData)->assertStatus(422);
    $this->assertSame('The given data was invalid.', ($response->getData())->message);
    $this->assertSame('The identifier must be a valid UUID.', $response->getData()->errors->identifier[0]);
    $this->assertSame('The approved field is required.', $response->getData()->errors->approved[0]);
  }

  public function test_an_auth_admin_must_be_a_master_admin_to_approve_admins() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);
    $helpAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);

    $this->adminHeaders($masterAdmin);

    $formData = [
      'identifier' => $helpAdmin->identifier,
      'approved' => true
    ];

    $response = $this->json('PATCH', '/api/admin/master/admin', $formData)->assertStatus(422);
    $this->assertSame('Invalid Permissions.', ($response->getData())->message);
    $this->assertSame('Master Admin privileges required.', $response->getData()->errors->admin[0]);
  }

  public function test_an_auth_master_admin_can_approve_lower_admins() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => Admin\Role::where('name', 'master')->first()->id]);
    $helpAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);
    $this->adminHeaders($masterAdmin);

    $this->assertDatabaseHas('admins', [
      'email' => $helpAdmin->email,
      'approved' => false
    ]);

    $formData = [
      'identifier' => $helpAdmin->identifier,
      'approved' => true
    ];

    $response = $this->json('PATCH', '/api/admin/master/admin', $formData)->assertStatus(200);

    $this->assertDatabaseHas('admins', [
      'email' => $helpAdmin->email,
      'approved' => true
    ]);

    $this->assertSame('Success.', $response->getData()->message);
  }

  public function test_disapproving_an_admin_request_deletes_admin_from_db() {
    $masterAdmin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => Admin\Role::where('name', 'master')->first()->id]);
    $helpAdmin = factory(\App\Models\Admin\Admin::class)->create(['approved' => false]);
    $this->adminHeaders($masterAdmin);

    $this->assertDatabaseHas('admins', [
      'email' => $helpAdmin->email,
      'approved' => false
    ]);

    $formData = [
      'identifier' => $helpAdmin->identifier,
      'approved' => false
    ];

    $response = $this->json('PATCH', '/api/admin/master/admin', $formData)->assertStatus(200);

    $this->assertDatabaseMissing('admins', [
      'email' => $helpAdmin->email,
    ]);
    $this->assertSame('Success.', $response->getData()->message);
  }
}
