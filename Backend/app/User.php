<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function allowsCommentsNotifications(User $actor)
    {
        $status = strtolower($this->settings->notification_comments);

        switch ($status) {
            case 'everyone': return true;
            case 'following': return $this->isFollowing($actor);
            default: return false;
        }
    }

    public function isFollowing(User $user): bool
    {
        return $this->followers->where('follower_id', $user->id)->count() > 0;
    }

    public function scopeOtherUsers($query)
    {
        return $query->where('id', '!=', auth()->user()->id);
    }

    public function following()
    {
        return $this->hasMany(UserFollow::class, 'follower_id');
    }

    public function followers()
    {
        return $this->hasMany(UserFollow::class, 'following_id');
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }
}
