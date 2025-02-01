@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') <h3 class="mb-0 page_title"><a href="{{route('admin.get-trailer')}}">Trailer Load Control</a> > View Trailer</h3> @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4">
        <div class=" pb-4 d-flex flex-wrap gap-2 justify-content-between">
                <div class="d-flex gap-2 align-items-center">
                    <p class="Delivery_title m-0">Trailer No.</p>
                    <div class="position-relative custom_number_select">
                        <img src="{{asset('Admin/images/select-arrow.svg')}}" alt="" class="select_arrow  z_index999">
                        <div class="input-group">
                            <select class="form-select" id="trailerNumber">
                                @foreach($trailers as $item)
                                    <option value="{{encrypt($item->id)}}" @if($trailer->id == $item->id) selected @endif>{{$item->trailer_number}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
               <div class="lockToggle d-flex align-items-center">
                    <span class="detailTitle">Lock</span>
                    <label class="switch">
                        <input type="checkbox" id="lockTrailer" @if($trailer->is_locked == 1) checked @endif>
                        <span class="slider round"></span>
                    </label>
               </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th>Parcel Ref No.</th>
                            <th>Shipper</th>
                            <th>Recipient </th>
                            <th>From</th>
                            <th>to</th>
                            <th>Price</th>
                            <th>Size</th>
                            @if($trailer->is_locked != 1)
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if($trailerLoads->count() > 0)
                            @foreach($trailerLoads as $trailerLoad)
                                <tr class="table_heading">
                                    <td>{{$trailerLoad->package->reference_number}}</td>
                                    <td>{{$trailerLoad->package->senderDetails->name}}</td>
                                    <td>{{$trailerLoad->package->reciverDetails->name}}</td>
                                    <td>{{$trailerLoad->package->senderDetails->city}}</td>
                                    <td>{{$trailerLoad->package->reciverDetails->city}}</td>
                                    <td>${{$trailerLoad->package->shipping_fee}}</td>
                                    <td>
                                        {{ \App\Helpers\ProjectConstants::PACKAGE_NAME_ARRAY[$trailerLoad->package->type] ?? "UNKNOWN" }}
                                    </td>
                                    @if($trailer->is_locked != 1)
                                        <td>
                                            <a href="" 
                                                onclick="confirmRemoveParcel(event,'{{ route('admin.remove-parcel-from-trailer', ['trailer_load_id' => $trailerLoad->id]) }}')">
                                                <img src="{{ asset('Admin/images/delete.svg') }}" alt="" class="pointer">
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            No Record
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#trailerNumber').on('change', function () {
            const selectedValue = $(this).val();
            window.location.href = "{{route('admin.view-trailer')}}" + "?trailer_id=" + selectedValue;
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#lockTrailer').on('change', function () {
            const isChecked = $(this).is(':checked');
            Swal.fire({
                title: isChecked ? 'Lock Trailer' : 'Unlock Trailer',
                text: `Are you sure you want to ${isChecked ? 'lock' : 'unlock'} the trailer?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('admin.lock-trailer')}}", 
                        method: 'GET',
                        data: {
                            trailer_id: '{{ $trailer->id }}',
                            is_locked: isChecked ? 1 : 0,
                        },
                        success: function (response) {
                            Swal.fire({
                                title: 'Success',
                                text: `The trailer has been ${isChecked ? 'locked' : 'unlocked'}.`,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload(); 
                                }
                            });
                        },
                        error: function (xhr, status, error) {
                            Swal.fire({
                                title: 'Error',
                                text: 'There was an issue updating the lock status.',
                                icon: 'error'
                            });
                            $('#lockTrailer').prop('checked', !isChecked);
                        }
                    });
                } else {
                    $('#lockTrailer').prop('checked', !isChecked);
                }
            });
        });
    });
</script>
<script>
    function confirmRemoveParcel(event, url) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will remove the parcel from the trailer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
@endsection