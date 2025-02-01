@extends('Admin.layouts.app')
@section('title') Driver @endsection
@section('page-title') Trailer Load Control @endsection
@section('content')
<div class="container-fluid">
    <div class="table_box mb-4 ">
      <div class="d-flex flex-column align-items-center justify-content-center paddingTop155 mb-2">
            <h2 class="processing_heading">Trailer number</h2>
            <div class="w-100 d-flex align-items-center justify-content-center custom_select_trailer">
                <select class="form-select" id="trailerNumber">
                    <option value="0" selected>Select Trailer Number</option>
                    @foreach($trailers as $trailer)
                        <option value="{{encrypt($trailer->id)}}">{{$trailer->trailer_number}}</option>  
                    @endforeach
                </select>
            </div>
      </div>
        <div class="row">
            <div class="col-lg-6 col-md-0"></div>
            <div class="col-lg-6 d-flex justify-content-end">
                <img src="{{asset('Admin/images/trailer-bg.svg')}}" alt="" class="img-fluid">
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
@endsection