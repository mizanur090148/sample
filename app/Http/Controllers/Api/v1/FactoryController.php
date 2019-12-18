<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FactoryCollection;
use App\Http\Resources\FactoryResource;
use Illuminate\Http\Request;
use App\Models\Factory;

class FactoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $factories = Factory::orderBy('id', 'desc');
        /*if (request('query')) {
            $factories = $factories->where('name', 'LIKE', '%'. request('query') .'%');
        }*/
        $factories = $factories->orderBy('id', 'desc')->paginate();       

        return new FactoryCollection($factories);
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
            'email' => 'required|max:30|unique:factories',
            'mobile_no' => 'required|max:30',
            'role_id' => 'required',
            'status' => 'required',
            'password' => 'required|min:6|max:20',
            'password' => 'required|min:6|same:confirm_password',
        ]);

        return new FactoryResource(Factory::create($request->all()));
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
            'name' => 'required|max:30|unique:factories,name,'.$id           
        ]);

        $factory = Factory::findOrFail($id);
        $factory->name = $request->name;        
        $factory->save();

        return new FactoryResource($factory);
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $factory = Factory::findOrFail($id);
        $factory->delete();

        return new FactoryResource($factory);
    }

    public function factoriesDropdown()
    {
        $factories_dropdown = Factory::all();

        return new FactoryCollection($factories_dropdown);
    }
}
