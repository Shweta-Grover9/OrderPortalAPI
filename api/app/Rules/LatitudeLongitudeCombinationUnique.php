<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LatitudeLongitudeCombinationUnique implements Rule
{
    private $request;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
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
        $origin = $this->request->get('origin');
        $destination = $this->request->get('destination');
        if (!empty($origin) && !empty($destination) && (count($origin) == count($destination))) {
            if (!empty(array_diff($origin, $destination))) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.origin_destination_unique');
    }
}
