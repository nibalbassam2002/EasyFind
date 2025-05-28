@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('contant')
    <section class="section dashboard">

        {{-- فلاتر الفترة الزمنية --}}
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

        {{-- ====================================================================== --}}
        {{-- =================== قسم الأدمن ومشرف المحتوى ====================== --}}
        {{-- ====================================================================== --}}
        @if(isset($role) && ($role === 'admin' || $role === 'content_moderator'))
            {{-- بطاقات الإحصائيات للأدمن/المشرف --}}
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
                         <div class="card h-100 shadow border-start border-danger border-4">
                             <div class="card-body d-flex align-items-center">
                                  <div class="flex-shrink-0 me-3"><i class="bi bi-hourglass-split text-danger fs-2"></i></div>
                                 <div class="flex-grow-1">
                                     <div class="text-muted text-uppercase small fw-bold">Pending Properties</div>
                                     <div class="fs-4 fw-bold">{{ $pendingPropertiesCount ?? 0 }}</div>
                                      {{-- يمكنك إضافة رابط هنا لصفحة العقارات المعلقة --}}
                                     {{-- <a href="{{ route('moderator.properties.pending') }}" class="small text-decoration-none d-block mt-1">Review Now</a> --}}
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             @endif
            <!-- نهاية البطاقة الخاصة بالمشرف -->

            <!-- صف الرسوم البيانية العامة للأدمن/المشرف -->
            <div class="row mt-2">
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

            <!-- جدول العمليات الأخيرة العام للأدمن/المشرف -->
            <div class="row">
                <div class="col-12">
                    <div class="card-1 recent-sales ">
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold card-title">Latest Transactions</h5>
                             <form method="GET" class="mb-0" action="{{ route('dashboard') }}">
                                {{-- ... (كود فلتر المعاملات) ... --}}
                            </form>
                        </div>
                        <div class="card-body pt-3">
                             <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                   {{-- ... (كود جدول المعاملات) ... --}}
                                </table>
                            </div>
                            {{-- ... (كود الترقيم) ... --}}
                        </div>
                    </div>
                </div>
            </div> <!-- نهاية جدول العمليات العام -->


        {{-- ====================================================================== --}}
        {{-- ======================= قسم البائع (Property Lister) =================== --}}
        {{-- ====================================================================== --}}
        @elseif(isset($role) && $role === 'property_lister')

            {{-- ▼▼▼ بطاقة معلومات الخطة الحالية (تظهر دائمًا للبائع) ▼▼▼ --}}
            @if(isset($planName) && $planName !== 'N/A')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card info-card shadow-sm rounded-3 border-start {{ $isFreePlan ? 'border-primary' : 'border-success' }} border-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi {{ $isFreePlan ? 'bi-patch-check-fill text-primary' : 'bi-gem text-success' }} fs-1 me-3"></i>
                                    <div>
                                        <h5 class="card-title fw-bold mb-1 p-0">
                                            Your Current Plan: <span class="text-dark">{{ $planName }}</span>
                                        </h5>
                                        @if($isFreePlan)
                                            <span class="badge bg-primary">Free Plan</span>
                                        @else
                                            <span class="badge bg-success">Active Plan</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        @if(isset($propertiesLimit) && $propertiesLimit > 0)
                                            <p class="mb-1">
                                                <i class="bi bi-bar-chart-line-fill me-1"></i>
                                                Listings: <strong>{{ $propertiesListed ?? 0 }} / {{ $propertiesLimit }}</strong> used.
                                            </p>
                                            @if(isset($propertiesRemaining) && $propertiesRemaining > 0)
                                                <p class="text-success mb-0 small">
                                                    <i class="bi bi-plus-circle-dotted"></i>
                                                    You can list <strong>{{ $propertiesRemaining }}</strong> more.
                                                </p>
                                            @else
                                                <p class="text-danger mb-0 small">
                                                    <i class="bi bi-exclamation-octagon-fill"></i>
                                                    Property limit reached.
                                                </p>
                                            @endif
                                        @else
                                            <p class="mb-1"><i class="bi bi-infinity me-1"></i> <strong>Unlimited</strong> property listings.</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">
                                            <i class="bi bi-calendar-check-fill me-1"></i>
                                            Plan active until: <strong>{{ $planEndsAt ?? 'N/A' }}</strong>
                                        </p>
                                        @if(isset($allowedPropertyTypesString) && $allowedPropertyTypesString !== 'N/A')
                                            <p class="mb-0 small text-muted">
                                                <i class="bi bi-tags-fill me-1"></i>
                                                Allowed Types: {{ $allowedPropertyTypesString }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($isFreePlan || (isset($propertiesLimit) && $propertiesLimit > 0 && isset($propertiesRemaining) && $propertiesRemaining <= 0))
                                    <hr class="my-3">
                                    <div class="text-center">
                                        <a href="{{ route('frontend.pricing') }}" class="btn btn-gold btn-lg lift shadow-sm">
                                            <i class="bi bi-arrow-up-circle-fill me-2"></i> Upgrade Your Plan
                                        </a>
                                        <p class="small text-muted mt-2">
                                            @if($isFreePlan)
                                                Unlock more features, list more property types, and increase your visibility!
                                            @else
                                                Upgrade to list more properties and access premium features.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- رسالة إذا لم يتم العثور على معلومات الخطة (للبائع) --}}
                <div class="alert alert-warning" role="alert">
                    Could not retrieve your current plan details. Please ensure you have an active subscription.
                    If this issue persists, please <a href="#" class="alert-link">contact support</a>.
                    You can also <a href="{{ route('frontend.pricing') }}" class="alert-link">view available plans</a>.
                </div>
            @endif
            {{-- ▲▲▲ نهاية بطاقة معلومات الخطة ▲▲▲ --}}


            {{-- ▼▼▼ تخصيص واجهة البائع بناءً على نوع الخطة ▼▼▼ --}}
            @if(isset($isFreePlan) && $isFreePlan)
                {{-- *** واجهة مبسطة للبائع ذي الخطة المجانية *** --}}
                <div class="row">
                    <!-- بطاقة عدد العقارات النشطة (مهمة للخطة المجانية) -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                        <div class="card h-100 shadow border-start border-primary border-4">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3"><i class="bi bi-building-check text-primary fs-2"></i></div>
                                <div class="flex-grow-1">
                                    <div class="text-muted text-uppercase small fw-bold">My Active Listings</div>
                                    <div class="fs-4 fw-bold">{{ $activeListingsCount ?? 0 }}</div>
                                    <a href="{{ route('lister.properties.index', ['status' => 'approved']) }}" class="small text-decoration-none d-block mt-1">View Active</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- بطاقة عدد العقارات المعلقة (مهمة للخطة المجانية) -->
                    <div class="col-lg-6 col-md-6 col-sm-12 mb-4">
                        <div class="card h-100 shadow border-start border-warning border-4">
                            <div class="card-body d-flex align-items-center">
                                <div class="flex-shrink-0 me-3"><i class="bi bi-hourglass-split text-warning fs-2"></i></div>
                                <div class="flex-grow-1">
                                    <div class="text-muted text-uppercase small fw-bold">Pending Submissions</div>
                                    <div class="fs-4 fw-bold">{{ $pendingListingsCount ?? 0 }}</div>
                                    <a href="{{ route('lister.properties.index', ['status' => 'pending']) }}" class="small text-decoration-none d-block mt-1">Review Pending</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- مخطط حالة العقارات (بسيط ومناسب للخطة المجانية) --}}
                <div class="row mt-2">
                    <div class="col-lg-12 mb-4"> {{-- جعل المخطط يأخذ عرض كامل --}}
                        <div class="card-1 h-100">
                             <div class="card-body d-flex flex-column">
                                 <h5 class="card-title fw-bold text-center mb-3">My Property Statuses</h5>
                                 <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;">
                                    <canvas id="myPropertiesStatusChart"></canvas>
                                    <div id="myPropertiesStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">No property status data available</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- رسالة تشجيعية أو إرشادات بسيطة --}}
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Free Plan Dashboard</h4>
                            <p>You are currently on the Free Plan. You can list up to <strong>{{ $propertiesLimit ?? 'N/A' }}</strong> properties of type: <strong>{{ $allowedPropertyTypesString ?? 'N/A' }}</strong>.</p>
                            <p>To access advanced features like detailed analytics, more listings, and wider property type options, consider upgrading your plan.</p>
                            <hr>
                            <p class="mb-0">Use the sidebar menu to "Add New Property" or "View My Properties".</p>
                        </div>
                    </div>
                </div>

            @else
                {{-- *** واجهة البائع العادية (للخطط المدفوعة أو إذا لم يتم تحديد isFreePlan بشكل صحيح) *** --}}
                <div class="row">
                    <!-- Active Listings Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                        {{-- ... (بطاقة Active Listings كما هي) ... --}}
                    </div>
                    <!-- Pending Submissions Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                        {{-- ... (بطاقة Pending Submissions كما هي) ... --}}
                    </div>
                    <!-- Total Earnings Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                        {{-- ... (بطاقة Total Earnings كما هي) ... --}}
                    </div>
                </div>

                <div class="row mt-2">
                    <!-- مخطط حالة عقارات Lister (دائري) -->
                    <div class="col-lg-6 mb-4">
                        {{-- ... (مخطط My Property Statuses كما هو) ... --}}
                    </div>
                     <!-- مخطط معاملات Lister الشهرية (خطي) -->
                    <div class="col-lg-6 mb-4">
                        {{-- ... (مخطط My Monthly Transactions كما هو) ... --}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                         <div class="card recent-activity shadow">
                            {{-- ... (جدول Recent Activity كما هو) ... --}}
                        </div>
                    </div>
                </div>
            @endif
            {{-- ▲▲▲ نهاية تخصيص واجهة البائع ▲▲▲ --}}

        {{-- ====================================================================== --}}
        {{-- ========================= قسم المستخدم العادي (Customer) ===================== --}}
        {{-- ====================================================================== --}}
        @elseif(isset($role) && $role === 'customer')
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-start border-info border-4">
                        <div class="card-body text-center py-5 px-md-5">
                            <i class="bi bi-person-circle display-1 text-info mb-3"></i>
                            <h4 class="card-title fw-bold">Welcome, {{ Auth::user()->name ?? 'Guest' }}!</h4>
                            @if(isset($message))
                                <p class="text-muted lead mb-4">{{ $message }}</p>
                            @else
                                <p class="text-muted lead mb-4">This is your personal dashboard. What would you like to do?</p>
                            @endif
                            <hr class="my-4">
                            <div class="row justify-content-center gy-3">
                                <div class="col-md-auto">
                                    <a href="{{ route('frontend.pricing') }}" class="btn btn-gold btn-lg w-100 lift">
                                        <i class="bi bi-tags-fill me-2"></i> View Plans & Become a Seller
                                    </a>
                                    <small class="d-block text-muted mt-1">List your properties and reach buyers.</small>
                                </div>
                                <div class="col-md-auto">
                                    <a href="{{ route('frontend.account') }}" class="btn btn-outline-secondary btn-lg w-100">
                                        <i class="bi bi-pencil-square me-2"></i> Edit My Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        {{-- ====================================================================== --}}
        {{-- =========================== قسم المستخدم غير المحدد ======================= --}}
        {{-- ====================================================================== --}}
        @else
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body py-5 text-center">
                             <i class="bi bi-gear-wide-connected display-1 text-muted mb-3"></i>
                             <h5 class="card-title fw-bold">Welcome!</h5>
                             <p class="text-muted">Your dashboard is currently being prepared or your role ({{ $role ?? 'undefined' }}) is not fully configured for this view.</p>
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
            const currentRole = "{{ $role ?? 'unknown' }}";
            // تأكد من أن $isFreePlan يتم تمريرها بشكل صحيح من الكنترولر
            // إذا لم تكن متاحة هنا، افترض أنها false لتجنب أخطاء JavaScript
            const isFreePlan = {{ (isset($isFreePlan) && $isFreePlan === true) ? 'true' : 'false' }};

            function createChart(canvasId, noDataId, chartType, chartData, chartOptions) {
                const canvas = document.getElementById(canvasId);
                if (!canvas) {
                    // console.log(`Chart canvas ${canvasId} not found.`);
                    return;
                }
                const noDataEl = document.getElementById(noDataId);
                const ctx = canvas.getContext('2d');
                let hasData = false;
                if (chartData && chartData.datasets && chartData.datasets.length > 0) {
                    const dataPoints = chartData.datasets.reduce((acc, dataset) => acc.concat(dataset.data || []), []);
                    if (Array.isArray(dataPoints) && dataPoints.length > 0) {
                        hasData = dataPoints.some(val => val > 0 || (typeof val === 'object' && val !== null));
                    }
                }

                if (hasData) {
                    if (noDataEl) noDataEl.style.display = 'none';
                    canvas.style.display = 'block';
                    const existingChart = Chart.getChart(canvasId);
                    if (existingChart) { existingChart.destroy(); }
                    new Chart(ctx, { type: chartType, data: chartData, options: chartOptions });
                } else {
                    if (noDataEl) noDataEl.style.display = 'block';
                    canvas.style.display = 'none';
                }
            }

            const doughnutOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 15 } }, tooltip: {} }, cutout: '60%' };
            const lineOptions = { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } }, elements: { line: { tension: 0.3, borderWidth: 2 }, point: { radius: 3, hoverRadius: 5 } } };

            if (currentRole === 'admin' || currentRole === 'content_moderator') {
                fetch(chartEndpoint)
                    .then(response => { if (!response.ok) throw new Error('Network response was not ok'); return response.json(); })
                    .then(data => {
                        if (data.users && document.getElementById('usersStatusChart')) { createChart('usersStatusChart', 'usersStatusChartNoData', 'doughnut', { labels: ['Active', 'Inactive'], datasets: [{ data: [data.users.active ?? 0, data.users.inactive ?? 0], backgroundColor: ['#198754', '#dc3545'], hoverBackgroundColor: ['#157347', '#bb2d3b'] }] }, doughnutOptions); }
                        if (data.properties && document.getElementById('propertyTypesChart')) { createChart('propertyTypesChart', 'propertyTypesChartNoData', 'doughnut', { labels: ['For Sale', 'For Rent', 'For Lease'], datasets: [{ data: [data.properties.sale ?? 0, data.properties.rent ?? 0, data.properties.lease ?? 0], backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107'], hoverBackgroundColor: ['#0b5ed7', '#31d2f2', '#ffca2c'] }] }, doughnutOptions); }
                        if (data.transactionsStatus && document.getElementById('transactionsStatusChart')) { createChart('transactionsStatusChart', 'transactionsStatusChartNoData', 'doughnut', { labels: ['Completed', 'Pending', 'Failed'], datasets: [{ data: [data.transactionsStatus.completed ?? 0, data.transactionsStatus.pending ?? 0, data.transactionsStatus.failed ?? 0], backgroundColor: ['#198754', '#ffc107', '#dc3545'], hoverBackgroundColor: ['#157347', '#ffca2c', '#bb2d3b'] }] }, doughnutOptions); }
                        if (data.monthlyTransactions && document.getElementById('monthlyTransactionsChart')) { createChart('monthlyTransactionsChart', 'monthlyTransactionsChartNoData', 'line', { labels: data.monthlyTransactions.labels ?? [], datasets: [{ label: 'Transactions', data: data.monthlyTransactions.counts ?? [], borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true }] }, lineOptions); }
                    })
                    .catch(error => console.error('Error fetching admin/moderator chart data:', error));
            } else if (currentRole === 'property_lister') {
                fetch(chartEndpoint)
                    .then(response => { if (!response.ok) throw new Error('Network response was not ok'); return response.json(); })
                    .then(data => {
                        if (data.myPropertiesStatus && document.getElementById('myPropertiesStatusChart')) {
                            createChart('myPropertiesStatusChart', 'myPropertiesStatusChartNoData', 'doughnut', {
                                labels: data.myPropertiesStatus.labels ?? [],
                                datasets: [{ data: data.myPropertiesStatus.counts ?? [], backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14'], hoverBackgroundColor: ['#157347', '#ffca2c', '#bb2d3b', '#31d2f2', '#5a359e', '#d96a0d'] }]
                            }, doughnutOptions);
                        }
                        // اعرض مخطط المعاملات فقط إذا لم تكن الخطة مجانية
                        if (!isFreePlan && data.myMonthlyTransactions && document.getElementById('myMonthlyTransactionsChart')) {
                            createChart('myMonthlyTransactionsChart', 'myMonthlyTransactionsChartNoData', 'line', {
                                labels: data.myMonthlyTransactions.labels ?? [],
                                datasets: [{ label: 'My Transactions', data: data.myMonthlyTransactions.counts ?? [], borderColor: '#198754', backgroundColor: 'rgba(25, 135, 84, 0.1)', fill: true }]
                            }, lineOptions);
                        }
                    })
                    .catch(error => console.error('Error fetching property_lister chart data:', error));
            }
        });
    </script>
@endsection