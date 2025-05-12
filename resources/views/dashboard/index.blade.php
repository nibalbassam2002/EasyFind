@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('contant')
    <section class="section dashboard">

        
        @if(isset($role) && in_array($role, ['admin', 'content_moderator', 'property_lister']))
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-end align-items-center">
                    <span class="me-3 text-muted small fw-bold">Filter Period:</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Time Period Filter">
                        <a href="{{ request()->fullUrlWithQuery(['period' => 'today']) }}" class="btn {{ ($period ?? 'all') == 'today' ? 'btn-gold' : 'btn-outline-gold1' }}">Today</a>
                        <a href="{{ request()->fullUrlWithQuery(['period' => 'week']) }}" class="btn {{ ($period ?? 'all') == 'week' ? 'btn-gold' : 'btn-outline-gold1' }}">Week</a>
                        <a href="{{ request()->fullUrlWithQuery(['period' => 'month']) }}" class="btn {{ ($period ?? 'all') == 'month' ? 'btn-gold' : 'btn-outline-gold1' }}">Month</a>
                        <a href="{{ request()->fullUrlWithQuery(['period' => 'all']) }}" class="btn {{ !isset($period) || $period == 'all' ? 'btn-gold' : 'btn-outline-gold1' }}">All Time</a>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($role) && ($role === 'admin' || $role === 'content_moderator'))


            <div class="row">
                <!-- Total Users Card -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-primary border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-people text-primary fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Total Users <span class="text-lowercase">({{ ($period ?? 'all') == 'all' ? 'All' : ucfirst($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalUsers ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Properties Card -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-success border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-house text-success fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Properties <span class="text-lowercase">({{ ($period ?? 'all') == 'all' ? 'All' : ucfirst($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalProperties ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Total Requests Card -->
                 <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-info border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-envelope text-info fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Requests <span class="text-lowercase">({{ ($period ?? 'all') == 'all' ? 'All' : ucfirst($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalRequests ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- Completed Transactions Card -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-warning border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-check-circle text-warning fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Completed <span class="text-lowercase">({{ ($period ?? 'all') == 'all' ? 'All' : ucfirst($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $completedTransactions ?? 0 }}</div>
                                <div class="small text-muted">out of {{ $totalTransactions ?? 0 }} total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- نهاية صف البطاقات العامة -->

             @if ($role === 'content_moderator')
                 <div class="row">
                     <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                         <div class="card h-100 shadow-sm border-start border-danger border-4">
                             <div class="card-body d-flex align-items-center">
                                  <div class="flex-shrink-0 me-3"><i class="bi bi-hourglass-split text-danger fs-2"></i></div>
                                 <div class="flex-grow-1">
                                     <div class="text-muted text-uppercase small fw-bold">Pending Properties</div>
                                     <div class="fs-4 fw-bold">{{ $pendingPropertiesCount ?? 0 }}</div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             @endif
            <!-- نهاية البطاقة الخاصة بالمشرف -->

            <!-- صف الرسوم البيانية العامة -->
            <div class="row mt-2">
                {{-- مخططات Admin/Moderator --}}
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100"> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Users Status</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="usersStatusChart"></canvas> <div id="usersStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">No user data available</div> </div> </div> </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Property Types</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="propertyTypesChart"></canvas> <div id="propertyTypesChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">No property data available</div> </div> </div> </div>
                </div>
                 <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Transactions Status</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="transactionsStatusChart"></canvas> <div id="transactionsStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">No transaction data available</div> </div> </div> </div>
                </div>
                 <div class="col-lg-12 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body"> <h5 class="card-title fw-bold text-center mb-3">Monthly Transactions (Last 12 Months)</h5> <div style="min-height: 300px; position: relative;"> <canvas id="monthlyTransactionsChart"></canvas> <div id="monthlyTransactionsChartNoData" class="no-data-message text-muted" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">No transaction data for this period</div> </div> </div> </div>
                </div>
            </div> <!-- نهاية صف الرسوم البيانية العامة -->

            <!-- جدول العمليات الأخيرة العام -->
            <div class="row">
                <div class="col-12">
                    <div class="card-1 recent-sales ">
                        {{-- Card Header with filter --}}
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold card-title">Latest Transactions</h5>
                             <form method="GET" class="mb-0" action="{{ route('dashboard') }}">
                                <div class="input-group input-group-sm">
                                    <select name="type" class="form-select  select-g " onchange="this.form.submit()" aria-label="Filter by type">
                                        <option value="">All Types</option>
                                        <option value="sale" {{ ($type ?? null) == 'sale' ? 'selected' : '' }}>Sales</option>
                                        <option value="rent" {{ ($type ?? null) == 'rent' ? 'selected' : '' }}>Rentals</option>
                                    </select>
                                    @if(request('period'))
                                        <input type="hidden" name="period" value="{{ request('period') }}">
                                    @endif
                                    @if ($type ?? null)
                                        <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="btn btn-outline-secondary">Reset Type</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        {{-- Card Body with table --}}
                        <div class="card-body pt-3">
                             <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr> <th>ID</th> <th>Property</th> <th>Customer</th> <th>Type</th> <th>Price</th> <th>Status</th> <th>Date</th> </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions ?? collect() as $transaction)
                                             <tr>
                                                <td class="fw-bold">#{{ $transaction->id }}</td>
                                                <td><span class="text-dark">{{ $transaction->property?->title ?? 'N/A' }}</span></td>
                                                <td>{{ $transaction->user?->name ?? 'N/A' }}</td>
                                                <td><span class="badge rounded-pill bg-{{ $transaction->type == 'sale' ? 'primary' : 'info' }}">{{ ucfirst($transaction->type) }}</span></td>
                                                {{-- السعر هنا يعرض سعر العقار، وليس مبلغ المعاملة --}}
                                                <td>${{ number_format($transaction->property?->price ?? 0, 2) }}</td>
                                                <td>
                                                    @php $statusClass = 'warning'; if ($transaction->status == 'completed') $statusClass = 'success'; elseif ($transaction->status == 'failed') $statusClass = 'danger'; @endphp
                                                    <span class="badge rounded-pill bg-{{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                                                </td>
                                                <td>{{ $transaction->created_at?->format('M d, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr> <td colspan="7" class="text-center text-muted py-4">No transactions found {{ ($type ?? null) ? 'of type '. ucfirst($type) : '' }}.</td> </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                             {{-- Pagination --}}
                            @if (isset($recentTransactions) && $recentTransactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentTransactions->hasPages())
                                <div class="d-flex justify-content-end pt-3 border-top mt-3">
                                    {{ $recentTransactions->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div> <!-- نهاية جدول العمليات العام -->


        @elseif(isset($role) && $role === 'property_lister')

            <div class="row">

                <!-- Active Listings Card -->
                <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-start border-primary border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-building-check text-primary fs-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">My Active Listings</div>
                                
                                <div class="fs-4 fw-bold">{{ $activeListingsCount ?? 0 }}</div>
                                <a href="{{ route('lister.properties.index') }}?status=approved" class="small text-decoration-none">View Active</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Submissions Card -->
                <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-start border-warning border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-hourglass-split text-warning fs-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Pending Submissions</div>
                            
                                <div class="fs-4 fw-bold">{{ $pendingListingsCount ?? 0 }}</div>
                                <a href="{{ route('lister.properties.index') }}?status=pending" class="small text-decoration-none">Review Pending</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Earnings Card -->
                <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm border-start border-success border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <i class="bi bi-cash-coin text-success fs-2"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Total Earnings <span class="text-lowercase">({{ ($period ?? 'all') == 'all' ? 'All' : ucfirst($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">${{ number_format($totalEarnings ?? 0, 2) }}</div>
                                <small class="text-muted">From completed transactions</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-2">
                <!-- مخطط حالة عقارات Lister (دائري) -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 shadow-sm">
                         <div class="card-body d-flex flex-column">
                             <h5 class="card-title fw-bold text-center mb-3">My Property Statuses</h5>
                             <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;">
                                <canvas id="myPropertiesStatusChart"></canvas>
                                <div id="myPropertiesStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">No property status data available</div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- مخطط معاملات Lister الشهرية (خطي) -->
                <div class="col-lg-6 mb-4">
                     <div class="card h-100 shadow-sm">
                         <div class="card-body">
                             <h5 class="card-title fw-bold text-center mb-3">My Monthly Transactions (Last 12 Months)</h5>
                             <div style="min-height: 300px; position: relative;">
                                <canvas id="myMonthlyTransactionsChart"></canvas>
                                <div id="myMonthlyTransactionsChartNoData" class="no-data-message text-muted" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">No transaction data for this period</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> 

            <div class="row">
                <div class="col-12">
                     <div class="card recent-activity shadow-sm">
                   
                         <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                             <h5 class="mb-0 fw-bold card-title">Recent Activity</h5> 
                             <form method="GET" class="mb-0" action="{{ route('dashboard') }}">
                                 <div class="input-group input-group-sm">
                                     <select name="type" class="form-select" onchange="this.form.submit()" aria-label="Filter by type">
                                         <option value="">All Types</option>
                                         <option value="sale" {{ ($type ?? null) == 'sale' ? 'selected' : '' }}>Sales</option>
                                         <option value="rent" {{ ($type ?? null) == 'rent' ? 'selected' : '' }}>Rentals</option>
                                     </select>
                                     @if(request('period'))
                                         <input type="hidden" name="period" value="{{ request('period') }}">
                                     @endif
                                     @if ($type ?? null)
                                         <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="btn btn-outline-secondary">Reset Type</a>
                                     @endif
                                     <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button> --}}
                                 </div>
                             </form>
                         </div>
                         {{-- Card Body with table --}}
                         <div class="card-body pt-3">
                             <div class="table-responsive">
                                 
                                 <table class="table table-hover align-middle mb-0">
                                     <thead class="table-light">
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Property Info</th>
                                            <th scope="col">Customer Info</th>
                                            <th scope="col">Type</th>
                                            <th scope="col" class="text-end">Amount</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                     </thead>
                                     <tbody>
                                         @forelse($recentTransactions ?? collect() as $transaction)
                                             <tr>
                                                {{-- Date --}}
                                                <td class="small text-muted">{{ $transaction->created_at?->format('M d, Y') }}</td>

                                                {{-- Property Info --}}
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @php
                                                            $images = json_decode($transaction->property?->images, true);
                                                            $firstImage = $images[0] ?? null;
                                                            $imageUrl = asset('assets/img/placeholder-property.png'); // صورة افتراضية للعقار
                                                            if ($firstImage && Storage::disk('public')->exists($firstImage)) {
                                                                $imageUrl = Storage::url($firstImage);
                                                            }
                                                        @endphp
                                                        <img src="{{ $imageUrl }}" alt="Prop" width="60" height="45" class="me-2 rounded object-fit-cover">
                                                        <div>
                                                            <div class="fw-bold text-dark">{{ Str::limit($transaction->property?->title ?? 'N/A', 35) }}</div>
                                                            <div class="text-muted small">{{ Str::limit($transaction->property?->address ?? '', 40) }}</div>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Customer Info --}}
                                                <td>
                                                    @if($transaction->user)
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ $transaction->user->profile_image_url ?? asset('assets/img/profile.jpg') }}" alt="User" width="35" height="35" class="me-2 rounded-circle object-fit-cover">
                                                            <span class="fw-medium">{{ $transaction->user->name }}</span>
                                                        </div>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>

                                                {{-- Type --}}
                                                <td>
                                                    <span class="badge rounded-pill bg-{{ $transaction->type == 'sale' ? 'primary' : 'success' }} bg-opacity-75"> {{-- تعديل بسيط في الألوان --}}
                                                        <i class="bi {{ $transaction->type == 'sale' ? 'bi-tag' : 'bi-key' }} me-1"></i>
                                                        {{ ucfirst($transaction->type) }}
                                                    </span>
                                                </td>

                                                {{-- Amount --}}
                                                <td class="text-end fw-medium">
                                                    ${{ number_format($transaction->amount ?? 0, 2) }}
                                                </td>

                                                {{-- Status --}}
                                                <td class="text-center">
                                                    @php
                                                        $statusConfig = [
                                                            'completed' => ['color' => 'success', 'icon' => 'bi-check-circle-fill'],
                                                            'pending' => ['color' => 'warning', 'icon' => 'bi-hourglass-split'],
                                                            'failed' => ['color' => 'danger', 'icon' => 'bi-x-octagon-fill'],
                                                        ];
                                                        $sConfig = $statusConfig[strtolower($transaction->status)] ?? ['color' => 'secondary', 'icon' => 'bi-question-circle'];
                                                    @endphp
                                                    <span class="badge bg-{{ $sConfig['color'] }}">
                                                        <i class="{{ $sConfig['icon'] }} me-1"></i>
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>

                                    
                                             </tr>
                                         @empty
                                            
                                             <tr> <td colspan="6" class="text-center text-muted py-5">
                                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                                No recent activity found for your properties.
                                            </td> </tr>
                                         @endforelse
                                     </tbody>
                                 </table>
                             </div>
                             {{-- Pagination --}}
                             @if (isset($recentTransactions) && $recentTransactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $recentTransactions->hasPages())
                                 <div class="d-flex justify-content-end pt-3 border-top mt-3">
                                     {{ $recentTransactions->withQueryString()->links() }}
                                 </div>
                             @endif
                         </div>
                     </div>
                </div>
            </div> 

        @elseif(isset($role) && $role === 'customer')


            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <h4 class="card-title fw-bold">Welcome, {{ Auth::user()->name }}!</h4>
                            <p class="text-muted">This is your personal dashboard.</p>
                            <a href="{{ route('profile.index') }}" class="btn btn-outline-primary btn-sm me-2">My Profile</a>
                        </div>
                    </div>
                </div>
            </div>


        @else

   
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body py-5 text-center">
                             <h5 class="card-title">Welcome!</h5>
                             <p class="text-muted">Your dashboard is being prepared.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif 


    </section>
@endsection

@section('scripts')

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartEndpoint = "{{ route('dashboard.chartData') }}";

             function createChart(canvasId, noDataId, chartType, chartData, chartOptions) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) return; // Exit if canvas doesn't exist for this role

                const noDataEl = document.getElementById(noDataId);
                 if (!noDataEl) {
                    console.warn(`NoData element not found for ID: ${noDataId}`);
                 }

                 const ctx = canvas.getContext('2d');
                 let hasData = false;

                 if (chartData && chartData.datasets && chartData.datasets.length > 0) {
                     // Check if data is an array before using some()
                     const dataPoints = chartData.datasets[0].data;
                     if (Array.isArray(dataPoints)) {
                        if (chartType === 'doughnut' || chartType === 'pie') {
                            hasData = dataPoints.some(val => val > 0);
                        } else if (chartType === 'line' || chartType === 'bar') {
                            hasData = dataPoints.some(val => val > 0);
                        }
                     }
                 }

                 if (hasData) {
                     if (noDataEl) noDataEl.style.display = 'none';
                     canvas.style.display = 'block';
                     const existingChart = Chart.getChart(canvas);
                     if (existingChart) {
                         existingChart.destroy();
                     }
                     new Chart(ctx, { type: chartType, data: chartData, options: chartOptions });
                 } else {
                     if (noDataEl) noDataEl.style.display = 'block';
                     canvas.style.display = 'none';
                 }
            }

             const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 15 } }, tooltip: { } }, cutout: '60%' };
             const lineOptions = { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false, } }, elements: { line: { tension: 0.3, borderWidth: 2 }, point: { radius: 3, hoverRadius: 5 } } };

            fetch(chartEndpoint)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    // -------- Admin / Moderator Charts --------
                     if (data.users) {
                         createChart('usersStatusChart','usersStatusChartNoData','doughnut',
                             { labels: ['Active', 'Inactive'], datasets: [{ data: [data.users.active ?? 0, data.users.inactive ?? 0], backgroundColor: ['#198754', '#dc3545'], hoverBackgroundColor: ['#157347', '#bb2d3b'] }] }, doughnutOptions );
                     }
                    if (data.properties) {
                        createChart('propertyTypesChart','propertyTypesChartNoData','doughnut',
                            { labels: ['For Sale', 'For Rent', 'For Lease'], datasets: [{ data: [data.properties.sale ?? 0, data.properties.rent ?? 0, data.properties.lease ?? 0], backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107'], hoverBackgroundColor: ['#0b5ed7', '#31d2f2', '#ffca2c'] }] }, doughnutOptions );
                    }
                    if (data.transactionsStatus) {
                         createChart('transactionsStatusChart','transactionsStatusChartNoData','doughnut',
                             { labels: ['Completed', 'Pending', 'Failed'], datasets: [{ data: [data.transactionsStatus.completed ?? 0, data.transactionsStatus.pending ?? 0, data.transactionsStatus.failed ?? 0], backgroundColor: ['#198754', '#ffc107', '#dc3545'], hoverBackgroundColor: ['#157347', '#ffca2c', '#bb2d3b'] }] }, doughnutOptions );
                    }
                    if (data.monthlyTransactions) {
                        createChart('monthlyTransactionsChart','monthlyTransactionsChartNoData','line',
                             { labels: data.monthlyTransactions.labels ?? [], datasets: [{ label: 'Transactions', data: data.monthlyTransactions.counts ?? [], borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true }] }, lineOptions );
                    }

                    // -------- Property Lister Charts --------
                    if (data.myPropertiesStatus) {
                        createChart('myPropertiesStatusChart','myPropertiesStatusChartNoData','doughnut',
                            { labels: data.myPropertiesStatus.labels ?? [],
                              datasets: [{ data: data.myPropertiesStatus.counts ?? [],
                                           backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14'],
                                           hoverBackgroundColor: ['#157347', '#ffca2c', '#bb2d3b', '#31d2f2', '#5a359e', '#d96a0d']
                                        }] }, doughnutOptions );
                    }
                     if (data.myMonthlyTransactions) {
                         createChart('myMonthlyTransactionsChart','myMonthlyTransactionsChartNoData','line',
                             { labels: data.myMonthlyTransactions.labels ?? [], datasets: [{ label: 'My Transactions', data: data.myMonthlyTransactions.counts ?? [], borderColor: '#198754', backgroundColor: 'rgba(25, 135, 84, 0.1)', fill: true }] }, lineOptions );
                     }

                })
                .catch(error => {
                     console.error('Error fetching or processing chart data:', error);
                     document.querySelectorAll('.no-data-message').forEach(el => {
                         if (el.closest('.card')) {
                             el.textContent = 'Failed to load chart data.';
                             el.style.display = 'block';
                         }
                     });
                     document.querySelectorAll('canvas').forEach(canvas => {
                         if (canvas.closest('.card')) canvas.style.display = 'none';
                     });
                });
        });
    </script>
@endsection