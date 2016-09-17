<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class Chat extends Model {

    protected $table = 'chat';

    protected $guarded = ['id'];

    // protected $fillable = [
    // 	'subject',
    // 	'message',
    //     'user_id'
    // 	];

    
	public function user()
	{
	    return $this->belongsTo('App\User', 'originator', 'id');
	}

	public function messages()
	{
	    return $this->hasMany('App\ChatMessage', 'chat_id', 'id');
	}

	public function chat_users()
	{
		return $this->hasMany('App\Chatusers', 'chat_id', 'id');
	}
	
    //protected $appends = array('admin', 'users', 'roles', 'folders', 'files', 'settings', 'upload');

}
