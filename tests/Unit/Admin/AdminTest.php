<?php

namespace Tests\Unit\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_admin_belongs_to_a_role() {
  	$role = \App\Models\Admin\Role::first();
  	$admin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => $role->id]);

  	$this->assertInstanceOf('App\Models\Admin\Admin', $role->admins->first());
  }

  public function test_a_role_has_many_admins() {
  	$role = \App\Models\Admin\Role::first();
  	$admin = factory(\App\Models\Admin\Admin::class, 3)->create(['role_id' => $role->id]);

  	$this->assertEquals(3, $role->admins->count());
  }

  public function test_an_admin_has_one_role() {
  	$role = \App\Models\Admin\Role::first();
  	$admin = factory(\App\Models\Admin\Admin::class)->create(['role_id' => $role->id]);
  	$this->assertInstanceOf('App\Models\Admin\Role', $admin->role);
  }

  public function test_an_admin_creates_a_unique_identifier() {
  	$admin = factory(\App\Models\Admin\Admin::class)->create();
  	$this->assertNotNull($admin->identifier);
  }
}
