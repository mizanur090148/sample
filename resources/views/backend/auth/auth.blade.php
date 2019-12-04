<!doctype html>
<html class="no-js h-100" lang="en">
  <head>
    @include('backend.partials.styles')
  </head>
  <body class="h-100">    
    <div class="container-fluid">
      <div class="row">      
          <!-- / .main-navbar -->
          <div class="main-content-container container-fluid px-4">        
            <!-- PAGE START -->
            @yield('content')
            <!-- PAGE END -->           
          </div>
        </main>
      </div>
    </div>    
    
    @include('backend.partials.scripts')
  </body>
</html>