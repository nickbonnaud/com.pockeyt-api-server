<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Business\Photo;

class PhotoTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauthorized_business_cannot_store_a_photo() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.jpg', 1000, 720),
  		'is_logo' => true
  	];

    $response = $this->send("", 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(401);
  	$this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_cannot_store_a_photo_on_another_profile() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$unauthorizedProfile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($unauthorizedProfile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.jpg', 1000, 720),
  		'is_logo' => true
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(403);
  	$this->assertEquals('Permission denied.', ($response->getData())->errors);
  }

  public function test_a_valid_photo_must_be_submitted() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);
    $token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => 'not a photo',
  		'is_logo' => true
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);

  	$this->assertEquals('The given data was invalid.', ($response->getData())->message);
  	$this->assertEquals('The photo must be a file.', ($response->getData())->errors->photo[0]);
  }

  public function test_a_photo_must_be_of_correct_type() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.gif', 1000, 720),
  		'is_logo' => true
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);

  	$this->assertEquals('The given data was invalid.', ($response->getData())->message);
  	$this->assertEquals('The photo must be a file of type: jpg, jpeg, png.', ($response->getData())->errors->photo[0]);
  }

  public function test_a_profile_cannot_store_a_photo_without_is_logo_attribute() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.jpg', 1000, 720)
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);

  	$this->assertEquals('The given data was invalid.', ($response->getData())->message);
  	$this->assertEquals('The is logo field is required.', ($response->getData())->errors->is_logo[0]);
  }

  public function test_a_profile_cannot_store_a_photo_if_is_logo_attribute_is_not_string() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.jpg', 1000, 720),
  		'is_logo' => true
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);

  	$this->assertEquals('The given data was invalid.', ($response->getData())->message);
  	$this->assertEquals('The is logo must be a string.', ($response->getData())->errors->is_logo[0]);
  }

  public function test_logo_must_be_larger_than_150_x_150() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $file = UploadedFile::fake()->image('logo.jpg', 100, 100),
      'is_logo' => true
    ];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);
    $this->assertEquals('Logo must be larger than 150x150 pixels.', ($response->getData())->errors->photo[0]);
  }

  public function test_banner_must_be_larger_than_320_x_100() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $file = UploadedFile::fake()->image('banner.jpg', 300, 90),
      'is_logo' => false
    ];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->assertStatus(422);
    $this->assertEquals('Banner must be larger than 320x100 pixels.', ($response->getData())->errors->photo[0]);
  }

  public function test_an_authorized_profile_can_store_a_logo() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('logo.jpg', 1000, 720),
  		'is_logo' => true
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();
  	$this->assertNotNull($response->data->logo->name);
  	$this->assertNotNull($response->data->logo->small_url);
  	$this->assertNotNull($response->data->logo->large_url);

  	Storage::disk('public')->assertExists(Str::after($response->data->logo->small_url, "http://localhost/storage/"));
  	Storage::disk('public')->assertExists(Str::after($response->data->logo->large_url, "http://localhost/storage/"));

  	$this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->photos->logo_id, 'banner_id' => $profile->photos->banner_id]);
  }

  public function test_an_authorized_profile_can_store_a_banner() {
    Storage::fake('public');
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$token = $this->createBusinessToken($profile->business);

  	$attributes = [
  		'photo' => $file = UploadedFile::fake()->image('banner.jpg', 320, 100),
  		'is_logo' => false
  	];

    $response = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

  	$this->assertNotNull($response->data->banner->name);
  	$this->assertNotNull($response->data->banner->small_url);
  	$this->assertNotNull($response->data->banner->large_url);

  	Storage::disk('public')->assertExists(Str::after($response->data->banner->small_url, "http://localhost/storage/"));
  	Storage::disk('public')->assertExists(Str::after($response->data->banner->large_url, "http://localhost/storage/"));

  	$this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->photos->logo_id, 'banner_id' => $profile->photos->banner_id]);
  }

  public function test_changing_a_logo_removes_photo_from_db_and_storage() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('logo.jpg', 1000, 720),
      'is_logo' => true
    ];

    $responseOld = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($responseOld->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseOld->data->logo->large_url, "http://localhost/storage/"));

    $oldLogoId = $profile->photos->logo_id;

    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $oldLogoId, 'banner_id' => $profile->photos->banner_id]);

    $attributes = [
      'photo' => $fileNew = UploadedFile::fake()->image('new_logo.jpg', 1000, 720),
      'is_logo' => true
    ];

    $responseNew = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($responseNew->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseNew->data->logo->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

    Storage::disk('public')->assertMissing(Str::after($responseOld->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertMissing(Str::after($responseOld->data->logo->large_url, "http://localhost/storage/"));
    $this->assertDatabaseMissing('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $oldLogoId, 'banner_id' => $profile->photos->banner_id]);

    $this->assertEquals(1, Photo::count());
  }

  public function test_changing_a_banner_removes_photo_from_db_and_storage() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('banner.jpg', 1000, 720),
      'is_logo' => false
    ];

    $responseOld = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($responseOld->data->banner->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseOld->data->banner->large_url, "http://localhost/storage/"));

    $oldBannerId = $profile->photos->banner_id;

    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->photos->logo_id, 'banner_id' => $oldBannerId]);

    $attributes = [
      'photo' => $fileNew = UploadedFile::fake()->image('new_banner.jpg', 1000, 720),
      'is_logo' => false
    ];

    $responseNew = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($responseNew->data->banner->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseNew->data->banner->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

    Storage::disk('public')->assertMissing(Str::after($responseOld->data->banner->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertMissing(Str::after($responseOld->data->banner->large_url, "http://localhost/storage/"));
    $this->assertDatabaseMissing('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->photos->logo_id, 'banner_id' => $oldBannerId]);

    $this->assertEquals(1, Photo::count());
  }

  public function test_adding_logo_after_banner_does_not_affect_banner() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('banner.jpg', 1000, 720),
      'is_logo' => false
    ];

    $responseBanner = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('logo.jpg', 1000, 720),
      'is_logo' => true
    ];

    $responseLogo = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($responseLogo->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseLogo->data->logo->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

     Storage::disk('public')->assertExists(Str::after($responseBanner->data->banner->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseBanner->data->banner->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

    $this->assertEquals(2, Photo::count());
  }

  public function test_updating_logo_does_not_affect_banner() {
    Storage::fake('public');
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $token = $this->createBusinessToken($profile->business);

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('banner.jpg', 1000, 720),
      'is_logo' => false
    ];

    $responseBanner = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();


    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('logo.jpg', 1000, 720),
      'is_logo' => true
    ];

    $responseLogo = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    $oldLogoId = $profile->photos->logo_id;

    $attributes = [
      'photo' => $fileOld = UploadedFile::fake()->image('new_logo.jpg', 1000, 720),
      'is_logo' => true
    ];

    $newResponseLogo = $this->send($token, 'post', "/api/business/photos/{$profile->identifier}", $attributes)->getData();

    Storage::disk('public')->assertExists(Str::after($newResponseLogo->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($newResponseLogo->data->logo->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

     Storage::disk('public')->assertExists(Str::after($responseBanner->data->banner->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertExists(Str::after($responseBanner->data->banner->large_url, "http://localhost/storage/"));
    $this->assertDatabaseHas('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $profile->fresh()->photos->logo_id, 'banner_id' => $profile->fresh()->photos->banner_id]);

    Storage::disk('public')->assertMissing(Str::after($responseLogo->data->logo->small_url, "http://localhost/storage/"));
    Storage::disk('public')->assertMissing(Str::after($responseLogo->data->logo->large_url, "http://localhost/storage/"));
    $this->assertDatabaseMissing('profile_photos', ['profile_id' => $profile->id, 'logo_id' => $oldLogoId, 'banner_id' => $profile->photos->banner_id]);

    $this->assertEquals(2, Photo::count());
  }
}
