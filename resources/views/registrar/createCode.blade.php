@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-sm-10">
            <div class="card">
                <div class="card-header py-0">

                  <div class="row ">
                      {{ Form::open(['route' => 'registro.findDueno','class' => 'col-sm-6 col-md-6 mt-2']) }}
                      <div class="mb-3 mt-2">
                        <div class="input-group ">
                          <div class="input-group-prepend">
                            <span class="input-group-text">RUN</span>
                          </div>
                          <input type="text" id="buscarDueno" class="form-control mr-1" name="run" data-toggle="tooltip" data-placement="bottom" title="Ingrese run del ciclista" required  autofocus>

                          {{ Form::submit('Buscar', ['class' => 'btn btn-secondary', 'id'=>'btnBuscarDueno']) }}
                        </div>
                      </div>
                      {{ Form::close() }}
                      @if(isset($dueno))
                      @if($dueno->vehiculos->where('activo',true)->count() <= 1)
                      <div class="col-md-3 col-sm-2 col-lg-4">

                      </div>

                        {{ Form::open(['route' => 'registro.crearCodigoTercero','id' => 'formCreateCode']) }}
                        <input type="hidden" name="vehiculoId" class="vehiculoId" value="{{$dueno->vehiculos[0]->id}}">
                        <div class="col-sm-4 col-md-3 mt-2 col-lg-2 px-0">
                          <div class="input-group mx-auto">
                            <button style="z-index:1"type="button" onclick="generarCodigoTercero({{$dueno->vehiculos[0]->id}})" class="btn btn-success" id="generarCodigo" name="generarCodigo" data-toggle="modal" data-target="#generarCodigoModal"><b>Generar código</b></button>
                          </div>
                        </div>
                        {{ Form::close() }}
                      @else
                      <div class="text-right mt-3 px-3 pt-2 col-sm-6 col-md-6">
                      <h5 class="mb-0 pb-0">  <label class="text-secondary"> Código de retiro: <b class="codigoTercero text-danger"> </b></label></h5>
                      </div>

                      @endif
                      @endif
                    </div>
                  </div>


                  @if(isset($dueno))

                    <div class="row">
                      <div class="col-sm-6">
                        <div class="card m-3">
                          <div class="card-body">
                            <img src="{{ Storage::url($dueno->image) }}" class="img-fluid rounded mx-auto d-block mb-3" style="max-height:200px;">
                            <table class="table responsive-md table-sm mb-0 pb-0">
                              <tbody>
                                <tr>
                                  <th scope="row" style="width:30%;">Run</th>
                                  <td>{{ $dueno->rut }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Nombre</th>
                                  <td>{{ $dueno->nombre }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Área</th>
                                  <td>{{ $dueno->tipoDueno->description }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Correo</th>
                                  <td>{{ $dueno->correo }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Celular</th>
                                  <td>+569 {{ $dueno->celular }}</td>
                                </tr>

                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      @if($dueno->vehiculos->where('activo',true)->count()>1)
                      <div class="col-sm-6 my-3">
                        {{ Form::open(['route' => 'registro.crearCodigoTercero','id' => 'formCreateCode']) }}
                        <input type="hidden" class="vehiculoId" name="vehiculoId" value="">
                        @foreach($dueno->vehiculos->where('activo',true) as $vehiculo)
                        <div id="accordion">
                          <div class="card mt-1 mr-3">
                            <div class="card-header py-0" id="heading{{$vehiculo->id}}">
                              <div class="row justify-content-center" data-toggle="collapse" data-target="#collapseOne{{$vehiculo->id}}" aria-expanded="true" aria-controls="collapseOne{{$vehiculo->id}}">
                                <div class="col-sm-3 mt-1 px-0">
                                  <img src="{{ Storage::url($vehiculo->image) }}" class="img-fluid rounded" style="max-height:50px;" alt="">
                                </div>
                                <h5 class="mb-0 col-sm-6  px-0">
                                  <a class="btn btn-link text-primary" style="text-decoration:none" data-toggle="collapse" data-target="#collapseOne{{$vehiculo->id}}" aria-expanded="true" aria-controls="collapseOne{{$vehiculo->id}}">
                                    <b>{{$vehiculo->marca->description}} {{$vehiculo->modelo}}</b><br>
                                    {{$vehiculo->codigo}}
                                  </a>
                                </h5>
                                  <div class="col-sm-3  px-0">
                                    <div class="input-group mt-3">
                                      <button type="button" class="btn btn-success btn-sm" onclick="generarCodigoTercero({{$vehiculo->id}})" id="generarCodigo" name="generarCodigo" data-toggle="modal" data-target="#generarCodigoModal"><b>Generar código</b></button>
                                    </div>
                                  </div>
                                </div>


                              </div>

                            </div>

                            <div id="collapseOne{{$vehiculo->id}}" class="collapse" aria-labelledby="heading{{$vehiculo->id}}" data-parent="#accordion">
                              <div class="row card-body">
                                <div class="col-sm-5">
                                  <img src="{{ Storage::url($vehiculo->image) }}" class="img-fluid rounded mx-auto d-block mb-3" style="max-height:200px;" alt="">

                                </div>
                                <div class="col-sm-7">
                                  <table class="table responsive-md table-sm mb-4">
                                    <tbody>
                                      <tr>
                                        <th scope="row" style="width:30%;">Código</th>
                                        <td>{{ $vehiculo->codigo }}</td>
                                      </tr>
                                      <tr>
                                        <th scope="row">Marca</th>
                                        <td>{{ $vehiculo->marca->description }}</td>
                                      </tr>
                                      <tr>
                                        <th scope="row">Modelo</th>
                                        <td>{{ $vehiculo->modelo }}</td>
                                      </tr>
                                      <tr>
                                        <th scope="row">Color</th>
                                        <td>
                                          {{$vehiculo->color}}
                                        </td>
                                      </tr>



                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                        @endforeach
                        {{ Form::close() }}
                        </div>
                      </div>
                      @else

                      <div class="col-sm-6">
                        <div class="card m-3 pb-4">
                          <div class="card-body">
                            <img src="{{ Storage::url($dueno->vehiculos[0]->image) }}" class="img-fluid rounded mx-auto d-block mb-3" style="max-height:200px;">
                            <table class="table responsive-md table-sm mb-0 pb-0">
                              <tbody>
                                <tr>
                                  <th scope="row" style="width:35%;">Código</th>
                                  <td>{{ $dueno->vehiculos[0]->codigo }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Marca</th>
                                  <td>{{ $dueno->vehiculos[0]->marca->description }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Modelo</th>
                                  <td>{{ $dueno->vehiculos[0]->modelo }}</td>
                                </tr>
                                <tr>
                                  <th scope="row">Color</th>
                                  <td>{{$dueno->vehiculos[0]->color}}
                                  </td>
                                </tr>
                                <tr>
                                  <th>Código de retiro </th>
                                  <td><b class="codigoTercero text-danger"> </b> </td>
                                </tr>

                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      @endif

                  @else

                    <div class="card-body">
                      <div class="row justify-content-center">
                        @if(isset($registrarDueno))
                        <div class="row justify-content-center col-md-10">
                          <h3 class="text-danger">Este RUN no esta registrado en el sistema</h3>
                        </div>
                        <div class="row justify-content-center col-md-10">
                          @can('vehiculos.create')
                          <a class="btn btn-primary" href="{{route('vehiculos.create')}}" >Registrar</a>
                          @endcan
                        </div>
                        @else
                        <h3 class="text-secondary">Ingrese RUN del ciclista</h3>
                        @endif

                      </div>
                    </div>
                  @endif

                </div>


            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="generarCodigoModal" tabindex="-1" role="dialog" aria-labelledby="generarCodigoModal" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#00c851;">
            <h5 class="modal-title text-white"><b>Código generado</b></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Se ha generado el código correctamente! <br>
            El código <b class="codigoTercero">  </b> solo será válido hasta las 23:59 <br><br>
            ¿Desea envíar un e-mail a la cuenta
            @if(isset($dueno))
            <em><b>{{ $dueno->correo }}</b></em>,
            @endif
             con el código de retiro <b class="codigoTercero">  </b>?
          </div>
          <div class="modal-footer">
            <input type="hidden" id="vehiculo_id" name="vehiculo_id" value="">
            <input type="hidden" id="codigo_tercero" name="codigo_tercero" value="">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary">Enviar e-mail</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /Modal -->
    <script src="{{ asset('js/jquery-ui.js') }}" defer></script>
    <script defer type="text/javascript">
    $(document).ready(function() {

    $( "#buscarDueno" ).autocomplete({
        source: "{{url('autocompleteRunDueno')}}",
        minLength: 3
      });

    generarCodigoTercero = function(vehiculo_id){
      $('.vehiculoId').val(vehiculo_id);
      $.ajax({
         headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
         type: "POST",
         url: "{{route('registro.crearCodigoTercero')}}",
         data: {
           "vehiculoId": $('.vehiculoId').val(),
         },
         success: function(data)
         {
           $('.codigoTercero').html(data);
           $('#codigo_tercero').val(data);
           $('#vehiculo_id').val(vehiculo_id);
         }
     });
    };

});

    </script>
</div>
@endsection
