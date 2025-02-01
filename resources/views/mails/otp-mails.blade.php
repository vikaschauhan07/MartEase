<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <title>Hitchmail</title>
</head>
<body>
    <div style="width: 100%; height: auto; background-color: #1E1E1E;"
        class=" d-flex justify-content-center align-items-center p-0 p-lg-3">
        <div style="max-width: 37.5rem; background-color: white;" class=" w-100">
            <div style="padding: 1.25rem 1.875rem; flex-direction: column;"
                class=" d-flex justify-content-center align-items-center">
                <div class="w-100">
                    <img class="img-fluid mb-3 mb-lg-4" src="{{asset('Admin/images/logo.png')}}" alt="logo">
                </div>
               <div style="position: relative; width: 100%; height: 190px;">
                <img class="img-fluid" style="height: 190px; position: absolute; top: 0%; left: 0%; background-repeat: no-repeat; background-position: center; background-size: cover;" src="{{asset('Admin/images/email-bg.png')}}" alt="email-bg">
                <div style="position: absolute; top: 0%; left: 0%; width: 100%; height: 190px; border-radius: 10px; border: 1px solid #b2ddee; padding: 12px; background-repeat: no-repeat; background-position: center; background-size: cover; flex-direction: column;"
                    class=" d-flex justify-content-center align-items-center">
                    <img class="img-fluid" src="{{asset('Admin/images/msg-icon.png')}}" alt="msg-icon">
                    <p style="color: #1E1E1E; font-size: 18px; font-weight: 500;" class=" mt-3 mt-lg-4">Hello Zenith</p>
                </div>
               </div>
                <h2 style="color: #333333; font-size: 22px; font-weight: 600;" class=" mt-2 mt-lg-3">Welcome to
                    Hitchmail</h2>
                <p style="color: #6B6B6B; font-size: 14px; font-weight: 400; ">your go-to solution for fast, reliable
                    city-to-city parcel delivery!</p>
            </div>
            <div style="background-color: #18ABE3; padding: 30px; flex-direction: column;"
                class=" d-flex justify-content-center align-items-center w-100">
                <p style="color: white; font-size: 16px; font-weight: 500;" class=" text-center">To get started, please
                    verify your email with the code below</p>
                <h2 style="color: white; font-size: 44px; font-weight: 700; text-align: center; margin: 0;">{{$otp}}</h2>
            </div>
            <div style="padding: 30px; flex-direction: column;"
                class=" d-flex justify-content-center align-items-center">
                <p class="text-center">Enter this code in the app, and you're all set to start shipping with ease. We’re
                    excited to help you
                    send parcels faster and more affordably across cities!</p>
                <p style="color: #6B6B6B; font-size: 14px; font-weight: 400; ">If you didn’t request this code, just
                    ignore this email.</p>
                <div style="background-color: #EFEFEF; padding: 20px; border-radius: 10px; width: 100%; max-width: 33.75rem; "
                    class="gray_box d-flex justify-content-center align-items-center">
                    <p style="color: #6B6B6B; font-size: 14px; font-weight: 400; " class=" text-center m-0">Thanks for
                        joining the Hitchmail family! <br>
                        Happy shipping, <br>
                        The Hitchmail Team</p>
                </div>
            </div>
            <div style="background-color: #19191A; padding: 20px 30px; flex-direction: column;"
                class=" d-flex justify-content-center align-items-center">
                <div class="d-flex gap-2 mb-2 mb-lg-3">
                    {{-- <img class="img-fluid" src="{{asset('Admin/images/facebook.png')}}" alt="media">
                    <img class="img-fluid" src="{{asset('Admin/images/in.png')}}" alt="media">
                    <img class="img-fluid" src="{{asset('Admin/images/insta.png')}}" alt="media"> --}}
                </div>
                <p style="color: white; font-size: 12px; font-weight: 400;">All rights are reserved. I Copyrights © 2024
                </p>

            </div>
            <div style="background-color: #292929; height: 2px; width: 100%;" class="hr_line">

            </div>
            <div style="padding: 10px; background-color: #19191A;">
                <p style="color: #6C6C6C; font-size: 12px; font-weight: 400; text-decoration: underline; cursor: pointer;"
                    class="m-0 text-center">Privacy Policy &nbsp; | &nbsp; Unsubscribe</p>
            </div>
        </div>
</body>

</html>