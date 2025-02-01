<nav class="main-header navbar navbar-expand headerBar">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link hideLg" data-widget="pushmenu" href="#" role="button" id="sidebarToggle">
                <img src="{{asset('Admin/images/menu-icon.svg')}}" alt="nav-icon" class="nav_icon">
            </a>
            <div class="hideXl">
                <a class="" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
                    <img src="{{asset('Admin/images/menu-icon.svg')}}" alt="nav-icon" class="nav_icon">
                </a>
            </div>
        </li>
    </ul>
    <div class="dropdown profile ms-auto">
        <button class="btn profileButton dropdown-toggle dropdown-adminProfile profile_btn  d-flex" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-panel d-flex">
                <div class="image d-flex align-items-center justify-content-center p-0">
                    <img src="{{ asset(Auth::guard('admin')->user()->profile_pic ?? 'Admin/images/nouser.svg') }}" class="profile_img me-2" alt="User Image">
                </div>
                <div class="info text-start d-flex flex-column justify-content-center align-items-start">
                    <p class="profile_name mb-0">{{Auth::guard('admin')->user()->name}}</p>
                    <a href="#" class="admin-name profile_type d-block text-start">{{ Auth::guard('admin')->user()->name }}</a>
                </div>
            </div>
        </button>
        <ul class="dropdown-menu">
            <li class="menu_item">
                <a class="dropdown-item" href="{{ route('admin.profile-change-view') }}">
                    <i class="fa-regular fa-pen-to-square me-2 profileIcon"></i>
                    Edit Profile
                </a>
            </li>
            <li class="menu_item">
                <a class="dropdown-item" href="{{ route('admin.password-change-view') }}">
                    <i class="fa-solid fa-key me-2 profileIcon"></i>
                    Change Password
                </a>
            </li>
            <li class="menu_item">
                <a class="dropdown-item" href="{{ route('admin.logout') }}">
                    <i class="fa-solid fa-right-from-bracket me-2 profileIcon"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.profileButton.dropdown-toggle::after {
    display: inline-block !important;
    margin-left: 15px !important;
    vertical-align: 13px !important;
    content: "" !important;
    border-top: 8px solid !important;
    border-right: 8px solid transparent !important;
    border-bottom: 0 !important;
    border-left: 8px solid transparent !important;
}
.profile .dropdown-toggle::after {
	margin-top: 22px;
	color: #18ABE3 !important;
}

.profileButton{
    /* border:  1px solid #18ABE3 !important; */
    box-shadow: 0px 0px 0px rgba(1, 155, 214, 0.4) !important;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
    
}
</style>