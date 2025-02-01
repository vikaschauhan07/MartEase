@extends('Admin.layouts.app')
@section('title') My Profile @endsection
@section('content')
<div class="profile_rapper mt-3 shadow">
    <section class="myProfile d-flex align-items-center justify-content-center flex-column">
        <div class="change-password-section">
            <h2 class="login_heading text-center mb-3 mb-lg-4">Change Password</h2>
            <form action="{{ route('admin.password-change') }}" method="post">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="position-relative">
                            <label class="login_label" for="">Old Password</label>
                                <input id="oldPassword" name="old_password" value="{{old('old_password')}}" class="login_input w-100" type="password" placeholder="Old password">
                                        <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}"
                                            alt="">
                                        <i class="fas fa-eye-slash showImg eye_icon" data-target="oldPassword"></i>
                        </div>
                    </div>
                    @error('old_password')
                        <span class="text-danger validations mt-1">{{ $message }}</span>
                    @enderror
                    <div class="col-lg-12">
                        <div class="position-relative mt-3 mt-lg-4">
                            <label class="login_label" for="">New Password</label>
                                <input id="newPassword" name="new_password" value="{{old('new_password')}}" class="login_input w-100" type="password" placeholder="New password">
                                        <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}"
                                            alt="">
                                        <i class="fas fa-eye-slash showImg eye_icon" data-target="newPassword"></i>
                        </div>
                    </div>
                    @error('new_password')
                        <span class="text-danger validations">{{ $message }}</span>
                    @enderror
                    <div class="col-lg-12">
                        <div class="position-relative mt-3 mt-lg-4">
                            <label class="login_label" for="">Confirm Password</label>
                                <input id="confirmPassword" name="confirm_password" class="login_input w-100" type="password" value="{{old('confirm_password')}}"
                                    placeholder="Confirm password">
                                        <img class="input_icon" src="{{ asset('Admin/images/Lock.png') }}"
                                            alt="">
                                        <i class="fas fa-eye-slash showImg eye_icon" data-target="confirmPassword"></i>
                        </div>
                    </div>
                    @error('confirm_password')
                        <span class="text-danger validations">{{ $message }}</span>
                    @enderror

                    <div class="col-lg-12">
                        <button class="login_btn w-100 mt-3 mt-lg-4">Submit</button>
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
@endsection