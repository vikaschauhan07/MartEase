@extends('Admin.layouts.app')
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-user-list')}}">Users Management</a> > User Details</h3> @endsection
@section('content')
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="row">
            <div class="d-flex w-100">
                <div class="profilePhoto">
                    <figure class="inner mb-0">
                        <img id="previewImage" class=" detailProfile" src="{{asset($user->profile_image ?? 'Admin/images/nouser.svg')}}" alt="Preview Image">
                    </figure>
                </div>

                <div class="row w-100">
                    {{-- <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Driver ID</p>
                        <p class="detailTitle text-break">#E#E9592695926</p>
                    </div> --}}

                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Name</p>
                        <p class="detailTitle">{{$user->name}}</p>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Email</p>
                        <p class="detailTitle text-break">{{$user->email}}</p>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Phone Number</p>
                        <p class="detailTitle text-break">+1 {{$user->phone_number}}</p>
                    </div> 
                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Status</p>
                        @if($user->status == 1)
                            <p class="action_active">Active</p>
                        @else
                            <p class="action_active">Inactive</p>
                        @endif
                    </div>
                </div>
            </div>
                    
            <h5 class="my-3 detailTitle fw-bolder fs-6">Past Orders ({{$packages->count()}})</h5>

            <div class="card-body table-responsive p-0">
                    <table class="table">
                        <thead class="detail_table_body">
                            <tr class="table_heading">
                                <th>Parcel Ref No</th>
                                <th>Receiver Name</th>
                                <th>Order Date & Time</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="detail_table_body">
                            @if($packages->count() > 0)
                                @foreach($packages as $key => $package)
                                    <tr class="align-middle table_heading">
                                        <td>{{$package->reference_number}}</td>
                                        <td>{{$package->reciverDetails->name}}</td>
                                        <td>{{$package->created_at->format('d/m/Y, h:i A')}}</td>
                                        <td>{{$package->senderDetails->city}}</td>
                                        <td>{{$package->reciverDetails->city}}</td>
                                        <td>$ {{$package->shipping_fee}}</td>
                                        <td>
                                            {{\App\Helpers\ProjectConstants::PACKAGE_NAME_ARRAY[$package->type]}}
                                        </td> 
                                        <td>
                                            @switch($package->status)
                                                @case(1)
                                                    Shipped            
                                                    @break
                                                $@default
                                                    
                                            @endswitch
                                        </td>
                                    </tr>   
                                @endforeach    
                            @else
                                No Record Found
                            @endif
                        </tbody>
                    </table>
            </div>

        </div>
    </div>
</div>
@endsection