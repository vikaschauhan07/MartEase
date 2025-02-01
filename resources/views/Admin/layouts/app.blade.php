<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('Admin/images/hitchmail_fav.svg') }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('Admin/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('Admin/css/adminlte.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body class=" body_bg position-relative">
    <div class=" d-flex">
        <div class="sidebarRapper">
            @include('Admin.layouts.include.side-bar')
        </div>
        <div class="w-100 marginRight40">
            <div class="navbarRapper">
                @include('Admin.layouts.include.nav-bar')
            </div>
            @include('common-pages.flash-message')
            <main class="App_content_box">
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12 mt-3 mb-4">
                                <h3 class="mb-0 page_title">@yield('page-title')</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table_content_box ">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
  
</body>
</html>