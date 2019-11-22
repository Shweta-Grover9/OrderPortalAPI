<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class OrderTakenRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (strtoupper($value) == config('config.taken_order_status')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.status_only_taken');
    }
}
