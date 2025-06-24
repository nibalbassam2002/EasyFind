@extends('frontend.Layouts.frontend')

@section('title', '419 - Page Expired')

{{-- إضافة قسم CSS خاص لهذه الصفحة --}}
@push('styles')
<style>
    .timer-icon {
        font-size: 6rem; /* حجم الأيقونة الكلي */
        color: #ffc107; /* لون ذهبي متناسق مع تصميمك */
        position: relative;
        display: inline-block;
        animation: ring 2s ease-in-out infinite;
    }

    /* يمكنك اختيار تصميم من الاثنين أدناه */

    /* === التصميم الأول: ساعة بسيطة (أيقونة Bootstrap مع حركة) === */
    /* هذا هو الأسهل والأكثر توافقاً */
    @keyframes ring {
        0% { transform: rotate(0deg); }
        10% { transform: rotate(15deg); }
        20% { transform: rotate(-10deg); }
        30% { transform: rotate(5deg); }
        40% { transform: rotate(-5deg); }
        50%, 100% { transform: rotate(0deg); }
    }

    /* === التصميم الثاني: ساعة رملية (أكثر تعقيداً وجمالاً) === */
    /* إذا أردت استخدام هذا، استبدل أيقونة الساعة في HTML بالكود أدناه */
    .hourglass {
        position: relative;
        display: inline-block;
        width: 80px;
        height: 120px;
        animation: spin 4s linear infinite;
    }
    .hourglass-top,
    .hourglass-bottom {
        position: absolute;
        left: 0;
        width: 80px;
        height: 60px;
        border: 4px solid #6c757d; /* لون رمادي للإطار */
        overflow: hidden;
    }
    .hourglass-top {
        top: 0;
        border-radius: 50% 50% 0 0;
        transform: rotateX(180deg);
    }
    .hourglass-bottom {
        bottom: 0;
        border-radius: 0 0 50% 50%;
    }
    .sand {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 35px 55px 35px;
        border-color: transparent transparent #ffc107 transparent; /* لون الرمل الذهبي */
        animation: flow 4s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    @keyframes flow {
        0%, 100% { height: 55px; } /* ممتلئة */
        50% { height: 0; } /* فارغة */
    }
</style>
@endpush


@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">

            {{-- ====================================================== --}}
            {{-- ▼▼▼ الخيار الأول: استخدم أيقونة ساعة بسيطة (موصى به) ▼▼▼ --}}
            {{-- ====================================================== --}}
            <div class="mb-4">
                <i class="bi bi-clock-history timer-icon"></i>
            </div>
            
            {{-- ====================================================== --}}
            {{-- ▼▼▼ الخيار الثاني: استخدم الساعة الرملية (إذا أردت) ▼▼▼ --}}
            {{-- ====================================================== --}}
            {{-- لإظهار الساعة الرملية، احذف كود الخيار الأول وضع هذا بدلاً منه --}}
            {{--
            <div class="mb-5">
                <div class="hourglass">
                    <div class="hourglass-top"><div class="sand"></div></div>
                    <div class="hourglass-bottom"><div class="sand"></div></div>
                </div>
            </div>
            --}}


            <h1 class="display-4 fw-bold text-body-emphasis">Oops! Page Expired</h1>
            <p class="lead text-muted mb-4">
                For your security, this page has expired. This often happens if you leave a page open for too long.
            </p>
            <p class="text-muted">
                Don't worry, you can simply go back and try again.
            </p>
            
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="javascript:history.back()" class="btn btn-gold btn-lg">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>Go Back & Try Again
                </a>
                <a href="{{ route('frontend.home') }}" class="btn btn-outline-secondary btn-lg">
                    Go to Homepage
                </a>
            </div>

        </div>
    </div>
</div>
@endsection