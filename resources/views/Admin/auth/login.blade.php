@extends('Admin.auth.layout.authlayout')
@section('title') Login @endsection
@section('contents')
<div class="  login_form_card">
    <div class="login-right-form">
        <h2 class="login_heading">Sign into your Account</h2>
        <p class="para_text font_16">Enter your login details below.</p>
        <form id="loginForm" action="{{ route('admin.authenticate') }}" method="post">
            @csrf
            <div class="position-relative">
                <label class="login_label" for="email">USERNAME</label>
                <input class="login_input w-100" type="email" name="email" value="{{old('email')}}" placeholder="Enter Email Address" required autofocus>
                <img src="{{ asset('Admin/images/Message.png') }}" alt="Message" class="input_icon">
            </div>
            <span class="text-danger validations" id="email-error"></span>
            <div class="position-relative mb-2 mt-3 mt-lg-4">
                <label class="login_label" for="Password" class="Password">PASSWORD</label>
                <input class="login_input w-100" type="password" id="password" name="password" value="{{old('password')}}" placeholder="Enter Password">
                <img src="{{ asset('Admin/images/Lock.png') }}" alt="lock" class="input_icon">
                <i class="fa-solid fa-eye-slash showImg eye_icon"></i>
            </div>
            <span class="text-danger validations" id="password-error"></span>
            <div class="w-100 text-end mb-3 mb-lg-4">
                <a href="{{ route('admin.forget-password') }}" class="forgotPassword">
                    Forgot Password?
                </a>
            </div>
            <button class="login_btn w-100" type="submit" id="loginMe">Sign In</button>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                location.reload();
            } else {
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.checkBox_label').click(function() {
            var checkbox = $('#checkbox');
            checkbox.prop('checked', !checkbox.prop('checked'));
        });
    });
    $(document).ready(function() {
        $('.showImg').click(function() {
            var passwordField = $('#password');
            var fieldType = passwordField.attr('type');
            if (fieldType === 'password') {
                passwordField.attr('type', 'text');
                $('.showImg').removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                passwordField.attr('type', 'password');
                $('.showImg').removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#loginForm').on('submit', function (e) {
            e.preventDefault(); 
            $('#emailError').text('');
            $('#passwordError').text('');
            const formData = $(this).serialize();
            $.ajax({
                url: "{{ route('admin.authenticate') }}", 
                method: "POST",
                data: formData,
                beforeSend: function() {
                    $('#loginMe').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (response) {
                    $('#loginMe').prop('disabled', false).html('Sign In');
                    window.location.href = response.data.redirect_url || location.href;
                },
                error: function (xhr) {
                    $('#loginMe').prop('disabled', false).html('Sign In');
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.error;
                        var errorMessages = '';
                        $.each(errors, function(key, value) {
                            errorMessages = value[0];
                            $('#' + key + '-error').html(errorMessages);
                        });
                        // toastr.warning("Validation error");
                    } else{
                        toastr.error(xhr.responseJSON.message);
                    }
                    
                }
            });
        });
    });
</script>
@endsection
