<?php

namespace BiciRegistro\Http\Controllers;

use BiciRegistro\Marca;
use BiciRegistro\Dueno;
use BiciRegistro\Vehiculo;
use BiciRegistro\TipoDueno;
use Illuminate\Http\Request;
use Illuminate\Support\Facedes\Storage;

class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $vehiculos = Vehiculo::paginate(10);
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
        $this->validate($request, [
            'codigo' => 'required|unique:vehiculos',
            'marca_id' => 'required',
            'color' => 'required|string|max:255',
            'image' => 'required|image',
            'run_dueno' => 'required',
            'nombre_dueno' => 'required',
            'image_dueno' => 'required|image',
            'celular_dueno' => 'digits_between:0,8',
            'tipoDueno' => 'required',
            ]);

        $vehiculo = new Vehiculo();
        $dueno = new Dueno();

        if($request->hasFile('image')){
            $extension = $request->file('image')->getClientOriginalExtension();
            $vehiculo->image = $request->file('image')->storeAs('public/bicicletas',$request->input('codigo').'.'.$extension);
        }
        if($request->hasFile('image_dueno')){
            $extension = $request->file('image_dueno')->getClientOriginalExtension();
            $dueno->image = $request->file('image_dueno')->storeAs('public/duenos',$request->input('run_dueno').'.'.$extension);
        }
        $dueno_id = null;

        // Verificamos la existencia del dueño
        $duenos = Dueno::get();
        $existeDueno = false;
        foreach($duenos as $duenoActual){
            if($duenoActual->rut == $request->input('run_dueno')){
                $existeDueno = true;
                $dueno_id = $duenoActual->id;
                break;
            }
        }

        // No existe
        if(!$existeDueno){
            // Creamos el dueño
            $dueno = Dueno::create([
                'rut' => $request->input('run_dueno'),
                'nombre' => $request->input('nombre_dueno'),
                'correo' => $request->input('correo_dueno'),
                'celular' => $request->input('celular_dueno'),
                'tipoDueno_id' => $request->input('tipoDueno'),
                'image' => $dueno->image,
            ]);
            // Buscamos su id
            $duenos = Dueno::get();
            foreach($duenos as $duenoActual){
                if($duenoActual->rut == $request->input('run_dueno')){
                    // Lo asignamos
                    $dueno_id = $duenoActual->id;
                    break;
                }
            }
        }
        $vehiculo = Vehiculo::create([
            'codigo' => $request->input('codigo'),
            'marca_id' => $request->input('marca_id'),
            'modelo' => $request->input('modelo'),
            'color' => $request->input('color'),
            'dueno_id' => $dueno_id,
            'image' => $vehiculo->image,
        ]);

        if(!$existeDueno){
            return back()->with('info','Bicicleta guardada correctamente');
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
            'image' => 'image'
            ]);

        if($request->hasFile('image')){
            $extension = $request->file('image')->getClientOriginalExtension();
            $vehiculo->image = $request->file('image')->storeAs('public/bicicletas',$vehiculo->codigo.'.'.$extension);
        }

        $vehiculo->update([
            'marca_id' => $request->input('marca_id'),
            'modelo' => $request->input('modelo'),
            'color' => $request->input('color'),
            'image' => $vehiculo->image,
        ]);

        return redirect()->route('vehiculos.edit', $vehiculo->id)
        ->with('info','Bicicleta actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BiciRegistro\Vehiculo  $vehiculo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vehiculo $vehiculo)
    {
        //$vehiculo->delete();
        return back()->with('info','Deshabilitado correctamente (Terminar este método)');
    }
}
