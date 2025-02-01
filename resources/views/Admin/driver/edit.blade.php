@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-driver-list')}}">Drivers Management</a> > Edit Driver</h3> @endsection
@section('content')
<div class="container-fluid ">
    <div class="profile_rapper d-block shadow mb-3">
        <div class="row" id="driverForm">
            <div class="profileSet">
                <figure class="inner mb-0">
                    <img id="previewImage" class="detailProfile" src="{{ asset( $driver->profile_image ?? 'Admin/images/dummy-profile.svg') }}" alt="Preview Image">
                    <img id="image-btn" class="editCameraSet" src="{{ asset('Admin/images/camera.svg') }}" alt="camera">
                    <input type="file" id="profile_image" name="profile_image" class="d-none" value="{{ old('file') }}" accept="image/*">
                </figure>
                <span class="text-danger validation" id="profile_image-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Name</label>
                    <input id="name" class="login_input paddingStartInput w-100" value="{{$driver->name}}" type="text" placeholder="Enter Name" required>
                </div>
                <span class="text-danger validation" id="name-error"> </span>
            </div>


            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Email</label>
                    <input class="login_input paddingStartInput w-100" value="{{$driver->email}}" id="email" type="email" placeholder="Enter Name" required>
                </div>
                <span class="text-danger validation" id="email-error"> </span>
            </div>


            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Phone Number</label>
                    <input class="login_input paddingStartInput w-100" id="phone_number" value="{{$driver->phone_number}}" type="text" placeholder="Enter Phone Number" required>
                </div>
                <span class="text-danger validation" id="phone_number-error"> </span>
            </div>

            <div class=" col-lg-12 mb-3">
                <h4 class="marginTopInput mb-0">Upload Driving license</h4>
            </div>

            <div class="col-lg-6">
                <p class="upload_img_title">Front Side</p>
                <label class="upload_image_box w-100" for="" id="driving_licence_front_input">
                    <img id="driving_licence_front_preview" class="mb-2 driver_documnets" src="{{ asset($driver->documents->where('type',1)->first()->document) }}" alt="camera">
                </label>
                <input type="file" id="driving_licence_front" class="d-none" value="" accept="image/*">
                <span class="validation text-danger" id="driving_licence_front-error"></span>
            </div>

            <div class="col-lg-6 ">
                <p class="upload_img_title">Back Side</p>
                <label class="upload_image_box w-100" for="" id="driving_licence_back_input">
                    <img id="driving_licence_back_preview" class="mb-2 driver_documnets" src="{{ asset($driver->documents->where('type',2)->first()->document) }}" alt="camera">
                </label>
                <input type="file" id="driving_licence_back" class="d-none" value="" accept="image/*">
                <span class="validation text-danger" id="driving_licence_back-error"></span>
            </div>


            <div class="col-lg-6 marginTop25">
                <p class="upload_img_title">Vehicle Registration</p>
                <label class="upload_image_box w-100" for="" id="vehicle_registration_input">
                    <img id="vehicle_registration_preview" class="mb-2 driver_documnets" src="{{ asset($driver->documents->where('type',3)->first()->document) }}" alt="camera">
                </label>
                <input type="file" id="vehicle_registration" class="d-none" value="" accept="image/*">
                <span class="validation text-danger" id="vehicle_registration-error"></span>
            </div>

            <div class="col-lg-6 marginTop25">
                <p class="upload_img_title">Insurance</p>
                <label class="upload_image_box w-100" for="" id="vehicle_insurance_input">
                    <img id="vehicle_insurance_preview" class="mb-2 driver_documnets" src="{{ asset($driver->documents->where('type',4)->first()->document) }}" alt="camera">
                </label>
                <input type="file" id="vehicle_insurance" class="d-none" value="" accept="image/*">
                <span class="validation text-danger" id="vehicle_insurance-error"></span>
            </div>

            <div class="col-lg-12 text-end mt-4">
                <button class="login_btn redBtn maxWidth189 shadow-none mb-2 me-3 w-100" onclick="saveEditForm()">
                    Cancel
                </button>
                <button class="login_btn shadow-none  maxWidth189 mb-2 w-100" onclick="saveEditForm(event)">
                    Add
                </button>
            </div>

        </div>
    </div>
</div>
</div>
<script>
    $(document).ready(function() {
        $('#image-btn').click(function() {
            $('#profile_image').click();
        });

        $('#driving_licence_front_input').click(function() {
            $('#driving_licence_front').click();
        });

        $('#driving_licence_back_input').click(function() {
            $('#driving_licence_back').click();
        });

        $('#vehicle_registration_input').click(function() {
            $('#vehicle_registration').click();
        });

        $('#vehicle_insurance_input').click(function() {
            $('#vehicle_insurance').click();
        });

        $('#profile_image').change(function() {
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

        $('#driving_licence_front').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileType = file.type;
                if (fileType.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#driving_licence_front_preview').attr('src', e.target.result);
                        $('#driving_licence_front_preview').addClass('driver_documnets');
                        $('#driving_licence_front_preview_span').addClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Please add a valid image.")
                }
            }
        });

        $('#driving_licence_back').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileType = file.type;
                if (fileType.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#driving_licence_back_preview').attr('src', e.target.result);
                        $('#driving_licence_back_preview').addClass('driver_documnets');
                        $('#driving_licence_back_preview_span').addClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Please add a valid image.")
                }
            }
        });

        $('#vehicle_registration').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileType = file.type;
                if (fileType.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#vehicle_registration_preview').attr('src', e.target.result);
                        $('#vehicle_registration_preview').addClass('driver_documnets');
                        $('#vehicle_registration_preview_span').addClass('d-none');
                    }
                    reader.readAsDataURL(file);
                } else {
                    toastr.warning("Please add a valid image.")
                }
            }
        });

        $('#vehicle_insurance').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var fileType = file.type;
                if (fileType.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#vehicle_insurance_preview').attr('src', e.target.result);
                        $('#vehicle_insurance_preview').addClass('driver_documnets');
                        $('#vehicle_insurance_preview_span').addClass('d-none');
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
    function saveEditForm(event) {
        event.preventDefault();
        let formData = new FormData();
        formData.append("driver_id", {{$driver->id}});
        if($("#profile_image")[0].files[0]){
            formData.append("profile_image", $("#profile_image")[0].files[0]);
        }
        formData.append("name", $("#name").val());
        formData.append("email", $("#email").val());
        formData.append("phone_number", $("#phone_number").val());
        if($("#driving_licence_front")[0].files[0]){
            formData.append("driving_licence_front", $("#driving_licence_front")[0].files[0]);
        }
        if($("#driving_licence_back")[0].files[0]){
            formData.append("driving_licence_back", $("#driving_licence_back")[0].files[0]);
        }
        if($("#vehicle_registration")[0].files[0]){
            formData.append("vehicle_registration", $("#vehicle_registration")[0].files[0]);
        }
        if($("#vehicle_insurance")[0].files[0]){
            formData.append("vehicle_insurance", $("#vehicle_insurance")[0].files[0]);
        }
        $.ajax({
            url: "{{ route('admin.edit-driver-post') }}", 
            type: "POST",
            data: formData,
            processData: false, 
            contentType: false, 
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            success: function (response) {
                toastr.success(response.message);
                // window.location.href = response.data.redirect_url;
            },
            error: function (xhr) {
                if(xhr.status == 422){
                    var errors = xhr.responseJSON.error;
                    var errorMessages = '';
                    $.each(errors, function(key, value) {
                        errorMessages = value[0];
                        $('#' + key + '-error').html(errorMessages);
                    });
                    toastr.warning("Validation error");
                } else {
                    toastr.error(xhr.responseJSON.message);
                }
            }
        });
    }
</script>
@endsection