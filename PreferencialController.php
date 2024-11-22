<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreferencialController extends Controller
{
    //Funcio para insertar una nueva preferencia 
    public function crearPreferencia(Request $request){
        $CLAESTDSC = $request->CLAESTDSC;
        $CLAESTCOL = $request->CLAESTCOL;
        $CLAESTPRI = $request->CLAESTPRI;
        $CLAESTIDE = $request->CLAESTIDE;
        $CLAESTCOD = $request->CLAESTCOD;


        try{

            DB::table('ClaEstPac')->insert([
                'CLAESTCOD' => $CLAESTCOD,
                'CLAESTDSC' => $CLAESTDSC,
                'CLAESTCOL' => $CLAESTCOL,
                'CLAESTPRI' => $CLAESTPRI,
                'CLAESTIDE' => $CLAESTIDE
            ]);

            return response()->json(['message' => 'Preferencia creada correctamente', 'status' => 200]);


        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    //Funcion para traer la informacion de la tabla ClaEstPac
    public function indexPreferencias(Request $request){

        $CLAESTDSC = $request->input('CLAESTDSC');
        $CLAESTPRI = $request->input('CLAESTPRI');
        $CLAESTIDE = $request->input('CLAESTIDE');
        $CLAESTCOD = $request->input('CLAESTCOD');

        
        $preferencias = DB::table('ClaEstPac')
            ->select('CLAESTCOD', 'CLAESTDSC', 'CLAESTCOL', 'CLAESTPRI', 'CLAESTIDE')
            ->when($CLAESTDSC, function ($query, $CLAESTDSC) {
                return $query->where('CLAESTDSC', $CLAESTDSC );
            })
            ->when($CLAESTPRI, function ($query, $CLAESTPRI) {
                return $query->where('CLAESTPRI', 'like', '%' . $CLAESTPRI . '%');
            })
            ->when($CLAESTIDE, function ($query, $CLAESTIDE) {
                return $query->where('CLAESTIDE', 'like', '%' . $CLAESTIDE . '%');
            })
            ->when($CLAESTCOD, function ($query, $CLAESTCOD) {
                return $query->where('CLAESTCOD', 'like', '%' . $CLAESTCOD . '%');
            })
            ->orderBy('CLAESTCOD')
            ->get();
        
        return response()->json(['data' => $preferencias, 'status' => 200]);
        
    }

    //Funcion para actualizar la informacion de la tabla ClaEstPac
    public function actualizarPreferencia(Request $request){

        $CLAESTDSC = $request->CLAESTDSC;
        $CLAESTCOL = $request->CLAESTCOL;
        $CLAESTPRI = $request->CLAESTPRI;
        $CLAESTIDE = $request->CLAESTIDE;
        $CLAESTCOD = $request->CLAESTCOD;

        try{

            DB::table('ClaEstPac')
                ->where('CLAESTCOD', $CLAESTCOD)
                ->update([
                    'CLAESTDSC' => $CLAESTDSC,
                    'CLAESTCOL' => $CLAESTCOL,
                    'CLAESTPRI' => $CLAESTPRI,
                    'CLAESTIDE' => $CLAESTIDE
                ]);

            return response()->json(['message' => 'Preferencia actualizada correctamente', 'status' => 200]);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    //Funcion para eliminar la informacion de la tabla ClaEstPac
    public function eliminarPreferencia(Request $request){

        $CLAESTCOD = $request->CLAESTCOD;

        try{

            DB::table('ClaEstPac')
                ->where('CLAESTCOD', $CLAESTCOD)
                ->delete();

            return response()->json(['message' => 'Preferencia eliminada correctamente', 'status' => 200]);

        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
