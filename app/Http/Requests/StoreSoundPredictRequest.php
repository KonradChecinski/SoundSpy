<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreSoundPredictRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
//        https://github.com/minuteoflaravel/laravel-audio-video-validator
        return [
//            "sound" => 'required|audio|duration_min:29|duration_max:31|codec:mp3,pcm_s16le', //pcm_s16le==wav
        ];
    }
}
