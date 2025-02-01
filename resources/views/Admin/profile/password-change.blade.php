@extends('Admin.layouts.app')
@section('title') My Profile @endsection
@section('content')
<div class="profile_rapper mt-3 shadow">
    <section class="myProfile d-flex align-items-center justify-content-center flex-column">
        <div class="change-password-section">
            <h2 class="login_heading text-center mb-3 mb-lg-4">Change Password</h2>
            <form>
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="position-relative">
                            <label class="login_label" for="">Old Password</label>
                            <input id="oldPassword" name="old_password" value="{{old('old_password')}}" class="login_input w-100" type="password" placeholder="Old password">
                            <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}" alt="">
                            <i class="fas fa-eye-slash showImg eye_icon" data-target="oldPassword"></i>
                        </div>
                    </div>
                    <span class="text-danger validations mt-1" id="old_password-error"></span>
                    <div class="col-lg-12">
                        <div class="position-relative mt-3 mt-lg-4">
                            <label class="login_label" for="">New Password</label>
                            <input id="newPassword" name="new_password" value="{{old('new_password')}}" class="login_input w-100" type="password" placeholder="New password">
                            <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}" alt="">
                            <i class="fas fa-eye-slash showImg eye_icon" data-target="newPassword"></i>
                        </div>
                    </div>
                    <span class="text-danger validations" id="new_password-error"></span>
                    <div class="col-lg-12">
                        <div class="position-relative mt-3 mt-lg-4">
                            <label class="login_label" for="">Confirm Password</label>
                            <input id="confirmPassword" name="confirm_password" class="login_input w-100" type="password" value="{{old('confirm_password')}}" placeholder="Confirm password">
                            <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}" alt="">
                            <i class="fas fa-eye-slash showImg eye_icon" data-target="confirmPassword"></i>
                        </div>
                    </div>
                    <span class="text-danger validations" id="confirm_password-error"></span>
                    <div class="col-lg-12">
                        <button class="login_btn w-100 mt-3 mt-lg-4" onclick="changePassword(event)">Change Password</button>
                    </div>
                </div>
            </form>
        </div>

    </section>
</div>
<script>
    $(document).ready(function() {
        $('.showImg').click(function() {
            var targetInputId = $(this).data('target');
            var inputField = $('#' + targetInputId);
            var inputType = inputField.attr('type');

            if (inputType === 'password') {
                inputField.attr('type', 'text');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            } else {
                inputField.attr('type', 'password');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    });
</script>
<script>
    function changePassword(event){
        event.preventDefault();
        $(".validations").html('');
        // Swal.fire({
        //     title: 'Changing Password',
        //     text: 'Please wait while we change password...',
        //     allowOutsideClick: false,
        //     showConfirmButton: false,
        //     willOpen: () => {
        //         Swal.showLoading();
        //     }
        // });
        var formData = {
            old_password: $('#oldPassword').val(),
            new_password: $('#newPassword').val(),
            confirm_password: $("#confirmPassword").val()
        };
        $.ajax({
            url: "{{ route('admin.password-change') }}", 
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            success: function(response) {
                Swal.close(); 
                location.reload();
            },
            error: function(xhr, status, error) {
                Swal.close(); 
                if (xhr.status == 422) {
                    var errors = xhr.responseJSON.error;
                    $.each(errors, function(key, value) {
                        $('#' + key + '-error').html(value[0]);
                    });
                    // toastr.warning("Validation error");
                } else {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }
</script>
@endsection