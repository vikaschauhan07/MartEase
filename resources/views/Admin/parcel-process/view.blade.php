@extends('Admin.layouts.app')
@section('title') Parcel processing @endsection
@section('page-title') Parcel processing @endsection
@section('content')

<div class="container-fluid mb-3">
    <div class="parcel_rapper parcel_details_rapper_padding d-block shadow">

        <div class="parcel_processing_header d-flex justify-content-between align-items-center">
            <div class="d-flex align-center">
                <div class="search_box position-relative me-2">
                    <img src="{{asset('Admin/images/search.svg')}}" alt="" class="search_icon">
                    <input type="text" value="{{$package->reference_number}}" placeholder="Search" id="referenceNumber" class="search_input" id="search-input" @if($package->status == 1) disabled @endif>
                </div>
                @if($package->status > 1)
                    <div class="search_box position-relative me-2">
                        <button class="search_parcel-process" onclick="searchReferenceNumber(event)"> Search</button>
                    </div>
                @endif
            </div>
            <div class="d-flex gap_12 align-items-center flex-wrap">
                <a class="add_btn" data-bs-toggle="modal" data-bs-target="#exampleModal">Print Label</a>
                <div class="d-flex gap-2 align-items-center">

                    <p class="Delivery_title m-0">Trailer No.</p>
                    <div class="position-relative custom_number_select">
                        <img src="{{asset('Admin/images/select-arrow.svg')}}" alt="" class="select_arrow  z_index999">
                        <div class="input-group">
                            <select class="form-select" id="trailerNumber" @if($package->status > 1) disabled @endif>
                                <option selected>Select</option>
                                @foreach($trailers as $trailer)
                                    <option value="{{$trailer->id}}" @if($assignTrailer && $assignTrailer->id == $trailer->id) selected @endif>{{$trailer->trailer_number}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @if($assignTrailer)
                    @if($package->status == 1)
                        <a href="{{route('admin.add-parcel-to-trailer',['package_id' => $package->id,'trailer_id' => $assignTrailer->id])}}" class="add_btn red_btn">Add parcel to this trailer</a>
                    @endif
                    <div class="no_parcel_box">
                        <p class="m-0 ">No. of parcel in trailer</p>
                        <span class="m-0">
                            @if($package->status > 1)
                                {{$package->packaageLoadedTrailer->trailer->trailerLoad->count()}}
                            @else
                            {{$assignTrailer->trailerLoad->count()}}
                            @endif
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex justify-content-start align-items-center gap-4 gap-lg-5 mb-4">
            <div class="padding_start27">
                <p class="Delivery_title">Images</p>
                <div class="d-flex justify-content-start align-items-center gap-2">
                    @if($package->packageImages->count() > 0)
                        @foreach ($package->packageImages as $item)
                            <div class="parcel_image_box item_center blueShadow">
                                <img class="parcel_image img-fluid" src="{{asset($item->images)}}" alt="" class="search_icon">
                            </div>
                        @endforeach
                    @else
                        No images Available
                    @endif
                </div>
            </div>
            <div class="padding_start27">
                <p class="Delivery_title">Label</p>
                <div class="label_image_box">
                    <img class="parcel_image img-fluid" src="{{asset('Admin/images/label-img.svg')}}" alt="" class="search_icon">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="detail_title_highlight">Parcel Details</div>
        </div>
        <div class="card-body padding_start27 mt-1">
            <table class="table">
                <thead class="parcel_detail_table_body">
                    <tr class="table_heading bg_transparent">
                        <th>Order Date & Time</th>
                        <th>Dimensions (L*W*H)</th>
                        <th>Size</th>
                        <th>Cubic Inches</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody class="parcel_detail_table_body">
                    <tr class="align-middle table_heading">
                        <td>{{$package->created_at->format("d/m/Y, h:i A")}}</td>
                        <td>{{$package->height}} * {{$package->width}} * {{$package->length}}</td>
                        <td>{{ \App\Helpers\ProjectConstants::PACKAGE_NAME_ARRAY[$package->type]}}</td>
                        <td>{{ $package->area}}</td>
                        <td>${{$package->shipping_fee}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="detail_title_highlight blue_highlight">Shipper Details</div>

        <div class="card-body padding_start27 mt-1">
            <table class="table">
                <thead class="parcel_detail_table_body">
                    <tr class="table_heading bg_transparent">
                        <th>Shipper Name</th>
                        <th>Shipper Phone number</th>
                        <th>Shipper Email ID</th>
                        <th>Shipper address</th>
                    </tr>
                </thead>
                <tbody class="parcel_detail_table_body">
                    <tr class="align-middle table_heading">
                        <td>{{$package->senderDetails->name}}</td>
                        <td>+1 {{$package->senderDetails->phone_number}}</td>
                        <td>{{$package->senderDetails->email}}</td>
                        <td>{{$package->senderDetails->address}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="detail_title_highlight red_highlight">Recipient Details</div>

        <div class="card-body padding_start27 mt-1">
            <table class="table">
                <thead class="parcel_detail_table_body">
                    <tr class="table_heading bg_transparent">
                        <th>Receiver Name</th>
                        <th>Receiver Phone number</th>
                        <th>Receiver Email ID</th>
                        <th>Receiver address</th>
                    </tr>
                </thead>
                <tbody class="parcel_detail_table_body">
                    <tr class="align-middle table_heading bg_transparent">
                        <td>{{$package->reciverDetails->name}}</td>
                        <td>+1 {{$package->reciverDetails->phone_number}}</td>
                        <td>{{$package->reciverDetails->email}}</td>
                        <td>{{$package->reciverDetails->address}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- <div class="padding_start27 mt-4">
            <p class="Delivery_title">Delivery instuctions</p>
            <p class="Delivery_title fw-normal">
                Handle with Care: Premium Item Inside <br>
                Please Keep Safe and Avoid Rough Handling
            </p>
        </div> -->
        
        <div class="padding_start27 mt-4 max_width130">
            <p class="Delivery_title">Order Status</p>
            @if($package->status == 1)
                <div class="shipping_label statusPending">Pending</div>
            @endif
            @if($package->status == 2)
                <div class="shipping_label deActivate">Processed</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal"  tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-body modal_body">
        <h5 class="modal_heading text-center" id="exampleModalLabel">Hitchmail <span>Regular Parcel</span></h5>
        <div>
            <p class="title mb-0">From:</p>
            <div class="from_box mb-3">
                <p class="title mb-0">Stefan Trischuk</p>
                <p class="value mb-0">93 Ash Street<br>winnipeg, Manitoba<br>R3N OP4</p>
            </div>

            <p class="title mb-0">To:</p>
            <div class="from_box mb-3">
                <p class="title mb-0">Stefan Trischuk</p>
                <p class="value mb-0">93 Ash Street<br>winnipeg, Manitoba<br>R3N OP4</p>
            </div>
        </div>
        <p class="text-center value">Reference number : <span class="title">RE24862647</span></p>
        <div class="item_center justify-content-center mb-4">
            <img class="qrCode img-fluid" src="{{asset('Admin/images/qr-code.jpg')}}" alt="" class="qrCode"> 
        </div>
        <p class="value text-center mb-4">Scan the QR code to explore our website and learn more!</p>
        <div class="text-center mb-4 w-100"><span class="blue_status download_btn pointer w-100">Download</span></div>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function () {
        $('#trailerNumber').on('change', function () {
            const selectedValue = $(this).val();
            window.location.href = "{{route('admin.view-parcel-process')}}" + "?package_id={{encrypt($package->id)}}" + "&trailer_id=" + selectedValue;
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#referenceNumber').on('keydown', function (event) {
            if (event.keyCode === 13) { 
                searchReferenceNumber(event);
            }
        });
    });
    function searchReferenceNumber(event){
        event.preventDefault(); 
        const referenceNumber = $("#referenceNumber").val();
        $.ajax({
            url: "{{ route('admin.search-parcel') }}",
            type: 'GET',
            data: {
                reference_number: referenceNumber
            },
            success: function(response) {
                window.location.href = response.data.redirect_url;
            },
            error: function(xhr, status, error) {
                toastr.error(xhr.responseJSON.message);
            }
        });
    }
</script>
@endsection