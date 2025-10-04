 <div class="row">
     <div class="col-md-8">
         <div class="card card-round">
             <div class="card-header">
                 <div class="card-head-row">
                     <div class="card-title">User Statistics</div>
                     <div class="card-tools">
                         <a href="#" class="btn btn-label-success btn-round btn-sm me-2">
                             <span class="btn-label">
                                 <i class="fa fa-pencil"></i>
                             </span>
                             Export
                         </a>
                         <a href="#" class="btn btn-label-info btn-round btn-sm">
                             <span class="btn-label">
                                 <i class="fa fa-print"></i>
                             </span>
                             Print
                         </a>
                     </div>
                 </div>
             </div>
             <div class="card-body">
                 <div class="chart-container" style="min-height: 375px">
                     <canvas id="statisticsChart"></canvas>
                 </div>
                 <div id="myChartLegend"></div>
             </div>
         </div>
     </div>
     <div class="col-md-4">
         <div class="card card-primary card-round">
             <div class="card-header">
                 <div class="card-head-row">
                     <div class="card-title">Daily Sales</div>
                     <div class="card-tools">
                         <div class="dropdown">
                             <button class="btn btn-sm btn-label-light dropdown-toggle" type="button"
                                 id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true"
                                 aria-expanded="false">
                                 Export
                             </button>
                             <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <a class="dropdown-item" href="#">Action</a>
                                 <a class="dropdown-item" href="#">Another action</a>
                                 <a class="dropdown-item" href="#">Something else here</a>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="card-category">March 25 - April 02</div>
             </div>
             <div class="card-body pb-0">
                 <div class="mb-4 mt-2">
                     <h1>$4,578.58</h1>
                 </div>
                 <div class="pull-in">
                     <canvas id="dailySalesChart"></canvas>
                 </div>
             </div>
         </div>
         <div class="card card-round">
             <div class="card-body pb-0">
                 <div class="h1 fw-bold float-end text-primary">+5%</div>
                 <h2 class="mb-2">17</h2>
                 <p class="text-muted">Users online</p>
                 <div class="pull-in sparkline-fix">
                     <div id="lineChart"></div>
                 </div>
             </div>
         </div>
     </div>
 </div>
