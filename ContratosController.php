<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;
use Carbon\Carbon;


class ContratosController extends Controller
{
    //FUNCION PARA INSERTAR CONTRATO DE PACIENTE
    public function insertContrato(Request $request){

        $fechAct = Carbon::now('America/Bogota')->format('Ymd');

        $MPCedu = $request->input('MPCedu'); //Documento paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento paciente
        $MENNIT = $request->input('MENNIT'); //Nit del contrato
        $MTUCod = $request->input('MTUCod'); //Regimen del usuario
        $MTCodP = $request->input('MTCodP'); //Codigo tipo afiliado
        $MPNoCa = 0; //Numero de carnet
        $MPCUOM = '17530101'; //Fecha vigencia carnet
        $MPACMO = 0; //Semanas cotizadas
        $MPOrd = $request->input('MPOrd'); //Prioridad de liquidacion por topes
        $MPResExe = $request->input('MPResExe'); //Responsable exedente?
        $MpFicSIS = $request->input('MpFicSIS'); //Fecha sisben
        $MpPunSIS = $request->input('MpPunSIS'); //Puntaje sisben
        $MPPopla = NULL; //''
        $UltCtvPrx = 0;//Ultimo consecutivo de preexistencia


        try{

            if(!$MENNIT){
                throw new \Exception("El contrato es obligatorio");   
            }
    
            if(!$MTUCod){
                throw new \Exception("El régimen es obligatorio");   
            }
    
            if(!$MTCodP){
                throw new \Exception("El tipo de afiliado es obligatorio");   
            }
    
            if(!$MPOrd){
                throw new \Exception("Campo ORD es obligatorio");   
            }
    
            if(!$MpFicSIS){
                $MpFicSIS = 0;
            }
    
            if(!$MpPunSIS){
                 $MpPunSIS = 0;
            }

            $vigCnc = DB::table('MAECTOS')
                ->select('MENNIT', 'MeCfcha1', 'CtoFchIni', 'MeCnsCnt')
                ->where('MENNIT', $MENNIT)
                ->where('CtoFchIni','<=', $fechAct)
                ->where('MeCfcha1','>=', $fechAct)->first();
            

            $vigencia = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig','<=', $fechAct)
                ->orderByDesc('MEPPVig')->first();
        
            if ($vigencia == null){
                throw new \Exception("No hay vigencia para el contrato " .$MENNIT);
            }


            $validaCon = DB::table('MAEPAC')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('MENNIT', $MENNIT)->get();
            
            if(!$validaCon->isEmpty()){
                throw new \Exception('El contrato ya está asignado al paciente '. $MPCedu);  
            }

            $validaCon = DB::table('MAEPAC')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('MPOrd', $MPOrd)->first();

            if($validaCon != null){
                throw new \Exception('Ya existe un contrato o convenio con ese Orden para el paciente');
            }

            DB::Connection('sqlsrv')

            ->insert("INSERT INTO [MAEPAC] ([MPCedu], [MPTDoc], [MENNIT], [MTUCod], [MTCodP], 
                        [MPNoCa], [MPCUOM], [MPstatus], [MPACMO], [MPOrd], [MPResExe], [MpFicSIS], 
                        [MpPunSIS], [MPPopla], [UltCtvPrx])
                    VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$MENNIT." ' ,  '".$MTUCod."' ,  '".$MTCodP."' ,  
                        '".$MPNoCa."' ,  '".$MPCUOM."' ,  
                        'A' ,  $MPACMO ,  $MPOrd ,  '".$MPResExe."' ,  $MpFicSIS ,  $MpPunSIS ,
                        '".$MPPopla."' , convert(int, $UltCtvPrx))");
            
            $retorno = [
                'status' => 200,
                'message' => 'Contrato asignado correctamente'
            ];
        
        }catch(\Exception $e){

            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];

        }

        return response()->json($retorno);
    }

    //update del contrato
    public function atualizaContratoPte(Request $request){

        $MPCedu = $request->input('MPCedu'); //Documento paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento paciente
        $MENNIT = $request->input('MENNIT'); //Nit del contrato
        $MTUCod = $request->input('MTUCod'); //Codigo del usuario
        $MTCodP = $request->input('MTCodP'); //Codigo tipo afiliado
        $MPstatus = $request->input('MPstatus'); //Estado del paciente

        try{

            if(!$MENNIT){
                throw new \Exception("El contrato es obligatorio");   
            }
    
            if(!$MTUCod){
                throw new \Exception("El régimen es obligatorio");   
            }
    
            if(!$MTCodP){
                throw new \Exception("El tipo de afiliado es obligatorio");   
            }

            DB::table('MAEPAC')
            ->where('MPCedu', $MPCedu)
            ->where('MPTDoc', $MPTDoc)
            ->where('MENNIT', $MENNIT)
            ->update([
                'MTUCod' => $MTUCod,
                'MTCodP' => $MTCodP,
                'MPstatus' => $MPstatus
            ]);

            $retorno = [
                'status' => 200,
                'message' => 'Actualizacion exitosa del contrato'
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);


    }
}
