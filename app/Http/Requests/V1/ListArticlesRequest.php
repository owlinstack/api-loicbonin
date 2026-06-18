<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

final class ListArticlesRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'pageSize' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
