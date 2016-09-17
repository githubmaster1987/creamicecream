<?php namespace App\Http\Controllers;

use DB;
use Hash;
use App\Chat;
use App\ChatMessage;
use App\User;
use Auth, Input;
use App\Http\Requests\ChatRequest;
use App\Services\Paginator;
use App\Services\SpaceUsage;

class ChatController extends Controller {

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

	public function __construct(SpaceUsage $usage, Chat $chat, Paginator $paginator)
    {
        if (IS_DEMO) {
            $this->middleware('admin', ['only' => ['destroy', 'destroyAll']]);
        } else {
            $this->middleware('admin', ['only' => ['index', 'destroy', 'destroyAll']]);
        }

        $this->middleware('loggedIn');

        $this->spaceUsage = $usage;
        $this->model = $chat;
        $this->paginator = $paginator;
    }

    /**
     * Return a collection of all registered users.
     *
     * @return Collection
     */
    public function index()
    {
        return $this->paginator->paginate($this->model->with('user', 'messages')->whereHas('chat_users', function($q){
                $q->where("user_id", Auth::id());
        }), Input::all(), 'chat');
    }


    /**
     * Store a new role.
     *
     * @return array|void
     */
    public function store(ChatRequest $request)
    {
        $chat = new Chat;
        $chat->subject = $request->subject;
        $chat->originator = Auth::id();
        $chat->to_user = $request->to_user;
        $chat->save();

        DB::table('chat_user')->insert([
            ['chat_id' => $chat->id, 'user_id' => Auth::id()],
            ['chat_id' => $chat->id, 'user_id' => $request->to_user]
        ]);

        $chatmessage = new ChatMessage;
        $chatmessage->message = $request->message;
        $chatmessage->chat_id = $chat->id;
        $chatmessage->message_sender = Auth::id();
        $chatmessage->save();

        return response($chat, 200);
    }

	/**
	 * Update given users information.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(ChatRequest $request, $id)
	{
        $chat = Chat::findOrFail($id);

        $chatmessage = new ChatMessage;
        $chatmessage->chat_id = $id;
        $chatmessage->message = $request->message;
        $chatmessage->message_sender = Auth::id();
        $chatmessage->save();

        return response($chat, 200);

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		return Chat::destroy($id);
	}


    public function destroyAll()
    {
        if ( ! Input::has('chat')) return;
        
        $ids = [];

        foreach(Input::get('chat') as $k => $chat) {
            $ids[] = $chat['id'];
        }

        if ($deleted = Chat::destroy($ids)) {
            return response(trans('app.deleted', ['number' => $deleted]));
        }
    }
    /**
     * Delete all users given in input.
     *
     * return Response
     */

    public function lists(){
        $users = User::get()->toArray();
        return response($users, 200);
    }

    public function messages(){
        $messages = ChatMessage::get()->toArray();
        return response($messages, 200);
    }

    public function show(){
        $roles = Chat::get()->toArray();
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
