@extends('frontend.layouts.frontend')
@section('content')
    <section id="pm-banner-1" class="pm-banner-section-1 position-relative custom-css">
        <div class="container">
            <div class="pm-banner-content">
                <div class="row">
                    <div class="col-md-12 d-flex">
                        <div class="pm-banner-text pm-headline pera-content col-md-6">
                            <span class="pm-title-tag">&nbsp;&nbsp;&nbsp;&nbsp;{{setting('site_name')}}</span>
                            <br><br>
                            <h2>{{setting('site_description')}}</h2>
                            <p> {{strip_tags(setting('welcome_screen'))}}</p>
                            <div class="d-flex">
                                <div class="ei-banner-btn">
                                    <a href="{{ route('check-in.step-one') }}">
                                        <span>{{__('frontend.check_in')}}</span>
                                    </a>
                                </div>
                                <div class="ei-banner-btn ml-2">
                                    <a href="{{ route('check-in.scan-qr') }}">
                                        <span>{{__('frontend.scan_qr')}}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="pm-banenr-vedio d-flex justify-content-end col-md-6 vedioPreview">
                            <video autoplay id="preview" width="90%"></video>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            <hr class="hr-line">
            <div class="d-flex justify-content-center footer-text pb-3">
                <span> {{setting('site_footer')}}</span>
            </div>
        </div>
    </section>
@endsection

@push('js')

{{-- For Qrcode Scan --}}
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
    $(document).ready(function() {
        let scanner = new Instascan.Scanner({video: document.getElementById('preview')});
            Instascan.Camera.getCameras().then(function(cameras){
                if(cameras.length > 0 ){
                    scanner.start(cameras[0]);
                } else{
                    alert('No cameras found');
                }

            }).catch(function(e) {

                console.error(e);
            });

            scanner.addListener('scan',function(c){
                let appurl = "{{env('APP_URL')}}";
                appUrl = (new URL(appurl));
                appUrl = appUrl.hostname;
                let domain = (new URL(c));
                domain = domain.hostname;

                if (appUrl == domain) {
                    window.open(c, "_self");
                }else{
                    alert('Please Enter Valid Qrcode');
                }
                
            });
    });
 </script>
    
@endpush

