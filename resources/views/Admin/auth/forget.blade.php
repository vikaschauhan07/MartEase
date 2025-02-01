@extends('Admin.auth.layout.authlayout')
@section('title') Forget Password @endsection
@section('contents')
    <div class="login_form_card">
        <div class="login-right-form">
            <img src="{{asset('Admin/images/forgot-img.svg')}}" alt="Message" class="forgot-icon mb-2 mb-lg-3">
            <h2 class="text-center login_heading">Forgot Password?</h2>
            <p class="text-center para_text font_16 mb-4">Enter the email address that is associated with your account.</p>
            <form id="sendOtpForm">
                @csrf
                <div class="position-relative">
                    <label class="login_label" for="email">EMAIL</label>
                    <input class="login_input w-100" type="email" value="{{ old('email') }}" name="email" placeholder="Enter Email Address">
                    <img src="{{ asset('Admin/images/Message.png') }}" alt="Message" class="input_icon">
                </div>
                <span id="emailError" class="text-danger validations"></span>
                <button class="login_btn w-100 mt-3 mt-lg-4" type="button" id="sendOtpButton">Send Verification Code</button>
            </form>
            <div id="successMessage" class="text-success"></div>            
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script>
        $(document).ready(function() {
            $('#sendOtpForm').on('keypress', function(e) {
                if (e.which === 13) { 
                    e.preventDefault(); 
                }
            });
            $('#sendOtpButton').click(function(e) {
                e.preventDefault();
                let form = $('#sendOtpForm');
                let formData = form.serialize();
                let url = "{{route('admin.send-otp')}}";
                $('#emailError').text('');
                $('#successMessage').text('');
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#sendOtpButton').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        $('#sendOtpButton').prop('disabled', false).html('Send Verification Code');
                        window.location.href = response.data.redirect_url;
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { 
                            let errors = xhr.responseJSON.error;
                            $('#emailError').text(errors[0]);
                            $('#sendOtpButton').prop('disabled', false).html('Send Verification Code');
                        } else{
                            toastr.error(xhr.responseJSON.message);
                        }
                    },
                    complete: function() {
                        $('#sendOtpButton').prop('disabled', false).html('Send Verification Code');
                    }
                });
            });
        });
    </script>
@endsection