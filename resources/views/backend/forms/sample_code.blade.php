@extends('backend.layout')
@section('styles')  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.css" />
@endsection
@section('content')
  <div class="main-content-container container-fluid px-4">
    <div class="page-header row no-gutters py-4">
     {{--  <div class="col-12 col-sm-4 text-center text-sm-left mb-0">      
        <h3 class="page-title">Sample Code</h3>
      </div> --}}
    </div>
    @include('backend.partials.response_message')
    <div class="row">
      <div class="col-lg-8 offset-sm-2">
        <div class="card card-small mb-4">
          <div class="card-header border-bottom">
            <h6 class="m-0">New Sample Code</h6>
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item p-3">
              <div class="row">
                <div class="col">
                  {!! Form::model($sample_code, ['url' => 'sample-codes', 'method' => 'POST']) !!}
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="feFirstName">Buyer</label>
                        {!! Form::select('buyer_id', $buyers, null, ['class' => 'form-control select2', 'required']) !!}
                        @if($errors->has('buyer_id'))
                          <span class="text-danger">{{ $errors->first('buyer_id') }}</span>
                        @endif
                      </div>
                      <div class="form-group col-md-6">
                        <label for="feLastName">Color</label>
                        {!! Form::select('color_id', $colors, null, ['class' => 'form-control select2' , 'required']) !!}
                        @if($errors->has('color_id'))
                          <span class="text-danger">{{ $errors->first('color_id') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="form-row clone">
                      <div class="form-group col-md-6">
                        <label for="feEmailAddress">Size</label>
                        {!! Form::select('size_id[]', $sizes, null, ['class' => 'form-control select2', 'required']) !!}
                        
                        @if($errors->has('email'))
                          <span class="text-danger">{{ $errors->first('email') }}</span>
                        @endif
                      </div>
                      <div class="form-group col-md-5">
                        <label for="fePassword">Quantity</label>
                        {!! Form::number('quantity[]', null, ['class' => 'form-control', 'required']) !!}

                        @if($errors->has('personal_code'))
                          <span class="text-danger">{{ $errors->first('personal_code') }}</span>
                        @endif
                      </div>
                      <div class="form-group col-md-1" style="margin-top: 30px;">
                        <button type="button" class="btn btn-success add-more-btn"><i class="fa fa-plus"></i></button>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-2 offset-md-5 text-center">
                        <button type="submit" class="btn btn-accent">Submit</button>
                      </div>
                    </div>                   
                  {!! Form::close() !!}
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('scripts')
  {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script> --}}
  <script type="text/javascript">
    $(document).on('click', '.add-more-btn', function () {
      let cloneSizeArea = $(this).closest('.clone');
      let result = cloneSizeArea.clone();
      cloneSizeArea.after(result);
    });
  </script>
@endsection