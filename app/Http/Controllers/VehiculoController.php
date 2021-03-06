<?php

namespace BiciRegistro\Http\Controllers;

use BiciRegistro\Marca;
use BiciRegistro\Registro;
use BiciRegistro\Dueno;
use BiciRegistro\Vehiculo;
use BiciRegistro\TipoDueno;
use Illuminate\Http\Request;
use Image;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Datatables;
use \Illuminate\Support\Facades\Auth;
use Freshwork\ChileanBundle\Rut;


class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehiculos = Vehiculo::get();
        return view('vehiculos.index', compact('vehiculos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $marcas = Marca::orderBy('description')->get();
        $tipoDuenos = TipoDueno::orderBy('description')->get();
        return view('vehiculos.create', compact('marcas','tipoDuenos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $existeDueno=false;
      if(!empty($request->input('run_dueno'))){
        $this->validate($request, [
            'run_dueno' => 'required|cl_rut',
            ]);
        $rut = Rut::parse($request->input('run_dueno'))->format(Rut::FORMAT_WITH_DASH);
        $dueno = Dueno::where('rut','=',$rut)
                          ->get()->first();
      }



        if(isset($dueno)){
          $existeDueno=true;
          $this->validate($request, [
              'codigo' => 'required|unique:vehiculos',
              'marca_id' => 'required',
              'color' => 'required|string|max:255',
              'image' => 'required|image|mimes:jpeg,png,jpg',
              'run_dueno' => 'required|cl_rut',
              ]);
        }else{
          // Agregamos la validación del celular si este ingresa parte del celular
          if(!empty($request->input('celular_dueno'))){
            $this->validate($request, [
                'celular_dueno' => 'regex:/[92]{1}[987654321]\d{7}$/|max:9',
                'codigo' => 'required|unique:vehiculos',
                'marca_id' => 'required',
                'color' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg',
                'run_dueno' => 'required|cl_rut',
                'nombre_dueno' => 'required',
                'correo_dueno' => 'required|unique:duenos,correo',
                'image_dueno' => 'required|image|mimes:jpeg,png,jpg',
                'tipoDueno' => 'required',

              ]);
          }else{
            $this->validate($request, [
                'codigo' => 'required|unique:vehiculos',
                'marca_id' => 'required',
                'color' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg',
                'run_dueno' => 'required|cl_rut',
                'nombre_dueno' => 'required',
                'correo_dueno' => 'required|unique:duenos,correo',
                'image_dueno' => 'required|image|mimes:jpeg,png,jpg',
                'tipoDueno' => 'required',
                ]);


          }
          $dueno = new Dueno();
        }

        $vehiculo = new Vehiculo();
        $fechaHora=date("d-m-Y_g:i:s");

        if($request->hasFile('image')){

          $tamaño = getimagesize($request->file('image'));
          $width = intval($tamaño[0]);
          $height = intval($tamaño[1]);
          if($width > 500){
              $widthResize = $width * (500 / $width);
              $heightResize = $height * (500 / $width);
          }else{
            $widthResize = $width;
            $heightResize = $height;
          }
            $extension = $request->file('image')->getClientOriginalExtension();
            $vehiculo->image = 'bicicletas/'.$request->input('codigo').'_'.$fechaHora.'.'.$extension;
            Image::make($request->file('image'))->resize($widthResize,$heightResize)->save(storage_path('app/public/bicicletas/'.$request->input('codigo').'_'.$fechaHora.'.'.$extension));
        }

        if($request->hasFile('image_dueno') && !$existeDueno){

          $tamaño = getimagesize($request->file('image_dueno'));
          $width = intval($tamaño[0]);
          $height = intval($tamaño[1]);
          if($width > 500){
              $widthResize = $width * (500 / $width);
              $heightResize = $height * (500 / $width);
          }else{
            $widthResize = $width;
            $heightResize = $height;
          }
            $extension = $request->file('image_dueno')->getClientOriginalExtension();
            $dueno->image = 'duenos/'.$request->input('run_dueno').'_'.$fechaHora.'.'.$extension;
            Image::make($request->file('image_dueno'))->resize($widthResize,$heightResize)->save(storage_path('app/public/duenos/'.$request->input('run_dueno').'_'.$fechaHora.'.'.$extension));
        }

        $dueno->rut = Rut::parse($request->input('run_dueno'))->format(Rut::FORMAT_WITH_DASH);


        // No existe el dueño
        if(!$existeDueno){
            // Creamos el dueño
            $dueno = Dueno::create([
                'rut' => $dueno->rut,
                'nombre' => $request->input('nombre_dueno'),
                'correo' => $request->input('correo_dueno'),
                'celular' => $request->input('celular_dueno'),
                'tipoDueno_id' => $request->input('tipoDueno'),
                'image' => $dueno->image,
            ]);

        }
        $vehiculo = Vehiculo::create([
            'codigo' => $request->input('codigo'),
            'marca_id' => $request->input('marca_id'),
            'modelo' => $request->input('modelo'),
            'color' => $request->input('color'),
            'dueno_id' => $dueno->id,
            'image' => $vehiculo->image,
        ]);

        Registro::create([
            'vehiculo_id' => $vehiculo->id,
            'usuario_id' => Auth::user()->id,
            'accion' => "Ingreso",
        ]);

        if(!$existeDueno){
            return back()->with('success','Bicicleta guardada correctamente');
        }else{
            return back()->with('info','Bicicleta guardada correctamente. El Dueño ya estaba registrado');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \BiciRegistro\Vehiculo  $vehiculo
     * @return \Illuminate\Http\Response
     */
    public function show(Vehiculo $vehiculo)
    {
        return view('vehiculos.show', compact('vehiculo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BiciRegistro\Vehiculo  $vehiculo
     * @return \Illuminate\Http\Response
     */
    public function edit(Vehiculo $vehiculo)
    {
        $marcas = Marca::orderBy('description')->get();
        return view('vehiculos.edit', compact('vehiculo','marcas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \BiciRegistro\Vehiculo  $vehiculo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        $this->validate($request, [
            'marca_id' => 'required',
            'color' => 'required|string|max:255',
            'image' => 'image|mimes:jpeg,png,jpg|max:20480'
            ]);


        if($request->hasFile('image')){
          $fechaHora=date("d-m-Y_g:i:s");
          $tamaño = getimagesize($request->file('image'));
          $width = intval($tamaño[0]);
          $height = intval($tamaño[1]);
          if($width > 500){
              $widthResize = $width * (500 / $width);
              $heightResize = $height * (500 / $width);
          }else{
            $widthResize = $width;
            $heightResize = $height;
          }
            $extension = $request->file('image')->getClientOriginalExtension();
            $vehiculo->image = 'bicicletas/'.$vehiculo->codigo.'_'.$fechaHora.'.'.$extension;
            Image::make($request->file('image'))->resize($widthResize,$heightResize)->save(storage_path('app/public/bicicletas/'.$vehiculo->codigo.'_'.$fechaHora.'.'.$extension));
        }

        $vehiculo->update([
            'marca_id' => $request->input('marca_id'),
            'modelo' => $request->input('modelo'),
            'color' => $request->input('color'),
            'image' => $vehiculo->image,
        ]);

        return redirect()->route('vehiculos.edit', $vehiculo->id)
        ->with('success','Bicicleta actualizada correctamente');
    }

    /**
     * Desactiva la bicicleta
     *
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
      $vehiculo = Vehiculo::find($request->input('vehiculo_idModalDisable'));
      $vehiculo->activo = false;
      $vehiculo->update();
        //$vehiculo->delete();
        return back()->with('success','Deshabilitado correctamente');
    }

    /**
     * Activa la bicicleta
     *
     * @return \Illuminate\Http\Response
     */
    public function enable(Request $request)
    {
      $vehiculo = Vehiculo::find($request->input('vehiculo_idModalEnable'));
      $vehiculo->activo = true;
      $vehiculo->update();
        //$vehiculo->delete();
        return back()->with('success','Habilitado correctamente');
    }



    /*
    * Muestra la lista en Json (DataTable server side processing https://datatables.net/manual/server-side)
    */
    public function listar(){

      $model = Vehiculo::query()->join('marcas','marca_id', '=', 'marcas.id')
      ->join('duenos','duenos.id', '=', 'vehiculos.dueno_id')
      ->select('vehiculos.id','vehiculos.codigo','vehiculos.modelo','vehiculos.color','marcas.id as marca_id', 'duenos.nombre as dueno', 'vehiculos.image as image', 'vehiculos.activo as activo');

        return datatables()->eloquent($model)
        ->addColumn('marca', function($vehiculo) {
            return $vehiculo->marca->description;
        })
        ->addColumn('imagen', function($vehiculo) {
            return '<img src="'.url('/').Storage::url($vehiculo->image).'" class="img-fluid rounded " style="max-height: 35px" alt="">';
        })

        ->addColumn('accion', function($vehiculo) {
            $botones = '';

            if (Auth::user()->can('vehiculos.show')) {
              $botones .= '<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.show', $vehiculo->id).'">Ver</a>';
            }
            if (Auth::user()->can('vehiculos.edit')) {
              $botones .= '<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.edit', $vehiculo->id).'">Editar</a>';
            }
            if (Auth::user()->can('vehiculos.delete')) {
              if($vehiculo->activo){
                $botones .= '<button type="button" name="button" onclick="btnDeshabiliar(\''.$vehiculo->id.'\',\''.$vehiculo->codigo.'\',\''.$vehiculo->marca->description.'\',\''.$vehiculo->modelo.'\',\''.Storage::url($vehiculo->image).'\')" class="btn btn-danger btn-sm mx-1" data-toggle="modal" data-target="#deshabilitarVehiculoModal">Deshabilitar</button>';
              }else{
                $botones .= '<button type="button" name="button" onclick="btnHabiliar(\''.$vehiculo->id.'\',\''.$vehiculo->codigo.'\',\''.$vehiculo->marca->description.'\',\''.$vehiculo->modelo.'\',\''.Storage::url($vehiculo->image).'\')" class="btn btn-success btn-sm mx-1" data-toggle="modal" data-target="#habilitarVehiculoModal">Habilitar</button>';
              }
            }
            return $botones;
          })
          ->addColumn('showDetalle', function($vehiculo) {
              return '<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.show', $vehiculo->id).'" target="_blank">Detalle bicicleta</a>';
          })
        ->rawColumns(['imagen','accion','showDetalle'])
        ->toJson();

    }

    public function enEstablecimiento(){

      $modelo =  Vehiculo::join('marcas','marca_id', '=', 'marcas.id')
      ->join('duenos','dueno_id', '=', 'duenos.id')
      ->select('vehiculos.id','vehiculos.isInside','vehiculos.codigo','vehiculos.modelo','vehiculos.color',
      'marcas.id as marca_id', 'marcas.description as marca',
      \DB::raw("if(TRUNCATE((TIMESTAMPDIFF(hour , vehiculos.updated_at, now())/24),0) >= 1, concat(TRUNCATE((TIMESTAMPDIFF(hour , vehiculos.updated_at, now())/24),0), if(TRUNCATE((TIMESTAMPDIFF(hour , vehiculos.updated_at, now())/24),0) >= 2, ' días',' día')), if(HOUR(SEC_TO_TIME(TIMESTAMPDIFF(second , vehiculos.updated_at, now()))) > 0, concat(HOUR(SEC_TO_TIME(TIMESTAMPDIFF(second , vehiculos.updated_at, now()))), ' hrs'), concat(MINUTE(SEC_TO_TIME(TIMESTAMPDIFF(second , vehiculos.updated_at, now()))),' min') )) AS tiempo"),
      \DB::raw("TIMESTAMPDIFF(hour , vehiculos.updated_at, now()) AS horas"),
      'duenos.rut as rutDueno','duenos.nombre as dueno')
      ->where('vehiculos.isInside','=','1');
      return datatables()->eloquent($modelo)
      ->addColumn('showDetalle', function($vehiculo) {
          return '<a class="btn btn-light btn-sm mx-1" href="'.route('vehiculos.show', $vehiculo->id).'" target="_blank">ver detalle</a>';
      })
      ->rawColumns(['showDetalle'])
      ->toJson();
    }
}
