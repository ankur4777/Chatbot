<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'domain' => ['required', 'string'],
        'visitor_id' => ['nullable', 'exists:visitors,id'],
        'conversation_id' => ['nullable', 'exists:chat_conversations,id'],
        'message' => ['required', 'string', 'max:5000'],
        

        'name' => ['nullable', 'string', 'max:255'],
'email' => ['nullable', 'email', 'max:255'],
'phone' => ['nullable', 'string', 'max:20'],
'notes' => ['nullable', 'string'],
'session_id' => ['required', 'string'],
    ];
}


}
