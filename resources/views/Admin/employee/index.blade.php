@extends('Admin.layouts.app')
@section('title') Home @endsection
@section('page-title') Dashboard @endsection
@section('content')

<div class="container-fluid"> 
            <div class="row mb-3"> 
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="dashboard_titles d-flex align-items-center justify-content-between">
                        <span>Shipper</span> 
                        <span class="red">{{$userCount}}</span>
                    </div>

                    <div class="search_box_rapper shadow mb-2 mb-lg-3">
                        <p class="shpper_title">Track your Package</p>
                        <div class="d-flex align-items-center gap-2 mb-2 mb-lg-3">
                            <div class="position-relative w-100">
                                <input class="dashbord_searchInput" type="text" placeholder="Reference Number" id="referenceNumber">
                                <img class="box_search_icon" src="{{asset('Admin/images/box-search-icon.svg')}}" alt="search-icon" />
                            </div>
                            <button class="dashbord_search_btn" id="searchReferenceNumber">
                                <img class="" src="{{asset('Admin/images/white-search.svg')}}" alt="search-icon" />
                            </button>
                        </div>
                    </div>

                    <div class="search_box_rapper shadow">
                        <div class="d-flex align-items-center gap-2 justify-content-between mb-2 mb-lg-3">
                            <p class="mb-0 shpper_title">Parcel Summary</p>

                            <div class="position-relative custom_number_select">
                                <img src="{{asset('Admin/images/select-arrow.svg')}}" alt="" class="select_arrow  z_index999">
                                <div class="input-group">
                                    <select class="form-select" id="trailerNumber" name="trailerNumber">
                                        <option selected>Select</option>
                                            <option value="1">One</option>
                                            <option value="1">two</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard yellowCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-yellow.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title yellow">Shipment Created </span>
                                    <span class="driverCard_count">20</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard blueCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-blue.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title blue">Processed Parcel</span>
                                    <span class="driverCard_count">150</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-green.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title green">Claimed by Recipient</span>
                                    <span class="driverCard_count">5</span>
                                </div>
                            </div>
                            <p class="shiper_box_headings">In Transit</p>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard purpleCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-purple.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title purple">Calgary to Edmonton</span>
                                    <span class="driverCard_count">10</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard purpleCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-purple.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title purple">Edmonton to Calgary</span>
                                    <span class="driverCard_count">210</span>
                                </div>
                            </div>
                            <p class="shiper_box_headings">Arrived</p>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard darkBlueCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-darkblue.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title darkBlue">Arrived in Calgary</span>
                                    <span class="driverCard_count">230</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard darkBlueCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-darkblue.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title darkBlue">Arrived in Calgary</span>
                                    <span class="driverCard_count">230</span>
                                </div>
                            </div>
                            <p class="shiper_box_headings">Ready for Pickup</p>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard redCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-red.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title red">Arrived in Calgary</span>
                                    <span class="driverCard_count">230</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-xxl-4 col-lg-12 col-md-6 col-sm-12">
                                <div class="dashbord_driverCard redCard">
                                    <img class="driverCard_icon" src="{{asset('Admin/images/box-icon-red.svg')}}" alt="search-icon" />
                                    <span class="driverCard_title red">Arrived in Calgary</span>
                                    <span class="driverCard_count">230</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6"> 
                    <div class="dashboard_titles blue_bg d-flex align-items-center justify-content-between">
                        <span>Driver</span> 
                        <span class="blue">{{$driverCount}}</span>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard">
                                <img class="driverCard_icon" src="{{asset('Admin/images/green-trailer.svg')}}" alt="search-icon" />
                                <span class="driverCard_title green">Total Trailers</span>
                                <span class="driverCard_count">20</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard purpleCard">
                                <img class="driverCard_icon" src="{{asset('Admin/images/purpal-trailer.svg')}}" alt="search-icon" />
                                <span class="driverCard_title purple">In-Transit Trailers</span>
                                <span class="driverCard_count">150</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard">
                                <img class="driverCard_icon" src="{{asset('Admin/images/green-card.svg')}}" alt="search-icon" />
                                <span class="driverCard_title green">Approved Drivers</span>
                                <span class="driverCard_count">5</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard yellowCard">
                                <img class="driverCard_icon" src="{{asset('Admin/images/yellow-card.svg')}}" alt="search-icon" />
                                <span class="driverCard_title yellow">Pending Drivers</span>
                                <span class="driverCard_count">10</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard blueCard">
                                <img class="mg-fluid mb-2" src="{{asset('Admin/images/trip-icon-blue.svg')}}" alt="search-icon" />
                                <span class="driverCard_title blue">Trips Posted</span>
                                <span class="driverCard_count">210</span>
                            </div>
                        </div>
                        <div class="col-xl-12 col-xxl-6 col-lg-12 col-md-6 col-sm-12">
                            <div class="dashbord_driverCard redCard">
                                <img class="img-fluid mb-2" src="{{asset('Admin/images/trip-icon-red.svg')}}" alt="search-icon" />
                                <span class="driverCard_title red">trips needing a driver</span>
                                <span class="driverCard_count">230</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end w-100">
                                <img class="dashboard_trailer_img" src="{{asset('Admin/images/trailer-bg.svg')}}" alt="search-icon" />
                            </div>
                        </div>
                    </div>
                </div>
            </div> 
        </div> 
@endsection