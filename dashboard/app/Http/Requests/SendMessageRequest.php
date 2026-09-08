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
        'widget_key' => ['required', 'string'],
        'domain' => ['required', 'string'],
        'visitor_id' => ['nullable', 'exists:visitors,id'],
        'conversation_id' => ['nullable', 'exists:chat_conversations,id'],
        'message' => ['nullable', 'string', 'max:5000'],
        'attachment' => [
            'nullable',
            'file',
            'max:10240',
            'mimes:jpg,jpeg,png,webp,pdf,mp4,webm,mov,ogg',
            'mimetypes:image/jpeg,image/png,image/webp,application/pdf,video/mp4,video/webm,video/quicktime,video/ogg',
        ],
        

        'name' => ['nullable', 'string', 'max:255'],
'email' => ['nullable', 'email', 'max:255'],
'phone' => ['nullable', 'string', 'max:30'],
'notes' => ['nullable', 'string'],
'session_id' => ['required', 'string'],
    ];
}

public function messages(): array
{
    return [
        'attachment.file' => 'Upload an image, PDF, or video up to 10 MB.',
        'attachment.max' => 'Upload an image, PDF, or video up to 10 MB.',
        'attachment.mimes' => 'Upload an image, PDF, or video up to 10 MB.',
        'attachment.mimetypes' => 'Upload an image, PDF, or video up to 10 MB.',
    ];
}

}
