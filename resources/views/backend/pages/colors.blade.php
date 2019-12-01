@extends('backend.layout')
@section('content')
  <div class="main-content-container container-fluid px-4">
    <color-component></color-component>
  </div>
  <!-- form modal------>
  {{-- <div class="modal fade" id="buyerModal" tabindex="-1" role="dialog" aria-labelledby="buyerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="buyerModalLabel">New Color</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        {!! Form::open(['id' => 'colorForm']) !!}
        <div class="modal-body">          
          <div class="form-group">
            <label for="recipient-name" class="col-form-label">Name</label>
            <input type="text" class="form-control" id="recipient-name">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">SAVE</button>
        </div>
        {!! Form::close() !!}
      </div>
    </div>
  </div> --}}
@endsection