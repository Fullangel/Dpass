@extends('frontend.layouts.frontend')

@section('content')
<!-- Default Page -->
<section id="pm-banner-1" class="custom-css-step">
    <div class="container custom-prereg">
        <div class="card" style="margin-top:40px;">

            <div class="card-body">
                <div style="margin:40px;">
                    {!! Form::open(['route' => 'check-in.find.visitor', 'id' => 'myForm']) !!}
                    <div class="save">
                        <div class="row">
                            <div class="col-md-6 col-sm-12 left-side">
                                <h4 style="color: #111570;font-weight: bold" >Buscar Visitante</h4>

                                <div class="form-group {{ $errors->has('email') ? 'has-error' : ''}}">
                                    {!! Html::decode(Form::label('email',trans('frontend.visitor_email_phone'). "<span class='text-danger'>*</span>", ['class' => 'control-label heading'])) !!}
                                    {!! Form::text('email', null, ('' == 'required') ? ['class' => 'form-control input','id '=>'email','required' => 'required', 'placeholder'=>trans('frontend.search_email')] : ['class' => 'form-control input','id '=>'email', 'placeholder'=>trans('frontend.search_email_or_phone')]) !!}
                                    {!! $errors->first('email', '<p class="text-danger">No se Encuentra Registrado</p>') !!}
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <a href="{{ route('/') }}" class="btn cancel text-light float-left ">
                                            {{__('frontend.cancel')}}
                                        </a>
                                    </div>
                                    @if($errors->any())
                                    <div class="col-4">
                                        <a href="{{ route('check-in.step-one') }}" class="btn text-light float-left continue">
                                            Registrar
                                        </a>
                                    </div>
                                    @endif
                                    <div class="col-4">
                                        <button type="submit" class="btn text-light float-right continue" id="continue">
                                            Buscar
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