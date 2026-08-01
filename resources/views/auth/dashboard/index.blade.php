@extends('layouts.dashboard')
@section('title', 'Dashboard') {{-- يمكنك تخصيص هذا لاحقًا إذا أردت --}}

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('contant')
    <section class="section dashboard">

        {{-- فلاتر الفترة الزمنية (تظهر للأدوار التي تحتاجها) --}}
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
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-primary border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-people text-primary fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Total Users <span class="text-lowercase">({{ ($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalUsers ?? 0 }}</div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-success border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-house text-success fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Properties <span class="text-lowercase">({{ ($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalProperties ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-info border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-envelope text-info fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Requests <span class="text-lowercase">({{ ($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $totalRequests ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card h-100 shadow border-start border-warning border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-shrink-0 me-3"><i class="bi bi-check-circle text-warning fs-2"></i></div>
                            <div class="flex-grow-1">
                                <div class="text-muted text-uppercase small fw-bold">Completed <span class="text-lowercase">({{ ($period ?? 'all') }})</span></div>
                                <div class="fs-4 fw-bold">{{ $completedTransactions ?? 0 }}</div>
                                <div class="small text-muted">out of {{ $totalTransactions ?? 0 }} total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($role === 'content_moderator')
                 <div class="row">
                     <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                         <div class="card h-100 shadow border-start border-danger border-4">
                             <div class="card-body d-flex align-items-center">
                                  <div class="flex-shrink-0 me-3"><i class="bi bi-hourglass-split text-danger fs-2"></i></div>
                                 <div class="flex-grow-1">
                                     <div class="text-muted text-uppercase small fw-bold">Pending Properties</div>
                                     <div class="fs-4 fw-bold">{{ $pendingPropertiesCount ?? 0 }}</div>
                                     <a href="{{ route('moderator.properties.pending') }}" class="small text-decoration-none d-block mt-1">Review Now</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             @endif

            <div class="row mt-2">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100"> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Users Status</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="usersStatusChart"></canvas> <div id="usersStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">Loading...</div> </div> </div> </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Property Types</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="propertyTypesChart"></canvas> <div id="propertyTypesChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">Loading...</div> </div> </div> </div>
                </div>
                 <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">Transactions Status</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="transactionsStatusChart"></canvas> <div id="transactionsStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">Loading...</div> </div> </div> </div>
                </div>
                 <div class="col-lg-12 mb-4">
                    <div class="card-1 h-100 "> <div class="card-body"> <h5 class="card-title fw-bold text-center mb-3">Monthly Transactions (Last 12 Months)</h5> <div style="min-height: 300px; position: relative;"> <canvas id="monthlyTransactionsChart"></canvas> <div id="monthlyTransactionsChartNoData" class="no-data-message text-muted" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">Loading...</div> </div> </div> </div>
                </div>
            </div>

            {{-- جدول العمليات الأخيرة العام للأدمن/المشرف --}}
            <div class="row">
                <div class="col-12">
                    <div class="card-1 recent-sales shadow-sm">
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold card-title">Latest Transactions</h5>
                            <form method="GET" class="mb-0" action="{{ route('dashboard') }}">
                                <div class="input-group input-group-sm">
                                    <select name="type" class="form-select select-g" onchange="this.form.submit()" aria-label="Filter by type">
                                        <option value="">All Types</option>
                                        <option value="sale" {{ ($type ?? '') == 'sale' ? 'selected' : '' }}>Sales</option>
                                        <option value="rent" {{ ($type ?? '') == 'rent' ? 'selected' : '' }}>Rentals</option>
                                    </select>
                                    @if(request('period'))
                                        <input type="hidden" name="period" value="{{ request('period') }}">
                                    @endif
                                    @if ($type ?? null)
                                        <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="btn btn-outline-secondary btn-sm">Reset Type</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="card-body pt-3">
                             <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th> <th>Property</th> <th>User</th> <th>Type</th>
                                            <th>Amount</th> <th>Status</th> <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions ?? collect() as $transaction)
                                             <tr>
                                                {{-- <td class="fw-bold">#{{ $transaction->id }}</td> --}}
                                                <td class="fw-bold">{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="{{ $transaction->property ? route('frontend.property.show', $transaction->property_id) : '#' }}" target="_blank" class="text-dark">
                                                        {{ Str::limit($transaction->property?->title, 30) ?? 'N/A' }}
                                                    </a>
                                                </td>
                                                <td>{{ $transaction->user?->name ?? 'N/A' }}</td>
                                                <td><span class="badge rounded-pill bg-{{ $transaction->type == 'sale' ? 'primary' : ($transaction->type == 'rent' ? 'success' : 'info') }}">{{ ucfirst($transaction->type) }}</span></td>
                                                <td>{{ $transaction->currency ?? 'USD' }} {{ number_format($transaction->amount ?? 0, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = 'secondary'; // Default
                                                        if ($transaction->status == 'completed') $statusClass = 'success';
                                                        elseif ($transaction->status == 'pending') $statusClass = 'warning';
                                                        elseif ($transaction->status == 'failed' || $transaction->status == 'cancelled') $statusClass = 'danger';
                                                    @endphp
                                                    <span class="badge rounded-pill bg-{{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
                                                </td>
                                                <td>{{ $transaction->created_at?->format('d M, Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr> <td colspan="7" class="text-center text-muted py-4">No transactions found{{ ($type ?? null) ? ' of type '. ucfirst($type) : '' }}.</td> </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if (isset($recentTransactions) && $recentTransactions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $recentTransactions->hasPages())
                                <div class="d-flex justify-content-end pt-3 border-top mt-3">
                                    {{ $recentTransactions->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ====================================================================== --}}
        {{-- ======================= قسم البائع (Property Lister) =================== --}}
        {{-- ====================================================================== --}}
        @if(isset($role) && $role === 'property_lister')
            {{-- بطاقة معلومات الخطة الحالية --}}
            @if(isset($planName) && $planName !== 'N/A')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card info-card shadow-sm rounded-3 border-start {{ (isset($isFreePlan) && $isFreePlan) ? 'border-primary' : 'border-success' }} border-4">
                            <div class="card-body p-4">
                                {{-- ... (كود بطاقة معلومات الخطة كما هو في ردك السابق، فهو ممتاز) ... --}}
                                 <div class="d-flex align-items-center mb-3">
                                    <i class="bi {{ (isset($isFreePlan) && $isFreePlan) ? 'bi-patch-check-fill text-primary' : 'bi-gem text-success' }} fs-1 me-3"></i>
                                    <div>
                                        <h5 class="card-title fw-bold mb-1 p-0">
                                            Your Current Plan: <span class="text-dark">{{ $planName }}</span>
                                        </h5>
                                        @if(isset($isFreePlan) && $isFreePlan)
                                            <span class="badge bg-primary">Free Plan</span>
                                        @else
                                            <span class="badge bg-success">Active Plan</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        @if(isset($propertiesLimit) && $propertiesLimit > 0)
                                            <p class="mb-1"><i class="bi bi-bar-chart-line-fill me-1"></i>Listings: <strong>{{ $propertiesListed ?? 0 }} / {{ $propertiesLimit }}</strong> used.</p>
                                            @if(isset($propertiesRemaining) && $propertiesRemaining > 0)
                                                <p class="text-success mb-0 small"><i class="bi bi-plus-circle-dotted"></i>You can list <strong>{{ $propertiesRemaining }}</strong> more.</p>
                                            @else
                                                <p class="text-danger mb-0 small"><i class="bi bi-exclamation-octagon-fill"></i>Property limit reached.</p>
                                            @endif
                                        @else
                                            <p class="mb-1"><i class="bi bi-infinity me-1"></i> <strong>Unlimited</strong> property listings.</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><i class="bi bi-calendar-check-fill me-1"></i>Plan active until: <strong>{{ $planEndsAt ?? 'N/A' }}</strong></p>
                                        @if(isset($allowedPropertyTypesString) && $allowedPropertyTypesString !== 'N/A')
                                            <p class="mb-0 small text-muted"><i class="bi bi-tags-fill me-1"></i>Allowed Types: {{ $allowedPropertyTypesString }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if((isset($isFreePlan) && $isFreePlan) || (isset($propertiesLimit) && $propertiesLimit > 0 && isset($propertiesRemaining) && $propertiesRemaining <= 0))
                                    <hr class="my-3">
                                    <div class="text-center">
                                        <a href="{{ route('frontend.pricing') }}" class="btn btn-gold btn-lg lift shadow-sm">
                                            <i class="bi bi-arrow-up-circle-fill me-2"></i> Upgrade Your Plan
                                        </a>
                                        <p class="small text-muted mt-2">
                                            @if(isset($isFreePlan) && $isFreePlan)
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
                <div class="alert alert-warning" role="alert">
                    Could not retrieve your current plan details. Please ensure you have an active subscription.
                    You can <a href="{{ route('frontend.pricing') }}" class="alert-link">view available plans</a>.
                </div>
            @endif

            {{-- واجهة البائع --}}
            @if(isset($isFreePlan) && $isFreePlan)
                {{-- واجهة مبسطة للخطة المجانية --}}
                <div class="row">
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
                <div class="row mt-2">
                    <div class="col-lg-12 mb-4">
                        <div class="card-1 h-100">
                             <div class="card-body d-flex flex-column">
                                 <h5 class="card-title fw-bold text-center mb-3">My Property Statuses</h5>
                                 <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;">
                                    <canvas id="myPropertiesStatusChart"></canvas>
                                    <div id="myPropertiesStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">Loading...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- إخفاء مخطط المعاملات الشهرية للخطة المجانية --}}
                    <div class="col-lg-6 mb-4" id="monthlyTransactionsChartContainer" style="display: none;">
                         <div class="card-1 h-100">
                            <div class="card-body"><h5 class="card-title fw-bold text-center mb-3">My Monthly Transactions</h5>
                                <div style="min-height: 300px; position: relative;">
                                    <canvas id="myMonthlyTransactionsChart"></canvas>
                                    <div id="myMonthlyTransactionsChartNoData" class="no-data-message text-muted" style="display: block; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">This chart is available on paid plans.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Free Plan Dashboard</h4>
                            <p>You are currently on the Free Plan. You can list up to <strong>{{ $propertiesLimit ?? 'N/A' }}</strong> properties of type: <strong>{{ $allowedPropertyTypesString ?? 'N/A' }}</strong>.</p>
                            <p class="mb-0">Use the sidebar menu to "Add New Property" or "View My Properties".</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- واجهة البائع للخطط المدفوعة --}}
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4"> <div class="card h-100 shadow border-start border-primary border-4"> <div class="card-body d-flex align-items-center"> <div class="flex-shrink-0 me-3"><i class="bi bi-building-check text-primary fs-2"></i></div> <div class="flex-grow-1"> <div class="text-muted text-uppercase small fw-bold">My Active Listings</div> <div class="fs-4 fw-bold">{{ $activeListingsCount ?? 0 }}</div> <a href="{{ route('lister.properties.index', ['status' => 'approved']) }}" class="small text-decoration-none d-block mt-1">View Active</a> </div> </div> </div> </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4"><div class="card h-100 shadow border-start border-warning border-4"> <div class="card-body d-flex align-items-center"> <div class="flex-shrink-0 me-3"><i class="bi bi-hourglass-split text-warning fs-2"></i></div> <div class="flex-grow-1"> <div class="text-muted text-uppercase small fw-bold">Pending Submissions</div> <div class="fs-4 fw-bold">{{ $pendingListingsCount ?? 0 }}</div> <a href="{{ route('lister.properties.index', ['status' => 'pending']) }}" class="small text-decoration-none d-block mt-1">Review Pending</a> </div> </div> </div> </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-4"><div class="card h-100 shadow border-start border-success border-4"> <div class="card-body d-flex align-items-center"> <div class="flex-shrink-0 me-3"><i class="bi bi-cash-coin text-success fs-2"></i></div> <div class="flex-grow-1"> <div class="text-muted text-uppercase small fw-bold">Total Earnings <span class="text-lowercase">({{ ($period ?? 'all') }})</span></div> <div class="fs-4 fw-bold">${{ number_format($totalEarnings ?? 0, 2) }}</div> <small class="text-muted">From completed transactions</small> </div> </div> </div> </div>
                </div>
                <div class="row mt-2">
                    <div class="col-lg-6 mb-4"> <div class="card-1 h-100 "> <div class="card-body d-flex flex-column"> <h5 class="card-title fw-bold text-center mb-3">My Property Statuses</h5> <div class="flex-grow-1 d-flex justify-content-center align-items-center" style="position: relative; min-height: 250px;"> <canvas id="myPropertiesStatusChart"></canvas> <div id="myPropertiesStatusChartNoData" class="no-data-message text-muted" style="display: none; position: absolute;">Loading...</div> </div> </div> </div> </div>
                    <div class="col-lg-6 mb-4"> <div class="card-1 h-100 "> <div class="card-body"> <h5 class="card-title fw-bold text-center mb-3">My Monthly Transactions</h5> <div style="min-height: 300px; position: relative;"> <canvas id="myMonthlyTransactionsChart"></canvas> <div id="myMonthlyTransactionsChartNoData" class="no-data-message text-muted" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">Loading...</div> </div> </div> </div> </div>
                </div>
                <div class="row"> <div class="col-12"> <div class="card recent-activity shadow"> {{-- جدول Recent Activity للبائع المدفوع --}} <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center"> <h5 class="mb-0 fw-bold card-title">Recent Activity</h5> <form method="GET" class="mb-0" action="{{ route('dashboard') }}"> <div class="input-group input-group-sm"> <select name="type" class="form-select" onchange="this.form.submit()" aria-label="Filter by type"> <option value="">All Types</option> <option value="sale" {{ ($type ?? '') == 'sale' ? 'selected' : '' }}>Sales</option> <option value="rent" {{ ($type ?? '') == 'rent' ? 'selected' : '' }}>Rentals</option> </select> @if(request('period')) <input type="hidden" name="period" value="{{ request('period') }}"> @endif @if ($type ?? null) <a href="{{ request()->fullUrlWithQuery(['type' => null]) }}" class="btn btn-outline-secondary btn-sm">Reset Type</a> @endif </div> </form> </div> <div class="card-body pt-3"> <div class="table-responsive"> <table class="table table-hover align-middle mb-0"> <thead class="table-light"> <tr> <th scope="col">Date</th> <th scope="col">Property Info</th> <th scope="col">Customer Info</th> <th scope="col">Type</th> <th scope="col" class="text-end">Amount</th> <th scope="col" class="text-center">Status</th> </tr> </thead> <tbody> @forelse($recentTransactions ?? collect() as $transaction) <tr> <td class="small text-muted">{{ $transaction->created_at?->format('M d, Y') }}</td> <td> <div class="d-flex align-items-center"> @php $images = json_decode($transaction->property?->images, true); $firstImage = $images[0] ?? null; $imageUrl = asset('assets/img/placeholder-property.png'); if ($firstImage && Storage::disk('public')->exists($firstImage)) { $imageUrl = Storage::url($firstImage); } @endphp <img src="{{ $imageUrl }}" alt="Prop" width="60" height="45" class="me-2 rounded object-fit-cover"> <div> <div class="fw-bold text-dark">{{ Str::limit($transaction->property?->title ?? 'N/A', 35) }}</div> <div class="text-muted small">{{ Str::limit($transaction->property?->address ?? '', 40) }}</div> </div> </div> </td> <td> @if($transaction->user) <div class="d-flex align-items-center"> <img src="{{ $transaction->user->profile_image_url ?? asset('assets/img/profile.jpg') }}" alt="User" width="35" height="35" class="me-2 rounded-circle object-fit-cover"> <span class="fw-medium">{{ $transaction->user->name }}</span> </div> @else N/A @endif </td> <td> <span class="badge rounded-pill bg-{{ $transaction->type == 'sale' ? 'primary' : 'success' }} bg-opacity-75"> <i class="bi {{ $transaction->type == 'sale' ? 'bi-tag' : 'bi-key' }} me-1"></i> {{ ucfirst($transaction->type) }} </span> </td> <td class="text-end fw-medium"> {{ $transaction->property?->currency ?? 'USD' }} {{ number_format($transaction->amount ?? 0, 2) }} </td> <td class="text-center"> @php $statusConfig = [ 'completed' => ['color' => 'success', 'icon' => 'bi-check-circle-fill'], 'pending' => ['color' => 'warning', 'icon' => 'bi-hourglass-split'], 'failed' => ['color' => 'danger', 'icon' => 'bi-x-octagon-fill'], ]; $sConfig = $statusConfig[strtolower($transaction->status)] ?? ['color' => 'secondary', 'icon' => 'bi-question-circle']; @endphp <span class="badge bg-{{ $sConfig['color'] }}"> <i class="{{ $sConfig['icon'] }} me-1"></i> {{ ucfirst($transaction->status) }} </span> </td> </tr> @empty <tr> <td colspan="6" class="text-center text-muted py-5"> <i class="bi bi-journal-x fs-3 d-block mb-2"></i> No recent activity found. </td> </tr> @endforelse </tbody> </table> </div> @if (isset($recentTransactions) && $recentTransactions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $recentTransactions->hasPages()) <div class="d-flex justify-content-end pt-3 border-top mt-3"> {{ $recentTransactions->withQueryString()->links() }} </div> @endif </div> </div> </div> </div>
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
                            @if(isset($message) && $message)
                                <p class="text-muted lead mb-4">{{ $message }}</p>
                            @else
                                <p class="text-muted lead mb-4">This is your personal dashboard.</p>
                            @endif
                            <hr class="my-4">
                            <div class="row justify-content-center gy-3">
                                <div class="col-md-auto">
                                    <a href="{{ route('frontend.pricing') }}" class="btn btn-gold btn-lg w-100 lift">
                                        <i class="bi bi-tags-fill me-2"></i> View Plans & Become a Seller
                                    </a>
                                </div>
                                <div class="col-md-auto">
                                    <a href="{{ route('frontend.account') }}" class="btn btn-outline-secondary btn-lg w-100">
                                        <i class="bi bi-pencil-square me-2"></i> My Account
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartEndpoint = "{{ route('dashboard.chartData') }}";
            const currentRole = "{{ $role ?? 'unknown' }}";
            const isFreePlanForJs = {{ (isset($isFreePlan) && $isFreePlan === true) ? 'true' : 'false' }};

            // Log initial state
            console.log(`Dashboard JS: Role: ${currentRole}, FreePlan: ${isFreePlanForJs}, Endpoint: ${chartEndpoint}`);

            function createChart(canvasId, noDataElementId, chartType, chartData, chartOptions) {
                const canvas = document.getElementById(canvasId);
                const noDataEl = document.getElementById(noDataElementId);

                if (!canvas) {
                    console.warn(`Chart canvas '${canvasId}' not found in DOM.`);
                    if (noDataEl) {
                        noDataEl.textContent = `Chart container '${canvasId}' is missing.`;
                        noDataEl.style.display = 'block';
                    }
                    return;
                }
                 if (!noDataEl) {
                    console.warn(`No-data element '${noDataElementId}' not found for chart '${canvasId}'.`);
                }


                const ctx = canvas.getContext('2d');
                let hasSufficientData = false;
                if (chartData && chartData.datasets && chartData.datasets.length > 0) {
                    hasSufficientData = chartData.datasets.some(dataset =>
                        dataset.data && dataset.data.length > 0 && dataset.data.some(point => (typeof point === 'number' && point !== 0) || (typeof point === 'object' && point !== null && typeof point !== 'undefined'))
                    );
                }
                console.log(`Chart JS: Canvas: ${canvasId}, Type: ${chartType}, HasSufficientData: ${hasSufficientData}`);
                if (chartData && chartData.datasets) console.log(`Chart JS: Data for ${canvasId}:`, JSON.parse(JSON.stringify(chartData.datasets)));


                if (hasSufficientData) {
                    if (noDataEl) noDataEl.style.display = 'none';
                    canvas.style.display = 'block';
                    const existingChart = Chart.getChart(canvasId); // Use canvasId
                    if (existingChart) {
                        existingChart.destroy();
                    }
                    new Chart(ctx, { type: chartType, data: chartData, options: chartOptions });
                     console.log(`Chart JS: Chart '${canvasId}' rendered.`);
                } else {
                    canvas.style.display = 'none';
                    if (noDataEl) {
                        noDataEl.textContent = 'No sufficient data to display this chart.';
                        noDataEl.style.display = 'block';
                    }
                    console.warn(`Chart JS: Not rendering chart '${canvasId}' due to insufficient or zero data.`);
                }
            }

            const doughnutPieOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 15, boxWidth: 12, font: {size: 10} } }, tooltip: { bodyFont: {size: 11}, titleFont: {size: 12} } }, cutout: '60%' };
            const lineBarOptions = { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0, font: {size: 10} } }, x: { grid: { display: false }, ticks: {font: {size: 10}} } }, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false, bodyFont: {size: 11}, titleFont: {size: 12} } }, elements: { line: { tension: 0.3, borderWidth: 2 }, point: { radius: 3, hoverRadius: 5 } } };
            const rolesThatNeedCharts = ['admin', 'content_moderator', 'property_lister'];

            if (rolesThatNeedCharts.includes(currentRole)) {
                fetch(chartEndpoint)
                    .then(response => {
                        if (!response.ok) {
                            console.error(`Chart JS: Network error for ${chartEndpoint} - ${response.status} ${response.statusText}`);
                            return response.text().then(text => { throw new Error("Server error: " + text.substring(0, 300)) }); // Show only part of HTML error
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Chart JS: Data received from server:", data);

                        if (currentRole === 'admin' || currentRole === 'content_moderator') {
                            if (data.users && document.getElementById('usersStatusChart')) { createChart('usersStatusChart', 'usersStatusChartNoData', 'doughnut', { labels: ['Active', 'Inactive'], datasets: [{ data: [data.users.active ?? 0, data.users.inactive ?? 0], backgroundColor: ['#198754', '#dc3545'], hoverBackgroundColor: ['#157347', '#bb2d3b'] }] }, doughnutPieOptions); } else { console.warn("Chart JS: No data for usersStatusChart or canvas not found.")}
                            if (data.properties && document.getElementById('propertyTypesChart')) { createChart('propertyTypesChart', 'propertyTypesChartNoData', 'doughnut', { labels: ['For Sale', 'For Rent'], datasets: [{ data: [data.properties.sale ?? 0, data.properties.rent ?? 0, ], backgroundColor: ['#0d6efd', '#0dcaf0', '#ffc107'], hoverBackgroundColor: ['#0b5ed7', '#31d2f2', '#ffca2c'] }] }, doughnutPieOptions); } else { console.warn("Chart JS: No data for propertyTypesChart or canvas not found.")}
                            if (data.transactionsStatus && document.getElementById('transactionsStatusChart')) { createChart('transactionsStatusChart', 'transactionsStatusChartNoData', 'doughnut', { labels: ['Completed', 'Pending', 'Failed'], datasets: [{ data: [data.transactionsStatus.completed ?? 0, data.transactionsStatus.pending ?? 0, data.transactionsStatus.failed ?? 0], backgroundColor: ['#198754', '#ffc107', '#dc3545'], hoverBackgroundColor: ['#157347', '#ffca2c', '#bb2d3b'] }] }, doughnutPieOptions); } else { console.warn("Chart JS: No data for transactionsStatusChart or canvas not found.")}
                            if (data.monthlyTransactions && document.getElementById('monthlyTransactionsChart')) { createChart('monthlyTransactionsChart', 'monthlyTransactionsChartNoData', 'line', { labels: data.monthlyTransactions.labels ?? [], datasets: [{ label: 'Transactions', data: data.monthlyTransactions.counts ?? [], borderColor: '#0d6efd', backgroundColor: 'rgba(13, 110, 253, 0.1)', fill: true }] }, lineBarOptions); } else { console.warn("Chart JS: No data for monthlyTransactions or canvas not found.")}
                        }

                        if (currentRole === 'property_lister') {
                            if (data.myPropertiesStatus && document.getElementById('myPropertiesStatusChart')) {
                                createChart('myPropertiesStatusChart', 'myPropertiesStatusChartNoData', 'doughnut', {
                                    labels: data.myPropertiesStatus.labels ?? [],
                                    datasets: [{ data: data.myPropertiesStatus.counts ?? [], backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1'], hoverBackgroundColor: ['#218838', '#e0a800', '#c82333', '#138496', '#5a32a3'] }]
                                }, doughnutPieOptions);
                            } else { console.warn("Chart JS: No data for myPropertiesStatusChart or canvas not found.")}

                            const monthlyChartContainer = document.getElementById('monthlyTransactionsChartContainer');
                            if (!isFreePlanForJs) {
                                if (data.myMonthlyTransactions && document.getElementById('myMonthlyTransactionsChart')) {
                                    if(monthlyChartContainer) monthlyChartContainer.style.display = 'block';
                                    createChart('myMonthlyTransactionsChart', 'myMonthlyTransactionsChartNoData', 'line', {
                                        labels: data.myMonthlyTransactions.labels ?? [],
                                        datasets: [{ label: 'My Transactions', data: data.myMonthlyTransactions.counts ?? [], borderColor: '#28a745', backgroundColor: 'rgba(40, 167, 69, 0.1)', fill: true }]
                                    }, lineBarOptions);
                                } else {
                                    console.warn("Chart JS: No data for myMonthlyTransactions (Paid Plan) or canvas not found.");
                                    if(monthlyChartContainer) monthlyChartContainer.style.display = 'block'; // Keep container visible to show "no data"
                                    const noDataEl = document.getElementById('myMonthlyTransactionsChartNoData');
                                    if(noDataEl) {
                                        noDataEl.textContent = 'No transaction data available for this period.';
                                        noDataEl.style.display = 'block';
                                        const canvasEl = document.getElementById('myMonthlyTransactionsChart');
                                        if(canvasEl) canvasEl.style.display = 'none';
                                    }
                                }
                            } else {
                                // For free plan, ensure container is hidden
                                if(monthlyChartContainer) monthlyChartContainer.style.display = 'none';
                                console.log("Chart JS: Monthly transactions chart hidden for Free Plan.");
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Chart JS: Main Fetch/Processing Error for Charts:', error);
                        document.querySelectorAll('.no-data-message').forEach(el => { if (el.closest('.card-body') || el.closest('.card-1')) { el.textContent = 'Failed to load chart data.'; el.style.display = 'block'; } });
                        document.querySelectorAll('canvas').forEach(canvas => { if (canvas.closest('.card-body') || canvas.closest('.card-1')) canvas.style.display = 'none'; });
                    });
            } else {
                console.log("Chart JS: No charts configured for role:", currentRole);
                document.querySelectorAll('.no-data-message').forEach(el => { if (el.closest('.card-body') || el.closest('.card-1')) { el.textContent = 'Charts are not applicable for this role.'; el.style.display = 'block'; } });
                document.querySelectorAll('canvas').forEach(canvas => { if (canvas.closest('.card-body') || el.closest('.card-1')) canvas.style.display = 'none'; });
            }
        });
    </script>
@endsection