<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordHistory;

class NotInPasswordHistory implements ValidationRule
{

    protected $user;
    protected $maxHistory;

    public function __construct($user, $maxHistory = 5)
    {
        $this->user = $user;
        $this->maxHistory = $maxHistory;
    }

    public function passes($attribute, $value)
    {
        // Skip check jika user baru (belum punya password history)
        if (!$this->user->exists) {
            return true;
        }

        // Ambil hash password lama dari database (bukan session)
        $previousPasswords = PasswordHistory::where('customer_id', $this->user->id)
            ->latest()
            ->take($this->maxHistory)
            ->pluck('password');

        foreach ($previousPasswords as $oldHash) {
            if (Hash::check($value, $oldHash)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return __('Password tidak dapat digunakan karena sudah pernah dipakai sebelumnya.');
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
    }
}
