<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Clé primaire utilisée dans cette table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Attributs pouvant être remplis en masse (via formulaire, factory, etc.).
     *
     * @var array
     */
    protected $fillable = [
        'name',              // Nom de famille
        'firstname',         // Prénom
        'email',             // Adresse email
        'email_verified_at', // Timestamp de vérification email
        'phone',
        'phone_verified_at',
        'password',          // Mot de passe (crypté)
        'remember_token',    // Token de session
        'pseudo',            // Pseudonyme
        'id_role',           // Rôle de l'utilisateur (1=admin, 2=membre, etc.)
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_attempts',
        'email_notifications',
        'profile_visibility',
        'equipped_reward_id',
        'chat_banned_until',
        'chat_banned_permanently',
        'chat_ban_reason',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Attributs masqués lors de la sérialisation (ex : JSON ou API).
     *
     * @var array
     */
    protected $hidden = [
        'password',         // Masque le mot de passe
        'remember_token',   // Masque le token
        'two_factor_code',
    ];

    /**
     * Transformation automatique de certains champs (cast).
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // transforme en instance DateTime
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',            // hash automatiquement si modifié
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'email_notifications' => 'boolean',
            'last_login_at' => 'datetime',
            'chat_banned_until' => 'datetime',
            'chat_banned_permanently' => 'boolean',
        ];
    }

    /**
     * Active les colonnes `created_at` et `updated_at` automatiquement.
     *
     * @var bool
     */
    public $timestamps = true;

    public function getRoleLabelAttribute(): string
    {
        return match ((int) $this->id_role) {
            1 => 'Super administrateur',
            3 => 'Gestionnaire',
            default => 'Membre',
        };
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->pseudo && ! str_contains($this->pseudo, '@')) {
            return $this->pseudo;
        }

        return ($this->firstname ?: 'Membre').'#'.$this->id;
    }

    public function equippedReward()
    {
        return $this->belongsTo(Reward::class, 'equipped_reward_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
