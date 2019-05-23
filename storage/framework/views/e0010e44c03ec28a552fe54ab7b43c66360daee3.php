<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">

                  <div class="row ">
                      <?php echo e(Form::open(['route' => 'registro.find','class' => 'col-md-6'])); ?>

                      <div class="mb-3 mt-2">
                        <div class="input-group ">
                          <div class="input-group-prepend">
                            <span class="input-group-text">Código bicicleta</span>
                          </div>
                          <input type="text" id="buscarVehiculo" class="form-control mr-1" name="codigo" data-toggle="tooltip" data-placement="bottom" title="Ingrese código de la bicicleta" required  autofocus>

                          <?php echo e(Form::submit('Buscar', ['class' => 'btn btn-secondary', 'id'=>'btnBuscarVehiculo'])); ?>

                        </div>
                      </div>
                      <?php echo e(Form::close()); ?>


                      <?php if(isset($vehiculo)): ?>
                      <?php echo e(Form::open(['route' => 'registro.validarTercero','class' => 'col-md-6'])); ?>

                      <div class="mb-3 mt-2" >
                        <div class="input-group ">
                          <div class="input-group-prepend">
                            <span class="input-group-text">Código tercero</span>
                          </div>
                          <input type="text" class="form-control mr-1" name="codigo" data-toggle="tooltip" data-placement="bottom" title="Validar código para retiro por terceros" required  >
                          <?php echo e(Form::submit('Validar', ['class' => 'btn btn-secondary'])); ?>

                        </div>
                      </div>
                      <?php echo e(Form::close()); ?>

                      <?php endif; ?>
                    </div>
                  </div>


                  <?php if(isset($vehiculo)): ?>
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item pt-0 pb-2">
                        <div class="row justify-content-center pt-3 pl-2">
                          <?php if($retiroPorTercero): ?>
                          <div class="col-md-10 mb-2">
                            <div class="alert alert-info" role="alert">
                            <b>Esta bicicleta puede ser retirada por un tercero, verifique el código para retiro por terceros</b>
                            </div>
                          </div>
                          <?php endif; ?>
                          <div class="col-md-8">
                            <h4 class="text-danger pl-2">Acción: <?php echo e($accion); ?> </h4>
                            <h4 class="text-danger pl-2"><?php setlocale(LC_ALL, 'es_CL'); echo (date("d/m/Y g:i a")); ?> </h4>
                          </div>
                          <div class="col-md-4 pt-2">
                            <?php echo e(Form::open(['route' => 'registro.store'])); ?>

                              <input type="hidden" name="vehiculo_id" value="<?php echo e($vehiculo->id); ?>">
                              <?php echo e(Form::submit('REGISTRAR', ['class' => 'btn btn-success'])); ?>

                              <button type="button" class="btn btn-outline-danger  ml-2" data-toggle="modal" data-target="#rechazarModal" title="Rechazar si no es el dueño">RECHAZAR</button>
                            <?php echo e(Form::close()); ?>

                          </div>
                        </div>
                      </li>

                    <!-- Modal -->
                    <div class="modal fade" id="rechazarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-danger" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title"><b>Se ha rechazado la salida!</b></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            ...
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- /Modal -->

                    <div class="card-body">
                      <div class="row">
                        <div class="col-sm-6 pb-2">
                          <div class="card">
                            <div class="card-body mb-2">
                              <table class="table responsive-md table-sm mb-4">
                                <img src="<?php echo e(Storage::url($vehiculo->image)); ?>" class="img-fluid rounded mx-auto d-block mb-3" style="max-height:200px;" alt="">
                                <tbody>
                                  <tr>
                                    <th scope="row" style="width:30%;">Código</th>
                                    <td><?php echo e($vehiculo->codigo); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Marca</th>
                                    <td><?php echo e($vehiculo->marca->description); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Modelo</th>
                                    <td><?php echo e($vehiculo->modelo); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Color</th>
                                    <td>
                                      <div class="cuadrado"  style="width: 50px;
                                      height: 25px; border-radius: 3px; border: 1px solid #555;
                                      background-color: <?php echo e($vehiculo->color); ?>">
                                      </div>
                                    </td>
                                  </tr>

                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="card">
                            <div class="card-body">
                              <img src="<?php echo e(Storage::url($vehiculo->dueno->image)); ?>" class="img-fluid rounded mx-auto d-block mb-3" style="max-height:200px;">
                              <table class="table responsive-md table-sm mb-0 pb-0">
                                <tbody>
                                  <tr>
                                    <th scope="row" style="width:30%;">Run</th>
                                    <td><?php echo e($vehiculo->dueno->rut); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Nombre</th>
                                    <td><?php echo e($vehiculo->dueno->nombre); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Área</th>
                                    <td><?php echo e($vehiculo->dueno->tipoDueno->description); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Correo</th>
                                    <td><?php echo e($vehiculo->dueno->correo); ?></td>
                                  </tr>
                                  <tr>
                                    <th scope="row">Celular</th>
                                    <td>+569 <?php echo e($vehiculo->dueno->celular); ?></td>
                                  </tr>

                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>

                    </div>
                    </ul>
                  <?php else: ?>

                    <div class="card-body">
                      <div class="row justify-content-center">
                        <?php if(isset($registrarNuevaBicicleta)): ?>
                        <div class="row justify-content-center col-md-10">
                          <h3 class="text-danger">Esta bicicleta no esta registrada en el sistema</h3>
                        </div>
                        <div class="row justify-content-center col-md-10">
                          <?php if (\Shinobi::can('vehiculos.create')): ?>
                          <a class="btn btn-primary" href="<?php echo e(route('vehiculos.create')); ?>" >Registrar bicicleta</a>
                          <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <h3 class="text-secondary">Ingrese el código de la bicicleta</h3>
                        <?php endif; ?>

                      </div>
                    </div>
                  <?php endif; ?>

                </div>


            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>