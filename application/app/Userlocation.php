<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Userlocation extends Model {

    protected $table = 'users_locations';

    protected $guarded = ['id'];
    

}
