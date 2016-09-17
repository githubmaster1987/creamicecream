<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model {

    protected $table = 'chat_message';

    protected $guarded = ['id'];

    protected $appends = array('username');

	public function sender()
	{
	    return $this->belongsTo('App\User', 'message_sender', 'id');
	}

	public function getUsernameAttribute(){
		$user = User::find($this->attributes['message_sender']);
        return $user->first_name." ".$user->last_name;
	}


}
