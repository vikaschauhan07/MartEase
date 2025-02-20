@extends('Admin.layouts.app')
@section('title') Category @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-all-category')}}">Category Management</a> > Add Category</h3> @endsection
@section('content')
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{route('admin.add-category')}}" class="add_btn text-decoration-none">Add New Category</a>
                    <a href="{{route('admin.edit-category',['category_id'=>encrypt($category->id)])}}" class="add_btn text-decoration-none">Edit Category</a>
                    {{-- <a href="javascript:void(0);" class="add_btn text-decoration-none" onclick="confirmDelete('{{ route('admin.delete-blog', ['blog_id' => encrypt($category->id)]) }}')">Delete Category</a> --}}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-3 mb-lg-4">
                <div class="position-relative">
                    <p class="login_input paddingStartInput w-100"> {{$category->name}}</p>
                </div>
            </div>
            <div class="col-lg-12 mb-3 mb-lg-4">
                <div class="position-relative">
                    <p class="login_input paddingStartInput w-100"> {{$category->description}}</p>
                </div>
            </div>
            <div class="col-lg-12 mb-3 mb-lg-4">    
                <span class="pip">
                    <img class="imageThumb" src="{{$category->image}}">
                </span>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmDelete(deleteUrl) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl; 
            }
        });
    }
</script>
@endsection