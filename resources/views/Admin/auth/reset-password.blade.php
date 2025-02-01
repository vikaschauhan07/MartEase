@extends('Admin.auth.layout.authlayout')
@section('title') Reset Password @endsection
@section('contents')
<style>
    .password-container {
        position: relative;
    }
    .eye-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }
    .eye-toggle i {
        color: #555;
    }
    .showImg{
        cursor: pointer;
    }
</style>
<div class="login_form_card">
    <div class="login-right-form">
        <h2 class="login_heading">Create New Password</h2>
        <p class="para_text font_16">Create your new password here.</p>
        <form action="{{ route('admin.reset-password') }}" method="post">
            @csrf
            <div class="position-relative">
                <label class="login_label" for="new_password">New Password</label>
                <input type="hidden" name="id" value="{{encrypt($user->id)}}">
                <input class="login_input w-100" type="password" id="new_password" name="new_password" placeholder="Enter New Password" value="{{old('new_password')}}">
                <img src="{{ asset('Admin/images/Lock.png') }}" alt="lock" class="input_icon">
                <i class="fa-solid fa-eye-slash showImg" data-toggle="new_password"></i>
            </div>
            @error('new_password')
                <span class="text-danger validations text-start mt-1">{{ $message }}</span>
            @enderror
            <div class="position-relative mt-3 mt-lg-4">
                <label class="login_label" for="confirm_password">Confirm Password</label>
                <input class="login_input w-100" type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" value="{{old('confirm_password')}}">
                <img src="{{ asset('Admin/images/Lock.png') }}" alt="lock" class="input_icon">
                <i class="fa-solid fa-eye-slash showImg" data-toggle="confirm_password"></i>
            </div>
            @error('confirm_password')
                <span class="text-danger validations text-start mt-1">{{ $message }}</span>
            @enderror
            <button class="login_btn w-100  mt-3 mt-lg-4" type="submit">Reset Password</button>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.showImg').on('click', function() {
            const fieldId = $(this).data('toggle');
            const $passwordField = $('#' + fieldId);
            if ($passwordField.attr('type') === 'password') {
                $passwordField.attr('type', 'text');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye'); // Change icon
            } else {
                $passwordField.attr('type', 'password');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash'); // Revert icon
            }
        });
    });
</script>
@endsection