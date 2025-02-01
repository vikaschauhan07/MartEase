@extends('Admin.layouts.app')
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-user-list')}}">User Management</a> > User Details</h3> @endsection
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
                            <p class="green">Active</p>
                        @else
                            <p class="red">Inactive</p>
                        @endif
                    </div>
                </div>
            </div>
                    
        </div>
    </div>
</div>
@endsection