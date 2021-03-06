<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="card">
                <div class="card-header"><h3 style="margin-bottom: 0px">Editar dueño </h3> </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="<?php echo e(Storage::url($dueno->image)); ?>" class="img-fluid rounded img-thumbnail" alt=".   Imagen dueño">
                        </div>
                        <div class="col-md-8">
                            <?php echo e(Form::model($dueno, ['enctype' => 'multipart/form-data','method'  => 'put', 'route' => [ 'duenos.update', $dueno, 'file'=>true]])); ?>

                                <?php echo $__env->make('duenos.partials.form', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                                <div class="form-group float-right">
                                    <a href="<?php echo e(route('duenos.index')); ?>" class="btn btn-light mr-2">Volver</a>
                                    <?php echo e(Form::submit('Guardar', ['class' => 'btn btn-primary'])); ?>

                                </div>

                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>