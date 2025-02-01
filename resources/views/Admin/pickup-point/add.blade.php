@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-pickup-points')}}">Manage Pickup Point</a> > Add Pickup Point</h3> @endsection
@section('content')

<div class="container-fluid ">
    <div class="profile_rapper d-block shadow mb-3">
        <div class="row">
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">business name</label>
                    <input class="login_input paddingStartInput w-100" id="buisnessName" placeholder="Enter " required>
                </div>
                <span class="text-danger validation" id="buisness_name-error"></span>
            </div>
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">phone number.</label>
                    <input class="login_input paddingStartInput w-100" id="phoneNumber" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="phone_number-error"></span>
            </div>
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">address</label>
                    <input class="login_input paddingStartInput w-100" id="address" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="address-error"></span>
            </div>
            <div class="col-lg-12 text-end mt-4">
                <button class="login_btn redBtn maxWidth189 shadow-none mb-2 me-3 w-100">
                    Cancel
                </button>
                <button class="login_btn shadow-none  maxWidth189 mb-2 w-100" onclick="savePickupData(event)">
                    Add
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    function savePickupData(event){
        var formData = {
            buisness_name: $('#buisnessName').val(),
            address: $('#address').val(),
            phone_number: $('#phoneNumber').val()
        };
        $.ajax({
            url: "{{ route('admin.add-pickup-point-post') }}", 
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            success: function(response) {
                window.location.href = response.data.redirect_url;
            },
            error: function(xhr, status, error) {
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