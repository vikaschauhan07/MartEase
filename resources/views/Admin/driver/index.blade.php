@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') Drivers Management @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4">
        <div class="py-2 pb-4 d-flex flex-wrap gap-2 justify-content-between">
            <div class="d-flex flex-wrap gap-2">
                <div class="search_box position-relative">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
                    <input type="text" placeholder="Search" class="search_input" id="search-input">
                </div>
                <div class="tab-container">
                    <div class="tabs">
                        <a href="{{route('admin.get-driver-list', ['status' => 1])}}" class="tab-button @if($status == 1) active @endif">Approved</a>
                        <a href="{{route('admin.get-driver-list', ['status' => 0])}}" class="tab-button @if($status == 0) active @endif">Requested</a>
                        <a href="{{route('admin.get-driver-list', ['status' => 2])}}" class="tab-button @if($status == 2) active @endif">Rejected</a>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{route('admin.add-driver')}}" class="add_btn text-decoration-none">+ Add Driver</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th>Seriral Id</th>
                            <th>Name</th>
                            <th>Eamil</th>
                            <th>Phone Number</th>
                            <th>Approval Status</th>
                            @if($status == 1)
                                <th>Account Status</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($drivers->count() > 0)
                            @foreach($drivers as $key => $driver)
                                <tr class="align-middle table_heading">
                                    <td>
                                        {{ ($drivers->currentPage() - 1) * $drivers->perPage() + $key + 1 }}</td>
                                    </td>
                                    <td>{{$driver->name}}</td>
                                    <td>{{$driver->email}}</td>
                                    <td>+1 {{$driver->phone_number}}</td>
                                    <td>
                                        @switch($driver->is_admin_approved)
                                            @case(1)
                                                <p class="driver_approved common_p_driver m-0">Approved</p>
                                                @break
                                            @case(2)
                                                <p class="driver_reject common_p_driver m-0">Rejected</p>
                                                @break
                                            @case(0)
                                                <p class="driver_pending  common_p_driver m-0">Pending</p>
                                                @break
                                            @default
                                                Nill
                                        @endswitch
                                    </td>
                                    @if($status == 1)
                                        <td>
                                            <select class="form-select status_select @if($driver->status == 1) active_option @else deactivate_option @endif" aria-label="Default select example" id="status-user-{{$driver->id}}" onchange="changeStatus(event, {{$driver->id}},{{$driver->status}})"  data-original-status="{{$driver->status + 1}}" >
                                                <option class="approved @if($driver->status == 1) 'active' @endif" 
                                                        value="1" 
                                                        @if($driver->status == 1) selected @endif>
                                                    Active
                                                </option>
                                                <option class="rejected @if($driver->status != 1) 'active' @endif" 
                                                        value="0" 
                                                        @if($driver->status != 1) selected @endif>
                                                    Inactive
                                                </option>
                                            </select>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="item_center">
                                            <a href="{{route('admin.view-driver',['driver_id' => encrypt($driver->id)])}}">
                                                <img class="me-2 action_icon" src="{{asset('Admin/images/view.svg')}}" alt="action-icon" />
                                            </a>
                                            @if($driver->is_admin_approved == 1)
                                                <a href="{{route('admin.edit-driver',['driver_id' => encrypt($driver->id)])}}">
                                                    <img class="me-2 action_icon" src="{{asset('Admin/images/edit-icon.svg')}}" alt="action-icon" />
                                                </a>
                                                <img class="me-2 action_icon" src="{{asset('Admin/images/delete.svg')}}" alt="action-icon" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else 
                            <td>
                                No record Found
                            </td>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer mt-3">
            {{ $drivers->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
<script>
    function changeStatus(event, driverId, status) {
        event.preventDefault();
        var selectElement = $(event.target);
        var selectedStatus = selectElement.val(); 
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to change the driver's status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'No, cancel!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.driver-status-change') }}",
                    type: 'GET',
                    data: {
                        driver_id: driverId,
                        status: selectedStatus
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Driver status updated successfully.',
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
                            $("#status-user-"+driverId).val(status);
                            Swal.fire({
                                title: 'Validation Error',
                                html: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        } else if (xhr.status === 404) {
                            Swal.fire({
                                title: 'Not Found',
                                text: 'Driver with this ID not found.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            $("#status-user-"+driverId).val(status);
                        } else {
                            Swal.fire({
                                title: 'Server Error',
                                text: 'There was a problem with the server. Please try again later.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                        $("#status-user-"+driverId).val(status);
                    }
                });
            } else {
                $("#status-user-"+driverId).val(status);
            }
        });
    }
</script>
@endsection

<script>
    $("#search-input").on()
</script>
