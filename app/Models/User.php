<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

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
        'role',
        'mapel_id',
        'kelase_id',
        'nomor_absen',
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
        ];
    }

    public function mapel()
    {
        return $this->belongsToMany(Mapel::class);
    }


    public function kelase()
    {
        return $this->belongsTo(Kelase::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Panel /app : Khusus Siswa
        if ($panel->getId() === 'app') {
            return $this->role === 'siswa';
        }

        // Panel /ujian-app : Khusus Admin, Guru, dan Pengawas
        if ($panel->getId() === 'ujian-app') {
            return in_array($this->role, ['super_admin', 'guru', 'pengawas']);
        }

        return false;
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => $value ? Str::title(mb_strtolower(trim($value))) : null,
        );
    }
}
