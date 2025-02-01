@extends('Admin.layouts.app')
@section('title') Notified Emails @endsection
@section('page-title') Notified Emails @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-3">
        <div class="py-2 pb-4 d-flex justify-content-between flex-wrap">
            <div class="d-flex">
                {{-- <a href="{{route("admin.add-user")}}" class="add_btn me-2">+ Add User</a> --}}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr class="table_heading">
                            <th style="width: 100px">Serial No.</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($getNotifiedEmails->count() > 0)
                            @foreach($getNotifiedEmails as $key => $getNotifiedEmail)
                            <tr class="align-middle table_heading">
                                <td>
                                    {{ ($getNotifiedEmails->currentPage() - 1) * $getNotifiedEmails->perPage() + $key + 1 }}
                                </td>
                                <td>{{$getNotifiedEmail->email}}</td>
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
            {{ $getNotifiedEmails->withQueryString()->links('vendor.pagination.bootstrap-5') }}
        </div>
    </div>
</div>
@endsection