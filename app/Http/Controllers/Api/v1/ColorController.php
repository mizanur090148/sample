<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorCollection;
use App\Http\Resources\ColorResource;
use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $colors = Color::orderBy('id', 'desc');
        if (request('query')) {
            $colors = $colors->where('name', 'LIKE', '%'. request('query') .'%');
        }
        $colors = $colors->orderBy('id', 'desc')->paginate();

        return new ColorCollection($colors);
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
            'name' => 'required|max:60|unique:colors'
        ]);

        return new ColorResource(Color::create($request->all()));
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
            'name' => 'required|max:60|unique:colors,name,'.$id           
        ]);

        $color = Color::findOrFail($id);
        $color->name = $request->name;        
        $color->save();

        return new ColorResource($color);
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return new ColorResource($color);
    }
}
