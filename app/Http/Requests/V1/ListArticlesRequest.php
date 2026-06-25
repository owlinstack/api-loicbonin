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

    /**
     * Get the validated data from the request.
     *
     * @param  array<int, string>|string|null  $key
     * @param  mixed  $default
     * @return array{category?: string|null, tag?: string|null, page?: int|null, pageSize?: int|null}
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (! \is_array($validated)) {
            return [];
        }

        if (isset($validated['page'])) {
            $validated['page'] = (int) $validated['page'];
        }

        if (isset($validated['pageSize'])) {
            $validated['pageSize'] = (int) $validated['pageSize'];
        }

        /** @var array{category?: string|null, tag?: string|null, page?: int|null, pageSize?: int|null} $validated */
        return $validated;
    }
}
