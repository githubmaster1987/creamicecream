<?php namespace App\Http\Controllers;

use Hash;
use App\Role;
use Auth, Input;
use App\Http\Requests\RoleRequest;
use App\Services\Paginator;
use App\Services\SpaceUsage;

class RolesController extends Controller {

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

	public function __construct(SpaceUsage $usage, Role $role, Paginator $paginator)
    {
        if (IS_DEMO) {
            $this->middleware('admin', ['only' => ['destroy', 'destroyAll']]);
        } else {
            $this->middleware('admin', ['only' => ['index', 'destroy', 'destroyAll']]);
        }

        $this->middleware('loggedIn');

        $this->spaceUsage = $usage;
        $this->model = $role;
        $this->paginator = $paginator;
    }

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
	public function index()
	{
        return $this->paginator->paginate($this->model, Input::all(), 'roles');
	}


    /**
     * Store a new role.
     *
     * @return array|void
     */
    public function store(RoleRequest $request)
    {
        $input       = Input::all();
        $role        = new Role;
        $role->fill($input)->save();
        return response($role, 200);
    }

	/**
	 * Update given users information.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(RoleRequest $request, $id)
	{
        $input       = Input::all();

        $role        = Role::findOrFail($id);

        $role->fill($input)->save();

        return response($role, 200);

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
        if ( ! Input::has('roles')) return;
        
        $ids = [];

        foreach(Input::get('roles') as $k => $role) {
            $ids[] = $role['id'];
        }

        if ($deleted = Role::destroy($ids)) {
            return response(trans('app.deleted', ['number' => $deleted]));
        }
    }

    public function show(){
        $roles = Role::get()->toArray();
        return response($roles, 200);
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
