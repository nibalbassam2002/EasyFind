@extends('frontend.Layouts.frontend') 

@section('title', 'About Us - EasyFind')
@section('description', 'Learn more about EasyFind, our mission, vision, and the team behind our innovative real estate platform for Gaza.') {{-- وصف الصفحة للـ SEO --}}

@push('styles')
    <style>
         .card-height { 
            height: 120px ;
        }
        @media (min-width: 200px) and (max-width: 700px) {
            .card-height {
                height: 120px ;
            }
        }
        .redi {
            border-radius: 5%;
        }
        .featurette-heading { 
            margin-top: 2rem;
            margin-bottom: 0.5rem;
        }
        .featurette .lead, .featurette ul li {
            line-height: 1.7;
        }
        .featurette ul {
            padding-left: 1.2rem;
        }
        .featurette ul li {
            margin-bottom: 0.5rem;
        }
    </style>
@endpush

@section('content')
    
     <div class="container py-5">
         <div class="text-center mb-5">
             <h1 class="display-4 fw-bold">About EasyFind</h1>
             <p class="lead text-muted">Discover our story, mission, and what makes us unique.</p>
         </div>

         <div class="row featurette align-items-center"> 
            <div class="col-md-7">
                 <h2 class="featurette-heading fw-normal lh-1">Our Story</h2> 
                 <p class="lead">Easy Find is a smart real estate platform designed to simplify access to housing in conflict-affected areas, with a primary focus on the Gaza Strip. Our idea was born from the urgent need for flexible and rapid housing solutions following the widespread destruction caused by repeated wars. We set out to build an integrated digital system that directly and securely connects property seekers with property owners.</p>
            </div>
            <div class="col-md-5 text-center"> 

                 <img src="{{ asset('frontend/assets/logo black bg.jpg') }}" aria-label="Our Vision" class="redi bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" style="max-height: 400px;" alt="Real Estate Vision">
            </div>
        </div>
        <hr class="my-5">

        <div class="row featurette align-items-center">
            <div class="col-md-7">
                <h2 class="featurette-heading fw-normal lh-1">Our Mission</h2>
                <p class="lead">To restore hope and stability for individuals and families by providing technological solutions that help them find housing easily, affordably, and transparently—while ensuring privacy and security.</p>
                <h2 class="featurette-heading fw-normal lh-1 mt-4">Our Vision</h2> 
                <p class="lead">To become the leading real estate platform in Palestine and other crisis-affected areas, leveraging cutting-edge technology to support communities and foster reconstruction and social stability.</p>
            </div>
            <div class="col-md-5 order-md-1 text-center"> 
                <img src="{{ asset('assets/img/E2.jpg') }}" class="redi bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" style="max-height: 400px;" alt="Five Factors">
            </div>
        </div>
        <hr class="my-5">

         <div class="row featurette align-items-center">
            <div class="col-md-7 order-md-2"> 
                 <h2 class="featurette-heading fw-normal lh-1">What Makes Easy Find Unique?</h2>
                  <ul class="lead"> 
                    <li><span class="fw-bold">User-Friendly PWA Interface</span> that works on any device without needing a downloadable app.</li>
                    <li><span class="fw-bold">Advanced Search Engine</span> that allows users to filter properties by location, price, area, and amenities.</li>
                    <li><span class="fw-bold">Secure Electronic Payment</span> integrated with banking apps for safe, hassle-free transactions.</li>
                    <li><span class="fw-bold">Support for Vulnerable Groups</span> by offering affordable and diverse housing options.</li>
                    <li><span class="fw-bold">Reliable Ratings System</span> for both properties and owners to promote transparency and trust.</li>
                    <li><span class="fw-bold">Live Chat Feature</span> to enable direct and quick communication between owners and potential tenants or buyers.</li>
                  </ul>
            </div>
            <div class="col-md-5 order-md-1 text-center">
                <img src="{{ asset('assets/img/E1.jpg') }}" class="redi bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" style="max-height: 400px;" alt="Unique Features">
            </div>
        </div>
        <hr class="my-5">

        <div class="row featurette align-items-center">
            <div class="col-md-7">
                 <h2 class="featurette-heading fw-normal lh-1">Our Team</h2>
                 <p class="lead">We are Engineer Amir and Engineer Nibal, graduates of the University of Palestine. United by our passion for information technology and our desire to serve our community, we developed this project as a genuine contribution to improving the housing situation in Gaza.</p>
            </div>
            <div class="col-md-5 text-center">
                 <img src="{{ asset('assets/img/E3.jpg') }}" 
                      aria-label="Our Team" class="redi bd-placeholder-img bd-placeholder-img-lg featurette-image img-fluid mx-auto" style="max-height: 400px;" alt="Our Team">
            </div>
        </div>
        <hr class="my-5">

   
        <div class="container p-5 text-center bg-body-tertiary rounded-3">
            <img src="{{ asset('frontend/assets/logo-white.png') }}" height="100px" width="100px" alt="EasyFind Logo" class="mb-3"> {{-- تقليل حجم الشعار قليلاً --}}
            <h1 class="text-body-emphasis">Ready to start?</h1>
            <p class="col-lg-8 mx-auto fs-5 text-muted">
                Whether you're looking to buy, sell, or rent, EasyFind is here to guide you every step of the way.
            </p>
            <div class="d-inline-flex flex-wrap justify-content-center gap-2 mb-3"> {{-- استخدام flex-wrap للشاشات الصغيرة --}}
                {{-- تعديل الروابط لتستخدم route() --}}
                <a href="{{ route('register') }}" class="btn btn-gold btn-lg px-4 rounded-pill d-inline-flex align-items-center" type="button">
                    Start Your Trip <div style="width: 4px;"></div><i class="bi bi-arrow-right"></i>
                </a>
                <a href="{{ Auth::check() && Auth::user()->role !== 'customer' ? route('lister.properties.create') : '#' }}"
                   class="btn btn-outline-gold btn-lg px-4 rounded-pill"
                   type="button"
                   @if(Auth::guest() || (Auth::check() && Auth::user()->role === 'customer')) data-bs-toggle="modal" data-bs-target="#subscribeModal" @endif >
                    Or Start Selling
                </a>
            </div>
        </div>
    </div>
@endsection
