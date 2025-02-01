@extends('Admin.layouts.app')
@section('title') Trips @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-trip-list')}}">Trips Management</a> > View Trip</h3> @endsection
@section('content')

<div class="container-fluid">
    <div class="profile_rapper d-block shadow">

        <div class="d-flex w-100 align-items-center justify-content-start flex-wrap gap-4">

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Trip number</p>
                <p class="detailTitle min_width280">#{{$trip->id}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Starting Point</p>
                <p class="detailTitle min_width280 text-break">{{$trip->from_city}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Destination</p>
                <p class="detailTitle min_width280 text-break">{{$trip->to_city}}</p>
            </div> 

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Assigned Driver</p>
                <p class="detailTitle min_width280 text-break">Jason Peter</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Delivery Price</p>
                <p class="detailTitle min_width280 text-break">${{$trip->delivery_price}}</p>
            </div>
            
            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Date</p>
                <p class="detailTitle min_width280 text-break">{{$trip->pickup_date}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Starting Time Window</p>
                <p class="detailTitle min_width280 text-break">{{\App\Helpers\ProjectConstants::TIME_SLOTS[$trip->pickup_window]}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Ending Time Window</p>
                <p class="detailTitle min_width280 text-break">{{\App\Helpers\ProjectConstants::TIME_SLOTS[$trip->dropoff_window]}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Trailer Number</p>
                <p class="detailTitle min_width280 text-break">{{$trip->trailer_number}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Distance</p>
                <p class="detailTitle min_width280 text-break">{{$trip->distance}} Km</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Pick Up Location</p>
                <p class="detailTitle min_width280 text-break">{{$trip->pickup_location}}</p>
            </div>

            <div class=" ">
                <p class="detailTitle min_width280 opacity-50">Drop Off Location</p>
                <p class="detailTitle min_width280 text-break">{{$trip->dropoff_location}}</p>
            </div>

        </div>

        <p class="upload_img_title mb-2">Trailer description</p>

        <div class="d-flex w-100 align-items-center justify-content-start flex-wrap gap-4">
            
            <div class="">
                <p class="detailTitle min_width280 opacity-50">Trailer Size</p>
                <p class="detailTitle min_width280 text-break">
                    {{$trip->trailer_length}} * {{$trip->trailer_breadth}} * {{$trip->trailer_height}} Ft 
                </p>
            </div>

            <div class="">
                <p class="detailTitle min_width280 opacity-50">Weight</p>
                <p class="detailTitle min_width280 text-break">{{$trip->trailer_weight}} Kg</p>
            </div>
        </div>
        
        
        <!-- <h5 class="mb-3 detailTitle fw-bolder fs-6">Attached Images</h5>

        <div class="d-flex justify-content-start flex-wrap gap-3">
            <div class="mb-2">
                <label class="upload_image_box trip_view_img" for="" id="vehicle_registration_input">
                    <img class="parcel_image img-fluid" src="{{ asset('Admin/images/parcel-img.svg') }}" alt="" class="parcel_image">
                </label>
            </div>

            <div class="">
                <label class="upload_image_box trip_view_img" for="" id="vehicle_registration_input">
                    <img class="parcel_image img-fluid" src="{{ asset('Admin/images/parcel-img.svg') }}" alt="" class="parcel_image">
                </label>
            </div>
        </div> -->
      
    </div>
</div>
@endsection