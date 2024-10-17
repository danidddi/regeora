<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthdate' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'Имя обязательно.',
            'last_name.required' => 'Фамилия обязательна.',
            'birthdate.required' => 'Дата рождения обязательна.',
            'birthdate.date' => 'Дата рождения должна быть корректной.',
        ];
    }
}
