<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SizeCollection;
use App\Http\Resources\SizeResource;
use Illuminate\Http\Request;
use App\Models\Size;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sizes = Size::orderBy('id', 'desc');
        if (request('query')) {
            $sizes = $sizes->where('name', 'LIKE', '%'. request('query') .'%');
        }
        $sizes = $sizes->orderBy('id', 'desc')->paginate();

        return new SizeCollection($sizes);
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
            'name' => 'required|max:30|unique:sizes'
        ]);

        return new SizeResource(Size::create($request->all()));
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
            'name' => 'required|max:30|unique:sizes,name,'.$id           
        ]);

        $size = Size::findOrFail($id);
        $size->name = $request->name;        
        $size->save();

        return new SizeResource($size);
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return new SizeResource($size);
    }
}
