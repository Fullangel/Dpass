@extends('frontend.layouts.frontend')
@section('style')
<style>
    #myOnlineCamera video {
        width: 320px;
        height: 240px;
        margin: 15px;
        float: left;
    }

    #myOnlineCamera canvas {
        width: 320px;
        height: 240px;
        margin: 15px;
        float: left;
    }

    #myOnlineCamera button {
        clear: both;
        margin: 30px;
    }
</style>
@endsection
@section('content')
<!-- Default Page -->
<section id="pm-banner-1" class="custom-css-step">
    <div class="container camera-container">
        <div class="card border-0 bg-body" style="margin-top:45px;">
            <div class="card-header border-0 bg-body" id="Details" align="center">
                @if(!setting('photo_capture_enable'))
                <h4 style="color: #111570;font-weight: bold">{{__('Visitor Card Information')}}</h4>
                @endif
            </div>
            <form action="{{ route('check-in.step-two.next') }}" method="POST" enctype="multipart/form-data">
                @csrf
            <div class="card-body custom-camera">
                <div class="row">
                    @if(setting('photo_capture_enable')  && strpos($image, "default/user.png") !== false )
                    <div class="col-md-6 left-div">
                        <div class="card border-0 ">
                            <h4 style="color: #111570;font-weight: bold" class="text-center">{{__('Tomar foto')}}</h4>

                            <div class="card-body">
                                <div class="video-options mb-4">
                                    <select name="" id="" class="custom-select">
                                        <option value="">Select camera</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-9">
                                        <video width="100%" height="200px" id="videos" autoplay></video>
                                        <canvas class="d-none" style="border:5px solid #d3d3d3; display: none"></canvas>
                                        <input type="hidden" id="image" name="photo" value="">
                                    </div>
                                    <div class="col-3">
                                        <button type="button" id="screenshot" title="ScreenShot" class='retakephoto'>
                                            <img class="img" src="{{ asset('images/capture.png')}}" style="height: 80px">
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <span class="text-center">{!! $errors->first('photo', '<p class="text-danger">:message</p>') !!}</span>
                    </div>

                    <div class="col-md-6">
                        <div class="img-cards" id="printidcard">
                            <div class="id-card-holder">
                                <div class="id-card custom-id-card">
                                    <div class="id-card-photo">
                                        <img id="card-img" style="width: 120px;height: 120px;" class="screenshot-image" alt="">
                                    </div>
                                    <h2 class="name mt-4">{{$visitingDetails['first_name']}}
                                    {{$visitingDetails['last_name']}}</h2>
                                    <h2 class="info"> {{__('C.I .: ')}}{{$visitingDetails['national_identification_no']}}</h2>
                                    <h2 class="info"> {{__('Telefono : ')}}{{$visitingDetails['phone']}}</h2>
                                    @isset($visitingDetails['company_name'])
                                    @endisset

                                    <h2 class="visit mt-4">{{__('Visita a:')}}</h2>
                                    @if($employee)
                                    <h3 class="email">{{__('')}} {{$employee->name}}</h3>
                                    @endif
                                    <p class="email">{{__('')}} {{$employee->phone}} </p>
                                    <hr class="bar">
                                    <p class="company">{{ setting('site_address') }} </p>
                                    <p class="email">{{__('')}}{{ setting('site_email') }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-md-12">
                        <div class="img-cards" id="printidcard">
                            <div class="id-card-holder">
                                <div class="id-card">
                                    <div class="id-card-photo">
                                        <img id="card-img" style="width: 80px;height: 70px;margin: 3px;" src="{{ @$image ? $image : asset('images/'.setting('site_logo')) }}" class="screenshot-image" alt="">
                                    </div>
                                    <h2>{{$visitingDetails['first_name']}} {{$visitingDetails['last_name']}}</h2>
                                    <h2>{{$visitingDetails['phone']}}</h2>
                                    @if(isset($visitingDetails['email']))
                                    <h2>{{$visitingDetails['email']}}</h2>
                                    @endif
                                    <h2>{{$visitingDetails['address']}}</h2>
                                    <h2>{{$visitingDetails['company_name']}}</h2>
                                    <h2>{{__('VISITED TO')}}</h2>
                                    @if($employee)
                                    <h3>{{__('Host:')}} {{$employee->name}}</h3>
                                    @endif
                                    <hr>
                                    <p><strong>{{ setting('site_name') }} </strong></p>
                                    <p><strong>{{ setting('site_address') }} </strong></p>
                                    <p>{{__('Ph:')}}{{ setting('site_phone') }} | E-mail: {{ setting('site_email') }} </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-footer custom-footer">
                <div class="row py-4">
                    <div class="col-md-6">
                        <a href="{{ route('check-in.step-one') }}" class="btn btn-primary float-left text-white cancel px-5 py-2">
                           {{__('Regresar')}}
                        </a>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn  float-right continue text-light px-5 py-2 btn-submit-two" id="hide">
                        {{__('Continuar')}}
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
        <div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">{{__('Terms & condition')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{strip_tags(setting('terms_condition'))}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="hr-line">
    <div class="d-flex justify-content-center footer-text pb-3">
        <span> {{setting('site_footer')}}</span>
    </div>
</section>
@endsection
@section('scripts')
<script src="{{ asset('js/photo.js') }}"></script>

<script>
        $(document).ready(function() {

            $(".btn-submit-two").attr("disabled", false);

            $(".btn-submit-two").click(function(){
                $(".btn-submit-two").attr("disabled", true);
                $('form').submit();
            });

        });
    </script>

@endsection
