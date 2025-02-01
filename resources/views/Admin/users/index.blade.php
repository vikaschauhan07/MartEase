@extends('Admin.layouts.app')
@section('title') Users @endsection
@section('page-title') User Management @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-3">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
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
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th class="ps-3">Status</th>
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
                                <td class="table_user_nameMax">
                                    <span  data-bs-toggle="tooltip" data-bs-placement="top" title="{{$user->name}}">
                                        {{$user->name}}
                                    </span>
                                </td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->phone_number}}</td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" id="togBtn-{{$user->id}}" @if($user->status == 1) checked @endif onchange="changeStatus(event, {{$user->id}},{{$user->status}})" >
                                        <div class="slider round">
                                          <span class="on">Active</span>
                                          <span class="off">Inactive</span>
                                        </div>
                                    </label>
                                </td>
                                <td>
                                    <div class="item_center">
                                        <a href="{{route('admin.view-user',["user_id" => encrypt($user->id)])}}">
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
        const search = $("#search-input").val();
        const status = $(this).data('value');
        window.location.href = "{{route('admin.get-user-list')}}" + "?search=" + search + "&status=" + status;
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

    function resetSearch(event){
        event.preventDefault();
        window.location.href = "{{ route('admin.get-user-list') }}";
    }

    function changeStatus(event, userId, status) {
        event.preventDefault();
        const selectElement = $(event.target);
        const selectedStatus = selectElement.is(':checked') ? 1 : 0;
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the user's status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'green',
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