<?php namespace App\Http\Controllers;

use Hash;
use App\User;
use App\Userlocation;
use Auth, Input, DB;
use App\Services\Paginator;
use App\Services\SpaceUsage;

class UsersController extends Controller {

    /**
     * Eloquent User model instance.
     *
     * @var User
     */
    private $model;

    /**
     * Paginator Instance.
     *
     * @var Paginator
     */
    private $paginator;

	public function __construct(SpaceUsage $usage, User $user, Paginator $paginator)
    {
        if (IS_DEMO) {
            $this->middleware('admin', ['only' => ['destroy', 'destroyAll']]);
        } else {
            $this->middleware('admin', ['only' => ['index', 'destroy', 'destroyAll']]);
        }

        $this->middleware('loggedIn');

        $this->spaceUsage = $usage;
        $this->model = $user;
        $this->paginator = $paginator;
    }

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
	public function index()
	{
        if(Auth::user()->role == 2){
            return $this->paginator->paginate($this->model->with('roles')->with(array("locations" => function($q){
                $q->select('*', DB::raw('"true" as `checked`'));
            }))->where("role", 3), Input::all(), 'users');
        }else{
            return $this->paginator->paginate($this->model->with('roles')->with(array("locations" => function($q){
                $q->select('*', DB::raw('"true" as `checked`'));
            })), Input::all(), 'users');
        }
       
	}

	/**
	 * Update given users information.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$currentUser = Auth::user();
        $input       = Input::all();
        $user        = User::findOrFail($id);

        if ($currentUser->isAdmin || $currentUser->id == $user->id) {

            //has the password if we get one passed in input
            if (isset($input['password'])) {
                $input['password'] = Hash::make($input['password']);
            }

            $user->fill($input)->save();

            Userlocation::where('user_id', $user->id)->delete();

            foreach ($input['locations'] as $location) {
                $location_id = $location['id'];
                Userlocation::create(['user_id' => $user->id, 'location_id' => $location_id]);
            }

            return response($user, 200);
        }

        return response(trans('app.noPermissions'), 403);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		return User::destroy($id);
	}

    /**
     * Delete all users given in input.
     *
     * return Response
     */
    public function destroyAll()
    {
        if ( ! Input::has('users')) return;

        $ids = [];

        foreach(Input::get('users') as $k => $user) {
            $ids[] = $user['id'];
        }

        if ($deleted = User::destroy($ids)) {
            return response(trans('app.deleted', ['number' => $deleted]));
        }
    }

    public function permission()
    {
        $data = [];
        $data['permission'] = User::with('locations')->where('role', 2)->get();
        return $data;
    }

    /**
     * Get disk space user is currently using.
     *
     * return int
     */
    public function getSpaceUsage()
    {
        return $this->spaceUsage->info();
    }
}
