<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function lists(): HasMany
    {
        return $this->hasMany(TaskList::class);
    }

    /**
     * Generate, store, and return a fresh 6-digit OTP valid for 10 minutes.
     */
    public function issueOtp(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->forceFill([
            'otp_code' => $code,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ])->save();

        return $code;
    }

    public function otpIsValid(string $code): bool
    {
        return $this->otp_code !== null
            && $this->otp_expires_at !== null
            && Carbon::now()->lessThanOrEqualTo($this->otp_expires_at)
            && hash_equals($this->otp_code, trim($code));
    }

    /**
     * Mark the email verified, clear the OTP, and (first user only) claim any
     * legacy lists that were imported without an owner.
     */
    public function markVerified(): void
    {
        $this->forceFill([
            'email_verified_at' => $this->email_verified_at ?? Carbon::now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ])->save();

        // The first account to verify inherits the imported, ownerless lists.
        if (! TaskList::withoutGlobalScopes()->whereNotNull('user_id')->exists()) {
            TaskList::withoutGlobalScopes()->whereNull('user_id')->update(['user_id' => $this->id]);
        }
    }
}
