@extends('frontend.layouts.frontend')

@section('content')
    <section id="pm-banner-1" class="pm-banner-section-1 position-relative custom-css">
         @if(!auth()->user())
         <div class="container">
            <div class="pm-banner-content position-relative">
                <div class="pm-banner-text pm-headline pera-content">
                    <span class="pm-title-tag">&nbsp;&nbsp;&nbsp;&nbsp;{{setting('site_name')}}</span>
                    <br><br>
                    <h2>Iniciar Sesión</h2>
                    <p>Por favor inicie sesión para continuar dentro de nuestro sistema de recepción de visitantes</p>
                    <div class="d-flex">
                        <div class="ei-banner-btn">
                            <a href="{{ route('login') }}">
                                <span>Iniciar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="pm-banenr-img position-absolute d-flex justify-content-end">
                    <img src="{{asset('images/quick-pass.png')}}" styles="width:30px" alt="">
                </div>
            </div>
            <hr class="hr-line">
         </div>
         @else
         <div class="container">
            @if(session('blocked_visitor'))
                <!-- DEBUG: Visitante bloqueado detectado -->
                <!-- DEBUG: {{ session('blocked_visitor')->first_name ?? 'No name' }} -->
                <!-- Card de Visitante Bloqueado -->
                <div class="row justify-content-center mt-4">
                    <div class="col-md-8">
                        <div class="card border-danger shadow-lg">
                            <div class="card-header bg-danger text-center">
                                <h3 class="mb-0 text-danger">
                                    <i class="fas fa-ban mr-2"></i>
                                    ACCESO DENEGADO
                                </h3>
                            </div>
                            <div class="card-body text-center p-4">
                                @if(session('blocked_visitor')->image)
                                    <div class="mb-3">
                                        <img src="{{ session('blocked_visitor')->image }}"
                                             alt="Foto del visitante" 
                                             class="rounded-circle border border-danger" 
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <div class="rounded-circle border border-danger d-inline-flex align-items-center justify-content-center bg-light" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-muted"></i>
                                        </div>
                                    </div>
                                @endif
                                
                                <h4 class="text-danger mb-3">
                                    El/La visitante <strong>{{ session('blocked_visitor')->first_name }} {{ session('blocked_visitor')->last_name }}</strong> 
                                    tiene prohibida la entrada al SENIAT
                                </h4>
                                
                                <div class="alert alert-danger border-0 mb-3">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Este visitante ha sido bloqueado y no puede acceder al edificio.</strong>
                                </div>
                                
                                <div class="row text-muted">
                                    <div class="col-md-6">
                                        <p><strong>Teléfono:</strong> {{ session('blocked_visitor')->phone }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> {{ session('blocked_visitor')->email }}</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <a href="{{ route('home') }}" class="btn btn-primary">
                                        <i class="fas fa-home mr-2"></i>
                                        Volver al Inicio
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="pm-banner-content position-relative custom-css">
                    <div class="pm-banner-text pm-headline pera-content">
                        <span class="pm-title-tag">&nbsp;&nbsp;&nbsp;&nbsp;{{setting('site_name')}}</span>
                        <br><br>
                        <p>Seleccione las opciones pertinentes</p>
                        <div class="d-flex">
                            <div class="ei-banner-btn">
                                <a href="{{ route('check-in.return') }}">
                                    <span>Registrar Visitante</span>
                                </a>
                            </div>
                            <!-- <div class="ei-banner-btn ml-2">
                                <a href="{{ route('checkout.index') }}">
                                    <span>Registrar Salida</span>
                                </a>
                            </div> -->
                        </div>
                    </div>
                    <div class="pm-banenr-img position-absolute d-flex justify-content-end">
                        <img src="{{asset('images/quick-pass.png')}}" alt="">
                    </div>
                </div>
            @endif
            <hr class="hr-line">
            <div class="d-flex justify-content-center footer-text pb-3">
                <span> {{setting('site_footer')}}</span>
            </div>
        </div>
         @endif
    </section>
@endsection

