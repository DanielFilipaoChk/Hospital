<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BD;
use Symfony\Component\Console\Input\Input;

class ManejoDeCamasController extends Controller
{

    //FUNCION QUE GENERA INFORMACIÓN DE LA TABLA GENERAL QUE MUESTRA EN MANEJAO DE CAMAS AL SELECCIONAR UN PABELLON
    public function tablaGeneral (Request $request){

        $MPCodP = $request->input('MPCodP');
        $EMPCOD = $request->input('EMPCOD');
        $MPMCDpto = $request->input('MPMCDpto');
        $MPDisp = $request->input('MPDisp'); //estado de la cama

        if($MPDisp != ''){
            $MPDispVal = "MPDisp";
        }else{
            $MPDispVal = "' '";
            $MPDisp = "' '";
        }

        try{

            if(!$MPCodP || !$EMPCOD || !$MPMCDpto){
                throw new \Exception("Algo salió mal");
            }

            $tablaCamas = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MPCodP], T2.[MPMCDpto], T2.[EMPCOD], T1.[MPDisp], T1.[MPCbAtD],T1.[MpCodCA], T5.[PrNomb] AS nomProceCama,
                            CASE T1.[MPDisp]
                                WHEN 0 THEN 'LIBRE'
                                WHEN 1 THEN 'OCUPADA'
                                WHEN 9 THEN 'DESINFECCION'
                                WHEN 8 THEN 'MANTENIMIENTO'
                                WHEN 10 THEN 'BLOQUEADA'
                            END AS EstadoCama,
                            T1.[MPUced], T1.[MPUDoc], T3.[MPNOMC], T4.TmCtvIng, T4.TFMENi, T1.[MPPrcAcm], T1.[MPNumC], T1.[MPCodC],
                            T1.[MPActCam], T2.[MPCLAPRO], T1.[MPCtvIn], T4.[TFFCES], T4.[MPFEsH]
                        FROM ([MAEPAB1] T1 WITH (NOLOCK)
                            INNER JOIN [MAEPAB] T2 WITH (NOLOCK) ON T2.[MPCodP] = T1.[MPCodP])
                            LEFT JOIN [CAPBAS] T3 WITH (NOLOCK) ON T3.[MPCedu] = T1.[MPUced] AND T3.MPTDoc = T1.[MPUDoc]
                            LEFT JOIN [TMPFAC] T4 WITH (NOLOCK) ON T4.[TFCedu] = T1.[MPUced] AND T4.[TFTDoc] = T1.[MPUDoc] AND T4.[TmCtvIng] = T1.[MPCtvIn]
                            LEFT JOIN [MAEPRO] T5 ON T1.[MpCodCA] = T5.[PRCODI]
                        WHERE (T1.[MPCodP] =  $MPCodP ) AND (T2.[EMPCOD] =  $EMPCOD ) AND (T2.[MPMCDpto] =  '".$MPMCDpto."' ) 
                            AND $MPDispVal = $MPDisp AND (T1.[MPActCam] <> 'S')
                        ORDER BY T1.[MPCodP]");
            $status = 200;

            //Asignar al tablaCamas si esta o no reservada
            foreach($tablaCamas as $cama){
                $reserva = DB::table('MAEPABRES')
                    ->select('MPNCReEst', 'MPNCReFeH', 'MPNumC', 'MPCodP')
                    ->where('MPNumC', $cama->MPNumC)
                    ->where('MPCodP', $cama->MPCodP)
                    ->where('MPNCReEst', 'A')
                    ->first();
                
                if($reserva != null){
                    $cama->reserva = 'S';
                }else{
                    $cama->reserva = 'N';
                }
            }

        }catch(\exception $e){

            $tablaCamas = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($tablaCamas, $status);
    }

    public function tablaGeneral2 (Request $request){

        $MPCodP = $request->input('MPCodP');
        $EMPCOD = $request->input('EMPCOD');
        $MPMCDpto = $request->input('MPMCDpto');
        $MPDisp = $request->input('MPDisp'); //estado de la cama

        if($MPDisp != ''){
            $MPDispVal = "MPDisp";
        }else{
            $MPDispVal = "' '";
            $MPDisp = "' '";
        }

        try{

            if(!$MPCodP || !$EMPCOD || !$MPMCDpto){
                throw new \Exception("Algo salió mal");
            }

            $tablaCamas = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MPCodP], T2.[MPMCDpto], T2.[EMPCOD], T1.[MPDisp], T1.[MPCbAtD],T1.[MpCodCA], T5.[PrNomb] AS nomProceCama,
                            CASE T1.[MPDisp]
                                WHEN 0 THEN 'LIBRE'
                                WHEN 1 THEN 'OCUPADA'
                                WHEN 9 THEN 'DESINFECCION'
                                WHEN 8 THEN 'MANTENIMIENTO'
                                WHEN 10 THEN 'BLOQUEADA'
                            END AS EstadoCama,
                            T1.[MPUced], T1.[MPUDoc], T3.[MPNOMC], T4.TmCtvIng, T4.TFMENi, T1.[MPPrcAcm], T1.[MPNumC], T1.[MPCodC],
                            T1.[MPActCam], T2.[MPCLAPRO], T1.[MPCtvIn], T4.[TFFCES], T4.[MPFEsH]
                        FROM ([MAEPAB1] T1 WITH (NOLOCK)
                            INNER JOIN [MAEPAB] T2 WITH (NOLOCK) ON T2.[MPCodP] = T1.[MPCodP])
                            LEFT JOIN [CAPBAS] T3 WITH (NOLOCK) ON T3.[MPCedu] = T1.[MPUced] AND T3.MPTDoc = T1.[MPUDoc]
                            LEFT JOIN [TMPFAC] T4 WITH (NOLOCK) ON T4.[TFCedu] = T1.[MPUced] AND T4.[TFTDoc] = T1.[MPUDoc] AND T4.[TmCtvIng] = T1.[MPCtvIn]
                            LEFT JOIN [MAEPRO] T5 ON T1.[MpCodCA] = T5.[PRCODI]
                        WHERE (T1.[MPCodP] =  $MPCodP ) AND (T2.[EMPCOD] =  $EMPCOD ) AND (T2.[MPMCDpto] =  '".$MPMCDpto."' ) 
                            AND $MPDispVal = $MPDisp AND (T1.[MPActCam] <> '')
                        ORDER BY T1.[MPCodP]");
            $status = 200;

            //Asignar al tablaCamas si esta o no reservada
            foreach($tablaCamas as $cama){
                $reserva = DB::table('MAEPABRES')
                    ->select('MPNCReEst', 'MPNCReFeH', 'MPNumC', 'MPCodP')
                    ->where('MPNumC', $cama->MPNumC)
                    ->where('MPCodP', $cama->MPCodP)
                    ->where('MPNCReEst', 'A')
                    ->first();
                
                if($reserva != null){
                    $cama->reserva = 'S';
                }else{
                    $cama->reserva = 'N';
                }
            }

        }catch(\exception $e){

            $tablaCamas = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($tablaCamas, $status);
    }

    //ALQUILER DE CAMAS
    public function alquilerCama(Request $request){

        $MPCodP = $request->input('MPCodP');
        $MPNumC = $request->input('MPNumC');
        $MPNCFInAl = $request->input('MPNCFInAl');

        try{

            if(!$MPCodP || !$MPNumC || !$MPNCFInAl){
                throw new \Exception("Algo salió mal");
            }

            $alquiler = DB::Connection('sqlsrv')

            ->select("SELECT [MPNCCtvAl], [MPNCFFiAl], [MPNCFInAl], [MPNumC], [MPCodP]
                    FROM [MAEPABALQ] WITH (NOLOCK)
                    WHERE ([MPCodP] =   $MPCodP  and [MPNumC] =  '".$MPNumC."' ) AND ([MPNCFInAl] <=  '".$MPNCFInAl."' )
                    AND ([MPNCFFiAl] >=  '".$MPNCFInAl."' ) ORDER BY [MPCodP], [MPNumC]");

            $status = 200;

        }catch(\exception $e){

            $alquiler = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($alquiler, $status);
    }

    //RESERVA DE CAMAS
    public function reservaCama(Request $request){

        $MPCodP = $request->input('MPCodP');
        $MPNumC = $request->input('MPNumC');

        try{

            if(!$MPCodP || !$MPNumC ){
                throw new \Exception("Algo salió mal");
            }

            $reserva = DB::Connection('sqlsrv')

            ->select("SELECT MAEPABRES.MPNCReEst, MAEPABRES.MPNCReFeH, MAEPABRES.MPNumC, MAEPABRES.MPCodP,
                             MAEPABRES.MPNCReCtv, CAPBAS.MPNOMC, CAPBAS.MPTDoc, CAPBAS.MPCedu, 
                        CASE MPNCReEst 
                            WHEN 'A' THEN 'ACTIVA'
                            WHEN 'P' THEN 'CON PACIENTE'
                            WHEN 'C' THEN 'CANCELADA'
                        end as EstadoReserva
                    FROM [MAEPABRES] WITH (NOLOCK)
                    LEFT JOIN CAPBAS WITH (NOLOCK) ON CAPBAS.MPCedu = MAEPABRES.MPCedu AND CAPBAS.MPTDoc = MAEPABRES.MPTDoc
                    WHERE ([MPCodP] =  $MPCodP  and [MPNumC] =  '".$MPNumC."' ) 
                    AND ([MPNCReEst] = 'A') ORDER BY [MPCodP], [MPNumC]");

            $status = 200;
        }catch(\exception $e){

            $reserva = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($reserva, $status);
    }

    //FUNCION PARA LEVANTAR EL PACIENTE DE LA CAMA
    public function liberaCama(Request $request){

        $MPCodP = $request->input('MPCodP'); //Codigo del pabellon
        $MPNumC = $request->input('MPNumC'); //Cama del paciente
        $TFCedu = $request->input('TFCedu'); //Documento del paciente
        $TFTDoc = $request->input('TFTDoc'); //Tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //Consecutivo de ingreso
        $MENNIT = $request->input('MENNIT'); //Nit del contrato del paciente
        $IngUsuMoP = $request->input('IngUsuMoP'); //Usuario que registra el movimiento


        try{

            //Valida si la cama esta ocupada 
            $ocupada = DB::table('MAEPAB1')
                ->select('MPDisp')
                ->where('MPNumC', $MPNumC)
                ->where('MPActCam', 'N')->first();
            
            if($ocupada->MPDisp != '1'){

                //Pregunta si la cama esta reservada
                $reserva = DB::table('MAEPABRES')
                    ->select('MPNCReEst', 'MPNCReFeH', 'MPNumC', 'MPCodP')
                    ->where('MPNumC', $MPNumC)
                    ->where('MPNCReEst', 'A')
                    ->first();
                
                if($reserva != null){

                    //Actualizamos el estado a P
                    DB::table('MAEPABRES')
                        ->where('MPNumC', $MPNumC)
                        ->where('MPCodP', $MPCodP)
                        ->where('MPNCReEst', 'A')
                        ->update([
                            'MPNCReEst' => 'P'
                        ]);
                    
                    return response()->json([
                        'status' => 200,
                        'message' => 'Cama liberada correctamente de la reserva'
                    ]);
                    
                }else{
                    throw new \Exception('La cama debe estar ocupada para iniciar proceso de liberación'); 
                }

            }

            //Validacion si tiene salida Clinica
            $salidaClinica = DB::table('INGRESOS')
                ->select( 'ClaPro', 'IngCsc', 'MPTDoc', 'MPCedu', 'IngInSlC', 'IngFecEgr')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng) ->first();
            
            if($salidaClinica == null){
                throw new \Exception('El paciente no tiene ingreso clínico valido');
            }

            if($salidaClinica->IngInSlC == 'N' || $salidaClinica->IngFecEgr == '1753-01-01 00:00:00.000'){
                throw new \Exception('El paciente no cuenta con salida clínica, no se le puede realizar salida administrativa');
            }

            //Consultas de datos para validaciones
            //con518
            $proceCamas = DB::table('MAEPAB1')
                ->join('MAEPAB','MAEPAB.MPCodP','=','MAEPAB1.MPCodP')
                ->select('MAEPAB1.MPNumC', 'MAEPAB1.MPCodP', 'MAEPAB1.MPCodC', 'MAEPAB1.MpCodCA',
                        'MAEPAB1.MPCbAtD', 'MAEPAB1.MPCamLiqE', 'MAEPAB.MPLiqEst')
                ->where('MAEPAB1.MPCodP',$MPCodP)
                ->where('MAEPAB1.MPNumC',$MPNumC)->first();
                
            //con104
            $portafolio = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig','<=',Carbon::now('America/Bogota')->format('Ymd'))
                ->orderBy('MEPPVig', 'desc')->first();

            if($portafolio == null){
                throw new \Exception('Portafolio no tiene vigencia para el contrato '.$MENNIT);
            }

            $validaPortafolio = DB::table('PORTAR1')
                ->select('PTPROEST', 'PRCODI', 'PTCodi', 'PTPorc', 'TrfCod')
                ->where('PTCodi', $portafolio->PTCodi)
                ->where('PRCODI', $proceCamas->MPCodC)
                ->where('PTPROEST', 'S')->first();

            //--------------------------------------------------------------------------
            $validaPortafolio2 = DB::table('PORTAR1')
                ->select('PTPROEST', 'PRCODI', 'PTCodi', 'PTPorc', 'TrfCod')
                ->where('PTCodi', $portafolio->PTCodi)
                ->where('PRCODI', $proceCamas->MpCodCA)
                ->where('PTPROEST', 'S')->first();

            if($validaPortafolio == null ){
                throw new \Exception('No existe portafolio para elprocedimiento '.$proceCamas->MPCodC.' en la cama '.$MPNumC);
            }

            $tarifario = DB::table('HOMPROC')
                ->select('TrfCod', 'PRCODI', 'HomProCnt', 'HomProLH', 'HomProVlr')
                ->where('PRCODI', $proceCamas->MPCodC)
                ->where('TrfCod', $validaPortafolio->TrfCod)->first();
            //---------------------------------------------------------------------------
            $tarifario2 = DB::table('HOMPROC')
                ->select('TrfCod', 'PRCODI', 'HomProCnt', 'HomProLH', 'HomProVlr')
                ->where('PRCODI', $proceCamas->MpCodCA)
                ->where('TrfCod', $validaPortafolio->TrfCod)->first();

            if($tarifario == null){
                throw new \Exception('No existe tarifario para el para el procedimiento '.$proceCamas->MPCodC);
            }

            //Ultimo consecutivo de la cama comparativo con el ultimo movimiento de la cama

            $consMovimiento = DB::table('MAEPAB11')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->orderBy('HisCamCtv', 'desc')->first();
            
            if($consMovimiento != null){

                //Actualizacion del consecutivo

                DB::table('MAEPAB1')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->update([
                        'MpUltCtvo' => $consMovimiento->HisCamCtv
                    ]);
            }
            
            //consulta consecutivo nuevo para el movimiento

            $ultCtvoCama = DB::table('MAEPAB1')
                ->select('MpUltCtvo')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)->first();

            //Levanta paciente de la cama
            DB::table('MAEPAB1')
            ->where('MPCodP', $MPCodP)
            ->where('MPNumC', $MPNumC)
            ->update([
                'MpUltCtvo' => $ultCtvoCama->MpUltCtvo + 1,
                'MPDisp' => 9,
                'MPUced' => '',
                'MPUDoc' => '',
                'MPCtvIn' => 0,
                'MPUdx' => '',
                'MPFchI' => '17530101'
            ]);

            //Update de la temporal TMPFAC
            DB::table('TMPFAC')
            ->where('TFCedu', $TFCedu)
            ->where('TFTDoc', $TFTDoc)
            ->where('TmCtvIng', $TmCtvIng)
            ->update([
                'TFcCodCam' => ' '
            ]);

            //Insert del primer movimiento donde la cama queda L
            DB::table('MAEPAB11')
            ->insert([
                ['MPCodP' => $MPCodP, 'MPNumC' =>  $MPNumC, 'HisCamCtv' => $ultCtvoCama->MpUltCtvo + 1, 'HisCamEdo' => 'L',
                'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                'MPCedu' => $TFCedu, 'MPTDoc' => $TFTDoc, 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => $TmCtvIng]
            ]);

            //Segundo consecutivo para el insert del movimiento
            $ultCtvoCama2 = DB::table('MAEPAB1')
                ->select('MpUltCtvo')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)->first();

            DB::table('MAEPAB1')
            ->where('MPCodP', $MPCodP)
            ->where('MPNumC', $MPNumC)
            ->update([
                'MpUltCtvo' => $ultCtvoCama2->MpUltCtvo + 1
            ]);

            DB::table('MAEPAB11')
            ->insert([
                ['MPCodP' => $MPCodP, 'MPNumC' =>  $MPNumC, 'HisCamCtv' => $ultCtvoCama2->MpUltCtvo + 1, 'HisCamEdo' => 'D',
                'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                'MPCedu' => '', 'MPTDoc' => '', 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => 0]
            ]);

            //Actualizacion del ingreso del paciente
            $ultCtbvoPte = DB::table('INGRESOS')
                ->select('IngCsc', 'MPTDoc', 'MPCedu', 'IngUlcMoP', 'ClaPro')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)->first();

            //Actualizacion de ese ingreso en INGRESOMP
            DB::table('INGRESOMP')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc',$TFTDoc)
                ->where('IngCsc',$TmCtvIng)
                ->where('IngCtvMoP',$ultCtbvoPte->IngUlcMoP)
                ->update([
                    'IngFecMoE' => Carbon::now('America/Bogota')->format('Ymd H:i:s')
                ]);

            DB::table('INGRESOS')
                ->where('mpcedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)
                ->update([
                    'MPCodP' => $MPCodP,
                    'MPNumC' => $MPNumC
                ]);


            $retorno = [
                'status' => 200,
                'message' => 'Paciente levantado de la cama'
            ];
        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //CAMBIO DE ESTADO DE LA CAMA
    public function cambioEstadoCama(Request $request){
        $MPDisp = $request->input('MPDisp'); //estado de la cama al que se va a cambiar
        $MPCodP = $request->input('MPCodP'); //pabellon
        $MPNumC = $request->input('MPNumC'); //cama escogida
        $HisCamUsu = $request->input('HisCamUsu'); //Uusario que registra el cambio de estado

        try{

            $estadoAct = DB::table('MAEPAB1')
                ->select('MPNumC', 'MPCodP', 'MPDisp')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)->first();
            
            if($MPDisp == $estadoAct->MPDisp){
                throw new \Exception('La cama ya se encuentra en este estado');
            }

            if($MPDisp == 0){
                    if($estadoAct->MPDisp == 1 || $estadoAct->MPDisp == 0){
                        throw new \Exception('La cama debe estar en mantenimiento, bloqueada, en desinfección o con acompañante');
                    }
            }else{
                    if($estadoAct->MPDisp == 1){
                        throw new \Exception('La cama no puede estar ocupada');

                    }
            }

           //Estado en el que queda la cama
           switch ($MPDisp) {

            case 0 :
                $HisCamEdo = 'L';
                break;

            case 1 :
                $HisCamEdo = 'O';
                break;

            case 9 :
                $HisCamEdo = 'D';
                break;

            case 8 :
                $HisCamEdo = 'M';
                break;
            
            case 10 :
                $HisCamEdo = 'B';
                break;
           }

            //Ultimo consecutivo de la cama comparativo con el ultimo movimiento de la cama

            $consMovimiento = DB::table('MAEPAB11')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->orderBy('HisCamCtv', 'desc')->first();
            
            if($consMovimiento != null){

                //Actualizacion del consecutivo

                DB::table('MAEPAB1')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->update([
                        'MpUltCtvo' => $consMovimiento->HisCamCtv
                    ]);
            }

           //Ultimo consecutivo de movimiento de la cama
           $ultCtvoCama = DB::table('MAEPAB1')
                    ->select('MpUltCtvo')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)->first();


           //ACTUALIZA EL ESTADO EN LA MAESTRA DE LAS CAMAS
           DB::table('MAEPAB1')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->update([
                    'MpUltCtvo' => $ultCtvoCama->MpUltCtvo + 1,
                    'MPDisp' => $MPDisp,
                    'MPFchI' => '17530101',
                    'MPUced' => '',
                    'MPUDoc' => '',
                    'MPCtvIn' => 0,
                    'MPUdx' => ''
                ]);

            //INSERT DEL MOVIMIENTO
            DB::table('MAEPAB11')
                ->insert([
                    'MPCodP' => $MPCodP,
                    'MPNumC' => $MPNumC,
                    'HisCamCtv' => $ultCtvoCama->MpUltCtvo + 1,
                    'HisCamEdo' => $HisCamEdo,
                    'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd'),
                    'HisCamHor' =>  Carbon::now('America/Bogota')->format('H:i:s'),
                    'MPCedu' => '',
                    'MPTDoc' => '',
                    'HisCamUsu' => $HisCamUsu,
                    'HisCnsIng' => 0
                ]);

            $retorno = [
                'status' => 200,
                'message' => 'Cambio de estado realizado correctamente'
            ];

        }
        catch(\exception $e){

            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //FUNCION PARA TRASLADO DEL PACIENTE
    public function trasladoPaciente (Request $request){
        $IngFecMoP = Carbon::now('America/Bogota')->format('Ymd H:i:s');

        $MPCodP = $request->input('MPCodP'); //Codigo del pabellon al que se va a trasladar el paciente
        $MPNumC = $request->input('MPNumC'); //Cama para donde se va a trasladar el paciente
        $MPCedu = $request->input('MPCedu'); //Cedula del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documeto del paciente
        $MENNIT = $request->input('MENNIT'); //Contrato del paciente
        $IngUsuMoP = $request->input('IngUsuMoP'); //Usuario que registra el movimiento
        $HisCnsIng = $request->input('HisCnsIng'); //Consecutivo de ingreso
        $MPDisp = $request->input('MPDisp'); //Estado de la cama actual


        try{

            if($MPDisp != 1){
                throw new \Exception('La cama debe estar ocupada para realizar el traslado');
            }

            //validacion de reserva de la cama y el paciente
            
            $reservaCama = DB::table('MAEPABRES')
                    ->select('MPNCReCtv', 'MPCodP', 'MPNumC', 'MPNCReEst', 'MPNCReFeH', 'MPTDoc', 'MPCedu')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->where('MPNCReFeH', '<=', Carbon::now('America/Bogota')->format('Ymd H:i:s'))
                    ->where('MPNCReEst', 'A')->first();
    
            $reservaPaciente = DB::table('MAEPABRES')
                    ->select('MPNCReCtv', 'MPTDoc', 'MPCedu', 'MPNCReEst', 'MPNCReFeH', 'MPCodP', 'MPNumC')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->where('MPNCReFeH', '<=',Carbon::now('America/Bogota')->format('Ymd H:i:s'))
                    ->where('MPNCReEst', 'A')->first();

            if($reservaCama != null){
            
                if($reservaCama->MPCedu != $MPCedu){
                    throw new \Exception('La cama '.$MPNumC.' está reservada para el paciente con documento: '.$reservaCama->MPCedu);
                }

                //actualiza estado de la reserva
                    DB::table('MAEPABRES')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->where('MPNCReEst', 'A')
                    ->update([
                        'MPNCReEst' => 'P'
                ]);

            }else{

                if($reservaPaciente != null){
                    if($reservaPaciente->MPNumC != $MPNumC){
                        throw new \Exception('El paciente con documento: '.$MPCedu.' tiene reservada la cama '.$reservaPaciente->MPNumC);
                    }
                }
            }


            //Consultas de datos para validaciones
            //con518
            $proceCamas = DB::table('MAEPAB1')
                ->join('MAEPAB','MAEPAB.MPCodP','=','MAEPAB1.MPCodP')
                ->select('MAEPAB1.MPNumC', 'MAEPAB1.MPCodP', 'MAEPAB1.MPCodC', 'MAEPAB1.MpCodCA',
                        'MAEPAB1.MPCbAtD', 'MAEPAB1.MPCamLiqE', 'MAEPAB.MPLiqEst')
                ->where('MAEPAB1.MPCodP',$MPCodP)
                ->where('MAEPAB1.MPNumC',$MPNumC)->first();

            //con104
            $portafolio = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig','<=',Carbon::now('America/Bogota')->format('Ymd'))
                ->orderBy('MEPPVig', 'desc')->first();

            if($portafolio == null){
                throw new \Exception('Portafolio no tiene vigencia para el contrato '.$MENNIT);
            }

            $validaPortafolio = DB::table('PORTAR1')
                ->select('PTPROEST', 'PRCODI', 'PTCodi', 'PTPorc', 'TrfCod')
                ->where('PTCodi', $portafolio->PTCodi)
                ->where('PRCODI', $proceCamas->MPCodC)
                ->where('PTPROEST', 'S')->first();
            //--------------------------------------------------------------------------
            $validaPortafolio2 = DB::table('PORTAR1')
                ->select('PTPROEST', 'PRCODI', 'PTCodi', 'PTPorc', 'TrfCod')
                ->where('PTCodi', $portafolio->PTCodi)
                ->where('PRCODI', $proceCamas->MpCodCA)
                ->where('PTPROEST', 'S')->first();

            if($validaPortafolio == null ){
                throw new \Exception('No existe portafolio para el procedimiento '.$proceCamas->MPCodC.' en la cama '.$MPNumC);
            }

            $tarifario = DB::table('HOMPROC')
                ->select('TrfCod', 'PRCODI', 'HomProCnt', 'HomProLH', 'HomProVlr')
                ->where('PRCODI', $proceCamas->MPCodC)
                ->where('TrfCod', $validaPortafolio->TrfCod)->first();
            //---------------------------------------------------------------------------
            $tarifario2 = DB::table('HOMPROC')
                ->select('TrfCod', 'PRCODI', 'HomProCnt', 'HomProLH', 'HomProVlr')
                ->where('PRCODI', $proceCamas->MpCodCA)
                ->where('TrfCod', $validaPortafolio->TrfCod)->first();

            if($tarifario == null){
                throw new \Exception('No existe tarifario para el para el procedimiento '.$proceCamas->MPCodC);
            }
            //--------------------------------------------------------------------------------------------------------------------------

            //Cama actual del paciente
            $camaActual = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFcCodCam', 'TFcCodPab' )
                ->where('TFCedu', $MPCedu)
                ->where('TFTDoc', $MPTDoc)
                ->where('TmCtvIng', $HisCnsIng)->first();


            //Levantar al paciente de la cama
            //Ultimo consecutivo de la cama comparativo con el ultimo movimiento de la cama

            $consMovimiento = DB::table('MAEPAB11')
                ->where('MPCodP', $camaActual->TFcCodPab)
                ->where('MPNumC', $camaActual->TFcCodCam)
                ->orderBy('HisCamCtv', 'desc')->first();
            
            if($consMovimiento != null){

                //Actualizacion del consecutivo

                DB::table('MAEPAB1')
                    ->where('MPCodP', $camaActual->TFcCodPab)
                    ->where('MPNumC', $camaActual->TFcCodCam)
                    ->update([
                        'MpUltCtvo' => $consMovimiento->HisCamCtv
                    ]);
            }

            //ultimo consecutivo de la cama donde estaba inicialmente el paciente
            $ultCtvoCama = DB::table('MAEPAB1')
                ->select('MpUltCtvo')
                ->where('MPCodP', $camaActual->TFcCodPab)
                ->where('MPNumC', $camaActual->TFcCodCam)->first();

            //levanta el paciente de la cama
            DB::table('MAEPAB1')
            ->where('MPCodP', $camaActual->TFcCodPab)
            ->where('MPNumC', $camaActual->TFcCodCam)
            ->update([
                'MpUltCtvo' => $ultCtvoCama->MpUltCtvo + 1,
                'MPDisp' => 0,
                'MPUced' => '',
                'MPUDoc' => '',
                'MPCtvIn' => 0,
                'MPUdx' => '',
                'MPFchI' => '17530101'
            ]);

            //registro del movimiento de la cama
            DB::table('MAEPAB11')
                ->insert([
                    ['MPCodP' => $camaActual->TFcCodPab, 'MPNumC' =>  $camaActual->TFcCodCam, 'HisCamCtv' => $ultCtvoCama->MpUltCtvo + 1, 'HisCamEdo' => 'L',
                    'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                    'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                    'MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => $HisCnsIng]
                ]);

            //Para estado en desinfeccion
            DB::table('MAEPAB1')
            ->where('MPCodP', $camaActual->TFcCodPab)
            ->where('MPNumC', $camaActual->TFcCodCam)
            ->update([
                'MPDisp' => 9,
                'MpUltCtvo' => $ultCtvoCama->MpUltCtvo + 2
            ]);

            //registro del movimiento de la cama para D
            DB::table('MAEPAB11')
                ->insert([
                    ['MPCodP' => $camaActual->TFcCodPab, 'MPNumC' =>  $camaActual->TFcCodCam, 'HisCamCtv' => $ultCtvoCama->MpUltCtvo + 2, 'HisCamEdo' => 'D',
                    'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                    'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                    'MPCedu' => '', 'MPTDoc' => '', 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => 0]
                ]);


            //ACOSTAR EL PACIENTE

            $dx = DB::table('INGRESOS')
                ->select('IngEntDx', 'ClaPro', 'IngAtnAct', 'IngUlcMoP')
                ->where('mpcedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $HisCnsIng)->first();

            DB::table('INGRESOS')
                ->where('mpcedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $HisCnsIng)
                ->update([
                    'MPCodP' => $MPCodP,
                    'MPNumC' => $MPNumC
                ]);

            
            //Ultimo consecutivo de la cama comparativo con el ultimo movimiento de la cama

            $consMovimientoA = DB::table('MAEPAB11')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->orderBy('HisCamCtv', 'desc')->first();
            
            if($consMovimientoA != null){
                //Actualizacion del consecutivo

                DB::table('MAEPAB1')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->update([
                        'MpUltCtvo' => $consMovimientoA->HisCamCtv
                    ]);
            }

            //ultimo consecutivo de la cama donde se va a acostar al paciente
            $ultCtvoCamaFn = DB::table('MAEPAB1')
                ->select('MpUltCtvo')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)->first();


            DB::table('MAEPAB1')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->update([
                    'MpUltCtvo' =>$ultCtvoCamaFn->MpUltCtvo + 1,
                    'MPUced' => $MPCedu,
                    'MPUDoc' => $MPTDoc,
                    'MPCtvIn' => $HisCnsIng,
                    'MPDisp' => 1,
                    'MPFchI' => Carbon::now('America/Bogota')->format('Ymd'),
                    'MPUdx' => $dx->IngEntDx
                ]);

            DB::table('MAEPAB11')
                ->insert([
                    ['MPCodP' => $MPCodP, 'MPNumC' =>  $MPNumC, 'HisCamCtv' => $ultCtvoCamaFn->MpUltCtvo + 1, 'HisCamEdo' => 'O',
                    'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                     'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                      'MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => $HisCnsIng]
                ]);



            DB::table('TMPFAC')
                ->where('TFCedu',$MPCedu)
                ->where('TFTDoc',$MPTDoc)
                ->where('TmCtvIng',$HisCnsIng)
                ->update([
                    'TFcCodCam' => $MPNumC , 'TFcCodPab' => $MPCodP ,
                ]);

            $movimiento = DB::table('INGRESOMP')
                ->select('IngCtvMoP', 'MPCedu', 'MPTDoc')
                ->where('MPCedu',$MPCedu)
                ->where('MPTDoc',$MPTDoc)
                ->where('ClaPro',$dx->ClaPro)
                ->orderBy('IngCtvMoP', 'desc')->first();

            $cscMov =  $dx->IngUlcMoP + 1 ;

            DB::table('INGRESOS')
                ->where('mpcedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $HisCnsIng)
                ->update([
                    'IngUlcMoP' => $dx->IngUlcMoP + 1
                ]);

            DB::Connection('sqlsrv')
            ->insert("INSERT INTO [INGRESOMP]
                        ([MPCedu], [MPTDoc], [ClaPro], [IngCsc], [IngCtvMoP], [IngCodPab],
                        [IngCodCam], [IngFecMoP], [IngUsuMoP], [IngUrgObs], [IngFecMoE])
                    VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$dx->ClaPro."' , $HisCnsIng , $cscMov ,
                            $MPCodP,  '".$MPNumC."' ,  '".$IngFecMoP."' ,  '".$IngUsuMoP."' ,  0, convert( DATETIME, '17530101', 112 ))");


            $retorno = [
                'status' => 200,
                'message' => 'Paciente trasladado exitosamente'
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);

    }

    public function datosEstadoCama(){

        try{
            $libre = DB::table('MAEPAB1')
                ->where ('mpdisp',0)
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $ocupada = DB::table('MAEPAB1')
                ->where ('mpdisp',1)
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $desinfeccion = DB::table('MAEPAB1')
                ->where ('mpdisp',9)
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $mantenimeento = DB::table('MAEPAB1')
                ->where ('mpdisp',8)
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $bloqueadas = DB::table('MAEPAB1')
                ->where ('mpdisp',10)
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $general = DB::table('MAEPAB1')
                ->where ('MPActCam', '<>', 'S')
                ->count();

            $retorno = [
                'status' => 200,
                'estados' => [
                    'libres' => $libre,
                    'ocupadas' => $ocupada,
                    'desinfeccion' => $desinfeccion,
                    'mantenimiento' => $mantenimeento,
                    'bloqueadas' => $bloqueadas,
                    'total' => $general,
                ]
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    }

    //Funcion para reservar una cama 
    public function reservarCama(Request $request){

        $MPCodP = $request->input('MPCodP'); //Codigo del pabellon al que se va a trasladar el paciente
        $MPNumC = $request->input('MPNumC'); //Cama para donde se va a trasladar el paciente
        $MPCedu = $request->input('MPCedu'); //Cedula del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documeto del paciente
        $MPNCReUsu = $request->input('MPNCReUsu'); //Usuario que registra la reserva
        $MPNCReObs = $request->input('MPNCReObs'); //Observaciones de la reserva
        $MPNCIngCs = $request->input('MPNCIngCs'); //Consecutivo de ingreso del paciente (puede ingresar en 0)


        try{

            //valida existencia del paciente
            $validaPaciente = DB::table('CAPBAS')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)->first();
            
            if($validaPaciente == null){
                throw new \Exception('No se encuentra el paciente con documento '.$MPCedu);
            }

            //Valida el estado de la cama 
            $validaCama = DB::table('MAEPAB1')
                ->select('MPDisp', 'MPCodP', 'MPNumC')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)->first();
            
            if($validaCama->MPDisp == 1){
                throw new \Exception('La cama '.$MPNumC.' se encuentra ocupada');
            }else if($validaCama->MPDisp != 0){
                throw new \Exception('La cama '.$MPNumC.' debe estar libre para reservar');
            }



            //Valida si la cama tiene una reserva
            $validaR = DB::table('MAEPABRES')
                ->select('MPNCReEst', 'MPNCReFeH', 'MPNumC', 'MPCodP', 'MPCedu', 'MPTDoc', 'MPNCReCtv')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->where('MPNCReEst', 'A')
                ->where('MPNCReFeH', '<=', Carbon::now('America/Bogota')->format('Ymd H:i:s'))->first();
            
            if($validaR != null){
                throw new \Exception('La cama '.$MPNumC.' ya tiene una reserva activa');
            }

            //valida si el paciente tiene una reserva activa
            $validaR2 = DB::table('MAEPABRES')
                ->select('MPNCReEst', 'MPNCReFeH', 'MPCedu', 'MPTDoc', 'MPNCReCtv')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('MPNCReEst', 'A')->first();
            
            if($validaR2 != null){
                throw new \Exception('El paciente con documento '.$MPCedu.' ya tiene una reserva activa para la cama '.$MPNumC);
            }

            //Consecutivo de reservas de la cama
            $consReserva = DB::table('MAEPABRES')
                ->select('MPCodP', 'MPNumC', 'MPNCReCtv')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->orderBy('MPNCReCtv', 'desc')->first();
            
            if($consReserva != null){
                $consReserva = $consReserva->MPNCReCtv + 1;
            }else{  
                $consReserva = 1;
            }

            //Insert de la reserva
            DB::table('MAEPABRES')
                ->insert([
                    [
                        'MPCodP' => $MPCodP ,
                        'MPNumC' => $MPNumC ,
                        'MPNCReCtv' => $consReserva ,
                        'MPCedu' => $MPCedu ,
                        'MPTDoc' => $MPTDoc,
                        'MPNCReFeH' => Carbon::now('America/Bogota')->format('Ymd H:i:s'),
                        'MPNCReUsu' => $MPNCReUsu,
                        'MPNCReEst' => 'A' ,
                        'MPNCIngCs' => $MPNCIngCs,
                        'MPNCAnReU' => '',
                        'MPNCAnReF' => '1753-01-01 00:00:00',
                        'MPNCReObs' => $MPNCReObs,
                        'MPCNSSOLC' => 0,
                    ]
                ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Reserva realizada correctamente'
            ]);


        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }

    }

    //Cancelar la reserva
    public function cancelarReserva(Request $request){

        $MPNCReCtv = $request->input('MPNCReCtv'); //Consecutivo de la reserva
        $MPNCReUsu = $request->input('MPNCReUsu'); //Usuario que registra la reserva
        $MPCodP = $request->input('MPCodP'); //Codigo del pabellon al que se va a trasladar el paciente
        $MPNumC = $request->input('MPNumC'); //Cama para donde se va a trasladar el paciente

        try{

            //Valida si la cama tiene una reserva
            $validaR = DB::table('MAEPABRES')
                ->select('MPNCReEst', 'MPNCReFeH', 'MPNumC', 'MPCodP', 'MPCedu', 'MPTDoc', 'MPNCReCtv')
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->where('MPNCReCtv', $MPNCReCtv)->first();
            
            if($validaR == null){
                throw new \Exception('No se encuentra reserva con consecutivo '.$MPNCReCtv);
            }else if ($validaR->MPNCReEst == 'C'){
                throw new \Exception('La reserva ya se encuentra cancelada');
            }

            //Insert de la reserva
            DB::table('MAEPABRES')
                ->where('MPNCReCtv', $MPNCReCtv)
                ->where('MPCodP', $MPCodP)
                ->where('MPNumC', $MPNumC)
                ->update([
                    'MPNCReEst' => 'C' ,
                    'MPNCAnReU' => $MPNCReUsu,
                    'MPNCAnReF' => Carbon::now('America/Bogota')->format('Ymd H:i:s')
                ]);
            
            return response()->json(['status' => 200, 'message' => 'Reserva cancelada correctamente']);


        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }

    }
}
