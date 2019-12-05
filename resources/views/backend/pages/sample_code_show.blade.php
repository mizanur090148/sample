@extends('backend.layout')
@section('content')
  <div class="main-content-container container-fluid px-4">
    <div class="page-header row no-gutters py-4">
      <div class="col-12 col-sm-4 text-center text-sm-left mb-0">      
        <h3 class="page-title">Sample Code</h3>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="card card-small mb-4">
          <div class="card-header border-bottom">
            <h6 class="m-0">Sample Code List</h6>            
          </div>
          @include('backend.partials.response_message')

          <div class="card-body p-0 pb-3 text-center">
            <table class="table table-sm">             
              <tbody>
                @foreach($sample_codes->chunk(3) as $sample_code)
                	<tr style="height: 50px !important;">
	                	@foreach($sample_code as $sample)
		                    <td>
		                    	<br/>
		                    	<?php echo DNS1D::getBarcodeSVG(str_pad($sample->id, 10, '0', STR_PAD_LEFT),  "C128A", 1.2, 35); ?><br/>
		                    </td>
		                @endforeach
	                </tr>
                @endforeach
              </tbody>             
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection