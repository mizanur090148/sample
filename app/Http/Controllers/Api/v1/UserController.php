<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('role:id,name')->orderBy('id', 'desc');
        /*if (request('query')) {
            $users = $users->where('name', 'LIKE', '%'. request('query') .'%');
        }*/
        $users = $users->orderBy('id', 'desc')->paginate();       

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [          
            'name' => 'required|max:50',
            'email' => 'required|max:30|unique:users',
            'mobile_no' => 'required|max:30',
            'role_id' => 'required',
            'factory_id' => 'required',
            'status' => 'required',
            'password' => 'required|min:6|max:20',
            'password' => 'required|min:6|same:confirm_password',
        ]);

        return new UserResource(User::create($request->all()));
    }    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:30|unique:users,name,'.$id           
        ]);

        $user = User::findOrFail($id);
        $user->name = $request->name;        
        $user->save();

        return new UserResource($user);
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return new UserResource($user);
    }
}
