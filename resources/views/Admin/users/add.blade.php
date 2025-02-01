@extends('Admin.layouts.app')
@section('title') Users @endsection
@section('page-title') <h3 class="mb-0 page_title"><span>Users Management</span> > Add User</h3> @endsection
@section('content')
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="row">
            <div class="profileSet">
                <figure class="inner mb-0">
                    <img id="previewImage" class="uploadProfile" src="{{ asset(Auth::guard('admin')->user()->profile_pic ?? 'Admin/images/dummy-profile.svg') }}" alt="Preview Image">
                    <img id="haveClick" class="cmeraSet" src="{{ asset('Admin/images/camera.svg') }}" alt="camera">
                    <input type="file" id="myFile" name="profile_pic" class="d-none" value="{{ old('file') }}" accept="image/*">
                </figure>
            </div>
                    
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Name</label>
                    <input class="login_input paddingStartInput w-100" name="name" 
                        type="text" placeholder="Enter Name" required>
                </div>
            </div>

            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Email</label>
                    <input class="login_input paddingStartInput w-100" name="name" 
                        type="email" placeholder="Enter Name" required>
                </div>
            </div>
            
            <div class="col-lg-6 marginTopInput">
                <div class="position-relative">
                    <label class="login_label" for="">Phone Number</label>
                    <input class="login_input paddingStartInput w-100" name="name" 
                        type="text" placeholder="Enter Phone Number" required>
                </div>
            </div>

            <div class="col-lg-12 text-end mt-4">
                <button class="login_btn redBtn maxWidth189 shadow-none mb-2 me-3 w-100"
                    onclick="saveEditForm()">
                    Cancel
                </button> 
                <button class="login_btn shadow-none  maxWidth189 mb-2 w-100"
                    onclick="saveEditForm()">
                    Add
                </button>
            </div>
        </div>
    </div>
</div>
@endsection