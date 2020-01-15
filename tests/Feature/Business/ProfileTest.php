<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauthorized_business_cannot_create_a_profile() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];

    $response = $this->json('POST', '/api/business/profile', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_create_a_profile() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $header = $this->businessHeaders($business);
    $name = $this->faker->company;
    $website = $this->faker->url;

    $attributes = [
      'name' => $name,
      'website' => 'www.pockeyt.com',
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];

    $response = $this->json('POST', '/api/business/profile', $attributes, $header)->getData();
    dd($response);
    $this->assertEquals($name, ($response->data->name));
    $this->assertEquals($website, ($response->data->website));
    $this->assertDatabaseHas('profiles', ['business_id' => $business->id]);
  }

  public function test_an_authorized_business_must_have_correct_profile_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $header = $this->businessHeaders($business);

    $attributes = [
      'name' => 'o',
      'website' => 'www.blah@yahoo.com',
      'description' => 'Less than 25 characters' 
    ];
    $response = $this->json('POST', '/api/business/profile', $attributes, $header)->getData();

    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The name must be at least 2 characters.', $response->errors->name[0]);
    $this->assertEquals('The website format is invalid.', $response->errors->website[0]);
    $this->assertEquals('The description must be at least 25 characters.', $response->errors->description[0]);
  }

  public function test_an_unauthorized_business_cannot_retrieve_their_profile_data() {
    $profile = factory(\App\Models\Business\Profile::class)->create();

    $response = $this->json('GET', '/api/business/profile')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_retrieve_their_profile_data() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $header = $this->businessHeaders($profile->business);

    $response = $this->json('GET', '/api/business/profile', $header)->getData();
    $this->assertEquals($profile->identifier, $response->data->identifier);
    $this->assertEquals($profile->name, $response->data->name);
  }

  public function test_an_unauthorized_business_cannot_update_their_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];

    $response = $this->json('PATCH', "/api/business/profile/{$profile->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_update_their_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $header = $this->businessHeaders($profile->business);

    $name = $this->faker->company;
    $website = $this->faker->url;
    $description = $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true);

    $attributes = [
      'name' => $name,
      'website' => $website,
      'description' => $description,
    ];

    $response = $this->json('PATCH', "/api/business/profile/{$profile->identifier}", $attributes, $header)->getData();
    $this->assertEquals($name, $response->data->name);
    $this->assertEquals($website, $response->data->website);
    $this->assertEquals($description, $response->data->description);
  }

  public function test_an_authorized_business_can_only_update_with_correct_data() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $header = $this->businessHeaders($profile->business);

    $attributes = [
      'name' => 'o',
      'website' => 'www.blah@yahoo.com',
      'description' => 'Less than 25 characters' 
    ];

    $response = $this->json('PATCH', "/api/business/profile/{$profile->identifier}", $attributes, $header)->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The name must be at least 2 characters.', $response->errors->name[0]);
    $this->assertEquals('The website format is invalid.', $response->errors->website[0]);
    $this->assertEquals('The description must be at least 25 characters.', $response->errors->description[0]);
  }

  public function test_an_authorized_business_cannot_update_another_profile() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $unauthorizedProfile = factory(\App\Models\Business\Profile::class)->create();
    $header = $this->businessHeaders($unauthorizedProfile->business);

    $attributes = [
      'name' => $this->faker->company,
      'website' => $this->faker->url,
      'description' => $this->faker->paragraph($nbSentences = 3, $variableNbSentences = true),
    ];

    $response = $this->json('PATCH', "/api/business/profile/{$profile->identifier}", $attributes, $header)->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }
}
