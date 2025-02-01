@extends('Admin.layouts.app')
@section('title') Parcel @endsection
@section('page-title') Parcel Management @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4">
        <div class="py-2 pb-4 d-flex justify-content-between">
            <div class="d-flex gap-2 flex-wrap">
                <div class="search_box position-relative">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
                    <input type="text" placeholder="Search" class="search_input">
                </div>
                <div class="tab-container">
                    <div class="tabs">
                        <a href="{{route('admin.get-parcel-list', ['status' => 1])}}" class="tab-button @if($status == 1) active @endif">Pending</a>
                        <a href="{{route('admin.get-parcel-list', ['status' => 2])}}" class="tab-button @if($status == 2) active @endif">Processed</a>
                        <a href="{{route('admin.get-parcel-list', ['status' => 3])}}" class="tab-button @if($status == 3) active @endif">In Transit</a>
                        <a href="{{route('admin.get-parcel-list', ['status' => 4])}}" class="tab-button @if($status == 4) active @endif">Claimed By Recipient</a>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr class="table_heading">
                        <th>Parcel Ref No</th>
                        <th>Shipper</th>
                        <th>Recipient</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Price</th>
                        <th>Size</th>
                        <th class="ps-3">Status</th>
                        <th class="ps-3">Trailer No</th>
                        <th>Generate Label</th>
                    </tr>
                </thead>
                <tbody>
                    @if($packages->count() > 0)
                        @foreach($packages as $key => $package)
                            <tr class="align-middle table_heading">
                                <td>{{$package->reference_number}}</td>
                                <td>{{$package->senderDetails->name}}</td>
                                <td>{{$package->reciverDetails->name}}</td>
                                <td>{{$package->senderDetails->name}}</td>
                                <td>{{$package->reciverDetails->city}}</td>
                                <td>${{$package->shipping_fee}}</td>
                                <td>
                                    {{ \App\Helpers\ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN" }}
                                </td>
                                <td>
                                    @switch($package->status)
                                        @case(1)
                                            <p class="driver_pending common_p_driver m-0">Pending</p>
                                            @break
                                        @case(2)
                                            <p class="driver_reject common_p_driver m-0">Processed</p>
                                            @break
                                        @case(3)
                                            <p class="driver_pending  common_p_driver m-0">In Transit</p>
                                            @break
                                        @case(4)
                                            <p class="driver_pending  common_p_driver m-0">Claimed By Recipient</p>
                                            @break
                                        @default
                                            Nill
                                    @endswitch
                                </td>
                                <td>
                                    21156
                                </td>
                                <td>
                                    <a class="blue_status" href="{{route('admin.view-parcel',['package_id' => encrypt($package->id)])}}">
                                        More Info
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        No Record Found
                    @endif
                </tbody>
            </table>
        </div>
        <div class="card-footer mt-3">
            {{ $packages->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection