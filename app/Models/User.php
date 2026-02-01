<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role constants - Sesuai dengan Q-Link
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_GURU = 'guru';    // Q-Link menggunakan 'guru', bukan 'teacher'
    const ROLE_USER = 'user';    // Siswa (tidak boleh akses Q-Game)

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        // SSO fields from Q-Link
        'nickname',
        'student_id',
        'grade',
        'gender',
        'is_active',
        'google_id',
        'avatar',
        'subscription_status',
        'subscription_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // --- HELPER METHODS UNTUK ROLE ---

    /**
     * Check if user has specific role
     * Support mapping 'teacher' -> 'guru' untuk backward compatibility
     */
    public function hasRole($role): bool
    {
        // Jika parameter array/collection
        if (is_array($role)) {
            // Map old role names
            $role = array_map(fn($r) => $this->mapRole($r), $role);
            return in_array($this->role, $role);
        }
        
        return $this->role === $this->mapRole($role);
    }

    /**
     * Map old role names to Q-Link standard
     */
    private function mapRole(string $role): string
    {
        // Backward compatibility: 'teacher' -> 'guru'
        if ($role === 'teacher') return 'guru';
        if ($role === 'student') return 'user';
        return $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is guru (teacher)
     */
    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    /**
     * Alias for isGuru() - backward compatibility
     */
    public function isTeacher(): bool
    {
        return $this->isGuru();
    }

    /**
     * Check if user is student (user role)
     */
    public function isStudent(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user can access Q-Game admin panel
     * Only admin and guru can access
     */
    public function canAccessQGame(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_GURU]);
    }

    /**
     * Get the dashboard route for this user
     */
    public function getDashboardRoute(): string
    {
        if ($this->canAccessQGame()) {
            return 'admin.dashboard';
        }
        
        // Siswa tidak punya akses ke Q-Game, redirect ke game utama
        return 'game';
    }

    /**
     * Get the dashboard URL for this user
     */
    public function getDashboardUrl(): string
    {
        try {
            return route($this->getDashboardRoute());
        } catch (\Exception $e) {
            return url('/game');
        }
    }
}
