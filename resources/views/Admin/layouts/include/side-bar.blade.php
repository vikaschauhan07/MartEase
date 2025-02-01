<!--begin::Sidebar-->
<aside class="main-sidebar app-sidebar shadow main_sidebar hideLg me-2 me-lg-3" id="sidebar">
    <div class="d-flex justify-content-center">
        <img src="{{asset('Admin/images/logo-svg.svg')}}" alt="logo" class="sidebar_logo">
        <img src="{{asset('Admin/images/hitchmail_fav.svg')}}" alt="logo" class="sidebar_logo_mobile">
    </div>
    <div class="sidebar-wrapper p-0 ">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item {{ request()->is('admin/dashboard*') ? ' active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <img src="{{asset('Admin/images/sidebar-1.svg')}}" alt="sidebar-icon" class="sidebar_icon active_img">
                        <span class="">Dashboard</span>
                    </a>
                </li>
                <hr>
                <li class="nav-item {{ request()->is('admin/users*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-user-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-6.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>
                            Shippers Management</span>
                    </a>
                </li>
               
                <li class="nav-item {{ request()->is('admin/parcels*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-parcel-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-3.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Parcels Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/parcel-processing*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-parcel-process-index') }}" class="nav-link">
                        <img src="{{asset('Admin/images/processing.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Parcels Processing</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/trailer*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-trailer') }}" class="nav-link">
                        <img src="{{asset('Admin/images/trailer.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>trailer load control</span>
                    </a>
                </li>
                
                <li class="nav-item {{ request()->is('admin/pickup*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-pickup-points') }}" class="nav-link">
                        <img src="{{asset('Admin/images/pickup.svg')}}" alt="sidebar-icon" class="sidebar_icon pickup_icon">
                        <span>Manage Pickup Point</span>
                    </a>
                </li>
                <hr>
                <li class="nav-item {{ request()->is('admin/drivers*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-driver-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-2.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Drivers Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/trips*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-trip-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-4.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Trip Management</span>
                    </a>
                </li>
                <!-- </li><li class="nav-item {{ request()->is('admin/hell*') ? ' active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-5.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Shipping Label </span>
                    </a>
                </li> -->
                
            </ul>
        </nav>
    </div>
</aside>

<!-- ---------------mobile-sidebar------------- -->


<div style="max-width: 300px;" class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <div class="d-flex justify-content-center">
        <img src="{{asset('Admin/images/logo-svg.svg')}}" alt="logo" class="sidebar_logo m-0">
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body main-sidebar p-0 w-100">
  <div class="sidebar-wrapper p-0 ">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item {{ request()->is('admin/dashboard*') ? ' active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <img src="{{asset('Admin/images/sidebar-1.svg')}}" alt="sidebar-icon" class="sidebar_icon active_img">
                        <span class="">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/users*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-user-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-6.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>
                            Shippers Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/drivers*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-driver-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-2.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Drivers Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/parcels*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-parcel-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-3.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Parcels Management</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/parcel-processing*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-parcel-process-index') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-3.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Parcels Processing</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->is('admin/trips*') ? ' active' : '' }}">
                    <a href="{{ route('admin.get-trip-list') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-4.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Trip Management</span>
                    </a>
                <!-- </li><li class="nav-item {{ request()->is('admin/hell*') ? ' active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <img src="{{asset('Admin/images/sidebar-5.svg')}}" alt="sidebar-icon" class="sidebar_icon">
                        <span>Shipping Label </span>
                    </a>
                </li> -->
                
            </ul>
        </nav>
    </div>
  </div>
</div>

<style>
    
    .main-sidebar .nav-item p {
        display: inline-block;
    }
    
</style>

<!-- jQuery for toggling sidebar -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('menu-collapsed-side-bar');
        });
    });
</script>
