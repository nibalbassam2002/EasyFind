@extends('layouts.dashboard')

@section('title', 'User Details: ' . $user->name)

@section('breadcrumb-items')
    @parent
    <li class="breadcrumb-item">
        Admin</li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
    <li class="breadcrumb-item active">View User</li>
@endsection

@push('styles')
    <style>
        .profile-overview .label {
            font-weight: 600;
            color: #798eb3;
            min-width: 120px;
        }

        .profile-overview .row {
            margin-bottom: 1rem;
        }

        .profile-card img {
            max-width: 120px;
            height: 120px;
            object-fit: cover;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .card-title-custom {
            padding: 15px 20px 10px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #012970;
            font-family: "Poppins", sans-serif;
            border-bottom: 1px solid #eef0f2;
            margin-bottom: 15px;
        }

        .list-group-item small a,
        .table td a {
            font-weight: 500;
            color: #012970;
            text-decoration: none;
        }

        .list-group-item small a:hover,
        .table td a:hover {
            text-decoration: underline;
            color: #0d6efd;
        }

        .list-group-item .text-danger {
            font-style: italic;
        }

        .table-sm th,
        .table-sm td {
            padding: 0.5rem 0.6rem;
        }

        .card-subtitle.text-muted {
            font-size: 0.9rem;
            color: #6c757d !important;
        }
    </style>
@endpush

@section('contant')
    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">
                {{-- بطاقة الصورة والمعلومات الأساسية --}}
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <img src="{{ $user->profile_image ? asset('storage/images/' . $user->profile_image) : asset('assets/img/profile.jpg') }}"
                            alt="{{ $user->name }} Profile" class="rounded-circle mb-3">
                        <h2>{{ $user->name }}</h2>
                        <h3 class="text-muted small">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</h3>
                        <span class="badge bg-{{ strtolower($user->status) == 'active' ? 'success' : 'secondary' }} mt-1">
                            {{ ucfirst($user->status) }}
                        </span>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-gold">
                            <i class="bi bi-pencil-square"></i> Edit User
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                {{-- البطاقة الرئيسية للتفاصيل الأساسية --}}
                <div class="card">
                    <div class="card-body pt-3">
                        <div class="profile-overview">
                            <h5 class="card-title-custom">About</h5>
                            <p class="small fst-italic px-3 pb-2">
                                {{ $user->description ?? 'No description provided.' }}
                            </p>

                            <h5 class="card-title-custom">Profile Details</h5>
                            <div class="px-3 pb-2">
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Full Name</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->name }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->email }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Phone</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->phone ?? 'N/A' }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Role</div>
                                    <div class="col-lg-9 col-md-8">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Status</div>
                                    <div class="col-lg-9 col-md-8">{{ ucfirst($user->status) }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Location</div>
                                    <div class="col-lg-9 col-md-8">
                                        @if ($user->area && $user->area->governorate)
                                            {{ $user->area->governorate->name }} - {{ $user->area->name }}
                                        @elseif($user->area)
                                            {{ $user->area->name }} (No Gov. Info)
                                        @else
                                            N/A
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Address</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->address ?? 'N/A' }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Joined</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->created_at->format('F j, Y, g:i a') }}
                                        ({{ $user->created_at->diffForHumans() }})</div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Last Updated</div>
                                    <div class="col-lg-9 col-md-8">{{ $user->updated_at->format('F j, Y, g:i a') }}
                                        ({{ $user->updated_at->diffForHumans() }})</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ▼▼▼ بداية: أقسام التقارير الإضافية بناءً على الدور ▼▼▼ --}}
                @if (isset($viewData))

                    {{-- ==================== قسم الزبون (Customer) ==================== --}}
                    @if ($user->role === 'customer')
                        {{-- 1. بطاقة سجل المعاملات للزبون --}}
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-receipt-cutoff me-2"></i>Recent Transactions
                                    {{ isset($viewData['customerTransactions']) && $viewData['customerTransactions']->count() > 0 ? '(Last ' . $viewData['customerTransactions']->count() . ')' : '' }}
                                </h5>
                                @if (isset($viewData['customerTransactions']) && $viewData['customerTransactions']->isNotEmpty())
                                    <div class="table-responsive px-3 pb-2">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Property</th>
                                                    <th>Type</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($viewData['customerTransactions'] as $transaction)
                                                    <tr>
                                                        <td>
                                                            @if ($transaction->property)
                                                                <a href="{{ route('frontend.property.show', $transaction->property->id) }}  
                                                                    title="{{ $transaction->property->title }}">
                                                                    {{ Str::limit($transaction->property->title, 30) }}
                                                                </a>
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td><span
                                                                class="badge bg-{{ $transaction->type === 'sale' ? 'success' : 'info' }}">{{ ucfirst($transaction->type) }}</span>
                                                        </td>
                                                        <td>{{ $transaction->currency ?? 'USD' }}
                                                            {{ number_format($transaction->amount, 2) }}</td>
                                                        <td>{{ $transaction->created_at->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted px-3 pb-3">No transactions found for this user.</p>
                                @endif
                            </div>
                        </div>

                        {{-- 2. بطاقة العقارات المفضلة للزبون --}}
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-heart-fill me-2 text-danger"></i>Favorited
                                    Properties
                                    {{ isset($viewData['customerFavorites']) && $viewData['customerFavorites']->count() > 0 ? '(Last ' . $viewData['customerFavorites']->count() . ')' : '' }}
                                </h5>
                                @if (isset($viewData['customerFavorites']) && $viewData['customerFavorites']->isNotEmpty())
                                    <ul class="list-group list-group-flush px-3 pb-2">
                                        @foreach ($viewData['customerFavorites'] as $favProperty)
                                            <li
                                                class="list-group-item ps-0 py-2 d-flex justify-content-between align-items-center">
                                                <a href="{{ route('frontend.property.show', $favProperty->id) }}"
                                                     title="{{ $favProperty->title }}">
                                                    {{ Str::limit($favProperty->title, 40) }}
                                                </a>
                                                <small
                                                    class="text-muted">{{ $favProperty->pivot->created_at->diffForHumans() }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted px-3 pb-3">This user has not favorited any properties yet.</p>
                                @endif
                            </div>
                        </div>

                        {{-- 3. بطاقة الطلبات/الاستفسارات للزبون --}}
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-envelope-paper-fill me-2"></i>Recent Requests
                                    {{ isset($viewData['customerRequests']) && $viewData['customerRequests']->count() > 0 ? '(Last ' . $viewData['customerRequests']->count() . ')' : '' }}
                                </h5>
                                @if (isset($viewData['customerRequests']) && $viewData['customerRequests']->isNotEmpty())
                                    <div class="table-responsive px-3 pb-2">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Property</th>
                                                    <th>Subject/Type</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($viewData['customerRequests'] as $requestItem)
                                                    <tr>
                                                        <td>
                                                            @if ($requestItem->property)
                                                                <a href="{{ route('frontend.property.show', $actionedProperty->id) }}"  title="{{ $actionedProperty->title }}">{{ Str::limit($actionedProperty->title, 30) }}</a>
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td>{{ Str::limit($requestItem->subject ?? ($requestItem->type ?? 'Inquiry'), 25) }}
                                                        </td>
                                                        <td>{{ $requestItem->created_at->format('d M Y') }}</td>
                                                        <td><span
                                                                class="badge bg-info">{{ ucfirst($requestItem->status ?? 'New') }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted px-3 pb-3">No requests or inquiries found for this user.</p>
                                @endif
                            </div>
                        </div>
                    @endif {{-- نهاية شرط الزبون --}}

                    {{-- ==================== قسم مشرف المحتوى (Content Moderator) ==================== --}}
                    @if ($user->role === 'content_moderator') 
                        {{-- بطاقة إحصائيات مراجعة العقارات مع التفاصيل --}}
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-card-checklist me-2"></i>Property Review Stats
                                </h5>
                                <div class="profile-overview px-3 pb-2">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-7 label">Total Properties Reviewed:</div>
                                        <div class="col-lg-4 col-md-5 fw-bold">
                                            {{ $viewData['total_properties_reviewed'] ?? 0 }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-7 label text-success">Properties Approved:</div>
                                        <div class="col-lg-4 col-md-5 text-success fw-bold">
                                            {{ $viewData['properties_approved_count'] ?? 0 }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-8 col-md-7 label text-danger">Properties Rejected:</div>
                                        <div class="col-lg-4 col-md-5 text-danger fw-bold">
                                            {{ $viewData['properties_rejected_count'] ?? 0 }}</div>
                                    </div>
                                </div>

                                {{-- عرض تفاصيل العقارات المرفوضة --}}
                                @if (isset($viewData['recent_rejected_properties_details']) &&
                                        $viewData['recent_rejected_properties_details']->isNotEmpty())
                                    <hr class="mx-3 my-3">
                                    <h6 class="card-subtitle mt-3 mb-2 text-muted small px-3">Recent Rejections:</h6>
                                    <ul class="list-group list-group-flush px-3 pb-2">
                                        @foreach ($viewData['recent_rejected_properties_details'] as $rejectedProperty)
                                            <li class="list-group-item ps-0 py-2">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div><a href="{{ route('frontend.property.show', $rejectedProperty->id) }}"
                                                             class="fw-semibold d-block"
                                                            title="{{ $rejectedProperty->title }}">
                                                            {{ Str::limit($rejectedProperty->title, 35) }}
                                                        </a>
                                                        @if ($rejectedProperty->rejection_reason)
                                                            <small class="text-danger fst-italic">Reason:
                                                            {{ Str::limit($rejectedProperty->rejection_reason, 60) }}</small>@else<small
                                                                class="text-muted fst-italic">No reason provided.</small>
                                                        @endif
                                                    </div><small
                                                        class="text-muted text-nowrap ms-2">{{ $rejectedProperty->moderated_at?->diffForHumans() }}</small>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif(isset($viewData['recent_rejected_properties_details']))
                                    <hr class="mx-3 my-3">
                                    <p class="text-muted px-3 pb-2 small">No properties recently rejected by this
                                        moderator.</p>
                                @endif

@if(isset($viewData['feedback_handled_count'])) 
                            <div class="card mt-4">
                                 <div class="card-body pt-3">
                                    <h5 class="card-title-custom"><i class="bi bi-chat-left-dots-fill me-2"></i>Feedback Handling Stats</h5>
                                    <div class="profile-overview px-3 pb-2">
                                        <div class="row">
                                            <div class="col-lg-8 col-md-7 label">Total Feedback Handled:</div>
                                            <div class="col-lg-4 col-md-5 fw-bold">{{ $viewData['feedback_handled_count'] ?? 0 }}</div>
                                        </div>
                                    </div>
                                    @if(isset($viewData['recent_handled_feedbacks']) && $viewData['recent_handled_feedbacks']->isNotEmpty())
                                        <hr class="mx-3 my-3">
                                        <h6 class="card-subtitle mt-3 mb-2 text-muted small px-3">Recent Handled Feedback:</h6>
                                        <ul class="list-group list-group-flush px-3 pb-2">
                                            @foreach($viewData['recent_handled_feedbacks'] as $feedback)
                                                <li class="list-group-item ps-0 py-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            {{-- تأكد أن لديك مسار 'moderator.feedback.show' أو عدّله --}}
                                                            <a href="{{ route('moderator.feedback.show', $feedback->id) }}"
                                                               class="fw-semibold d-block" title="{{ $feedback->subject ?? 'View Feedback Details' }}">
                                                                {{ Str::limit($feedback->subject ?? ($feedback->type ?? 'Feedback #'.$feedback->id), 35) }}
                                                            </a>
                                                            <small class="d-block text-muted">From: {{ $feedback->user?->name ?? 'N/A' }}</small>
                                                            <small class="d-block text-info">Status: {{ ucfirst($feedback->status ?? 'Unknown') }}</small>
                                                        </div>
                                                        <small class="text-muted text-nowrap ms-2">{{ $feedback->replied_at?->diffForHumans() ?? $feedback->updated_at?->diffForHumans() }}</small>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif(isset($viewData['recent_handled_feedbacks']))
                                        @if(($viewData['feedback_handled_count'] ?? 0) > 0)
                                            <hr class="mx-3 my-3">
                                            <p class="text-muted px-3 pb-2 small">No recent feedback details to display.</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @else 
                            <div class="card mt-4">
                                <div class="card-body pt-3">
                                    <h5 class="card-title-custom"><i class="bi bi-chat-left-dots-fill me-2"></i>Feedback Handling Stats</h5>
                                    <p class="text-muted px-3 pb-3">No feedback handled by this moderator yet.</p>
                                </div>
                            </div>
                        @endif

                        {{-- بطاقة أحدث إجراءات الإشراف --}}
                        @if (isset($viewData['recent_moderation_actions_detailed']) &&
                                $viewData['recent_moderation_actions_detailed']->isNotEmpty())
                            <div class="card mt-4">
                                <div class="card-body pt-3">
                                    <h5 class="card-title-custom"><i class="bi bi-clock-history me-2"></i>Recent
                                        Moderation Actions (Properties)</h5>
                                    <div class="table-responsive px-3 pb-2">
                                        <table class="table table-sm table-hover">
                                            {{-- ... (هيكل جدول أحدث الإجراءات) ... --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif {{-- نهاية @if ($user->role === 'content_moderator') --}}
                 @if ($user->role === 'property_lister')
                     
                 @endif
                 @if ($user->role === 'property_lister')
                    @if(isset($viewData['listerSubscriptionDetails']))
                            @php $subDetails = $viewData['listerSubscriptionDetails']; @endphp
                            <div class="card mt-4">
                                <div class="card-body pt-3">
                                    <h5 class="card-title-custom">
                                        <i class="bi bi-patch-check-fill me-2 {{ $subDetails['is_free_plan'] ? 'text-primary' : 'text-success' }}"></i>
                                        Current Subscription
                                    </h5>
                                    <div class="profile-overview px-3 pb-2">
                                        <div class="row">
                                            <div class="col-md-4 label">Plan Name:</div>
                                            <div class="col-md-8 fw-bold">
                                                {{ $subDetails['plan_name'] ?? 'N/A' }}
                                                @if(isset($subDetails['is_free_plan']) && $subDetails['is_free_plan'])
                                                    <span class="badge bg-primary ms-2">Free</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 label">Subscription Status:</div>
                                            <div class="col-md-8">
                                                <span class="badge bg-{{ strtolower($subDetails['status'] ?? '') === 'active' ? 'success' : 'warning' }}">
                                                    {{ $subDetails['status'] ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                        @if(isset($subDetails['starts_at']))
                                            <div class="row">
                                                <div class="col-md-4 label">Starts At:</div>
                                                <div class="col-md-8">{{ $subDetails['starts_at'] }}</div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-md-4 label">Expires At:</div>
                                            <div class="col-md-8">{{ $subDetails['ends_at'] ?? 'Not Applicable' }}</div>
                                        </div>
                                        <hr class="my-2">
                                        @if(isset($subDetails['properties_limit']) && $subDetails['properties_limit'] > 0)
                                            <div class="row">
                                                <div class="col-md-4 label">Property Listings:</div>
                                                <div class="col-md-8">
                                                    {{ $subDetails['properties_listed'] ?? 0 }} / {{ $subDetails['properties_limit'] }} used
                                                    <span class="small text-muted">({{ $subDetails['properties_remaining'] ?? $subDetails['properties_limit'] }} remaining)</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-md-4 label">Property Listings:</div>
                                                <div class="col-md-8">Unlimited</div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-md-4 label">Allowed Property Types:</div>
                                            <div class="col-md-8">{{ $subDetails['allowed_property_types'] ?? 'Not Specified' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 label">Featured Slots:</div>
                                            <div class="col-md-8">{{ $subDetails['featured_slots_limit'] ?? 0 }} available</div>
                                            {{-- يمكنك إضافة عدد المستخدم منها إذا كنت تتتبعه --}}
                                        </div>
                                    </div>
                                    {{-- يمكنك إضافة زر "إدارة الاشتراك" للأدمن هنا لاحقًا --}}
                                    {{-- <div class="card-footer text-end">
                                        <a href="#" class="btn btn-sm btn-outline-primary">Manage Subscription</a>
                                    </div> --}}
                                </div>
                            </div>
                        @else
                            <div class="card mt-4">
                                <div class="card-body pt-3">
                                    <h5 class="card-title-custom"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Subscription Information</h5>
                                    <p class="text-muted px-3 pb-3">This property lister does not currently have an active subscription or plan details are unavailable.</p>
                                    <div class="px-3 pb-3">
                                        <a href="{{ route('frontend.pricing') }}"  class="btn btn-sm btn-info">View Available Plans</a>
                                        {{-- يمكنك إضافة زر للأدمن لتعيين خطة لهذا المستخدم --}}
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(isset($viewData['listerStats']))
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-bar-chart-line-fill me-2"></i>Seller Performance</h5>
                                <div class="profile-overview px-3 pb-2">
                                    <div class="row"><div class="col-md-7 label">Total Properties Listed:</div><div class="col-md-5 fw-bold">{{ $viewData['listerStats']['total_properties'] ?? 0 }}</div></div>
                                    <div class="row"><div class="col-md-7 label">Active (Approved):</div><div class="col-md-5 fw-bold text-success">{{ $viewData['listerStats']['approved_properties'] ?? 0 }}</div></div>
                                    <div class="row"><div class="col-md-7 label">Pending Review:</div><div class="col-md-5 fw-bold text-warning">{{ $viewData['listerStats']['pending_properties'] ?? 0 }}</div></div>
                                    <div class="row"><div class="col-md-7 label">Sold Properties:</div><div class="col-md-5 fw-bold">{{ $viewData['listerStats']['sold_properties'] ?? 0 }}</div></div>
                                    <div class="row"><div class="col-md-7 label">Rented Properties:</div><div class="col-md-5 fw-bold">{{ $viewData['listerStats']['rented_properties'] ?? 0 }}</div></div>
                                    
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(isset($viewData['listerProperties']))
                        <div class="card mt-4">
                            <div class="card-body pt-3">
                                <h5 class="card-title-custom"><i class="bi bi-collection-fill me-2"></i>Listed Properties {{ $viewData['listerProperties']->count() > 0 ? '(Showing Last '.$viewData['listerProperties']->count().')' : '' }}</h5>
                                @if($viewData['listerProperties']->isNotEmpty())
                                    <div class="table-responsive px-3 pb-2">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th class="text-end">Price</th>
                                                    <th>Status</th>
                                                    <th>Added</th>
                                                    <th class="text-center">View</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($viewData['listerProperties'] as $property)
                                                <tr>
                                                    <td>
                                                        {{-- رابط لعرض العقار في الواجهة الأمامية --}}
                                                        <a href="{{ route('frontend.property.show', $property->id) }}"  title="{{ $property->title }}">{{ Str::limit($property->title, 30) }}</a>
                                                    </td>
                                                    <td>{{ $property->category?->name ?? 'N/A'}}</td>
                                                    <td class="text-end">{{$property->currency}} {{ number_format($property->price,0) }}</td>
                                                    <td>
                                                        @php
                                                            $statusClass = match(strtolower($property->status)) {
                                                                'approved' => 'success',
                                                                'pending' => 'warning',
                                                                'rejected' => 'danger',
                                                                'sold' => 'primary',
                                                                'rented' => 'info',
                                                                default => 'secondary',
                                                            };
                                                        @endphp
                                                        <span class="badge bg-{{$statusClass}}">{{ ucfirst($property->status) }}</span>
                                                    </td>
                                                    <td>{{ $property->created_at->format('d M Y') }}</td>
                                                    <td class="text-center">
                                                        {{-- رابط لعرض تفاصيل العقار من لوحة تحكم الأدمن (إذا كان لديك مسار مخصص) --}}
                                                        {{-- أو يمكنك استخدام نفس رابط الواجهة الأمامية --}}
                                                        <a href="{{ route('frontend.property.show', $property->id) }}"  class="btn btn-sm btn-outline-secondary px-2 py-1" title="View on Site">
                                                            <i class="bi bi-box-arrow-up-right"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($user->properties()->count() > $viewData['listerProperties']->count() && $viewData['listerProperties']->count() > 0)
                                        <div class="text-center mt-2 pb-2">
                                            {{-- رابط لصفحة الأدمن التي تعرض كل عقارات هذا البائع (ستحتاج لإنشاء هذا المسار والصفحة) --}}
                                            {{-- <a href="{{ route('admin.user.properties', $user->id) }}">View All {{ $user->properties()->count() }} Properties</a> --}}
                                            <small class="text-muted">Showing last {{ $viewData['listerProperties']->count() }} of {{ $user->properties()->count() }} total properties by this lister.</small>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted px-3 pb-3">This user has not listed any properties yet.</p>
                                @endif
                            </div>
                        </div>
                    @endif
                    @endif
                 @endif
          
            </div>
        </div>
    </section>
@endsection

