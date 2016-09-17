<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model {

    protected $table = 'roles';

    protected $guarded = ['id'];

    protected $fillable = [
    	'name', 
    	'permissions'
    	];

    protected $appends = array('admin', 'users', 'roles', 'folders', 'files', 'settings', 'upload');

    public function getPermissionsAttribute()
    {
        return isset($this->attributes['permissions']) ? json_decode($this->attributes['permissions']) : [];
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }

    public function getAdminAttribute()
    {
        return isset($this->permissions->admin) && $this->permissions->admin == true;
    }

    public function getUsersAttribute()
    {
        return isset($this->permissions->users) &&  $this->permissions->users == true;
    }

    public function getRolesAttribute()
    {
        return isset($this->permissions->roles) &&  $this->permissions->roles == true;
    }

    public function getFoldersAttribute()
    {
        return isset($this->permissions->folders) &&  $this->permissions->folders == true;
    }

    public function getFilesAttribute()
    {
        return isset($this->permissions->files) &&  $this->permissions->files == true;
    }

    public function getSettingsAttribute()
    {
        return isset($this->permissions->settings) &&  $this->permissions->settings == true;
    }
  
    public function getUploadAttribute()
    {
        return isset($this->permissions->upload) &&  $this->permissions->upload == true;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    

}
