@extends('frontend.layouts.frontend')

@section('content')
<!-- Default Page -->
<section id="pm-banner-1" class="custom-css-step">
    <div class="container custom-prereg">
        <div class="card" style="margin-top:40px;">

            <div class="card-body">
                <div style="margin: auto;">
                    {!! Form::open(['route' => 'check-in.find.pre.visitor', 'id' => 'myForm']) !!}
                    <div class="save">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 left-side">
                                <h4 style="color: #111570;font-weight: bold">{{__('frontend.pre_registered_visitor_details')}} </h4>

                                <div class="form-group {{ $errors->has('email') ? 'has-error' : ''}}">
                                    {!! Html::decode(Form::label('email', trans('frontend.visitor_email_phone')."<span class='text-danger'>*</span>", ['class' => 'control-label heading'])) !!}
                                    {!! Form::text('email', null, ('' == 'required') ? ['class' => 'form-control  input','id '=>'email','required' => 'required', 'placeholder'=>trans('frontend.search_email')] : ['class' => 'form-control input','id '=>'email', 'placeholder'=>trans('frontend.search_email_or_phone')]) !!}
                                    {!! $errors->first('email', '<p class="text-danger">:message</p>') !!}
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="{{ route('/') }}" class="btn cancel float-left text-light ">
                                            {{__('frontend.cancel')}}
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn continue float-right text-light" id="continue">
                                            {{__('frontend.continue')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 right-image">
                                <img src="{{asset('frontend/images/sample.png')}}" alt="">
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
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
<script type="application/javascript">
    $(document).ready(function() {
        $("#form-submit").click(function() {
            $("#myForm").submit(); // Submit the form
        });
    });
</script>
@endsection