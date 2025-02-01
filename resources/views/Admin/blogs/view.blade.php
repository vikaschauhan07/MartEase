@extends('Admin.layouts.app')
@section('title') Blog @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-blog-list')}}">Blog Management</a> > View Blog</h3> @endsection
@section('content')
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{route('admin.add-blog')}}" class="add_btn text-decoration-none">Add New Blog</a>
                    <a href="{{route('admin.edit-blog',['blog_id'=>encrypt($blog->id)])}}" class="add_btn text-decoration-none">Edit Blog</a>
                    <a href="javascript:void(0);" class="add_btn text-decoration-none" onclick="confirmDelete('{{ route('admin.delete-blog', ['blog_id' => encrypt($blog->id)]) }}')">Delete Blog</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-3 mb-lg-4">
                <div class="position-relative">
                    <p class="login_input paddingStartInput w-100"> {{$blog->title}}</p>
                </div>
            </div>
            <div class="col-lg-12 mb-3 mb-lg-4">
                {!! $blog->content !!}
            </div>
            <div class="col-lg-12 mb-3 mb-lg-4">
                @if($blog->blogFiles->count() > 0)
                    @foreach($blog->blogFiles as $file)
                        <span class="pip">
                            <img class="imageThumb" src="{{$file->file}}">
                        </span>
                    @endforeach
                @endif
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