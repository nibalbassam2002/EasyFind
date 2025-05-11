{{-- resources/views/layouts/partashals/nav.blade.php --}}
<ul class="sidebar-nav" id="sidebar-nav">

  {{-- === قسم لوحة التحكم (للجميع) === --}}
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}" href="{{ route('dashboard') }}">
      <i class="bi bi-grid"></i>
      <span>Dashboard</span>
    </a>
  </li><!-- End Dashboard Nav -->

  {{-- === قسم إدارة النظام (للأدمن فقط) === --}}
  @if(Auth::check() && Auth::user()->role == 'admin')
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.*') ? '' : 'collapsed' }}" data-bs-target="#admin-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}">
      <i class="bi bi-shield-lock"></i><span>Administration</span><i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="admin-nav" class="nav-content collapse {{ request()->routeIs('admin.*') || request()->routeIs('moderator.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav"> {{-- تعديل: تضمين moderator.* للإبقاء مفتوحاً --}}
      <li>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <i class="bi bi-circle"></i><span>User Management</span> {{-- تغيير الأيقونة --}}
        </a>
      </li>
      {{-- ▼▼▼ إضافة رابط مراجعة العقارات للأدمن أيضاً ▼▼▼ --}}
       <li>
        <a href="{{ route('moderator.properties.pending') }}" class="{{ request()->routeIs('moderator.properties.pending') ? 'active' : '' }}">
          <i class="bi bi-circle"></i><span>Pending Properties</span>
        </a>
      </li>
       {{-- ▲▲▲ نهاية الإضافة ▲▲▲ --}}
      <li>
        <a href="#"> {{-- TODO: Add route for admin property management --}}
          <i class="bi bi-circle"></i><span>All Properties</span>
        </a>
      </li>
       {{-- TODO: Add links for Categories, Cities etc. management --}}
       {{-- <li> <a href="#"> <i class="bi bi-circle"></i><span>Categories</span> </a> </li> --}}
       {{-- <li> <a href="#"> <i class="bi bi-circle"></i><span>Cities/Areas</span> </a> </li> --}}
    </ul>
  </li>
  @endif
  {{-- === نهاية قسم إدارة النظام === --}}


  {{-- === قسم مدير العقارات (Property Lister) === --}}
  @if(Auth::check() && Auth::user()->role == 'property_lister')
    <li class="nav-heading">My Properties</li>
    <li class="nav-item">
      {{-- جعل الرابط الرئيسي يفتح القائمة المنسدلة --}}
      <a class="nav-link {{ request()->routeIs('lister.properties.*') ? '' : 'collapsed' }}" data-bs-target="#lister-nav" data-bs-toggle="collapse" href="#" aria-expanded="{{ request()->routeIs('lister.properties.*') ? 'true' : 'false' }}">
        <i class="bi bi-building-gear"></i><span>Manage Properties</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
       <ul id="lister-nav" class="nav-content collapse {{ request()->routeIs('lister.properties.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('lister.properties.index') }}" class="{{ request()->routeIs('lister.properties.index') || request()->routeIs('lister.properties.show') || request()->routeIs('lister.properties.edit') ? 'active' : '' }}">
               <i class="bi bi-circle"></i><span>View My Properties</span>
            </a>
          </li>
          <li>
            <a href="{{ route('lister.properties.create') }}" class="{{ request()->routeIs('lister.properties.create') ? 'active' : '' }}">
               <i class="bi bi-circle"></i><span>Add New Property</span>
             </a>
          </li>
          {{-- TODO: Add link to view requests for lister's properties --}}
          {{-- <li> <a href="#"> <i class="bi bi-circle"></i><span>View Requests</span> </a> </li> --}}
       </ul>
    </li>
  @endif
  {{-- === نهاية قسم مدير العقارات === --}}


  {{-- ▼▼▼ قسم مشرف المحتوى (Content Moderator) - الكود الجديد ▼▼▼ --}}
  @if(Auth::check() && Auth::user()->role == 'content_moderator') {{-- شرط مشرف المحتوى فقط --}}
    <li class="nav-heading">Moderation Tools</li>
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('moderator.properties.pending') ? '' : 'collapsed' }}" href="{{ route('moderator.properties.pending') }}">
        <i class="bi bi-clipboard-check"></i>
        <span>Pending Properties</span>
        {{-- يمكنك إضافة عداد هنا إذا تم تمريره --}}
        {{-- @if(isset($pendingCount) && $pendingCount > 0) <span class="badge bg-warning ms-auto">{{ $pendingCount }}</span> @endif --}}
      </a>
    </li>
     {{-- يمكنك إضافة روابط أخرى للمشرف هنا --}}
  @endif
  {{-- ▲▲▲ نهاية قسم مشرف المحتوى ▲▲▲ --}}


  {{-- === قسم الملف الشخصي ) === --}}
   <li class="nav-heading">Account</li> {{-- إضافة عنوان للقسم --}}
  <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('profile.index') ? '' : 'collapsed' }}" href="{{ route('profile.index') }}">
        <i class="bi bi-person-circle"></i> {{-- تغيير الأيقونة --}}
        <span>My Profile</span>
    </a>
  </li>


</ul>