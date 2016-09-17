<?php namespace App;

use Laravel\Cashier\Billable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Cashier\Contracts\Billable as BillableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, BillableContract {

	use Authenticatable, CanResetPassword, Billable;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password', 'username', 'first_name', 'last_name', 'gender', 'role'];

    protected $dates = ['trial_ends_at', 'subscription_ends_at'];

    protected $appends = array('isAdmin', 'isSubscribed');

    public function getNameOrEmail() {
        $name = '';

        if ($this->first_name) {
            $name = $this->first_name;
        }

        if ($name && $this->last_name) {
            $name .= ' ' . $this->last_name;
        }

        if ($name) {
            return $name;
        } else {
            return $this->email;
        }
    }

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['remember_token'];

    public function getPermissionsAttribute()
    {
        return isset($this->attributes['permissions']) ? json_decode($this->attributes['permissions']) : [];
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }

    public function getIsSubscribedAttribute($value)
    {
        return $this->subscribed();
    }
    
    public function getIsAdminAttribute()
    {
        return 1;
    }

    public function roles()
    {
        return $this->belongsTo('App\Role', 'role', 'id');
    }

    public function folders()
    {
        //return $this->hasMany('App\Folder');
        return $this->belongsToMany('App\Folder', 'folders_users', 'user_id', 'folder_id');

    }

    public function files()
    {
        return $this->hasMany('App\File');
    }

    public function activity()
    {
        return $this->hasMany('App\Activity');
    }

    public function oauth()
    {
        return $this->hasMany('App\Social');
    }

    public function locations()
    {
        return $this->belongsToMany('App\Location', 'users_locations', 'user_id', 'location_id');
    }

}
