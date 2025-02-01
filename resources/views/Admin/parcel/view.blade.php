@extends('Admin.layouts.app')
@section('title') Parcel @endsection
@section('page-title') <h3 class="mb-0 page_title"><span>Parcel Management</span> >Parcel Detail</h3> @endsection
@section('content')

<div class="container-fluid mb-3">
    <div class="profile_rapper parcel_details_rapper_padding d-block shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="detail_title_highlight">Parcel Details</div>
            <div class="shipping_label deActivate pointer" data-bs-toggle="modal" data-bs-target="#exampleModal">Download Shipping Label</div>
        </div>
        <div class="card-body padding_start27 mt-1">
            <table class="table">
                <thead class="parcel_detail_table_body">
                    <tr class="table_heading">
                        <th>Parcel Ref No</th>
                        <th>Order Date & Time</th>
                        <th>Dimensions (L*W*H)</th>
                        <th>Weight</th>
                        <th>Size</th>
                        <th>Cubic Inches</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody class="parcel_detail_table_body">
                    <tr class="align-middle table_heading">
                        <td>{{$package->reference_number}}</td>
                        <td>{{$package->created_at->format("d/m/Y, h:i A")}}</td>
                        <td>{{$package->height}} * {{$package->width}} * {{$package->length}}</td>
                        <td>3KG</td>
                        <td>
                            {{ \App\Helpers\ProjectConstants::PACKAGE_NAME_ARRAY[$package->type]}}
                        </td>
                        <td>{{$package->area}}</td>
                        <td>${{$package->shipping_fee}}</td> 
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="detail_title_highlight blue_highlight">Sender Details</div>

        <div class="card-body padding_start27 mt-1">
            <table class="table">
                <thead class="parcel_detail_table_body">
                    <tr class="table_heading">
                        <th>Sender Name</th>
                        <th>Sender Phone number</th>
                        <th>Sender Email ID</th>
                        <th>Sender address</th>
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
                    <tr class="table_heading">
                        <th>Receiver Name</th>
                        <th>Receiver Phone number</th>
                        <th>Receiver Email ID</th>
                        <th>Receiver address</th>
                    </tr>
                </thead>
                <tbody class="parcel_detail_table_body">
                    <tr class="align-middle table_heading">
                        <td>{{$package->reciverDetails->name}}</td>
                        <td>+1 {{$package->reciverDetails->phone_number}}</td>
                        <td>{{$package->reciverDetails->email}}</td>
                        <td>{{$package->reciverDetails->address}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="padding_start27 mt-4">
            <p class="Delivery_title">Delivery instuctions</p>
            <p class="Delivery_title fw-normal">
                Handle with Care: Premium Item Inside <br>
                Please Keep Safe and Avoid Rough Handling
            </p>
        </div>
        
        <div class="padding_start27 mt-4 max_width130">
            <p class="Delivery_title">Order Status</p>
            <div class="shipping_label deActivate">In Transit</div>
        </div>
        
        <div class="padding_start27 mt-4">
            <p class="Delivery_title">Images</p>
            <div class="item_center flex-wrap gap-3">
                @if($package->packageImages->count() > 0)
                    @foreach ($package->packageImages as $item)
                    <div class="parcel_image_box item_center">
                        <img class="parcel_image img-fluid" src="{{asset($item->images)}}" alt="" class="parcel_image">
                    </div>
                    @endforeach
                @else
                    No images Available
                @endif
            </div>
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
        <div class="text-center mb-4"><span class="blue_status download_btn pointer">Download</span></div>
        
      </div>
    </div>
  </div>
</div>

@endsection