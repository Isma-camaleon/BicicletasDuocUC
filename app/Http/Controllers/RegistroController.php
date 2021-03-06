<?php

namespace BiciRegistro\Http\Controllers;

use BiciRegistro\Registro;
use BiciRegistro\Vehiculo;
use BiciRegistro\Dueno;
use BiciRegistro\Tercero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Freshwork\ChileanBundle\Rut;
use Illuminate\Support\Facades\Validator;

class RegistroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('registrar.index');
    }

    /**
     * Buscar la bicicleta para mostrar los datos en pantalla
     *
     * @return \Illuminate\Http\Response
     */
    public function find(Request $request)
    {
      setlocale(LC_ALL, 'es_CL');
        if(empty($request->input('codigo'))){
          return view('registrar.index',['danger'=>'Ingrese un código']);
        }
        $retiroPorTercero=false;
        $vehiculo = Vehiculo::where('codigo', $request->input('codigo'))->first();
        if(!isset($vehiculo)){
          $registrarNuevaBicicleta=true;
          return view('registrar.index', compact('registrarNuevaBicicleta','retiroPorTercero'));
        }else{
          if(!$vehiculo->activo){
            return view('registrar.index',['danger'=>'Bicicleta DESHABILITADA!!']);
          }
        }

        //$vehiculoEnRegistro = $this->obtenerMovimientoVehiculo($vehiculo);

        if($vehiculo->isInside){
          $accion="Salida";
        }else{
          $accion="Ingreso";
        }
        // Solo mostrará el mensaje "Puede ser retirado por un tercero", si la bicicleta esta dentro de la institución
        if($this->hasCodeTercero($vehiculo) && $vehiculo->isInside == 1){
          $retiroPorTercero=true;
        }

      return view('registrar.index', compact('vehiculo','accion','retiroPorTercero'));

    }

    public function hasCodeTercero(Vehiculo $vehiculo){
        $tercero = Tercero::where('vehiculo_id','=',$vehiculo->id)
        ->where('activo','=',1)
        ->first();
        if(isset($tercero->codigo_tercero)){
          return true;
        }else{
          return false;
        }
    }


    public function findDueno(Request $request)
    {
      /*$validacion = $this->validate($request, [
          'run' => 'required|cl_rut',
        ]);*/

      try {
        $run = Rut::parse($request->input('run'))->format(Rut::FORMAT_WITH_DASH);
      } catch (\Exception $e) {
          return view('registrar.createCode',['danger'=>'Error en el formato del Run']);
      }


        $dueno = Dueno::where('rut', $run)->first();

        if(!isset($dueno)){
          $registrarDueno=true;
          return view('registrar.createCode', compact('registrarDueno'));
        }else{
          if($dueno->vehiculos->where('activo',true)->count() >= 1){
            return view('registrar.createCode', compact('dueno'));
          }else{
            return view('registrar.createCode',['danger'=>'El usuario no tiene bicicletas registradas']);
          }

        }
        return back()->with('danger','Problemas al encontrar el Run');
    }

    /**
     * Buscar la bicicleta para mostrar los datos en pantalla
     *
     * @return \Illuminate\Http\Response
     */
    public function validarTercero(Request $request)
    {
      if($request->ajax()){
        if($request->input('codigo')!=null && $request->input('vehiculo_id')!=null){

          $tercero = Tercero::where('codigo_tercero','=',$request->input('codigo'))
                      ->where('vehiculo_id','=',$request->input('vehiculo_id'))
                      ->where('activo','=',1)
                      ->first();

          if(isset($tercero)){
            $tercero->activo = 0;
            $tercero->update();
            return "permitted";
          }
        }
      }

      return "denied";


    }


    public function terceroIndex(){
      return view('registrar.createCode');
    }

    // Genera el código para retiro por terceros y lo guarda en la BD
    public function crearCodigoTercero(Request $request){
      if($request->ajax()){

        $codigoRetiroTercero = rand(1000,9999);
        $vehiculo_id = $request->input('vehiculoId');
        $vehiculo = Vehiculo::find($vehiculo_id);
        if(isset($vehiculo)){
          if($this->hasCodeTercero($vehiculo)){
            // Como la petición es ajax, no mostrara un mensaje si lo mando
            return back();
          }
        }

        Tercero::create([
          "vehiculo_id" => $vehiculo_id,
          "codigo_tercero" => $codigoRetiroTercero
        ]);

        return $codigoRetiroTercero;
        //
      }
      return back()->with('danger','Error al cargar el código para retiro por terceros');
    }

    public function obtenerCodigoTerceroActual(Vehiculo $vehiculo){
      $codigoTercero = $vehiculo->terceros;
      $codigoTercero = $codigoTercero->select('codigo_tercero')->where('activo','=',1)->first();
      dd($codigoTercero);
    }

    /**
     * Guarda el registro de entrada o salida de la bicicleta
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $user = Auth::user();
      setlocale(LC_ALL, 'es_CL');
      $vehiculo = Vehiculo::find($request->input('vehiculo_id'));

    //  $vehiculoEnRegistro = $this->obtenerMovimientoVehiculo($vehiculo);

      if($vehiculo->isInside){
          $accion = "Salida";
          $vehiculo->isInside = false;
      }else{
          $accion="Ingreso";
          $vehiculo->isInside = true;
      }
      $vehiculo->update();
      Registro::create([
          'vehiculo_id' => $vehiculo->id,
          'usuario_id' => $user->id,
          'accion' => $accion,
      ]);
      return view('registrar.index',['success' => 'Registro guardado']);
    }

    /**
     * Retorna el último movimiento del vehiculo en el dia
     *
     * @param  \BiciRegistro\Vehiculo  $registro
     */
    public function obtenerMovimientoVehiculo(Vehiculo $vehiculo){
      // Obtenemos el ultimo movimiento de la bicicleta en el día
      $vehiculoEnRegistro = Registro::select('registros.vehiculo_id as vehiculo_id', 'registros.accion as accion', 'registros.created_at')
      ->join('vehiculos', 'vehiculos.id', '=', 'registros.vehiculo_id')
      ->where('registros.vehiculo_id','=',$vehiculo->id)
      ->where('registros.created_at','like',date("Y-m-d").'%')
      ->orderBy('registros.created_at', 'desc')
      ->first();
      return $vehiculoEnRegistro;
    }

    /*
    * Funcion que devuelve el rut de los dueños para el autocompletar del módulo Retiro por terceros
    */
    public function searchDueno(Request $request){
      $search = $request->get('term');
      $duenos = Dueno::select('rut')
      ->where('rut', 'LIKE', '%'. $search. '%')->get();
      $data = [];
      foreach ($duenos as $dueno) {
        $data[]=$dueno->rut;
      }
      return response($data);
    }





  ///////////////////////////////////////////////////////
  /////////////     REPORTES    ////////////////////////
  /////////////////////////////////////////////////////

  public function reportes(){
    $anios = Registro::query()
    ->select(\DB::raw(\DB::raw("YEAR(created_at) as anio")))
    ->groupBy('anio')
    ->get();
    return view('reportes.index',compact('anios'));
  }

  public function listarJson(Request $request){

      $modelo =  Registro::join('vehiculos','vehiculos.id', '=', 'vehiculo_id')
                ->join('users','usuario_id','=','users.id')
                ->join('marcas','vehiculos.marca_id','=','marcas.id')
                ->join('duenos','duenos.id','=','vehiculos.dueno_id')
                ->select(\DB::raw("CONCAT(marcas.description,' ' ,vehiculos.modelo) AS vehiculo"), "registros.id",
                "registros.accion","registros.created_at","vehiculos.codigo as codigoVehiculo","users.name as usuario",
                "users.email as correoUsuario","duenos.rut as dueno", "registros.vehiculo_id as vehiculo_id");
      return datatables()->eloquent($modelo)
      ->addColumn('showDetalle', function($registro) {
        //'<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.show', $registro->vehiculo_id).'" target="_blank" data-toggle="tooltip" data-placement="left" title="Ver detalle de la bicicleta y su dueño">Ver detalle</a>';
          return '<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.show', $registro->vehiculo_id).'" target="_blank" ">Detalle bicicleta</a>';
      })
      ->filterColumn('vehiculo', function($query, $keyword) {
                $query->whereRaw("CONCAT(marcas.description,' ',vehiculos.modelo) LIKE '%".$keyword."%'");
      })
      ->rawColumns(['showDetalle'])
      ->toJson();

  }

  ///////////////////////////////////////////////////////
  /////////////     Gráficos    ////////////////////////
  /////////////////////////////////////////////////////

  public function ultimoDiaDelMes($anio, $mes){
    return date('d',(mktime(0,0,0,$mes+1,1,$anio)-1));
  }

  public function registrosPorDias($anio, $mes){
    $primerDia = 1;
    $ultimoDia = $this->ultimoDiaDelMes($anio, $mes);

    $fechaInicial = date('Y-m-d H:i:s',strtotime($anio.'-'.$mes.'-'.$primerDia));
    $fechaFinal   = date('Y-m-d H:i:s',strtotime($anio.'-'.$mes.'-'.$ultimoDia));

    $entradas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("DAY(created_at) as dia"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Ingreso')
    ->groupBy('dia')
    ->get();
    $salidas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("DAY(created_at) as dia"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Salida')
    ->groupBy('dia')
    ->get();

    for($i=0;$i<$ultimoDia;$i++){
        $registrosEntrada[$i]=0;
        $registrosSalida[$i]=0;
    }

    foreach ($entradas as $registro) {
      $registrosEntrada[(($registro->dia)-1)]=$registro->countRegistros;
    }
    foreach ($salidas as $registro) {
      $registrosSalida[(($registro->dia)-1)]=$registro->countRegistros;
    }


    $data = array("totalDias"=>$ultimoDia, "entradas"=>$registrosEntrada, "salidas"=>$registrosSalida);
    return json_encode($data);

  }

  public function registrosPorHora($anio, $mes, $dia){
    $fechaInicial = date('Y-m-d H:i:s',strtotime($anio.'-'.$mes.'-'.$dia.' 00:00:00'));
    $fechaFinal   = date('Y-m-d H:i:s',strtotime($anio.'-'.$mes.'-'.$dia.' 23:59:59'));
    $rangoHora = ['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00',
                  '16:00','17:00','18:00','19:00','20:00','21:00','22:00','23:00','00:00','01:00'];

    $entradas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("HOUR(created_at) as hora"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Ingreso')
    ->groupBy('hora')
    ->get();

    $salidas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("HOUR(created_at) as hora"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Salida')
    ->groupBy('hora')
    ->get();

    for($i=0;$i<sizeof($rangoHora);$i++){
        $registrosEntrada[$i]=0;
        $registrosSalida[$i]=0;
      }
    for($j=0;$j<sizeof($entradas);$j++){
      for($i=0;$i<sizeof($rangoHora);$i++){
        $hora = date('H',strtotime($anio.'-'.$mes.'-'.$dia.' '.$rangoHora[$i]));
        if($hora == $entradas[$j]->hora){
          $registrosEntrada[$i]=$entradas[$j]->countRegistros;
          break;
        }
      }
    }

    for($j=0;$j<sizeof($salidas);$j++){
      $horasRegistradas= date('H',strtotime($salidas[$j]->created_at));
      for($i=0;$i<sizeof($rangoHora);$i++){
        $hora = date('H',strtotime($anio.'-'.$mes.'-'.$dia.' '.$rangoHora[$i]));
        if($hora == $salidas[$j]->hora){
          $registrosSalida[$i]=$salidas[$j]->countRegistros;
        }
      }
    }
    $data = array("ragoHoras" => $rangoHora,"entradas"=>$registrosEntrada, "salidas"=>$registrosSalida);
    return json_encode($data);

  }


  public function registrosMensual($anio){
    $fechaInicial = date('Y-m-d H:i:s',strtotime($anio.'-01-01'));
    $fechaFinal   = date('Y-m-d H:i:s',strtotime($anio.'-12-31'));
    $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];


    $entradas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("MONTH(created_at) as mes"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Ingreso')
    ->groupBy('mes')
    ->get();
    $salidas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("MONTH(created_at) as mes"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Salida')
    ->groupBy('mes')
    ->get();

    // 12 Meses
    for($i=0;$i<12;$i++){
        $registrosEntrada[$i]=0;
        $registrosSalida[$i]=0;
    }

    for($j=0;$j<sizeof($entradas);$j++){
      for($i=0;$i<12;$i++){
        $mes = date('m',strtotime($anio.'-'.($i+1)));
        if($mes == $entradas[$j]->mes){
          $registrosEntrada[$i]=$entradas[$j]->countRegistros;
          break;
        }
      }
    }

    for($j=0;$j<sizeof($salidas);$j++){
      for($i=0;$i<12;$i++){
        $mes = date('m',strtotime($anio.'-'.($i+1)));
        if($mes == $salidas[$j]->mes){
          $registrosSalida[$i]=$salidas[$j]->countRegistros;
          break;
        }
      }
    }
    $data = array("meses"=>$meses, "entradas"=>$registrosEntrada, "salidas"=>$registrosSalida);
    //dd($data);
    return json_encode($data);
  }


  public function registrosAnual(){
    $aniosEnBD = Registro::query()
    ->select(\DB::raw(\DB::raw("YEAR(created_at) as anio")))
    ->groupBy('anio')
    ->get();

    // Pasamos el obeto a un array
    for($i=0;$i<sizeof($aniosEnBD);$i++){
      $anios[$i]= $aniosEnBD[$i]->anio;
    }

    $fechaInicial = date('Y-m-d H:i:s',strtotime($anios[0].'-01-01'));
    $fechaFinal   = date('Y-m-d H:i:s',strtotime($anios[sizeof($anios)-1].'-12-31 23:59:59'));

    $entradas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("YEAR(created_at) as anio"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Ingreso')
    ->groupBy('anio')
    ->get();
    $salidas = Registro::query()
    ->select(\DB::raw("COUNT(*) as countRegistros"), \DB::raw("YEAR(created_at) as anio"))
    ->whereBetween('created_at',[$fechaInicial, $fechaFinal])
    ->where('accion','=','Salida')
    ->groupBy('anio')
    ->get();

    // Llenamos los registros anuaes con 0
    for($i=0;$i<sizeof($anios);$i++){
        $registrosEntrada[$i]=0;
        $registrosSalida[$i]=0;
    }

    for($j=0;$j<sizeof($entradas);$j++){
      for($i=0;$i<sizeof($anios);$i++){
        $anio = $anios[$i];
        if($anio == $entradas[$j]->anio){
          $registrosEntrada[$i]=$entradas[$j]->countRegistros;
          break;
        }
      }
    }

    for($j=0;$j<sizeof($salidas);$j++){
      for($i=0;$i<sizeof($anios);$i++){
        $anio = $anios[$i];
        if($anio == $salidas[$j]->anio){
          $registrosSalida[$i]=$salidas[$j]->countRegistros;
          break;
        }
      }
    }
    $data = array("anios"=>$anios, "entradas"=>$registrosEntrada, "salidas"=>$registrosSalida);
    //dd($data);
    return json_encode($data);
  }


}
