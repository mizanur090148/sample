<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampleCode;
use App\Models\Buyer;
use App\Models\Color;
use App\Models\Size;

class SampleCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sample_codes = SampleCode::where('status', 0)
            ->latest()
            ->paginate();

        return view('backend.pages.sample_codes', [
            'sample_codes' => $sample_codes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sample_code = null;
        $buyers = Buyer::pluck('name', 'id')->prepend('Select a buyer', '')->all();
        $colors = Color::pluck('name', 'id')->prepend('Select a color', '')->all();
        $sizes = Size::pluck('name', 'id')->prepend('Select a size', '')->all();

        return view('backend.forms.sample_code', [
            'buyers' => $buyers,
            'colors' => $colors,
            'sizes' => $sizes,
            'sample_code' => $sample_code
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $sample_code = SampleCode::create($request->only('buyer_id', 'color_id'));

        foreach ($request->quantity as $key => $sizeId) {
            $input = [
                'buyer_id' => $request->buyer_id[$key],
                'color_id' => $request->color_id[$key],
                'size_id' => $request->size_id[$key],
                //'' => $request->size_id[$key],
            ];
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
