<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Dotenv\Store\File\Paths;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\Inline\Element\Strong;
use Illuminate\Http\UploadedFile;
use App\Models\Category;

use Mail;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role: 1'],['except' => ['show','edit','update']]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //hiển thị danh sách user
        $users = User::all()->sortByDesc('created_at');
        return view('users.showUser',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('users.createUser');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //chi tiết user
        if(auth()->user()->role->role == 1){
            return view("users.showEachUser", compact('user'));
        }


        if(auth()->user()->id == $user->id)//0 là editor 1 admin
        {

            return view("users.showEachUser", compact('user'));

        }


        return view("home");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        if(auth()->user()->role->role == 1){
            return view("users.editUser", compact('user'));
        }


        if(auth()->user()->id == $user->id)//0 là editor 1 admin
        {

            return view("users.editUser", compact('user'));

        }


        return view("home");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {   
        $user->update([
            $user->name = $request->name,
            $user->email = $request->email,
        ]);

        if($request->full_name||$request->address||$request->avatar||$request->birthday){
                $user->profile->update([
                    'full_name' => $request->full_name,
                    'address' => $request->address,
                    'birthday' => $request->birthday,
                    ]);
                $this->uploadAvatar($request,$user->profile);
        }

        return redirect(route('users.show',compact('user')))->with('message','successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if($user->profile !== null){
            $user->profile->delete();
        }
        foreach($user->carts as $t){
            $t->tags()->detach();
            $t->delete();
        }

        $user->delete();

        return redirect()->back()->with('message','Xóa user thành công');
    }


    public function uploadAvatar($request,$profile){
        
        if($request->hasFile('avatar')){
            $filename = $request->avatar->getClientOriginalName();
            $this->deleteOldImage($profile);
            $request->avatar->storeAs('images',$filename,'public');
            $profile->update(['avatar' => $filename]);
            
        }

        
    }

    protected function deleteOldImage($profile){

        if($profile->avatar !== null){

            Storage::delete('public/images/'.$profile->avatar);

        }
    }


}
