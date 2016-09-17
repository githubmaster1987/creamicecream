<?php namespace App\Http\Controllers;

use DB;
use App;
use App\File;
use Input, Auth;
use App\Http\Requests;
use App\Services\Paginator;
use App\Services\FileSaver;
use App\Services\Photo\Deleter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FilesController extends Controller {

    /**
     * Paginator Instance.
     *
     * @var Paginator
     */
    private $paginator;

    public function __construct(FileSaver $saver, Deleter $deleter, Paginator $paginator)
    {
        $this->middleware('loggedIn', ['except' => 'store']);
        $this->middleware('spaceUsage', ['only' => 'store']);

        $this->saver = $saver;
        $this->deleter = $deleter;
        $this->user = Auth::user();
        $this->paginator = $paginator;
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

    public function index()
    {
        if (Input::get('all') === 'true' && ($this->user->isAdmin || IS_DEMO)) {

            $res = File::with('User')
                ->join('folders', 'files.folder_id', '=', 'folders.id')
                ->select('files.*', 'folders.name as folder_name')
                ->wherenull('files.deleted_at');
            
            if(Input::get('folder_id'))
                $res = $res->where('files.folder_id', Input::get('folder_id'));

                $res = $res->orderBy('files.updated_at', 'desc');
           
            return $this->paginator->paginate($res, Input::all(), 'files');
        } else {
            return Auth::user()->files()->get(['name', 'description', 'file_name', 'folder_id', 'id', 'user_id']);
        }
    }

	/**
	 * Find photo with given id.
	 *
	 * @param  int  $id
	 * @return File
	 */
	public function show($id)
	{
        return Auth::user()->files()->findOrFail($id);
	}

    /**
     * Store a new file.
     *
     * @return array|void
     */
    public function create_folder($name, $parent)
    {
        echo "Name". $name."<br/>";
        $res = Auth::user()->folders()->where('name', $name)->first();

        if ($res == null) {
            $path   = isset($parent['path']) ? $parent['path'].'/'.$name : 'root/'.$name;

            $res = Auth::user()->folders()->create([
                'name'      => $name,
                'path'      => $path,
                'share_id'  => Str::random(15),
                'folder_id' => $parent['name'] !== 'root' ? $parent['id'] : null,
                'user_id'   => Auth::user()->id
            ]);    
        }
        return $res->getAttributes();
    }

    public function save_file_into_folder($folder_id, $attach_id)
    {

        $file_name = Input::file('file')->getClientOriginalName();

        $res = Auth::user()->files()->where('name', $file_name);
        echo $file_name."<br/>";
        echo "RESULT : ";

        print_r($res->first());
        if($res->first() == null)
        {

            if (Input::file()) {
                $file = $this->saver->saveFiles(Input::file(), $folder_id, $attach_id);
                foreach ($file as $uploaded) {
                    if(isset($uploaded[0]))
                        DB::table('files_users')->insert([ 'user_id' => Auth::user()->id, 'file_id' => $uploaded[0]->id]);
                }
                
                return $file;
            }
        }
        else
        {
             return -1;
        }
    }

    public function store()
    {

        $file_path = Input::get('path');
        $folder_id = Input::get('folder');

        $path = pathinfo($file_path,PATHINFO_DIRNAME);

        $parent = Auth::user()->folders()->where('folders.id', $folder_id)->first()->getAttributes();
        
        /* Use tab and newline as tokenizing characters as well  */
        $tok = strtok($path, "/");

        if($tok !="") {
            $res = $this->create_folder($tok, $parent);
            $pos = strpos($res['path'], $path);
            echo "First:".$res['path']."+".$path."<br/>";
            if($pos != false)
                if($this->save_file_into_folder($res['id'], NULL) == -1)
                    return response()->json(trans('app.fileAlreadyExists'), 422);
        }
        else
        {
            echo "File Path:".$file_path;
            if($this->save_file_into_folder($folder_id, NULL) == -1)
                return response()->json(trans('app.fileAlreadyExists'), 422);
        }

        while ($tok !== false) {
            $tok = strtok("/");
            if($tok !="")
            {
                $res = $this->create_folder($tok, $res);
                $pos = strpos($res['path'], $path);
                echo "Second:".$res['path']."+".$path."<br/>";
                if($pos != false)
                    if($this->save_file_into_folder($res['id'], NULL) == -1)
                        return response()->json(trans('app.fileAlreadyExists'), 422);
            }
        }
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$file = Auth::user()->files()->findOrFail($id);

        $file->update(Input::all());

        return $file;
	}

    /**
     * Update files with specified ids.
     *
     * @return int
     */
    public function updateAll()
    {
        return Auth::user()->files()->whereIn('id', Input::get('ids'))->update(Input::get('data'));
    }

    /**
     * Return photos user has recently uploaded or modified.
     *
     * @return mixed
     */
    public function recent()
    {
        return Auth::user()->files()->orderBy('updated_at', 'desc')->limit(30)->get();
    }

    public function assign_file(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $file->users()->detach();

        $file->users()->attach($request->users);

        return $file;
    }
}
