@extends('Admin.layouts.app')
@section('page-title') 
<div class="d-flex align-items-center justify-content-between">
    <h3 class="mb-0 page_title"><a href="{{route('admin.get-driver-list')}}">Drivers Management</a> > Driver Details</h3>
    @if($driver->is_admin_approved == 0)
       <div class="d-flex align-item-center gap-3">
            <a class="add_btn edit_btn green_bg" onclick="driverApprove(event, {{$driver->id}},1)">Approve</a>
            <a class="add_btn edit_btn red_bg" onclick="driverApprove(event, {{$driver->id}},2)">Reject</a>
       </div>
    @else
    {{-- <div class="shipping_label deActivate pointer" data-bs-toggle="modal" data-bs-target="#exampleModal">Reject Modal</div> --}}
       @if($driver->is_admin_approved == 1)
            <a href="{{route('admin.edit-driver',['driver_id' => encrypt($driver->id)])}}" class="add_btn edit_btn text-decoration-none">
                <img class="me-2 action_icon" src="{{asset('Admin/images/white-edit-icon.svg')}}" alt="action-icon" /> Edit
            </a>
        @endif
    @endif
</div>

@endsection
@section('content')
<div class="container-fluid">
    <div class="profile_rapper d-block shadow">
        <div class="row">
            
            <div class="d-flex w-100">
                <div class="profilePhoto">
                    <figure class="inner mb-0">
                        <img id="" class=" detailProfile" src="{{ asset($driver->profile_image ?? 'Admin/images/nouser.svg') }}" alt="Preview Image">
                    </figure>
                </div>
                <div class="row w-100">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Name</p>
                        <p class="detailTitle">{{$driver->name}}</p>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Email</p>
                        <p class="detailTitle text-break">{{$driver->email}}</p>
                    </div>

                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Phone Number</p>
                        <p class="detailTitle text-break">+1 {{$driver->phone_number}}</p>
                    </div> 
                    <div class="col-lg-3 col-md-6 mb-3">
                        <p class="detailTitle opacity-50">Approval Status</p>
                        <p class="">
                            @switch($driver->is_admin_approved)
                                @case(1)
                                    Approved
                                @break
                                @case(2)
                                    Rejected
                                @break
                                @case(0)
                                    Requested
                                @break
                                @default
                                    Nill    
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>
            <h5 class="my-3 detailTitle fw-bolder fs-6">Documents</h5>
            @if($driver->documents->count() > 0)
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                    <img id="haveClick" class="documnent_iamge" src="{{ asset($driver->documents->where('type',1)->first()->document) }}" alt="camera">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                    <img id="haveClick" class="documnent_iamge" src="{{ asset($driver->documents->where('type',2)->first()->document) }}" alt="camera">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                    <img id="haveClick" class="documnent_iamge" src="{{ asset($driver->documents->where('type',3)->first()->document) }}" alt="camera">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                    <img id="haveClick" class="documnent_iamge" src="{{ asset($driver->documents->where('type',4)->first()->document) }}" alt="camera">
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function driverApprove(event, driverId, status) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to change the driver\'s status?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                if (status === 2) {
                    Swal.fire({
                        title: 'Reject Driver Profile',
                        html: '<h3 class="subHeading">Please provide a reason for rejection below:</h3>',
                        input: 'textarea',
                        inputPlaceholder: 'Enter the reason for rejection...',
                        inputAttributes: {
                            'aria-label': 'Reason for rejection'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'swal-confirm-btn',
                            cancelButton: 'swal-cancel-btn',
                            input: 'swal-input-box',
                            title: 'swal-title-custom',
                            inputPlaceholder: 'swal-input-placeholder'
                        },
                        inputValidator: (value) => {
                            if (!value) {
                                return 'A reason is required!';
                            }
                            if (value.length > 255) {
                                return 'The reason cannot exceed 255 characters.';
                            }
                        }
                    }).then((inputResult) => {
                        if (inputResult.isConfirmed) {
                            sendDriverStatusChangeRequest(driverId, status, inputResult.value);
                        }
                    });
                } else {
                    sendDriverStatusChangeRequest(driverId, status);
                }
            }
        });
    }
    function sendDriverStatusChangeRequest(driverId, status, reason = null) {
        $.ajax({
            url: "{{ route('admin.driver-verify') }}",
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            data: {
                driver_id: driverId,
                status: status,
                reason: reason 
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.error;
                    var errorMessage = '';
                    for (var key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            errorMessage += errors[key][0] + '<br>';
                        }
                    }
                    $("#status-user-" + driverId).val(status);
                    Swal.fire({
                        title: 'Validation Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else if (xhr.status === 404) {
                    $("#status-user-" + driverId).val(status);
                    Swal.fire({
                        title: 'Not Found',
                        text: 'Driver with this ID not found.',
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
            }
        });
    }
</script>
@endsection

<style>
    .swal-confirm-btn {
    background-color: #DB1F26 !important;
    color: white !important;
    border: none !important;
    padding: 10px 20px;
    border-radius: 5px;
}

.swal-cancel-btn {
    background-color: transparent !important;
    color: gray !important;
    border: 1px solid gray !important;
    padding: 10px 20px;
    border-radius: 5px;
}

.swal-input-box {
    /* border: 2px solid red !important; */
    border-radius: 5px;
    padding: 10px;
    color: black;
}
.swal-title-custom{
    font-size: 20px;
    color: #00142D;
    font-weight: 500;

}
.subHeading{
    color: #919EAB;
    font-size: 16px;
    font-weight: 400;
}

</style>