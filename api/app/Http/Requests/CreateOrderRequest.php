<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\LatitudeLongitudeCombinationUnique;

class CreateOrderRequest extends CustomAPIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'origin' => ['required', 'array','size:2', new LatitudeLongitudeCombinationUnique($this->request)],
            'destination' => 'required|array|size:2',
            'origin.0' => ['regex:'.config('config.latitude_regex'), 'string'],
            'destination.0' => ['regex:'.config('config.latitude_regex'), 'string'],
            'origin.1' => ['regex:'.config('config.longitude_regex'), 'string'],
            'destination.1' => ['regex:'.config('config.longitude_regex'), 'string']
        ];
    }
    
    public function messages()
    {
        return [
            'origin.required' => __('message.origin_required'),
            'destination.required' => __('message.destination_required'),
            'origin.size' => __('message.origin_size_incorrect'),
            'destination.size' => __('message.destination_size_incorrect'),
            'origin.*' => __('message.origin_format_invalid'),
            'destination.*' => __('message.destination_format_invalid'),
            'origin.0' => __('message.origin_format_invalid'),
            'destination.0' => __('message.destination_format_invalid')
        ];
    }
}
