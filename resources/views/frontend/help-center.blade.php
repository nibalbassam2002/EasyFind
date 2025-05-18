@extends('frontend.Layouts.frontend') 
@section('title', 'Help Center - ' )
@section('description', 'Find answers to your frequently asked questions about using the platform' )

@push('styles')
<style>
    :root {
        --theme-gold: #FFD700;
        --theme-gold-rgb: 255, 215, 0;
        --theme-dark-text: #333;
        --theme-light-text: #f8f9fa;
        --theme-primary-link: #b8860b;
    }

    .help-center-header {
        background-color: #eeeeee;
        padding: 3rem 1rem;
        margin-bottom: 2.5rem;
        border-radius: .5rem;
        border: 1px solid #eeeeee;
    }
    .help-center-header h1 {
        color: var(--theme-primary-link);
        font-weight: 600;
    }
    .help-center-header .lead {
        color: #555;
        font-size: 1.1rem;
    }
    .search-bar-help .form-control:focus {
        border-color: var(--theme-gold);
        box-shadow: 0 0 0 0.25rem rgba(var(--theme-gold-rgb), 0.25);
    }
    .search-bar-help .btn-primary {
        background-color: var(--theme-gold);
        border-color: var(--theme-gold);
        color: var(--theme-dark-text);
        font-weight: 500;
    }
    .search-bar-help .btn-primary:hover {
        background-color: #e6c300;
        border-color: #e6c300;
        color: var(--theme-dark-text);
    }

    .category-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid #eee;
        background-color: #fff;
        border-radius: .5rem;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
        border-left: 4px solid var(--theme-gold);
    }
    .category-card .card-title {
        color: var(--theme-primary-link);
        font-weight: 500;
    }
    .category-card .btn-outline-primary {
        color: var(--theme-primary-link);
        border-color: var(--theme-primary-link);
    }
    .category-card .btn-outline-primary:hover {
        background-color: var(--theme-primary-link);
        color: var(--theme-light-text);
    }
    .category-card i[class*="bi-"] {
        color: var(--theme-gold) !important;
    }

    .faq-section h3 {
        margin-top: 2.5rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--theme-gold);
        color: var(--theme-dark-text);
        font-weight: 500;
    }
    .faq-section h3 i {
        color: var(--theme-gold);
    }

    .accordion-button {
        font-weight: 500;
        color: #444;
    }
    .accordion-button:not(.collapsed) {
        color: var(--theme-dark-text);
        background-color: #fff3cd;
        box-shadow: inset 0 -1px 0 rgba(var(--theme-gold-rgb), .25);
    }
    .accordion-button:not(.collapsed)::after {
        filter: brightness(0) saturate(100%) invert(68%) sepia(63%) saturate(645%) hue-rotate(359deg) brightness(102%) contrast(101%);
    }
    .accordion-button:focus {
        border-color: var(--theme-gold);
        box-shadow: 0 0 0 0.25rem rgba(var(--theme-gold-rgb), 0.25);
    }
    .accordion-item {
        border: 1px solid #b3ad9d;
        margin-bottom: 0.5rem;
        border-radius: .375rem;
    }
    .accordion-body {
        background-color: #fffcf2;
        color: #454545;
        font-size:0.95rem;
        line-height: 1.6;
    }

    #contact-support .btn-success {
        background-color: var(--theme-gold);
        border-color: var(--theme-gold);
        color: var(--theme-dark-text);
        font-weight: 500;
    }
    #contact-support .btn-success:hover {
        background-color: #e6c300;
        border-color: #e6c300;
        color: var(--theme-dark-text);
    }
    #contact-support h3 i {
        color: var(--theme-gold);
    }
</style>
@endpush

@section('content')
<div class="container mt-5 mb-5">
    <div class="help-center-header text-center">
        <h1><i class="bi bi-question-circle-fill me-2"></i>Help Center</h1>
        <p class="lead">Welcome! How can we help you today?</p>

        <div class="search-bar-help">
            <form id="helpSearchForm" onsubmit="return false;">
                <div class="input-group input-group-lg">
                    <input type="search" id="helpSearchInput" class="form-control" placeholder="Search for answers..." aria-label="Search help center">
                    <button class="btn btn-primary" type="button"><i class="bi bi-search"></i> Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-5 text-center" id="categoriesSection">
        <div class="col-md-4">
            <div class="card category-card h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-person-check-fill fs-1 mb-3"></i>
                    <h5 class="card-title">For New Users</h5>
                    <p class="card-text small text-muted px-2">How to get started, create an account, and navigate the platform.</p>
                    <a href="#faq-getting-started" class="btn btn-outline-primary btn-sm stretched-link mt-auto">Learn more</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card category-card h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-search fs-1 mb-3"></i>
                    <h5 class="card-title">For Property Seekers</h5>
                    <p class="card-text small text-muted px-2">Search tips, contacting advertisers, and more.</p>
                    <a href="#faq-buyers" class="btn btn-outline-primary btn-sm stretched-link mt-auto">Learn more</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card category-card h-100">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <i class="bi bi-building-fill-add fs-1 mb-3"></i>
                    <h5 class="card-title">For Advertisers</h5>
                    <p class="card-text small text-muted px-2">How to add properties, manage listings, and pricing.</p>
                    <a href="#faq-sellers" class="btn btn-outline-primary btn-sm stretched-link mt-auto">Learn more</a>
                </div>
            </div>
        </div>
    </div>

    <div id="faq-list">
        <div class="faq-section" id="faq-getting-started">
            <h3><i class="bi bi-play-circle-fill me-2"></i>Getting Started</h3>
            <div class="accordion" id="accordionGettingStarted">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOneGS">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneGS" aria-expanded="false" aria-controls="collapseOneGS">
                            How do I create a new account on EasyFind?
                        </button>
                    </h2>
                    <div id="collapseOneGS" class="accordion-collapse collapse" aria-labelledby="headingOneGS" data-bs-parent="#accordionGettingStarted">
                        <div class="accordion-body">
                            To create a new account, click on the "Sign Up" button at the top of the page. You'll need to enter your full name, email address, and choose a strong password. After completing the form, we may send you a confirmation email to complete the registration process.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwoGS">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoGS" aria-expanded="false" aria-controls="collapseTwoGS">
                            I forgot my password, what should I do?
                        </button>
                    </h2>
                    <div id="collapseTwoGS" class="accordion-collapse collapse" aria-labelledby="headingTwoGS" data-bs-parent="#accordionGettingStarted">
                        <div class="accordion-body">
                            Don't worry! If you forgot your password, go to the login page and click on the "Forgot Password?" link. You'll be asked to enter your registered email address. We'll then send you instructions to reset your password.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThreeGS">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThreeGS" aria-expanded="false" aria-controls="collapseThreeGS">
                            Is using EasyFind platform free?
                        </button>
                    </h2>
                    <div id="collapseThreeGS" class="accordion-collapse collapse" aria-labelledby="headingThreeGS" data-bs-parent="#accordionGettingStarted">
                        <div class="accordion-body">
                            Browsing and searching for properties is completely free for all users. For advertisers who want to list properties for sale or rent, there may be subscription plans or specific fees for premium services or to increase visibility of their listings. Check our "Pricing" or "Subscription Plans" page for more details.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-section" id="faq-buyers">
            <h3><i class="bi bi-search me-2"></i>For Property Seekers</h3>
            <div class="accordion" id="accordionBuyers">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOneB">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneB" aria-expanded="false" aria-controls="collapseOneB">
                            How can I search for properties with specific criteria?
                        </button>
                    </h2>
                    <div id="collapseOneB" class="accordion-collapse collapse" aria-labelledby="headingOneB" data-bs-parent="#accordionBuyers">
                        <div class="accordion-body">
                            You can use the main search bar on the homepage or the "Properties" page. Enter keywords like property type (apartment, villa), location (city, area), or specific features. Additionally, use advanced filters to specify price range, number of rooms, area, and purpose (sale, rent) to narrow down search results.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwoB">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoB" aria-expanded="false" aria-controls="collapseTwoB">
                            How do I contact a property advertiser?
                        </button>
                    </h2>
                    <div id="collapseTwoB" class="accordion-collapse collapse" aria-labelledby="headingTwoB" data-bs-parent="#accordionBuyers">
                        <div class="accordion-body">
                            When you find a property you're interested in, you'll find the advertiser's contact information on the property details page (such as phone number or a button to send a message through the platform). Make sure you're logged in to use all communication features.
                        </div>
                    </div>
                </div>
                 <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThreeB">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThreeB" aria-expanded="false" aria-controls="collapseThreeB">
                            Can I save properties I like for later?
                        </button>
                    </h2>
                    <div id="collapseThreeB" class="accordion-collapse collapse" aria-labelledby="headingThreeB" data-bs-parent="#accordionBuyers">
                        <div class="accordion-body">
                            Yes! You can use the "Favorites" feature. While browsing properties, you'll see a heart icon <i class="bi bi-heart"></i>. Click it to add the property to your favorites list. You can access your favorites list from your account at any time.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="faq-section" id="faq-sellers">
            <h3><i class="bi bi-building-fill-gear me-2"></i>For Advertisers (Sellers/Landlords)</h3>
            <div class="accordion" id="accordionSellers">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOneS">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneS" aria-expanded="false" aria-controls="collapseOneS">
                            What are the requirements to list a property on EasyFind?
                        </button>
                    </h2>
                    <div id="collapseOneS" class="accordion-collapse collapse" aria-labelledby="headingOneS" data-bs-parent="#accordionSellers">
                        <div class="accordion-body">
                            To list a property, you must be registered as a "Property Lister" and have agreed to our terms of service. You'll need to provide accurate and complete information about the property, including location, price, detailed description, clear high-quality photos, and any documents proving ownership or rental rights if requested.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwoS">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoS" aria-expanded="false" aria-controls="collapseTwoS">
                            How long does it take for my listing to appear after adding it?
                        </button>
                    </h2>
                    <div id="collapseTwoS" class="accordion-collapse collapse" aria-labelledby="headingTwoS" data-bs-parent="#accordionSellers">
                        <div class="accordion-body">
                            New listings typically undergo a quick review by our team to ensure they meet our quality standards and platform policies. This may take a few hours. You'll receive a notification once your listing is approved and published, or if any modifications are needed.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="faq-section text-center" id="contact-support">
        <h3><i class="bi bi-headset me-2"></i>Didn't find your answer?</h3>
        <p class="lead">If you couldn't find what you're looking for, you can contact our support team directly.</p>
        <a href="{{ route('frontend.home') }}#feedback-section" class="btn btn-success btn-lg mt-3">
            <i class="bi bi-chat-left-text-fill me-2"></i> Contact Support 
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('helpSearchInput');
    const faqList = document.getElementById('faq-list');
    const allAccordionItems = faqList.querySelectorAll('.accordion-item');
    const allFaqSections = faqList.querySelectorAll('.faq-section');
    const categoriesSection = document.getElementById('categoriesSection');
    const contactSupportSection = document.getElementById('contact-support');
    const helpSearchForm = document.getElementById('helpSearchForm');

    let noResultsMessage = document.createElement('div');
    noResultsMessage.id = 'noResultsMessage';
    noResultsMessage.className = 'alert alert-warning text-center my-4';
    noResultsMessage.textContent = 'No results found matching your search.';
    noResultsMessage.style.display = 'none';
    faqList.parentNode.insertBefore(noResultsMessage, categoriesSection.nextSibling);

    if (helpSearchForm) {
        helpSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
    }

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let hasVisibleResults = false;

        allAccordionItems.forEach(function (item) {
            const questionButton = item.querySelector('.accordion-button');
            const answerBody = item.querySelector('.accordion-body');
            const questionText = questionButton.textContent.toLowerCase();
            const answerText = answerBody.textContent.toLowerCase();

            if (searchTerm === '' || questionText.includes(searchTerm) || answerText.includes(searchTerm)) {
                item.style.display = 'block';
                if (searchTerm !== '') hasVisibleResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        allFaqSections.forEach(section => {
            if (section.id === 'contact-support') {
                section.style.display = 'block';
                return;
            }

            const visibleItemsInSection = section.querySelectorAll('.accordion-item[style*="display: block"]');
            if (searchTerm === '' || visibleItemsInSection.length > 0) {
                section.style.display = 'block';
                if (searchTerm !== '' && visibleItemsInSection.length > 0) hasVisibleResults = true;
            } else {
                section.style.display = 'none';
            }
        });

        if (categoriesSection) {
            if (searchTerm === '') {
                categoriesSection.style.display = 'flex';
            } else {
                categoriesSection.style.display = 'none';
            }
        }

        if (searchTerm !== '' && !hasVisibleResults) {
            noResultsMessage.style.display = 'block';
            if (contactSupportSection) contactSupportSection.classList.add('mt-0');
        } else {
            noResultsMessage.style.display = 'none';
            if (contactSupportSection) contactSupportSection.classList.remove('mt-0');
        }

        if (searchTerm === '') {
            allAccordionItems.forEach(item => item.style.display = 'block');
            allFaqSections.forEach(section => section.style.display = 'block');
            if (categoriesSection) categoriesSection.style.display = 'flex';
            noResultsMessage.style.display = 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('keyup', performSearch);

        const searchButton = document.querySelector('.search-bar-help .btn-primary');
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }
    }

    const categoryCards = document.querySelectorAll('#categoriesSection .category-card a');
    categoryCards.forEach(cardLink => {
        cardLink.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                if (searchInput) searchInput.value = '';
                performSearch();

                targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });

                const firstAccordionItemCollapse = targetElement.querySelector('.accordion-item .accordion-collapse');
                if (firstAccordionItemCollapse) {
                    var bsCollapse = new bootstrap.Collapse(firstAccordionItemCollapse, {
                        toggle: true
                    });
                }
            }
        });
    });
});
</script>
@endpush