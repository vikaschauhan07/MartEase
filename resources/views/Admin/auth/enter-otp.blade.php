@extends('Admin.auth.layout.authlayout')
@section('title') Verify Otp @endsection
@section('contents')
    <div class="login_form_card">
        <div class="login-right-form">
        <img src="{{asset('Admin/images/forgot-img.svg')}}" alt="Message" class="forgot-icon mb-2 mb-lg-3">
            <h2 class="text-center login_heading">Enter OTP</h2>
            <p class="text-center para_text font_16 mb-4">Enter the OTP that we just sent you on your
                email address to reset your password.</p>
            <form id="otp-form" action="{{route('admin.verify-otp')}}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{encrypt($user->id)}}">
                <div class="d-flex justify-content-center align-items-center gap-3  form-group digitGroup" id="otp" required>
                    <input type="digit" id="first" name="digit[]" data-next="second" class="otp_input" maxlength="1" required>
                    <input type="digit" id="second" name="digit[]" data-next="third" data-previous="first" class="otp_input" maxlength="1" required>
                    <input type="digit" id="third" name="digit[]" data-next="fourth" data-previous="second" class="otp_input" maxlength="1" required>
                    <input type="digit" id="fourth" name="digit[]" data-next="fifth" data-previous="third" class="otp_input" maxlength="1" required>
                </div>
                <span class="text-danger text-center validations" id="otp-validation"></span>
                <button class="login_btn w-100 mb-3 mb-lg-4 mt-3 mt-lg-4" type="submit" id="verify-button">Verify</button>
                <a href="{{route('admin.resend-otp',['id'=>encrypt($user->id)])}}" class="text-center font_16">Didn’t receive your code? <span class="otp_span_text">Resend</span></a>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.digitGroup').find('input').each(function(index) {
                $(this).attr('maxlength', 1);
                $(this).on('keyup', function(e) {
                    var currentValue = $(this).val();
                    var parent = $($(this).parent());
                    var prevIndex = index - 1;
                    var nextIndex = index + 1;

                    if (e.keyCode === 8) {
                        $(this).val("");
                        if (prevIndex >= 0) {
                            var prevInput = $('.digitGroup').find('input').eq(prevIndex);
                            prevInput.focus();
                        }
                    }

                    if (((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode >= 65 && e.keyCode <= 90) || (e.keyCode >= 96 && e.keyCode <= 105) || e.keyCode === 39) && currentValue !== "") {
                        var next = parent.find('input#' + $(this).data('next'));
                        if (next.length) {
                            $(next).focus();
                        } else {
                            if (parent.data('autosubmit')) {
                                parent.submit();
                            }
                        }
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#otp-form').on('submit', function (e) {
                e.preventDefault(); 
                $('#otpError').text('');
                const formData = $(this).serialize();
                $.ajax({
                    url: "{{ route('admin.verify-otp') }}",
                    method: "POST",
                    data: formData,
                    beforeSend: function() {
                        $('#verify-button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#verify-button').prop('disabled', false).html('Verify');
                        window.location.href = response.data.redirect_url || location.href;
                    },
                    error: function (xhr) {
                        $('#verify-button').prop('disabled', false).html('Verify');
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.error;
                            var errorMessages = '';
                            $.each(errors, function (key, value) {
                                errorMessages = value[0]; 
                                $('#otp-validation').text(errorMessages); 
                                return false; 
                            });
                        }  else{
                            toastr.error(xhr.responseJSON.message);
                        }
                    }
                });
            });
        });
    </script>
@endsection