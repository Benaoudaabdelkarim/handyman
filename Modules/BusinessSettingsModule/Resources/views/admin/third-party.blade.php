@extends('adminmodule::layouts.master')

@section('title',translate('3rd_party'))

@push('css_or_js')

@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('3rd_party')}}</h2>
                    </div>

                    <!-- Nav Tabs -->
                    <div class="mb-3">
                        <ul class="nav nav--tabs nav--tabs__style2">
                            <li class="nav-item">
                                <button data-bs-toggle="tab" data-bs-target="#google-map" class="nav-link active">
                                    {{translate('google_map')}}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button data-bs-toggle="tab" data-bs-target="#firebase-push-notification"
                                        class="nav-link">
                                    {{translate('firebase_push_notification')}}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button data-bs-toggle="tab" data-bs-target="#recaptcha" class="nav-link">
                                    {{translate('recaptcha')}}
                                </button>
                            </li>
                        </ul>
                    </div>
                    <!-- End Nav Tabs -->

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="google-map">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="page-title">{{translate('google_map_api_key_setup')}}</h4>
                                </div>
                                <div class="card-body p-30">
                                    <div class="alert alert-danger mb-30">
                                        <p><i class="material-icons">info</i>
                                            {{translate('Client Key Should Have Enable Map Javascript Api And You Can Restrict It With Http Refere. Server Key Should Have Enable Place Api Key And You Can Restrict It With Ip. You Can Use Same Api For Both Field Without Any Restrictions.')}}
                                        </p>
                                    </div>
                                    <form action="{{route('admin.configuration.set-third-party-config')}}" method="POST"
                                          id="google-map-update-form" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="discount-type">
                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="mb-30">
                                                        <div class="form-floating">
                                                            <input name="party_name" value="google_map"
                                                                   class="hide-div">
                                                            <input type="text" class="form-control"
                                                                   name="map_api_key_server"
                                                                   placeholder="{{translate('map_api_key_server')}} *"
                                                                   required=""
                                                                   value="{{bs_data($data_values,'google_map')['map_api_key_server']??''}}">
                                                            <label>{{translate('map_api_key_server')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="mb-30">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control"
                                                                   name="map_api_key_client"
                                                                   placeholder="{{translate('map_api_key_client')}} *"
                                                                   required=""
                                                                   value="{{bs_data($data_values,'google_map')['map_api_key_client']??''}}">
                                                            <label>{{translate('map_api_key_client')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn--primary demo_check">
                                                {{translate('update')}}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade" id="firebase-push-notification">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="page-title">{{translate('firebase_push_notification_setup')}}</h4>
                                </div>
                                <div class="card-body p-30">
                                    <form action="{{route('admin.configuration.set-third-party-config')}}" method="POST"
                                          id="firebase-form" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="discount-type">
                                            <div class="row">
                                                <div class="col-md-12 col-12">
                                                    <div class="mb-30">
                                                        <div class="form-floating">
                                                            <input name="party_name" value="push_notification"
                                                                   class="hide-div">
                                                            <input type="text" class="form-control"
                                                                   name="server_key"
                                                                   placeholder="{{translate('server_key')}} *"
                                                                   required=""
                                                                   value="{{bs_data($data_values,'push_notification')['server_key']??''}}">
                                                            <label>{{translate('server_key')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn--primary demo_check">
                                                {{translate('update')}}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade" id="recaptcha">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="page-title">{{translate('recaptcha_setup')}}</h4>
                                </div>
                                <div class="card-body p-30">
                                    <form action="{{route('admin.configuration.set-third-party-config')}}" method="POST"
                                          id="recaptcha-form" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="discount-type">
                                            <div class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                                                <div class="custom-radio">
                                                    <input type="radio" id="active" name="status"
                                                           value="1" {{$data_values->where('key_name','recaptcha')->first()->live_values['status']?'checked':''}}>
                                                    <label for="active">{{translate('active')}}</label>
                                                </div>
                                                <div class="custom-radio">
                                                    <input type="radio" id="inactive" name="status"
                                                           value="0" {{$data_values->where('key_name','recaptcha')->first()->live_values['status']?'':'checked'}}>
                                                    <label for="inactive">{{translate('inactive')}}</label>
                                                </div>
                                            </div>

                                            <br>

                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="mb-30">
                                                        <div class="form-floating">
                                                            <input name="party_name" value="recaptcha" class="hide-div">
                                                            <input type="text" class="form-control"
                                                                   name="site_key"
                                                                   placeholder="{{translate('site_key')}} *"
                                                                   required=""
                                                                   value="{{bs_data($data_values,'recaptcha')['site_key']??''}}">
                                                            <label>{{translate('site_key')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <div class="mb-30">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control"
                                                                   name="secret_key"
                                                                   placeholder="{{translate('secret_key')}} *"
                                                                   required=""
                                                                   value="{{bs_data($data_values,'recaptcha')['secret_key']??''}}">
                                                            <label>{{translate('secret_key')}} *</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn--primary demo_check">
                                                {{translate('update')}}
                                            </button>
                                        </div>
                                    </form>

                                    <div class="mt-3">
                                        <h4 class="mb-3">{{translate('Instructions')}}</h4>
                                        <ol>
                                            <li>To get site key and secret keyGo to the Credentials page
                                                (<a href="https://developers.google.com/recaptcha/docs/v3" class="c1">Click
                                                    Here</a>)
                                            </li>
                                            <li>Add a Label (Ex: abc company)</li>
                                            <li>Select reCAPTCHA v2 as ReCAPTCHA Type</li>
                                            <li>Select Sub type: I'm not a robot Checkbox</li>
                                            <li>Add Domain (For ex: demo.6amtech.com)</li>
                                            <li>Check in “Accept the reCAPTCHA Terms of Service”</li>
                                            <li>Press Submit</li>
                                            <li>Copy Site Key and Secret Key, Paste in the input filed below and Save.
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Tab Content -->

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('#google-map').on('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(document.getElementById("google-map-update-form"));
            // Set header if need any otherwise remove setup part
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.configuration.set-third-party-config')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                success: function (response) {
                    console.log(response)
                    toastr.success('{{translate('successfully_updated')}}')
                },
                error: function () {

                }
            });
        });

        $('#firebase-form').on('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(document.getElementById("firebase-form"));
            // Set header if need any otherwise remove setup part
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.configuration.set-third-party-config')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                success: function (response) {
                    console.log(response)
                    toastr.success('{{translate('successfully_updated')}}')
                },
                error: function () {

                }
            });
        });

        $('#recaptcha-form').on('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(document.getElementById("recaptcha-form"));
            // Set header if need any otherwise remove setup part
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.configuration.set-third-party-config')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                success: function (response) {
                    console.log(response)
                    toastr.success('{{translate('successfully_updated')}}')
                },
                error: function () {

                }
            });
        });
    </script>
@endpush
