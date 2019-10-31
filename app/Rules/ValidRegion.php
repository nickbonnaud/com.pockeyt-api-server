<?php

namespace App\Rules;

use App\Models\Location\Region;
use Illuminate\Contracts\Validation\Rule;

class ValidRegion implements Rule
{
  /**
   * Create a new rule instance.
   *
   * @return void
   */
  public function __construct($regionData) {
    $this->regionData = $regionData;
  }

  /**
   * Determine if the validation rule passes.
   *
   * @param  string  $attribute
   * @param  mixed  $value
   * @return bool
   */
  public function passes($attribute, $city) {
    $this->regionData['city'] = $city;
    return Region::checkExists($this->regionData);
  }

  /**
   * Get the validation error message.
   *
   * @return string
   */
  public function message()
  {
      return env('BUSINESS_NAME') . ' is not currently available in your area.';
  }
}
