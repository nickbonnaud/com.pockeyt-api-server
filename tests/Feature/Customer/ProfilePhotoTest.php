<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Models\Customer\CustomerPhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfilePhotoTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_store_a_photo() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 600, 600)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_must_store_their_own_photo() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $otherProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($otherProfile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 600, 600)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->assertStatus(403);
    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }

  public function test_a_valid_avatar_must_be_submitted() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => 'not_an_avatar'
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->assertStatus(422);
    $this->assertEquals('The avatar must be a file.', ($response->getData())->errors->avatar[0]);
  }

  public function test_an_avatar_must_be_correct_type() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.gif', 600, 600)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->assertStatus(422);
    $this->assertEquals('The avatar must be a file of type: jpg, jpeg, png.', ($response->getData())->errors->avatar[0]);
  }

  public function test_an_avatar_must_be_larger_than_250_x_250() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 100, 100)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->assertStatus(422);
    $this->assertEquals('The avatar has invalid image dimensions.', ($response->getData())->errors->avatar[0]);
  }

  public function test_an_auth_customer_can_store_an_avatar() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->getData();
    $this->assertNotNull($response->data->profile->photos->name);
    $this->assertNotNull($response->data->profile->photos->small_url);

    Storage::disk('public')->assertExists(Str::after($response->data->profile->photos->small_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('customer_profile_photos', ['customer_profile_id' => $profile->id, 'avatar_id' => $profile->photo->avatar_id]);
  }

  public function test_adding_new_avatar_set_correctly_sets_customer_status() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)
    ];

    $response = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->getData();

    $this->assertEquals(102, $profile->customer->fresh()->status->code);
  }

  public function test_changing_avatar_removes_old_avatar_from_db_and_storage() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)
    ];

    $responseOld = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->getData();
    Storage::disk('public')->assertExists(Str::after($responseOld->data->profile->photos->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseOld->data->profile->photos->large_url, "http://localhost/storage/"));

    $oldAvatarId = $profile->photo->avatar_id;
    $this->assertDatabaseHas('customer_profile_photos', ['customer_profile_id' => $profile->id, 'avatar_id' => $oldAvatarId]);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar_new.jpg', 500, 500)
    ];

    $responseNew = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->getData();

    Storage::disk('public')->assertExists(Str::after($responseNew->data->profile->photos->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseNew->data->profile->photos->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('customer_profile_photos', ['customer_profile_id' => $profile->id, 'avatar_id' => $profile->fresh()->photo->avatar_id]);

    Storage::disk('public')->assertMissing(Str::after($responseOld->data->profile->photos->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertMissing(Str::after($responseOld->data->profile->photos->large_url, "http://localhost/storage/"));
    $this->assertEquals(1, CustomerPhoto::count());
  }

  public function test_changing_avatar_does_not_change_customer_status() {
    Storage::fake('public');
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $customer = $profile->customer;
    $customer->customer_status_id = \App\Models\Customer\CustomerStatus::where('code', 120)->first()->id;
    $customer->save();

    $header = $this->customerHeaders($profile->customer);

    $attributes = [
      'avatar' => $file = UploadedFile::fake()->image('avatar.jpg', 500, 500)
    ];

    $responseOld = $this->json('POST', "/api/customer/avatar/{$profile->identifier}", $attributes, $header)->getData();
    $this->assertEquals(120, $customer->fresh()->status->code);
  }
}
