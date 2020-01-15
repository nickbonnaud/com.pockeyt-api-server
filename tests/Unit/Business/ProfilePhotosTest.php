<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfilePhotosTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_profile_owns_profile_photos() {
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->make();
  	$profile->photos()->save($photos);
  	$this->assertInstanceOf('App\Models\Business\ProfilePhotos', $profile->photos);
  }

  public function test_profile_photos_is_owned_by_a_profile() {
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->make();
  	$profile->photos()->save($photos);
  	$this->assertInstanceOf('App\Models\Business\Profile', $photos->profile);
  }

  public function test_a_logo_photo_owns_profile_photos_logo() {
  	$photo = factory(\App\Models\Business\Photo::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['logo_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Business\ProfilePhotos', $photo->logo);
  }

  public function test_profile_photos_are_owned_by_a_logo_photo() {
  	$photo = factory(\App\Models\Business\Photo::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['logo_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Business\Photo', $photos->logo);
  }

  public function test_a_banner_photo_owns_profile_photos() {
  	$photo = factory(\App\Models\Business\Photo::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['banner_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Business\ProfilePhotos', $photo->banner);
  }

  public function test_profile_photos_are_owned_by_a_banner_photo() {
  	$photo = factory(\App\Models\Business\Photo::class)->create();
  	$photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['banner_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Business\Photo', $photos->banner);
  }

  public function test_creating_a_logo_and_not_banner_does_not_change_status_to_102() {
    $photo = factory(\App\Models\Business\Photo::class)->create();
    $photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['logo_id' => $photo->id, 'banner_id' => null]);
    $this->assertNotEquals(102, $photos->profile->business->account->fresh()->status->code);
  }

  public function test_creating_a_banner_and_not_logo_does_not_change_status_to_102() {
    $photo = factory(\App\Models\Business\Photo::class)->create();
    $photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['banner_id' => $photo->id, 'logo_id' => null]);
    $this->assertNotEquals(102, $photos->profile->business->account->fresh()->status->code);
  }

  public function test_creating_a_banner_and_logo_changes_status_to_102() {
    $photoBanner = factory(\App\Models\Business\Photo::class)->create();
    $photoLogo = factory(\App\Models\Business\Photo::class)->create();
    $photos = factory(\App\Models\Business\ProfilePhotos::class)->create(['banner_id' => $photoBanner->id, 'logo_id' => $photoLogo->id]);
    $this->assertEquals(102, $photos->profile->business->account->fresh()->status->code);
  }


}
