@extends('frontend.layouts.frontend')

@section('content')
    <section id="pm-banner-1" class="">
        <div class="container">
            <div class="row custom-css-step-one">
                <div class="col-lg-12">
                    <h2 class="text-dark">{{ __('frontend.tearms_conditons') }}</h2>
                    <br>
                   <p class=" text-dark">
                    {!! setting('terms_condition') !!}
                   </p>
                </div>
            </div>
            <hr class="hr-line">
            <div class="d-flex justify-content-center footer-text pb-3">
                <span> {{setting('site_footer')}}</span>
            </div>
        </div>
    </section>
@endsection
