@extends('Admin.layouts.app')
@section('title') Parcel Processiong @endsection
@section('page-title') Parcel Processiong @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4 processing_serch_rapper">
        <h2 class="processing_heading">Parcel Reference number</h2>
        <div class="search_box text-center me-2 w-100">
            <input type="text" placeholder="Search" class="search_input big" id="referenceNumber">
            <button class="search_parcel-process main_search_btn" onclick="searchReferenceNumber(event)">
                Search
            </button>
        </div>
    </div>
</div>

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