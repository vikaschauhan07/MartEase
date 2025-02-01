@extends('Admin.layouts.app')
@section('title') Trips @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-trip-list')}}">Trips Management</a> > Add Trip</h3> @endsection
@section('content')
<div class="container-fluid ">
    <div class="profile_rapper d-block shadow mb-3">
        <div class="row">
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative custom_select">
                    <label class="login_label z_index999" for="fromCity">Starting Location</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="fromCity">
                            <option value="0" selected>Select</option>
                            @foreach($cityArray as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>    
                            @endforeach
                        </select>
                    </div>
                </div>
                <span class="text-danger validation" id="from_city-error"></span>
            </div>
            
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative custom_select">
                    <label class="login_label z_index999" for="toCity">Destination</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="toCity">
                            <option value="0" selected>Select</option>
                            @foreach($cityArray as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>    
                            @endforeach
                        </select>
                    </div>
                </div>
                <span class="text-danger validation" id="to_city-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative custom_select">
                    <label class="login_label z_index999" for="">Select Trailer</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="trailerNumber">
                            <option value="0">Select</option>
                            @foreach($trailers as $trailer)
                                <option value="{{$trailer->id}}">{{$trailer->trailer_number}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <span class="text-danger validation" id="trailer_number-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Set delivery price</label>
                    <input class="login_input paddingStartInput w-100" id="deliveryPrice" type="number" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="delivery_price-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Pick up Location</label>
                    <input class="login_input paddingStartInput w-100" id="pickUpLocation" type="text" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="pickup_location-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Drop off Location</label>
                    <input class="login_input paddingStartInput w-100" id="dropOffLocation" type="text" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="dropoff_location-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Date</label>
                    <input class="login_input paddingStartInput w-100" id="pickUpDate" type="date" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="pickup_date-error"></span>
            </div>
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative custom_select">
                    <label class="login_label z_index999" for="">Starting Time Window</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="pickUpWindow">
                            <option selected>Select</option>
                            @foreach($time_slots_array as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <span class="text-danger validation" id="pickup_window-error"></span>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative custom_select">
                    <label class="login_label z_index999" for="">Ending Time Window</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="dropOffWindow">
                            <option selected>Select</option>
                            @foreach($time_slots_array as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <span class="text-danger validation" id="dropoff_window-error"></span>

            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">distance</label>
                    <input class="login_input paddingStartInput w-100" id="distance" type="number" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="distance-error"></span>
            </div>
        </div>
        <p class="upload_img_title marginTopInput">Trailer description</p>
        <div class="row">
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">trailer Size (L*W*H)</label>
                    <input class="login_input paddingStartInput w-10" id="trailerLength" type="number" placeholder="Enter" required>
                    *
                    <input class="login_input paddingStartInput w-10" id="trailerBreadth" type="number" placeholder="Enter" required>
                    *
                    <input class="login_input paddingStartInput w-00" id="trailerHeight" type="number" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="trailer_length-error"></span>
                <span class="text-danger validation" id="trailer_breadth-error"></span>
                <span class="text-danger validation" id="trailer_height-error"></span>
            </div>
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">trailer Weight</label>
                    <input class="login_input paddingStartInput w-100" id="trailerWeight" type="number" placeholder="Enter" required>
                </div>
                <span class="text-danger validation" id="trailer_weight-error"></span>
            </div>
            <div class="col-lg-12 text-end mt-4">
                {{-- <button class="login_btn redBtn maxWidth189 shadow-none mb-2 me-3 w-100" onclick="saveEditForm()">
                    Cancel
                </button> --}}
                <button class="login_btn shadow-none  maxWidth189 mb-2 w-100" onclick="saveTripData(event)">
                    Add
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    function saveTripData(event){
        $(".validation").html('');
        var pickupDate = $('#pickUpDate').val();
        var formattedPickupDate = moment(pickupDate).format('DD-MM-YYYY');
        var formData = {
            from_city: $('#fromCity').val(),
            to_city: $('#toCity').val(),
            trailer_number: $('#trailerNumber').val(),
            delivery_price: $('#deliveryPrice').val(),
            pickup_date: formattedPickupDate,
            pickup_window: $('#pickUpWindow').val(),
            dropoff_window: $('#dropOffWindow').val(),
            distance: $('#distance').val(),
            trailer_length: $('#trailerLength').val(),
            trailer_breadth: $('#trailerBreadth').val(),
            trailer_height: $('#trailerHeight').val(),
            trailer_weight: $('#trailerWeight').val(),
            dropoff_location: $("#dropOffLocation").val(),
            pickup_location: $("#pickUpLocation").val(),
        };

        $.ajax({
            url: "{{ route('admin.add-trip-post') }}", 
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
<script>
    $(document).ready(function () {
        $('#fromCity').on('change', function () {
            const fromCity = $(this).val();
            $('#toCity option').each(function () {
                $(this).prop('disabled', $(this).val() === fromCity);
            });
            $('#toCity').val(0)
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#pickupStartTime, #pickupEndTime').on('change', function () {
            const startTime = $('#pickupStartTime').val();
            const endTime = $('#pickupEndTime').val();
            if (startTime && endTime && startTime >= endTime) {
                $('#pickupWindowError').show();
            } else {
                $('#pickupWindowError').hide();
            }
        });
    });
</script>
@endsection
