<?php namespace App\Http\Controllers;

use App;
use App\File;
use Storage;
use Auth, Input;
use App\Http\Requests;
use App\Services\Photo\Deleter;
use Illuminate\Support\Facades\DB;

class TrashController extends Controller {

    public function __construct(Deleter $deleter)
    {
        $this->middleware('loggedIn');

        $this->deleter = $deleter;
    }

    /**
     * Return currently logged in users trashed photos.
     *
     * @return Collection
     */
    public function getUserTrash()
    {
        $folders = Auth::user()->folders()->onlyTrashed()->get();
        $files   = Auth::user()->files()->onlyTrashed()->get();

        return $files->merge($folders);
    }

    /**
     * Move files or folders to trash.
     *
     * @return int
     */
    public function put() {
        
        $items   = Input::get('items');
        $folders = [];
        $files   = [];
        
        foreach($items as $item) {
            if ($item['type'] === 'folder') {
                $folders[] = $item['id'];
            } else {
                $files[] = $item['id'];
            }
        }
        
        if (count($folders)) {
            $res = Auth::user()->folders()->whereIn('folders.id', $folders)->get();
            foreach($res as $c) {
                
                $path = $c->getAttributes()['name'];
                $path_sub = "%".$path."%";
                $user_id = Auth::user()->id;
                                 
                $res_sub = DB::table('folders')
                     ->where('path', 'LIKE', $path_sub)
                     ->where('user_id', '=', $user_id)
                     ->select('folders.*')->get();

                foreach($res_sub as $sub) {
                    $sub_id = $sub->id;

                    $res1 = Auth::user()->files()->where('files.folder_id', $sub_id)->get();

                    foreach($res1 as $c1) {
                        $c1->delete();
                    }   
                }

                DB::table('folders')
                     ->where('path', 'LIKE', $path_sub)
                     ->where('user_id', '=', $user_id)
                     ->select('folders.*')->delete();

                $c->delete();
            }   
        }

        if (count($files)) {

            $res = Auth::user()->files()->whereIn('files.id', $files)->get();
            foreach($res as $c) {
                $c->delete();
            }   
            
            // foreach ($files as $id) {
            //     $file = File::find($id);
            //     Storage::delete(storage_path() . '/uploads/'.Auth::user()->id.'/'.$file->id.'/'.$file->file_name);
            // }
        }

        return count($folders)+count($files);

    }

    /**
     * Restore photo with given id from trash.
     *
     * @return int
     */
    public function restore() {
        $items   = Input::get('items');
        $folders = [];
        $files   = [];

        foreach($items as $item) {
            if ($item['type'] === 'folder') {
                $folders[] = $item['id'];
            } else {
                $files[] = $item['id'];
            }
        }

        if (count($folders)) {
            Auth::user()->folders()->onlyTrashed()->whereIn('id', $folders)->restore();
        }

        if (count($files)) {
            Auth::user()->files()->onlyTrashed()->whereIn('id', $files)->restore();
        }

        return count($folders)+count($files);
    }
}
