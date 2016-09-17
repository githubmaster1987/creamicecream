<?php namespace App\Services;

use DB;
use App\User;
use App\Userlocation;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Registrar implements RegistrarContract {

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(array $data)
	{
		return Validator::make($data, [
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:4',
			'role' => 'required',
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data)
	{
		$user = User::create([
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
			'username' => isset($data['username']) ? $data['username'] : null,
			'first_name' => isset($data['first_name']) ? $data['first_name'] : null,
			'last_name' => isset($data['last_name']) ? $data['last_name'] : null,
			'role' => isset($data['role']) ? $data['role'] : null,
		]);

		foreach ($data['locations'] as $location) {
            $location_id = $location['id'];
            Userlocation::create(['user_id' => $user->id, 'location_id' => $location_id]);
        }
		
		return $user;
	}

}
