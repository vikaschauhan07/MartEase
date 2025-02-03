<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('Admin/css/admin-auth.css')}}">
    <!-- <link rel="icon" type="image/x-icon" href="{{ asset('Admin/images/favicon.svg') }}"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>@yield('title')</title>
</head>
<body>
    <section>
        <div class="owerflow_hidden position-relative">
        <img src="{{ asset('Admin/images/login.svg') }}" alt="" class="img-fluid loginBg">
           <div class="fixed_container">
           <div class="row">
                <!-- <div class="col-md-6 full_height item_center p-xl-0 p-4 mobile_bg">
                    <img src="{{ asset('Admin/images/sidebar-logo.svg') }}" alt="" class="img-fluid login_logo"> 
                </div> -->
                <div class="col-md-6 item_center  mobile_form_bg">
                    {{-- Auth content --}}
                        @yield('contents')
                    {{-- Auth content --}}
                </div>
            </div>
           </div>
        </div>
    </section>
    @include('common-pages.flash-message')
</body>
</html>