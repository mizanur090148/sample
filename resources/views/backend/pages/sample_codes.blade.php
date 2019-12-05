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
            {{-- <h6 class="m-0">Fund Transfer List</h6> --}}
            <a class="btn btn-sm btn-info" href="{{ url('sample-codes/create') }}">
              <i class="glyphicon glyphicon-plus"></i> New Sample Code
            </a>
          </div>
          @include('backend.partials.response_message')

          <div class="card-body p-0 pb-3 text-center">
            <table class="table table-sm">
              <thead class="bg-light">
                <tr>
                  <th scope="col" class="border-0">#</th>
                  <th scope="col" class="border-0">Challan No</th>
                  <th scope="col" class="border-0">Buyer</th>                
                  <th scope="col" class="border-0">Color</th>
                  <th scope="col" class="border-0">Size</th>
                  <th scope="col" class="border-0">Quantity</th>
                  <th scope="col" class="border-0">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse($sample_code_list as $sample_code)
                  <tr style="height: 20px !important;">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ str_pad($sample_code->id, 8, '0', STR_PAD_LEFT) }}</td>
                    <td>
                      {{ 
                        implode(', ', array_unique($sample_code->sample_codes->pluck('buyer.name')->toArray())) 
                      }}
                    </td>
                    <td>
                      {{ 
                        implode(', ', array_unique($sample_code->sample_codes->pluck('color.name')->toArray())) 
                      }}
                    </td>
                    <td>
                      {{ 
                        implode(', ', array_unique($sample_code->sample_codes->pluck('size.name')->toArray())) 
                      }}
                    </td>
                    <td>{{ $sample_code->sample_codes->count() }}</td>
                    <td>
                      <a class="btn btn-xs btn-success" href="{{ url('/sample-codes/'.$sample_code->id ) }}">
                        <i class="fa fa-eye"></i>
                      </a>
                      <a class="btn btn-xs btn-danger" href="{{ url('/sample-codes/'.$sample_code->id ) }}">
                        <i class="fa fa-times"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-danger">Not found</td>                  
                  </tr>
                @endforelse
              </tbody>
              <tfoot>
                @if($sample_code_list->total() > 15)
                  <tr>
                    <td colspan="7" align="center">
                      {{ $transactions->appends(request()->except('page'))->links() }}
                    </td>
                  </tr>
                @endif
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection