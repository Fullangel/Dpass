<?php
namespace App\Models;

use App\Models\Employee;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Shipu\Watchable\Traits\HasModelEvents;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, HasMedia
{
    use Notifiable, InteractsWithMedia, HasModelEvents, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'first_name', 'last_name', 'email', 'username', 'password', 'phone', 'address', 'roles', 'device_token','web_token', 'status',
    ];
 
    protected $guard_name = 'web';

   

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'options' => 'array',
    ];

    protected $appends = ['myrole'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    public function getImagesAttribute()
    {
        if (!empty($this->getFirstMediaUrl('user'))) {
            return asset($this->getFirstMediaUrl('user'));
        }
        return asset('assets/img/default/user.png');
    }



    public function routeNotificationForTwilio()
    {
        return $this->phone;
    }

    /**
     * Route notifications for the FCM channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForFcm($notification)
    {
        return $this->device_token;
    }

    public function getMyroleAttribute()
    {
        return $this->roles->pluck('id', 'id')->first();
    }

    public function getrole()
    {
        return $this->hasOne(Role::class, 'id', 'myrole');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function getMyStatusAttribute()
    {
        return trans('statuses.' . $this->status);
    }
}
