@extends('backend.auth.auth')
@section('content')
<div class="row">
  <div class="col-lg-4 offset-sm-4 mb-4 p-5">
    <div class="card card-small mb-4">
      <div class="card-header border-bottom">
        <h6 class="m-0">Login Form</h6>        
      </div>
      @include('backend.partials.response_message')
      <div class="row p-3">
        <div class="col-sm-12 col-md-12">
          @if(Session::has('error'))
            <div class="alert-dismissible text-center p-2">
              <span class="text-danger">{{ Session::get('error') }}</span>
            </div>
          @endif
          {!! Form::open(['url' => 'login-post', 'method' => 'POST']) !!}
            <div class="form-group">
              <strong class="text-muted d-block mb-2">E-mail</strong>
              {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Enter email', 'required']) !!}
              @if($errors->has('email'))
                <span class="text-danger">{{ $errors->first('email') }}</span>
              @endif
            </div>
            <div class="form-group">
              <strong class="text-muted d-block mb-2">Password</strong>
              {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Enter password', 'required']) !!}
              @if($errors->has('password'))
                <span class="text-danger">{{ $errors->first('password') }}</span>
              @endif
            </div>
            <div class="form-group text-center">
              {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
            </div>
          {!! Form::close() !!}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection