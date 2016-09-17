<?php namespace App\Http\Controllers;

use Hash;
use App\Location;
use Auth, Input;
use App\Http\Requests\LocationRequest;
use App\Services\Paginator;
use App\Services\SpaceUsage;

class LocationsController extends Controller {

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

	public function __construct(SpaceUsage $usage, Location $location, Paginator $paginator)
    {

        $this->spaceUsage = $usage;
        $this->model = $location;
        $this->paginator = $paginator;
    }

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
	public function index()
	{
        return $this->paginator->paginate($this->model, Input::all(), 'locations');
	}

    public function store(LocationRequest $request)
    {
        $input    = Input::all();
        $location = new Location;
        $location->fill($input)->save();
        return response($location, 200);
    }

    public function show(){
        $location = Location::get()->toArray();
        return response($location, 200);
    }

    public function update(LocationRequest $request, $id)
    {
        $input    = Input::all();

        $location = Location::findOrFail($id);

        $location->fill($input)->save();

        return response($location, 200);

    }

    public function destroyAll()
    {
        if ( ! Input::has('locations')) return;
        
        $ids = [];

        foreach(Input::get('locations') as $k => $location) {
            $ids[] = $location['id'];
        }

        if ($deleted = Location::destroy($ids)) {
            return response(trans('app.deleted', ['number' => $deleted]));
        }
    }
 
}
