<?php

namespace App\Rules;

use App\Models\Business\PayFacOwner;
use Illuminate\Contracts\Validation\Rule;

class PercentOwnership implements Rule
{
  /**
   * Create a new rule instance.
   *
   * @return void
   */
  public function __construct($owner = null) {
    $this->owner = $owner;
  }

  /**
   * Determine if the validation rule passes.
   *
   * @param  string  $attribute
   * @param  mixed  $value
   * @return bool
   */
  public function passes($attribute, $value) {
    return ($value <= 100) && $this->totalOwnershipCheck($value);
  }

  /**
   * Get the validation error message.
   *
   * @return string
   */
  public function message()
  {
    return 'Percent ownership is greater than 100.';
  }

  private function totalOwnershipCheck($value) {
    $owners = (auth('business')->user())->account->getPayFacOwners();
    if ($this->owner) {
      $owners = $owners->where('id', '!=', $this->owner->id);
    }
    $sum = $owners->sum('percent_ownership');
    return  ($value + $sum) <= 100;
  }
}
