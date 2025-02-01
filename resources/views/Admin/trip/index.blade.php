@extends('Admin.layouts.app')
@section('title') Trips @endsection
@section('page-title') Trips Management @endsection
@section('content')

<div class="container-fluid">
    <div class="table_box mb-4">
        <div class="py-2 pb-4 d-flex justify-content-between">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="search_box position-relative">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
                    <input type="text" placeholder="Search" class="search_input">
                </div>
                <div class="tab-container me-2">
                    <div class="tabs">
                        <a href="{{route('admin.get-trip-list', ['status' => 1])}}" class="tab-button @if($status == 1) active @endif">Pending</a>
                        <a href="{{route('admin.get-trip-list', ['status' => 2])}}" class="tab-button @if($status == 2) active @endif">Booked</a>
                        <a href="{{route('admin.get-trip-list', ['status' => 3])}}" class="tab-button @if($status == 3) active @endif">In Transit</a>
                        <a href="{{route('admin.get-trip-list', ['status' => 4])}}" class="tab-button @if($status == 4) active @endif">Completed</a>
                    </div>
                </div>
            </div>
            <div class="d-flex">
                <a href="{{route('admin.add-trip')}}" class="add_btn me-2 text-decoration-none">+ New Trip</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr class="table_heading">
                        <th>Trip number</th>
                        <th>Trailer number</th>
                        <th>Starting point</th>
                        <th>Destination</th>
                        <th>Distance</th>
                        @if($status != 1)
                            <th>Assigned Driver</th>
                        @endif
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if($trips->count() > 0)
                        @foreach($trips as $key => $trip)
                            <tr class="align-middle table_heading">
                                <td>
                                    {{ ($trips->currentPage() - 1) * $trips->perPage() + $key + 1 }}
                                </td>
                                <td>{{$trip->trailer_number}}</td>
                                <td>{{$trip->from_city}}</td>
                                <td>{{$trip->to_city}}</td>
                                <td>{{$trip->distance}} Km</td>
                                @if($status != 1)
                                    <td>
                                        The Driver
                                    </td>
                                @endif
                                <td>
                                    @switch($trip->status)
                                        @case(1)
                                            Pending
                                        @break
                                        @case(2)
                                            Booked
                                        @break
                                        @case(3)
                                            In Transit
                                        @break
                                        @case(4)
                                            Completed
                                        @break
                                        @default
                                            UNKNOWN
                                    @endswitch
                                </td>
                                
                                <td>
                                    <div class="item_center">
                                        <a href="{{route('admin.view-trip',['trip_id' => encrypt($trip->id)])}}">
                                            <img class="me-2 action_icon" src="{{asset('Admin/images/view.svg')}}" alt="action-icon" />
                                        </a>
                                        <a href="{{route('admin.edit-trip',['trip_id' => encrypt($trip->id)])}}">
                                            <img class="me-2 action_icon" src="{{asset('Admin/images/edit-icon.svg')}}" alt="action-icon" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td>No Record Found</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer mt-3">
            {{ $trips->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection