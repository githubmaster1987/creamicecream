<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model {

    protected $table = 'locations';

    protected $guarded = ['id'];

    protected $fillable = [
    	'name'
        ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    

}
