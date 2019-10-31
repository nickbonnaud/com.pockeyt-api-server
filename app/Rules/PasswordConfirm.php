<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class PasswordConfirm implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return Hash::check($value, (auth($this->type)->user())->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Incorrect old password.';
    }
}
