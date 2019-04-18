@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"><h3 style="margin-bottom: 0px">Editar bicicleta </h3> </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{$vehiculo->image}}" class="img-fluid rounded img-thumbnail" alt=".   Imagen bicicleta">
                        </div>
                        <div class="col-md-8">
                            {{ Form::model($vehiculo, ['method'  => 'put', 'route' => [ 'vehiculos.update', $vehiculo, 'file'=>true]]) }}
                                @include('vehiculos.partials.form')
                                <div class="form-group float-right">
                                    {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
                                </div>
                                
                            {{ Form::close() }}
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection