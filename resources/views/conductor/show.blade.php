@extends('adminlte::page')

@section('template_title')
    {{ $conductor->name ?? __('Mostrar') . " " . __('Conductor') }}
@endsection

@section('content')
    <section class="content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                        <div class="float-left">
                            <span class="card-title"><i class="fas fa-user-tie mr-2"></i>{{ __('Mostrar') }} Conductor</span>
                        </div>
                        <div class="float-right">
                            <a class="btn btn-primary btn-sm" href="{{ route('conductor.index') }}"> {{ __('Volver') }}</a>
                        </div>
                    </div>

                    <div class="card-body bg-white">
                        
                                <div class="form-group mb-2 mb20">
                                    <strong>Conductor Id:</strong>
                                    {{ $conductor->conductor_id }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Nombre:</strong>
                                    {{ $conductor->nombre }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Apellido:</strong>
                                    {{ $conductor->apellido }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Fecha Nacimiento:</strong>
                                    {{ $conductor->fecha_nacimiento ? \Carbon\Carbon::parse($conductor->fecha_nacimiento)->format('d/m/Y') : '' }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Ci:</strong>
                                    {{ $conductor->ci }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Celular:</strong>
                                    {{ $conductor->celular }}
                                </div>
                                <div class="form-group mb-2 mb20">
                                    <strong>Id Licencia:</strong>
                                    {{ $conductor->id_licencia }}
                                </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
