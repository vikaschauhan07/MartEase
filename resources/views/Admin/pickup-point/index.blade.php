@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') Pickup Point Management @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4">
        <div class="py-2 pb-4 d-flex flex-wrap gap-2 justify-content-between">
            <div class="d-flex flex-wrap gap-2">
                <div class="search_box position-relative">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
                    <input type="text" placeholder="Search" class="search_input">
                </div>
                <a href="{{route('admin.add-pickup-point')}}" class="add_btn text-decoration-none">+ Add</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th>Seriral No.</th>
                            <th>business name</th>
                            <th>address </th>
                            <th>Phone Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($pickuppoints->count() > 0)
                            @foreach($pickuppoints as $key => $pickuppoint)
                                <tr class="table_heading">
                                    <td>
                                        {{ ($pickuppoints->currentPage() - 1) * $pickuppoints->perPage() + $key + 1 }}</td>
                                    </td>
                                    <td>{{$pickuppoint->buisness_name}}</td>
                                    <td>{{$pickuppoint->address}}</td>
                                    <td>+1 {{$pickuppoint->phone_number}}</td>
                                    <td>
                                        <a href="http://">
                                            <img src="{{asset('Admin/images/delete.svg')}}" alt="" class="pointer">
                                        </a>
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
                {{ $pickuppoints->withQueryString()->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection