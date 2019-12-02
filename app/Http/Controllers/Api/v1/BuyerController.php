<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuyerCollection;
use App\Http\Resources\BuyerResource;
use Illuminate\Http\Request;
use App\Models\Buyer;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sizes = Buyer::orderBy('id', 'desc');
        if (request('query')) {
            $sizes = $sizes->where('name', 'LIKE', '%'. request('query') .'%');
        }
        $sizes = $sizes->orderBy('id', 'desc')->paginate();

        return new BuyerCollection($sizes);
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

        return new BuyerResource(Buyer::create($request->all()));
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
            'name' => 'required|max:30|unique:buyers,name,'.$id           
        ]);

        $buyer = Buyer::findOrFail($id);
        $buyer->name = $request->name;        
        $buyer->save();

        return new BuyerResource($buyer);
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $buyer = Buyer::findOrFail($id);
        $buyer->delete();

        return new BuyerResource($buyer);
    }
}
