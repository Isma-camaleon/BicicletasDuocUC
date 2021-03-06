<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo e(asset('images/favicon.ico')); ?>" type="image/vnd.microsoft.icon">

        <title>Biciregistro</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
        <link  defer href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            .btn-primary{
              color: white !important;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
          <?php if(Route::has('login')): ?>
              <div class="top-right links">
                  <?php if(auth()->guard()->check()): ?>
                      <a href="<?php echo e(url('/home')); ?>">Home</a>
                  <?php else: ?>
                      <a href="<?php echo e(route('login')); ?>">Ingresar</a>
                  <?php endif; ?>
              </div>
          <?php endif; ?>
            <div class="content">
                <div class="title m-b-md">
                    Registro de bicicletas
                    <div class="">

                      <?php if(Route::has('login')): ?>
                              <?php if(auth()->guard()->check()): ?>
                                  <a href="<?php echo e(url('/home')); ?>" class="btn btn-primary btn-lg"><b>Home</b></a>
                              <?php else: ?>
                                  <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg"><b>Ingresar</b></a>
                              <?php endif; ?>
                      <?php endif; ?>
                    </div>
                </div>


                <!--div class="links">
                    <a href="https://github.com/Isma-camaleon/BicicletasDuocUC">GitHub</a>
                </div-->
            </div>
        </div>
    </body>
</html>
