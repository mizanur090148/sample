<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SampleCode;
use App\Models\Buyer;
use App\Models\Color;
use App\Models\Size;
use Auth, DB, Session;

class SampleCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sample_code_list = SampleCode::with('sample_codes')
            ->whereNull('sample_code_id')
            ->latest()
            ->paginate();

        return view('backend.pages.sample_codes', [
            'sample_code_list' => $sample_code_list
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
        try {
            DB::beginTransaction();
            $input = [
                'challan_no' => userId().time()
            ];
            $sample_code = SampleCode::create($input);

            $child_input = [];
            foreach ($request->quantity as $key => $quantity) {
                for ($i = 0; $i < $quantity; $i++) {
                    $input = [
                        'buyer_id' => $request->buyer_id[$key],
                        'color_id' => $request->color_id[$key],
                        'size_id' => $request->size_id[$key],                    
                        'created_by' => Auth::user()->id,
                        'factory_id' => Auth::user()->factory_id,
                    ];
                    $child_input[] = $input;
                }
            }

            if (count($child_input)) {
                $sample_code->sample_codes()->createMany($child_input);
                DB::commit();                
            }
        } catch (Exception $e) {
            DB::rollback();
            Session::flash('error', $e->getMessage());
            return redirect()->back();
        }
        Session::flash('success', 'Successfully Created');
        return redirect('sample-codes');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sample_codes = SampleCode::where('sample_code_id', $id)->get();

        return view('backend.pages.sample_code_show', [
            'sample_codes' => $sample_codes
        ]);
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
