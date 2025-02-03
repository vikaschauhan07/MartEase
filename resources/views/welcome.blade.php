<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"
    integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p"
    crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
    integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF"
    crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <link rel="icon" type="image/x-icon" href="{{asset("landing/images/favicon.svg")}}"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <link rel="stylesheet" href="{{asset('landing/css/style.css')}}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <title>Hunkr</title>
</head>

<body>
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="{{asset('landing/images/shutterstock_1110960941 (1).mp4')}}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <div class="fade_content">
        <div class="container">
            <div class="content">
                <img class="logo img-fluid" src="{{asset('landing/images/main-logo.svg')}}" alt="logo">
                <h1 class="landingPage_heading">Our product <br>
                    is launching soon
                </h1>
                <p class="landingPage_content">We have something coming that is going to blow you away. Below is a sneak
                    peek of what we offer. Check back
                    often for our launch.
                </p>
                <div class="time_box">
                    <div class="time_count">
                        <span>Days</span>
                        <p>14</p>
                    </div>
                    <span class="time_divider">:</span>
                    <div class="time_count">
                        <span>Hours</span>
                        <p>07</p>
                    </div>
                    <span class="time_divider">:</span>
                    <div class="time_count">
                        <span>Minutes</span>
                        <p>23</p>
                    </div>
                    <span class="time_divider">:</span>
                    <div class="time_count">
                        <span>Seconds</span>
                        <p>27</p>
                    </div>
                </div>
                <div class="email_box d-flex align-items-center justify-content-center w-100 gap-2" id="notified-div">
                    <input class="email_input w-100" type="text" placeholder="Email" id="getnotifiedIn">
                    <button onclick="savePickupData()" class="email_button" id="getnotified"> 
                        <span class="button-text">
                            <img class="notify_icon me-2" src="{{asset('landing/images/notify.svg')}}" alt="icon">
                            Get Notified
                        </span>
                        <span class="loader d-none"></span>
                    </button>
                </div>
                <p class="text-danger text-left" id="email-error"></p>
                <p class=" text-left d-none" id="email-success"></p>
                <div class="w-100 card_rowBox">
                    <div class="card_row d-flex align-items-start justify-content-center w-100 flex-wrap">
                        <div class="cards">
                            <div class=" card_box">
                                <div class="cardIcon_bg">
                                    <img src="{{asset('landing/images/card-icon-6.svg')}}" alt="card-icon">
                                </div>
                                <h5 class="card-title">40 Trillion</h5>
                                <p class="card-text">In October 2024, more than 40 trillion gallons of rain fell on
                                    the Southeast United States, including North Carolina, from Hurricane Helene and
                                    other storms</p>
                            </div>
                        </div>

                        <div class="cards">
                            <div class=" card_box">
                                <div class="cardIcon_bg">
                                    <img src="{{asset('landing/images/card-icon-4.svg')}}" alt="card-icon">
                                </div>
                                <h5 class="card-title">112 Billion</h5>
                                <p class="card-text">Hurricane Ian (2022) dealt an estimated $112 Billion worth of
                                    damage. One of the strongest hurricanes to hit Florida</p>
                            </div>
                        </div>

                        <div class="cards">
                            <div class=" card_box">
                                <div class="cardIcon_bg">
                                    <img src="{{asset('landing/images/card-icon-3.svg')}}" alt="card-icon">
                                </div>
                                <h5 class="card-title">1.3 million</h5>
                                <p class="card-text">Hurricane claims across the Caribbean and SE US from Hurricane
                                    Irma (2017)</p>
                            </div>
                        </div>

                        <div class="cards">
                            <div class=" card_box">
                                <div class="cardIcon_bg">
                                    <img src="{{asset('landing/images/card-icon-5.svg')}}" alt="card-icon">
                                </div>
                                <h5 class="card-title">34</h5>
                                <p class="card-text">The number of U.S. major power outages related to hurricanes
                                    and tropical storms from 1992-2009
                                </p>
                            </div>
                        </div>

                        <div class="cards">
                            <div class=" card_box">
                                <div class="cardIcon_bg">
                                    <img src="{{asset('landing/images/card-icon-4.svg')}}" alt="card-icon">
                                </div>
                                <h5 class="card-title">100</h5>
                                <p class="card-text">Hurricane Maria left Puerto Rico and the US Virgin Islands with
                                    a large scale blackout lasting 100 days </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<style>
    #email-error {
        text-align: left;
        max-width: 550px;
        width: 100%;
        padding-top: 10px;
    }
    #email-success {
        background-color: rgba(94, 94, 95, 0.699);
        border: 1px solid rgb(99, 98, 98);
        border-radius: 16px;
        text-align: center;
        max-width: 550px;
        width: 100%;
        padding: 10px;
        font-weight: 700;
        font-size: 20px;
        color: #fff !important;
    }
    .loader {
        border: 2px solid #f3f3f3;
        border-radius: 50%;
        border-top: 2px solid #e5001300;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-left: 0px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<script>
    // $("#getnotified").on("click", function(){
      
    function savePickupData(event){
        $('#email-error').html('');
        var formData = {
            _token: '{{ csrf_token() }}', 
            email: $("#getnotifiedIn").val()
        };

        var button = $('#getnotified');
        var loader = button.find('.loader');
        var buttonText = button.find('.button-text');

        button.prop('disabled', true);
        loader.removeClass('d-none');
        buttonText.addClass('d-none');

        $.ajax({
            url: "{{ route('admin.get-notified') }}", 
            type: 'POST',
            data: formData,
            success: function(response) {
                $("#getnotifiedIn").val('');
                $("#notified-div").addClass("d-none");
                $('#email-success').html(response.message);
                $('#email-success').removeClass("d-none");
                setTimeout(function() {
                    $('#email-success').html('');
                    $('#email-success').addClass("d-none");
                    $("#notified-div").removeClass("d-none");
                }, 5000);
                // window.location.href = response.data.redirect_url;
                button.prop('disabled', false);
                loader.addClass('d-none');
                buttonText.removeClass('d-none');
            },
            error: function(xhr, status, error) {
                if(xhr.status == 422){
                    var errors = xhr.responseJSON.error;
                    var errorMessages = '';
                    $.each(errors, function(key, value) {
                        errorMessages = value[0];
                        $('#' + key + '-error').html(errorMessages);
                    });
                    // toastr.warning("Validation error");
                    button.prop('disabled', false);
                    loader.addClass('d-none');
                    buttonText.removeClass('d-none');
                } else {
                    toastr.error(xhr.responseJSON.message);
                    button.prop('disabled', false);
                    loader.addClass('d-none');
                    buttonText.removeClass('d-none');
                }
            }
        });
    }
    // });
</script>
<script>
    $(document).ready(function() {
    const targetDate = new Date("April 5, 2025 23:59:59").getTime();

    function updateTimer() {
        const now = new Date().getTime();
        let totalSeconds = Math.floor((targetDate - now) / 1000);

        if (totalSeconds > 0) {
            const days = Math.floor(totalSeconds / (24 * 60 * 60));
            const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
            const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
            const seconds = totalSeconds % 60;
            $('.time_box .time_count:nth-child(1) p').text(days);
            $('.time_box .time_count:nth-child(3) p').text(hours);
            $('.time_box .time_count:nth-child(5) p').text(minutes);
            $('.time_box .time_count:nth-child(7) p').text(seconds);
        } else {
            clearInterval(timerInterval);
            $('.time_box').text("Countdown finished!");
        }
    }

    const timerInterval = setInterval(updateTimer, 1000);
    updateTimer(); 
});

</script>
</html>