@extends('frontend.Layouts.frontend')

@section('title', 'Our Pricing Plans - EasyFind')
@section('description',
    'Choose a subscription plan that fits your needs to start selling or renting properties on
    EasyFind.')

    @push('styles')
        <style>
            .pricing-header {
                padding: 3rem 1.5rem;
                text-align: center;
            }

            .pricing-header h1 {
                font-weight: 700;
            }

            .card-deck .card {
                min-width: 260px;
                border: 1px solid #dee2e6;
                border-radius: 0.75rem;
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            }

            .card-deck .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }

            .card-deck .card .card-header {
                background-color: #eecb05;
                color: #fff;
                font-weight: 600;
                border-bottom: none;
                border-top-left-radius: calc(0.75rem - 1px);
                border-top-right-radius: calc(0.75rem - 1px);
            }

            .card-deck .card .card-title {
                font-size: 2.5rem;
                font-weight: 700;
            }

            .card-deck .card .list-unstyled li {
                padding: 0.5rem 0;
                border-bottom: 1px solid #f0f0f0;
            }

            .card-deck .card .list-unstyled li:last-child {
                border-bottom: none;
            }

            .btn-choose-plan {
                background-color: #FFD700;
                border-color: #FFD700;
                color: #fff;
                font-weight: 500;
            }

            .btn-choose-plan:hover {
                background-color: #FFD700;
                border-color: #FFD700;
            }

            .compare-section {
                margin-top: 4rem;
                margin-bottom: 4rem;
            }

            .table-compare th,
            .table-compare td {
                vertical-align: middle;
                text-align: center;
            }

            .table-compare th {
                font-weight: 600;
            }

            .table-compare .bi-check-lg {
                color: #198754;
                font-size: 1.5rem;
            }

            /* أخضر لعلامة الصح */
            .table-compare .plan-name-header {
                background-color: #f8f9fa;
            }

            .table-compare .feature-name {
                text-align: left;
                font-weight: 500;
            }
        </style>
    @endpush

@section('content')
    <div class="pricing-header">
        <p>Step 1 of 3</p> {{-- يمكنك إزالة هذا إذا لم تعد تستخدمه --}}
        <h1 class="display-4">Pricing Plans</h1>
        <p class="lead">Choose the plan that's right for you and start listing your properties.</p>
    </div>

    <div class="container mb-5">
        {{-- عرض رسائل الخطأ/المعلومات/النجاح من الكنترولر (مهم) --}}
        @if(session('error'))
            <div class="alert alert-danger mt-3 col-md-8 mx-auto text-center">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info mt-3 col-md-8 mx-auto text-center">{{ session('info') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success mt-3 col-md-8 mx-auto text-center">{{ session('success') }}</div>
        @endif

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 justify-content-center card-deck">
            @forelse ($plans as $plan)
                @php
                    // هذه المتغيرات يجب أن يتم تمريرها من FrontendController@showPricingPlans
                    // $userHasUsedFreePlan (boolean)
                    // $freePlanSlug (string - الـ slug الخاص بالخطة المجانية، مثلاً 'free')

                    $isThisTheFreePlan = (isset($freePlanSlug) && strtolower($plan->slug) === strtolower($freePlanSlug));
                    $shouldHideThisFreePlan = ($isThisTheFreePlan && isset($userHasUsedFreePlan) && $userHasUsedFreePlan);
                @endphp

                {{-- ▼▼▼ الشرط الرئيسي: إذا لم تكن هذه الخطة المجانية التي يجب إخفاؤها، اعرضها ▼▼▼ --}}
                @if (!$shouldHideThisFreePlan)
                    <div class="col">
                        <div class="card h-100 text-center">
                            <div class="card-header py-3">
                                <h4 class="my-0 fw-normal">{{ $plan->name }}</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h1 class="card-title pricing-card-title my-3">
                                    ${{ number_format($plan->price, 2) }}
                                    <small class="text-muted fw-light">/mo</small>
                                </h1>
                                <ul class="list-unstyled mt-3 mb-4 flex-grow-1"> {{-- flex-grow-1 لجعل القائمة تأخذ المساحة المتاحة --}}
                                    @if ($plan->description)
                                        <li class="text-muted small">{{ $plan->description }}</li> {{-- جعل الوصف أصغر --}}
                                    @endif

                                    @if ($plan->features)
                                        @if (isset($plan->features['max_properties']))
                                            <li>{{ $plan->features['max_properties'] == 0 ? 'Unlimited' : $plan->features['max_properties'] }} Properties Allowed</li>
                                        @endif
                                        @if (isset($plan->features['allowed_types']))
                                            <li>Types:
                                                {{ is_array($plan->features['allowed_types']) ? (in_array('all', array_map('strtolower', $plan->features['allowed_types'])) ? 'All' : implode(', ', array_map('ucfirst', $plan->features['allowed_types']))) : ucfirst($plan->features['allowed_types']) }}
                                            </li>
                                        @endif
                                        @if (isset($plan->features['property_view']) && $plan->features['property_view'])
                                            <li>Property View</li>
                                        @endif
                                        @if (isset($plan->features['property_details']) && $plan->features['property_details'])
                                            <li>Property Details</li>
                                        @endif
                                        @if (isset($plan->features['simple_search']) && $plan->features['simple_search'])
                                            <li>Simple Search</li>
                                        @endif
                                        @if (isset($plan->features['featured_slots']) && $plan->features['featured_slots'] > 0)
                                            <li>{{ $plan->features['featured_slots'] }} Featured Slots</li>
                                        @endif
                                    @endif
                                    <li>{{ $plan->duration_in_days == 0 ? 'Lifetime Access' : ($plan->duration_in_days == 1 ? '1 Day Access' : $plan->duration_in_days . ' Days Access') }}
                                    </li>
                                </ul>

                                {{-- زر اختيار الخطة --}}
                                @auth
                                    <a href="{{ route('frontend.checkout.payment_method', ['plan_slug' => $plan->slug]) }}"
                                    class="w-100 btn btn-lg btn-choose-plan mt-auto">
                                        {{ $plan->price == 0 ? 'Get Started' : 'Choose Plan' }}
                                    </a>
                                @else
                                    <a href="{{ route('login') }}?redirect_to={{ urlencode(route('frontend.pricing')) }}" class="w-100 btn btn-lg btn-outline-primary mt-auto">
                                        Login to Subscribe
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                {{-- ▼▼▼ بدلاً من إخفاء الخطة المجانية تمامًا، يمكننا عرضها مع زر معطل ورسالة ▼▼▼ --}}
                @elseif($isThisTheFreePlan && $shouldHideThisFreePlan)
                    <div class="col">
                        <div class="card h-100 text-center border-light bg-light"> {{-- تغيير مظهر البطاقة المعطلة --}}
                            <div class="card-header py-3 bg-secondary text-white">
                                <h4 class="my-0 fw-normal">{{ $plan->name }}</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h1 class="card-title pricing-card-title my-3 text-muted">
                                    ${{ number_format($plan->price, 2) }}
                                    <small class="text-muted fw-light">/mo</small>
                                </h1>
                                <ul class="list-unstyled mt-3 mb-4 flex-grow-1">
                                   {{-- ... (يمكن عرض الميزات هنا أيضًا أو رسالة عامة) ... --}}
                                   <li>You have already used this plan.</li>
                                </ul>
                                <button class="w-100 btn btn-lg btn-secondary mt-auto" disabled>
                                    Plan Used
                                </button>
                                <p class="btn-disabled-explanation">You can subscribe to this plan only once.</p>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- ▲▲▲ نهاية الشرط ▲▲▲ --}}
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No pricing plans available at the moment. Please check back later.
                    </div>
                </div>
            @endforelse
        </div>       
        {{-- قسم مقارنة الخطط --}}
        <div class="compare-section mt-5"> {{-- أضفت margin-top --}}
            <h2 class="text-center mb-4">Compare plans</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-compare">
                    <thead>
                        <tr>
                            <th style="width: 30%;" class="bg-light">Feature</th> {{-- خلفية خفيفة للعنوان --}}
                            @foreach ($plans as $plan)
                                <th class="plan-name-header text-center bg-light">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // قائمة بجميع الميزات التي تريد عرضها، مع مفاتيحها كما هي في JSON
                            // والنص الذي سيظهر في الجدول
                            $allDisplayFeatures = [
                                // المفاتيح من الـ Seeder التي قيمها boolean
                                'property_view' => 'Property View',
                                'property_details' => 'Property Details',
                                'simple_search' => 'Simple Search',
                                'advanced_search_filters' => 'Advanced Search Filters',
                                'agent_profile' => 'Agent Profile',
                                'direct_contact_info' => 'Direct Contact Info',
                                'analytics_dashboard' => 'Analytics Dashboard',

                                // المفاتيح التي قيمها نصية (مثل مستوى الدعم)
                                'support_level' => 'Support Level',

                                // الميزات التي تأتي من مفاتيح مخصصة في features أو أعمدة منفصلة (إذا كنت ستفعل ذلك)
                                // حاليًا، هذه موجودة أيضًا داخل features في الـ Seeder الخاص بك
                                'max_properties' => 'Max Properties Number',
                                'featured_slots' => 'Featured Slots',
                                'allowed_types' => 'Allowed Property Types',
                            ];
                        @endphp

                        @foreach ($allDisplayFeatures as $featureKey => $featureLabel)
                            <tr>
                                <td class="feature-name fw-bold">{{ $featureLabel }}</td> {{-- جعل اسم الميزة bold --}}
                                @foreach ($plans as $plan)
                                    <td class="text-center">
                                        @php
                                            // الوصول إلى قيمة الميزة من مصفوفة 'features'
                                            // $plan->features ستكون مصفوفة بفضل $casts في الموديل
                                            $featureValue = null;
                                            if (
                                                is_array($plan->features) &&
                                                array_key_exists($featureKey, $plan->features)
                                            ) {
                                                $featureValue = $plan->features[$featureKey];
                                            }
                                        @endphp

                                        @if ($featureKey === 'allowed_types')
                                            {{-- معاملة خاصة لعرض مصفوفة أنواع العقارات --}}
                                            @if (is_array($featureValue) && !empty($featureValue))
                                                {{ implode(', ', $featureValue) }}
                                            @elseif (!is_null($featureValue) && $featureValue !== '')
                                                {{ $featureValue }} {{-- إذا لم تكن مصفوفة ولكنها نص --}}
                                            @else
                                                <i class="bi bi-dash-lg text-muted fs-5"></i>
                                            @endif
                                        @elseif (is_bool($featureValue))
                                            @if ($featureValue)
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @else
                                                <i class="bi bi-x-lg text-danger fs-5"></i>
                                            @endif
                                        @elseif (!is_null($featureValue) && $featureValue !== '')
                                            {{ $featureValue }} {{-- يعرض الأرقام (مثل 5, 10) أو النصوص (مثل "Basic") --}}
                                        @else
                                            <i class="bi bi-dash-lg text-muted fs-5"></i> {{-- إذا لم يتم تعريف الميزة لهذه الخطة --}}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    {{-- أي JavaScript خاص بهذه الصفحة لاحقاً --}}
@endpush
