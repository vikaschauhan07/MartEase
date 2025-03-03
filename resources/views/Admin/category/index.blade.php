@extends('Admin.layouts.app')
@section('title') Blogs @endsection
@section('page-title') Blogs Management @endsection
@section('content')
<style>
    .active {
        background: white;
        color: red !important;
    }
</style>
<div class="container-fluid">
    <div class="table_box mb-3">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
                <div class="d-flex flex-wrap gap-2 me-2">
                    <a href="{{route('admin.add-category')}}" class="add_btn text-decoration-none">Add Category</a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                <a href="{{route('admin.get-all-category')}}" class="add_btn text-decoration-none me-2 @if($isRequested == 0) active @endif">App Category</a>
                    <a href="{{route('admin.get-all-category',['isRequested' => 1])}}" class="add_btn text-decoration-none @if($isRequested == 1) active @endif">Requested Category</a>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">

                <div class="search_box position-relative">
                    <input type="text" placeholder="Search" value="{{$search}}" class="search_input" id="search-input">
                </div>
                <div class="select-menu main_filter_select m-0">
                    
                    <div class="select">
                        <span>
                            @if($status == 1)
                                Inactive
                            @elseif($status == 2)
                                Active
                            @else 
                                Filter
                            @endif
                        </span>
                        <i class="fas fa-angle-down"></i>
                    </div>
                    <div class="options-list">
                        <div class="option" data-value="2" {{ $status == '2' ? 'selected' : '' }}>
                            Active
                        </div>
                        <div class="option" data-value="1" {{ $status == '1' ? 'selected' : '' }}>
                            Inactive
                        </div>
                    </div>
                    <input type="hidden" name="filter" id="filterInput" value="{{$status}}">
                </div>
                <button class="search_BUTTON" onclick="searchFunction(event)"> 
                    <i class="fa-solid fa-magnifying-glass" title="Search"></i>
                </button>
                <button class="search-reset-btn search_BUTTON" onclick="resetSearch(event)">
                    <i class="fa-solid fa-xmark" title="Reset search"></i>
                </button>
            </div>
        </div>
       
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th style="width: 100px">Serial No.</th>
                            <th>Name</th>
                            <th class="ps-3">Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($categorys->count() > 0)
                            @foreach($categorys as $key => $category)
                            <tr class="align-middle table_heading">
                                <td>
                                    {{ ($categorys->currentPage() - 1) * $categorys->perPage() + $key + 1 }}
                                </td>
                                
                                <td>{{$category->name}}</td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" id="togBtn-{{$category->id}}" @if($category->status == 1) checked @endif onchange="changeStatus(event, {{$category->id}},{{$category->status}})" >
                                        <div class="slider round">
                                          <span class="on">Active</span>
                                          <span class="off">Inactive</span>
                                        </div>
                                    </label>
                                </td>
                                <td>
                                    <div class="item_center">
                                        <a href="{{route('admin.view-category',["category_id" => encrypt($category->id)])}}">
                                            <img class="me-2 action_icon" src="{{asset('Admin/images/view.svg')}}" alt="action-icon" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <td
                                className="text-center p-2 p-lg-3 p-xl-5"
                                colSpan="100%"
                                style="text-align: center; vertical-align: middle; height: 150px;"
                            
                            >
                                No Record Found
                            </td>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer mt-3">
            {{ $categorys->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<script>
    const $select = $(".select");
    const $optionsList = $(".options-list");
    const $options = $(".option");
    const $filterInput = $('#filterInput');

    $select.on("click", function() {
        $optionsList.toggleClass("active");
        $select.find(".fa-angle-down").toggleClass("fa-angle-up");
    });

    $options.on("click", function() {
        $options.removeClass("selected");
        $select.find("span").html($(this).html());
        $(this).addClass("selected");
        $filterInput.val($(this).data('value'));
        $optionsList.toggleClass("active");
        $select.find(".fa-angle-down").toggleClass("fa-angle-up");
        const search = $("#search-input").val();
        const status = $(this).data('value');
        window.location.href = "{{route('admin.get-blog-list')}}" + "?search=" + search + "&status=" + status;
    });
</script>
<script>
    function searchFunction(event) {
        event.preventDefault();
        const search = $("#search-input").val();
        const status = $('#filterInput').val();
        
        if (search.length < 1 && status < 1) {
            toastr.warning("Please enter a search term.");
        } else {
            if(status == 0){
                window.location.href = "{{ route('admin.get-blog-list') }}" + "?search=" + encodeURIComponent(search);
            } else{
                window.location.href = "{{ route('admin.get-blog-list') }}" + "?search=" + encodeURIComponent(search) + "&status=" + encodeURIComponent(status);
            }
        }
    }

    function resetSearch(event){
        event.preventDefault();
        window.location.href = "{{ route('admin.get-blog-list') }}";
    }

    function changeStatus(event, blogId, status) {
        event.preventDefault();
        const selectElement = $(event.target);
        const selectedStatus = selectElement.is(':checked') ? 1 : 0;
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the blog's status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'green',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.blog-status-change') }}",
                    type: 'GET',
                    data: {
                        blog_id: blogId,
                        status: selectedStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Blog status updated successfully.',
                            icon: 'success',
                            confirmButtonColor: '#E10E0E',
                            confirmButtonText: 'OK'
                        });            
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.error;
                            var errorMessage = '';
                            for (var key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    errorMessage += errors[key][0] + '<br>';
                                }
                            }
                            Swal.fire({
                                title: 'Validation Error',
                                html: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else if (xhr.status === 404) {
                            Swal.fire({
                                title: 'Not Found',
                                text: 'Blog with this ID not found.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                title: 'Server Error',
                                text: 'There was a problem with the server. Please try again later.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                        selectElement.prop('checked', status === 1);
                    }
                });
            } else {
                selectElement.prop('checked', status === 1);
            }
        });
    }

</script>
@endsection