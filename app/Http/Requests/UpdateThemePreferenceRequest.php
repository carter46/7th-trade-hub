<?php

namespace App\Http\Requests;

use App\Services\ThemeManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateThemePreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ThemeManager $themes */
        $themes = app(ThemeManager::class);

        return [
            'theme' => ['required', 'string', Rule::in($themes->availablePreferences())],
            'system_theme' => ['sometimes', 'nullable', 'string', Rule::in($themes->availableResolvedThemes())],
        ];
    }
}
