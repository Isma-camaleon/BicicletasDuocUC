@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"><h3 style="margin-bottom: 0px">Editar Guardia </h3> </div>

                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            {{ Form::model($role, ['method'  => 'put', 'route' => [ 'roles.update', $role, 'file'=>true]]) }}
                                @include('roles.partials.form')
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
