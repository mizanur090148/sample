@extends('backend.layout')
@section('styles')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
@endsection
@section('content')
  <!-- Small Stats Blocks -->  
  <div class="main-content-container container-fluid px-4">
    <div class="page-header row no-gutters py-2">
      {{-- <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle">Dashboard</span>
        <h3 class="page-title">Blog Overview</h3>
      </div> --}}
    </div>
    <div class="row">
      <div class="col-lg col-md-6 col-sm-6 mb-4">
        <div class="stats-small stats-small--1 card card-small">
          <div class="card-body p-0 d-flex">
            <div class="d-flex flex-column m-auto">
              <div class="stats-small__data text-center">
                <span class="stats-small__label text-uppercase">Today Sent</span>
                <h6 class="stats-small__value count my-3">10</h6>
              </div>
              {{-- <div class="stats-small__data">
                <span class="stats-small__percentage stats-small__percentage--increase">4.7%</span>
              </div> --}}
            </div>
            <canvas height="120" class="blog-overview-stats-small-1"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-6 col-sm-6 mb-4">
        <div class="stats-small stats-small--1 card card-small">
          <div class="card-body p-0 d-flex">
            <div class="d-flex flex-column m-auto">
              <div class="stats-small__data text-center">
                <span class="stats-small__label text-uppercase">Total Sent</span>
                <h6 class="stats-small__value count my-3">180</h6>
              </div>
              {{-- <div class="stats-small__data">
                <span class="stats-small__percentage stats-small__percentage--increase">12.4%</span>
              </div> --}}
            </div>
            <canvas height="120" class="blog-overview-stats-small-2"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-4 col-sm-6 mb-4">
        <div class="stats-small stats-small--1 card card-small">
          <div class="card-body p-0 d-flex">
            <div class="d-flex flex-column m-auto">
              <div class="stats-small__data text-center">
                <span class="stats-small__label text-uppercase">Today Received</span>
                <h6 class="stats-small__value count my-3">12</h6>
              </div>
              {{-- <div class="stats-small__data">
                <span class="stats-small__percentage stats-small__percentage--decrease">3.8%</span>
              </div> --}}
            </div>
            <canvas height="120" class="blog-overview-stats-small-3"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-4 col-sm-6 mb-4">
        <div class="stats-small stats-small--1 card card-small">
          <div class="card-body p-0 d-flex">
            <div class="d-flex flex-column m-auto">
              <div class="stats-small__data text-center">
                <span class="stats-small__label text-uppercase">Total Received</span>
                <h6 class="stats-small__value count my-3">155</h6>
              </div>
              {{-- <div class="stats-small__data">
                <span class="stats-small__percentage stats-small__percentage--increase">12.4%</span>
              </div> --}}
            </div>
            <canvas height="120" class="blog-overview-stats-small-4"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg col-md-4 col-sm-12 mb-4">
        <div class="stats-small stats-small--1 card card-small">
          <div class="card-body p-0 d-flex">
            <div class="d-flex flex-column m-auto">
              <div class="stats-small__data text-center">
                <span class="stats-small__label text-uppercase">Left</span>
                <h6 class="stats-small__value count my-3">25</h6>
              </div>
             {{--  <div class="stats-small__data">
                <span class="stats-small__percentage stats-small__percentage--decrease">2.4%</span>
              </div> --}}
            </div>
            <canvas height="120" class="blog-overview-stats-small-5"></canvas>
          </div>
        </div>
      </div>
    </div>
    <!-- End Small Stats Blocks -->
    <div class="row">
      <!-- Users Stats -->
      <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
        <div class="card card-small">
          <div class="card-header border-bottom">
            <h6 class="m-0 text-center">Graphical Representation of Sample Sent & Receievd</h6>
          </div>
          <div class="card-body pt-0">
            <div class="row border-bottom py-2 bg-light">
              {{-- <div class="col-12 col-sm-6">
                <div id="blog-overview-date-range" class="input-daterange input-group input-group-sm my-auto ml-auto mr-auto ml-sm-auto mr-sm-0" style="max-width: 350px;">
                  <input type="text" class="input-sm form-control" name="start" placeholder="Start Date" id="blog-overview-date-range-1">
                  <input type="text" class="input-sm form-control" name="end" placeholder="End Date" id="blog-overview-date-range-2">
                  <span class="input-group-append">
                    <span class="input-group-text">
                      <i class="material-icons"></i>
                    </span>
                  </span>
                </div>
              </div>
              <div class="col-12 col-sm-6 d-flex mb-2 mb-sm-0">
                <button type="button" class="btn btn-sm btn-white ml-auto mr-auto ml-sm-auto mr-sm-0 mt-3 mt-sm-0">View Full Report &rarr;</button>
              </div> --}}
              <canvas style="height: 300px;" id="myChart"></canvas>
              {{-- <canvas id="bar-chart-grouped" width="800" height="450"></canvas> --}}
            </div>
            
          </div>
        </div>
      </div>
      <!-- End Users Stats -->
      {{-- <chart-component></chart-component> --}}
      <!-- Users By Device Stats -->
      {{-- <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="card card-small h-100">
          <div class="card-header border-bottom">
            <h6 class="m-0">Users by device</h6>
          </div>
          <div class="card-body d-flex py-0">
            <canvas height="220" class="blog-users-by-device m-auto"></canvas>
          </div>
          <div class="card-footer border-top">
            <div class="row">
              <div class="col">
                <select class="custom-select custom-select-sm" style="max-width: 130px;">
                  <option selected>Last Week</option>
                  <option value="1">Today</option>
                  <option value="2">Last Month</option>
                  <option value="3">Last Year</option>
                </select>
              </div>
              <div class="col text-right view-report">
                <a href="#">Full report &rarr;</a>
              </div>
            </div>
          </div>
        </div>
      </div> --}}
      <!-- End Users By Device Stats -->
    </div>
  </div>
@endsection
@section('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
    new Chart(document.getElementById("myChart"), {
      type: 'bar',
      data: {
          labels: ["Today", "Last Day", "This Week", "Last Week", "This Month", "Last Month", 'This Year'],
          datasets: [
              {
                  label: "Sent data",
                  backgroundColor: "#0CC2AA",
                  data: [15, 15, 25, 50, 67, 80, 130]
              },
              {
                  label: "Received data",
                  backgroundColor: "#6887FF",
                  data: [13, 12, 35, 45, 60, 70, 120]
              }
          ]
      },
      options: {
          /*title: {
              display: true,
              text: ''
          },*/
          maintainAspectRatio: false,
          layout: {
              padding: {
                  left: 20,
                  right: 20
              }
          },
          hover: {
              animationDuration: 0
          },
          animation: {
              duration: 1,
              onComplete: function () {
                  var chartInstance = this.chart,
                  ctx = chartInstance.ctx;
                 /* ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);*/
                  ctx.textAlign = 'center';
                  ctx.textBaseline = 'bottom';
                  this.data.datasets.forEach(function (dataset, i) {
                      var meta = chartInstance.controller.getDatasetMeta(i);
                      meta.data.forEach(function (bar, index) {
                          var data = dataset.data[index];
                          ctx.fillText(data, bar._model.x, bar._model.y - 5);
                      });
                  });
              }
          },
          scales: {
              yAxes: [{
                  display: true,
                  scaleLabel: {
                      display: true,
                      labelString: 'Send & Received Data'
                  }
              }]
          },
        }
      });
    });
  </script>
@endsection