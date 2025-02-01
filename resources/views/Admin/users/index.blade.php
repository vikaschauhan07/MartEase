@extends('Admin.layouts.app')
@section('title') Users @endsection
@section('page-title') Users Management @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-3">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
                {{-- <a href="{{route("admin.add-user")}}" class="add_btn me-2">+ Add User</a> --}}
            </div>
            <div class="d-flex gap-2 flex-wrap">

                <div class="search_box position-relative">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
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
                <a href="" class="add_btn" onclick="searchFunction(event)">Search</a>
                <a href="{{route("admin.get-user-list")}}" class="add_btn">Reset</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th>Sr. No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            {{-- <th>Profile Image</th> --}}
                            <th>Phone Number</th>
                            <th class="ps-3">Status</th>
                            <th>No of Orders</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($users->count() > 0)
                            @foreach($users as $key => $user)
                            <tr class="align-middle table_heading">
                                <td>
                                    {{ ($users->currentPage() - 1) * $users->perPage() + $key + 1 }}
                                </td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->email}}</td>
                                {{-- <td>
                                    <img src="{{asset($user->profile_image ?? 'Admin/images/nouser.svg')}}" alt="" class="profile_img">
                                </td> --}}
                                <td>+1 {{$user->phone_number}}</td>
                                <td>
                                    <select class="form-select status_select @if($user->status == 1) active_option @else deactivate_option @endif " aria-label="Default select example" id="status-user-{{$user->id}}" onchange="changeStatus(event, {{$user->id}},{{$user->status}})"  data-original-status="{{$user->status + 1}}" >
                                        <option class="approved @if($user->status == 1) 'active' @endif" 
                                                value="1" 
                                                @if($user->status == 1) selected @endif>
                                            Active
                                        </option>
                                        <option class="rejected @if($user->status != 1) 'active' @endif" 
                                                value="0" 
                                                @if($user->status != 1) selected @endif>
                                            Inactive
                                        </option>
                                    </select>
                                </td>
                                <td>{{$user->packages->where("status" ,'>', 0)->where("step", 5)->count()}}</td>
                                <td>
                                    <div class="item_center">
                                        <a href="{{route('admin.view-user',["user_id" => encrypt($user->id)])}}">
                                            <img class="me-2 action_icon" src="{{asset('Admin/images/view.svg')}}" alt="action-icon" />
                                        </a>
                                        <img class="me-2 action_icon" src="{{asset('Admin/images/edit-icon.svg')}}" alt="action-icon" />
                                    </div>
                                
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <td colspan="12"> No Users Found</td>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer mt-3">
            {{ $users->withQueryString()->links('vendor.pagination.bootstrap-5') }}
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
                window.location.href = "{{ route('admin.get-user-list') }}" + "?search=" + encodeURIComponent(search);
            } else{
                window.location.href = "{{ route('admin.get-user-list') }}" + "?search=" + encodeURIComponent(search) + "&status=" + encodeURIComponent(status);
            }
        }
    }

    function changeStatus(event, userId, status) {
        event.preventDefault();
        var selectElement = $(event.target);
        var selectedStatus = selectElement.val(); 
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the user's status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.user-status-change') }}",
                    type: 'GET',
                    data: {
                        user_id: userId,
                        status: selectedStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'User status updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); 
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
                                text: 'User with this ID not found.',
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
                        $("#status-user-"+userId).val(status);

                    }
                });
            } else {
                $("#status-user-"+userId).val(status);
            }
        });
    }


</script>
@endsection