@extends('frontend.Layouts.frontend') 

@section('title', 'Our Pricing Plans - EasyFind')
@section('description', 'Choose a subscription plan that fits your needs to start selling or renting properties on EasyFind.')

@push('styles')
<style>
    .pricing-header { padding: 3rem 1.5rem; text-align: center; }
    .pricing-header h1 { font-weight: 700; }
    .card-deck .card {
        min-width: 260px; 
        border: 1px solid #dee2e6;
        border-radius: 0.75rem;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-deck .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    }
    .card-deck .card .card-header {
        background-color: #eecb05; 
        color: #fff;
        font-weight: 600;
        border-bottom: none;
        border-top-left-radius: calc(0.75rem - 1px);
        border-top-right-radius: calc(0.75rem - 1px);
    }
    .card-deck .card .card-title { font-size: 2.5rem; font-weight: 700; }
    .card-deck .card .list-unstyled li { padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
    .card-deck .card .list-unstyled li:last-child { border-bottom: none; }
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
    .compare-section { margin-top: 4rem; margin-bottom: 4rem; }
    .table-compare th, .table-compare td { vertical-align: middle; text-align: center; }
    .table-compare th { font-weight: 600; }
    .table-compare .bi-check-lg { color: #198754; font-size: 1.5rem; } /* أخضر لعلامة الصح */
    .table-compare .plan-name-header { background-color: #f8f9fa; }
    .table-compare .feature-name { text-align: left; font-weight: 500; }
</style>
@endpush

@section('content')
    <div class="pricing-header">
        <p>Step 1 of 3</p>
        <h1 class="display-4">Pricing Plans</h1>
        <p class="lead">Choose the plan that's right for you and start listing your properties.</p>
    </div>

    <div class="container mb-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 justify-content-center card-deck">
            @forelse ($plans as $plan)
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
                            <ul class="list-unstyled mt-3 mb-4">
                                @if($plan->description)
                                    <li>{{ $plan->description }}</li>
                                @endif

                                @if($plan->features)
                                    @if(isset($plan->features['max_properties']))
                                        <li>{{ $plan->features['max_properties'] }} Properties Allowed</li>
                                    @endif
                                    @if(isset($plan->features['allowed_types']))
                                        <li>Types: {{ implode(', ', array_map('ucfirst', $plan->features['allowed_types'])) }}</li>
                                    @endif
                                    {{-- يمكنك إضافة المزيد من الميزات --}}
                                     @if(isset($plan->features['property_view']) && $plan->features['property_view'])
                                        <li>Property View</li>
                                    @endif
                                    @if(isset($plan->features['property_details']) && $plan->features['property_details'])
                                        <li>Property Details</li>
                                    @endif
                                     @if(isset($plan->features['simple_search']) && $plan->features['simple_search'])
                                        <li>Simple Search</li>
                                    @endif
                                     @if(isset($plan->features['featured_slots']) && $plan->features['featured_slots'] > 0)
                                        <li>{{ $plan->features['featured_slots'] }} Featured Slots</li>
                                    @endif
                                @endif
                                <li>{{ $plan->duration_in_days == 0 ? 'Lifetime Access' : ($plan->duration_in_days == 1 ? '1 Day Access' : $plan->duration_in_days . ' Days Access') }}</li>
                            </ul>
                            {{-- زر اختيار الخطة --}}
                             {{-- سنعدل الرابط لاحقاً ليمرر plan_slug --}}
                            <a href="{{ route('frontend.checkout.payment_method', ['plan_slug' => $plan->slug]) }}"
                               class="w-100 btn btn-lg btn-choose-plan mt-auto">
                                {{ $plan->price == 0 ? 'Get Started' : 'Choose Plan' }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No pricing plans available at the moment. Please check back later.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- قسم مقارنة الخطط  --}}
        <div class="compare-section">
            <h2 class="text-center mb-4">Compare plans</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered table-compare">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Feature</th>
                            @foreach ($plans as $plan) {{-- عرض أسماء الخطط كأعمدة --}}
                                <th class="plan-name-header">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                       
                        @php
                            $allPossibleFeatures = [
                                'personal_use' => 'For Personal Use',
                                'good_seller' => 'For Good Seller',
                                'big_company' => 'For Big Company',
                                'market_analysis' => 'Market analysis',
                                'unlimited_listings' => 'Unlimited Listings', // تغيير الاسم
                                'extra_security' => 'Extra security',
                                // أضف ميزات أخرى هنا بنفس الطريقة
                                'max_properties' => 'Max Properties Number',
                                'featured_slots' => 'Featured Slots',
                            ];
                        @endphp
                        @foreach($allPossibleFeatures as $featureKey => $featureLabel)
                        <tr>
                            <td class="feature-name">{{ $featureLabel }}</td>
                            @foreach ($plans as $plan)
                                <td>
                                    @if(isset($plan->features[$featureKey]))
                                        @if(is_bool($plan->features[$featureKey]) && $plan->features[$featureKey])
                                            <i class="bi bi-check-lg"></i>
                                        @elseif(!is_bool($plan->features[$featureKey]))
                                            {{-- عرض القيمة إذا لم تكن boolean (مثل عدد العقارات) --}}
                                            {{ $plan->features[$featureKey] }}
                                        @endif
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