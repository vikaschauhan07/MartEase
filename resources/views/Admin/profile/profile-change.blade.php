@extends('Admin.layouts.app')
@section('title') My Profile @endsection
@section('content')
<div class=" profile_rapper mt-3 shadow">
    <section class="myProfile d-flex align-items-center justify-content-center flex-column">
        <h2 class="login_heading text-center mb-3 mb-lg-4">Change Profile</h2>
        <div class="container-fluid ">
            <div class="row align-items-center">
                <form id="edit-profile-form" action="{{ route('admin.profile-change') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-lg-12">
                        <div class="uploadImg d-flex flex-column align-items-center justify-content-center mb-4 mb-lg-5">
                            <figure class="inner">
                                <div class="image p-0">
                                    <img id="previewImage" class="uploadFile profile_page_img"
                                        src="{{ asset(Auth::guard('admin')->user()->profile_pic ?? 'Admin/images/nouser.svg') }}"
                                        alt="Preview Image">
                                </div>
                                <i class="fa-solid fa-plus addSign" id="haveClick"></i>
                                <input type="file" id="myFile" name="profile_pic" class="d-none" value="{{ old('file') }}" accept=".jpg, .jpeg, .png" />
                            </figure>
                            @error('profile_pic')
                                <span class="text-danger validations">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="position-relative">
                            <label class="login_label" for="">Name</label>
                            <input class="login_input w-100" name="name" value="{{ Auth::guard('admin')->user()->name }}"
                                type="text" placeholder="Name" required>
                            <img class="input_icon" src="{{ asset('Admin/images/Profile.svg') }}" alt="">
                        </div>
                    </div>
                    @error('name')
                        <span class="text-danger validations mt-1">{{ $message }}</span>
                    @enderror
                    <div class="col-lg-12">
                        <div class="position-relative mt-3 mt-lg-4">
                            <label class="login_label" for="">Mail</label>
                            <input class="login_input w-100" value="{{ Auth::guard('admin')->user()->email }}" type="email"
                                placeholder="eMail" disabled>
                            <img class="input_icon" src="{{ asset('Admin/images/Message.png') }}" alt="">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button class="login_btn w-100 mt-3 mt-lg-4"
                            onclick="saveEditForm()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

</div>
<script>
    $(document).ready(function() {
        $('#haveClick').click(function() {
            $('#myFile').click();
        });

        $('#myFile').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileType = file.type;
                if (fileType.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImage').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Please add a valid image.")
                }
            }
        });
    });
</script>
<script>
    function saveEditForm() {
        var isValid = true;
        $("input[required]").each(function() {
            if (!$(this).val()) {
                isValid = false;
                return false;
            }
        });
    }
</script>
<style>
#haveClick {
    position: relative;
    bottom: 30px;
    left: 80px;
    border: 1px solid #18ABE3;
    background: #18ABE3;
    color: #fff;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
}
.profile_page_img {
    width: 100px !important;
    height: 100px !important;
    border-radius: 50%;
    object-fit: cover;
}
</style>
@endsection