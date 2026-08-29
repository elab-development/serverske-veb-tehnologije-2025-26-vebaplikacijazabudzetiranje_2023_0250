<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * $fillable = lista kolona koje SME da se popuni preko User::create([...])
     * Ovo je Laravel sigurnosna mera protiv "mass assignment" napada -
     * bez ovoga, neko bi teoretski mogao da ubaci proizvoljne kolone kroz formu
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // NOVO - dodato da bismo mogli da postavimo ulogu prilikom registracije
    ];

    /**
     * $hidden = kolone koje se NIKAD ne vraćaju u JSON odgovoru
     * (npr. kad vratiš User objekat kao API odgovor, password se automatski izbacuje)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Laravel AUTOMATSKI hash-uje ovo polje kad se postavi!
        ];
    }
        public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function expensesPaid()
    {
        return $this->hasMany(Expense::class, 'paid_by');
    }
}