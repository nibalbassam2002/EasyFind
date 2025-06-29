<ul class="sidebar-nav" id="sidebar-nav">

    {{-- === قسم لوحة التحكم (للجميع) === --}}
    <li class="nav-item"> {{-- <--- تم إصلاح الكلاس هنا --}}
        <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('frontend.home') ? '' : 'collapsed' }}"
            href="{{ route('frontend.home') }}">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>View Site</span>
        </a>
    </li>

    {{-- === قسم إدارة النظام (للأدمن فقط) === --}}
    @if (Auth::check() && Auth::user()->role == 'admin')
        <li class="nav-item">
            <a class="nav-link {{ request()->is('dashboard/admin*') ? '' : 'collapsed' }}" data-bs-target="#admin-nav"
                data-bs-toggle="collapse" href="#"
                aria-expanded="{{ request()->is('dashboard/admin*') ? 'true' : 'false' }}"> {{-- استخدام request()->is() لمطابقة البادئة --}}
                <i class="bi bi-shield-lock"></i><span>Administration</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="admin-nav"
                class="nav-content collapse {{ request()->is('dashboard/admin*') || request()->is('dashboard/moderator/properties/pending') || request()->is('dashboard/moderator/feedback*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>User Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('moderator.properties.pending') }}"
                        class="{{ request()->routeIs('moderator.properties.pending') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Pending Properties</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('moderator.feedback.index') }}"
                        class="{{ request()->routeIs('moderator.feedback.*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Manage Feedback</span>
                    </a>
                </li>
                <li>
                    {{-- <a href="#"> --}} {{-- TODO: Add route for admin property management --}}
                    {{-- <i class="bi bi-circle"></i><span>All Properties (Admin)</span> تمييز أنها للأدمن --}}
                    </a>
                </li>
            </ul>
        </li>
    @endif
    {{-- === نهاية قسم إدارة النظام === --}}


    {{-- === قسم مدير العقارات (Property Lister) === --}}
    {{-- هذا القسم سيظهر للـ customer الذي اشترك في الخطة المجانية وتغير دوره --}}
    @if (Auth::check() && Auth::user()->role == 'property_lister')
        <li class="nav-heading">My Properties</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('lister.properties.*') ? '' : 'collapsed' }}"
                data-bs-target="#lister-nav" data-bs-toggle="collapse" href="#"
                aria-expanded="{{ request()->routeIs('lister.properties.*') ? 'true' : 'false' }}">
                <i class="bi bi-building-gear"></i><span>Manage Properties</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="lister-nav"
                class="nav-content collapse {{ request()->routeIs('lister.properties.*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('lister.properties.index') }}"
                        class="{{ request()->routeIs('lister.properties.index') || request()->routeIs('lister.properties.show') || request()->routeIs('lister.properties.edit') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>View My Properties</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('lister.properties.create') }}"
                        class="{{ request()->routeIs('lister.properties.create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Add New Property</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('dashboard.viewingRequests') }}"
                        class="{{ request()->routeIs('dashboard.viewingRequests') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Viewing Requests</span>
                    </a>
                </li>
            </ul>
        </li>
    @endif
    {{-- === نهاية قسم مدير العقارات === --}}


    {{-- === قسم مشرف المحتوى (Content Moderator) === --}}
    @if (Auth::check() && Auth::user()->role == 'content_moderator')
        <li class="nav-heading">Moderation Tools</li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('moderator.properties.pending') ? '' : 'collapsed' }}"
                href="{{ route('moderator.properties.pending') }}">
                <i class="bi bi-clipboard-check"></i>
                <span>Pending Properties</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('moderator.feedback.*') ? '' : 'collapsed' }}"
                href="{{ route('moderator.feedback.index') }}">
                <i class="bi bi-chat-left-text"></i>
                <span>Manage Feedback</span>
            </a>
        </li>
    @endif
    {{-- === نهاية قسم مشرف المحتوى === --}}


    {{-- === قسم الملف الشخصي (للجميع) === --}}
    <li class="nav-heading">Account</li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('profile.index') ? '' : 'collapsed' }}"
            href="{{ route('profile.index') }}">
            <i class="bi bi-person-circle"></i>
            <span>My Profile</span>
        </a>
    </li>
</ul>
