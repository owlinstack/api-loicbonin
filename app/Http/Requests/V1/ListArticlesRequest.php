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

    protected function prepareForValidation(): void
    {
        if ($this->has('is_pinned')) {
            $this->merge([
                'is_pinned' => filter_var($this->query('is_pinned'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
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
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array<int, string>|string|null  $key
     * @param  mixed  $default
     * @return array{category?: string|null, tag?: string|null, page?: int|null, pageSize?: int|null, is_pinned?: bool|null}
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (! \is_array($validated)) {
            return [];
        }

        if (isset($validated['page'])) {
            $validated['page'] = \is_scalar($validated['page']) ? (int) $validated['page'] : null;
        }
        if (isset($validated['pageSize'])) {
            $validated['pageSize'] = \is_scalar($validated['pageSize']) ? (int) $validated['pageSize'] : null;
        }
        if (isset($validated['is_pinned'])) {
            $validated['is_pinned'] = filter_var($validated['is_pinned'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        /** @var array{category?: string|null, tag?: string|null, page?: int|null, pageSize?: int|null, is_pinned?: bool|null} $validated */
        return $validated;
    }
}
