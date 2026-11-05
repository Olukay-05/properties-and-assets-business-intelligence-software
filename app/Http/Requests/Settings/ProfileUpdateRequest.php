<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * The email rule uses `prohibited` when the address differs from the
     * current one: changing an address redirects the password-reset flow, and
     * the database withholds UPDATE on `users.email` for exactly that reason.
     * Rejecting it in validation gives a clear message instead of a 500 from
     * the privilege boundary.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        $rules['email'] = [$this->user()->email === $this->input('email') ? 'required' : 'prohibited'];

        return $rules;
    }
}
