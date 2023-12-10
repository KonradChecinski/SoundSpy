<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreHistorySoundPredictRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

//    protected function prepareForValidation()
//    {
//        $this->merge([
//            "history" => json_decode($this->history),
//        ]);
//    }
    public function validator($factory)
    {
        return $factory->make(
            $this->sanitize(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    public function sanitize()
    {
        $this->merge([
            'history' => json_decode($this->input('history'), true)
        ]);
        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "history" => "required|array",
            "history.*.id" => "required|numeric",
            "history.*.user_id" => "numeric|nullable",
            "history.*.result" => "required|json",
            "history.*.created_at" => "required|date",
            "history.*.updated_at" => "required|date|after_or_equal:created_at",
        ];
    }
}
