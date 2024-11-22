<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use BD;
use Illuminate\Cache\Repository;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\Facades\Redis;
use PhpParser\Node\Stmt\Return_;
use Symfony\Component\Console\Input\Input;

class ProcesoAdmController extends Controller
{
   //FUNCION QUE REVERSA LA SALIDA DEL PACIENTE
    public function reversaSalida(Request $request){
        $MPCedu = $request->input('MPCedu'); //Documento del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento del paciente
        $IngCsc = $request->input('IngCsc'); //Consecutivo de ingreso del paciente
        $FHCIndEsp = $request->input('FHCIndEsp'); //Preguntar (espacialidad enfermeria?) solo para HCCOM1

        try {

            $salidaClinica = DB::table('INGRESOS')
                ->select( 'ClaPro', 'IngCsc', 'MPTDoc', 'MPCedu', 'IngInSlC', 'IngFecEgr')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $IngCsc) ->first();

            if($salidaClinica->IngInSlC == 'N' || $salidaClinica->IngFecEgr == '1753-01-01 00:00:00.000'){
                throw new \Exception('El paciente no cuenta con salida clínica, no se le puede reversar la salida');
            }

            DB::table('INGRESOS')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $IngCsc)
                ->update([
                    'IngInSlC' => 'N',
                    'IngFecEgr' => '1753-01-01'
                ]);
            try{

                DB::table('TMPFAC')
                    ->where('TFCedu', $MPCedu)
                    ->where('TFTDoc', $MPTDoc)
                    ->where('TmCtvIng', $IngCsc)
                    ->update([
                        'MPFEsH' => '1753-01-01',
                        'TFEstS' => 0
                    ]);
                try{

                    DB::table('HCCOM1')
                        ->where('HISCKEY', $MPCedu)
                        ->where('HISTipDoc', $MPTDoc)
                        ->where('HCtvIn1', $IngCsc)
                        ->where('FHCIndEsp', $FHCIndEsp)
                        ->update([
                            'HISFECSAL' => '1753-01-01',
                        ]);
                    $retorno = [
                        'status' => 200,
                        'message' => 'Salida reversada'
                    ];
            }catch(\exception $e){
                $retorno = [
                    'status' => 401,
                    'message' => 'No fue posible reversar la salida en historia clinica'
                ];
            }
            }catch(\exception $e){
                $retorno = [
                    'status' => 401,
                    'message' => 'No fue posible reversar la salida por cargos'
                ];
            }

        }catch(\exception $e){
            $retorno = [
                'status' => 401,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //FUNCION PARA ANULACION DEL INGRESO
    public function anulaIngreso(Request $request){

        //variables que entran para todo el proceso
        $TFCedu = $request->input('TFCedu'); //documento del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //consecutivo de ingreso del paciente
        $ClaPro = $request->input('ClaPro'); //clapro
        $MPCodP = $request->input('MPCodP'); //codigo del pabellon
        $MPNumC = $request->input('MPNumC'); //cama del paciente (puede pasar en blanco y no depende del tipo de atencion)
        $HisCamUsu = $request->input('HisCamUsu'); //usuario registra movimientos
        $IngObsAnu = $request->input('IngObsAnu'); //Observacion del ususario

        try{

            //Validacion de si el ingreso ya se encuentra anulado

            $ingreso = DB::table('INGRESOS')
                ->select('MPCedu', 'MPTDoc', 'IngCsc', 'ClaPro', 'IngFecEgr', 'IngFchAnu')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)
                ->first();
            
            if($ingreso == null){
                throw new \Exception('No se encontro el ingreso');
            }
            
            if($ingreso->IngFchAnu > '1900-01-01'){
                throw new \Exception('El ingreso ya se encuentra anulado, por favor verifique');
                
            }

            //valida si el clapro = 2 y no tiene cama asiganda
            if($ClaPro == 2 AND $MPNumC == ''){
                throw new \Exception("El paciente no tiene cama asignada");
            }

            $proc = DB::table('HCCOM5')
                ->select('HCPrcCod')
                ->where('HISCKEY', $TFCedu)
                ->where('HISTipDoc', $TFTDoc)
                ->where('HCtvIn5', $TmCtvIng)->get();

            //validacion de procedimientos pendientes
            $procedimienntosP = DB::table('TMPFAC1')
                ->select('TFEstaAnu1','TmCtvIng','TmCtvIng','TFTDoc','TFCedu','TFCscP')
                ->where('TFCedu',$TFCedu)
                ->where('TFTDoc',$TFTDoc)
                ->where('TmCtvIng',$TmCtvIng)
                ->where('TFEstaAnu1','<>','S')->get();

            //valida si tiene suministros cargados
            $suministrosP = DB::table('TMPFAC2')
                ->select('TFEstaAnu2','TmCtvIng','TFTDoc','TFCedu','TFCscS')
                ->where('TFCedu',$TFCedu)
                ->where('TFTDoc',$TFTDoc)
                ->where('TmCtvIng',$TmCtvIng)
                ->where('TFEstaAnu2','<>','S')->get();

            //validacion de cirugias en la programacion en estado confirmada, realizada o pendiente
            $cirugiasP = DB::table('PROCIR')
                ->select('ProCtvIn', 'ProEsta', 'MPTDoc', 'MPCedu', 'ProVivo', 'ProMCDpto',
                        'ProEmpCod', 'ProCirCod')
                ->where('ProEsta', '<>', 1)
                ->where('ProEsta', '<>', 3)
                ->where('ProEsta', '<>', 5)
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('ProCtvIn', $TmCtvIng)->get();

            if(!$procedimienntosP->isEmpty() || !$suministrosP->isEmpty() || !$cirugiasP->isEmpty() || !$proc->isEmpty()){
                throw new \Exception('El paciente ' . $TFCedu . ' tiene suministros o procedimientos pendientes en el ingreso '.$TmCtvIng);

            }

            //ELIMINACION DE LAS TABLAS TEMPORALES
            //TMPFAC4
            DB::table('TMPFAC4')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)->delete();

            //TMPFAC1
            DB::table('TMPFAC1')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)->delete();

            //TMPFAC2
            DB::table('TMPFAC2')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)->delete();

            //TMPFAC
            DB::table('TMPFAC')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)->delete();

            //LEVANTAR DE LA CAMA cuando se atencion hospitalaria
            if ($MPNumC != ''){
                //levanta la asignacion en la maestra MEAPAB1
                DB::table('MAEPAB1')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->update([
                        'MPDisp' => 0,
                        'MPFchI' => '1753-01-01',
                        'MPUced' => '',
                        'MPUDoc' => '',
                        'MPCtvIn' => 0,
                        'MPUdx' => ''
                    ]);

                //levanta paciente de TMPFAC
                DB::table('TMPFAC')
                    ->where('TFCedu', $TFCedu)
                    ->where('TFTDoc', $TFTDoc)
                    ->where('TmCtvIng', $TmCtvIng)
                    ->update([
                        'TFcCodCam' => ' '
                    ]);

                //ultimo consecutivo de movimiento de la cama
                $ultCtvo = DB::table('MAEPAB11')
                    ->select('MPCodP', 'MPNumC', 'HisCamCtv')
                    ->where('MPCodP',$MPCodP)
                    ->where('MPNumC',$MPNumC)->max('HisCamCtv');

                $consecutivoCama = json_decode($ultCtvo);

                //actualizacion del ult consecutivo en la MAEPAB1
                DB::table('MAEPAB1')
                    ->where('MPCodP',$MPCodP)
                    ->where('MPNumC',$MPNumC)
                    ->update([
                        'MpUltCtvo' => $consecutivoCama + 1
                    ]);

                //inserta movimiento en MAEPAB11
                DB::table('MAEPAB11')->insert([
                    [
                        'MPCodP' => $MPCodP,
                        'MPNumC' => $MPNumC,
                        'HisCamCtv' => $consecutivoCama + 1,
                        'HisCamEdo' => 'L',
                        'HisCamFec' =>  Carbon::now('America/Bogota')->format('Ymd'),
                        'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s'),
                        'MPCedu' => $TFCedu,
                        'MPTDoc' => $TFTDoc,
                        'HisCamUsu' => $HisCamUsu,
                        'HisCnsIng' => $TmCtvIng
                    ],
                ]);
            }

            //ultlimo consecutivo del movimiento del ingreso
            $ctvoMovimiento = DB::table('INGRESOS')
                ->select('IngCsc', 'MPTDoc', 'MPCedu', 'IngUlcMoP', 'ClaPro')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)->first();


            //actualiza INGRESOMP
            DB::table('INGRESOMP')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)
                ->where('IngCtvMoP', $ctvoMovimiento->IngUlcMoP)
                ->update([
                    'IngFecMoE' => Carbon::now('America/Bogota')->format('Ymd H:i:s')
                ]);

            //actualiza informacion del ingreso
            DB::table('INGRESOS')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)
                ->update([
                    'IngUsuAnu' => $HisCamUsu,
                    'IngFchAnu' => Carbon::now('America/Bogota')->format('Ymd H:i:s'),
                    'IngObsAnu' => $IngObsAnu,
                    'IngFecEgr' => Carbon::now('America/Bogota')->format('Ymd H:i:s')
                ]);

        $retorno = [
            'status' => 200,
            'message' => 'Anulación del ingreso realizada correctamente'
        ];


        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    public function cambioServicio (Request $request){
        $MPCedu = $request->input('MPCedu'); //Documento del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento del paciente
        $IngCsc = $request->input('IngCsc'); //Consecutivo de ingreso del paciente
        $ClaPro = $request->input('ClaPro'); //Tipo de ingreso INICIAL
        $MPCodP = $request->input('MPCodP'); //Pabellon escogido para hacer el traslado del paciente
        $MPNumC = $request->input('MPNumC'); //Cama escogida para hacer el traslado
        $ClaProFn = $request->input('ClaProFn'); //Tipo de ingreso destino del paciente
        $MPSexo = $request->input('MPSexo'); //sexo del paciente
        $IngUsuMoP = $request->input('IngUsuMoP'); //Usuario que realiza el cambio

        $IngUlcMoP = $request->input('IngUlcMoP'); //Ultimo movimiento de ingreso del paciente
        $IngEntDx = $request->input('IngEntDx'); //Diagnostico de entrada
        $IngEntDx2 = $request->input('IngEntDx2'); //Segundo diagnostico
        //$IngDxCli = $request->input('IngDxCli'); //Diagnostico clinico
        $IngHosTTo = $request->input('IngHosTTo'); //Tratamiento
        $IngUrgObs = $request->input('IngUrgObs'); //Ingreso a observacion?
        $TFCoMI = $request->input('TFCoMI'); //Medico tratante
        $TFEsMI = $request->input('TFEsMI'); //Especialidad de medico ingreso
        $TFViaI = $request->input('TFViaI'); //via ingreso
        $SCCCod = $request->input('SCCCod'); //codigo de la sede

        try{

            if ($ClaPro == 2){
                throw new \Exception("No se puede cambiar el servicio para atencion Hospitalaria");
            }

            if ($ClaPro == 5){
                throw new \Exception("No se puede cambiar el servicio para atencion de tipo TRIAGE");
            }

            if (!$ClaProFn){
                throw new \Exception("Ingresar servicio al cual se desea realizar el cambio");
            }

            if($ClaPro == 3 && $ClaProFn == 5){
                throw new \Exception('No puede retornar al servicio de triage, paciente esta en urgencias');
            }

            if (!$MPCodP){
                throw new \Exception("Ingresar pabellón al cual se desea realizar el cambio");
            }
            /* 
            if($ClaPro == $ClaProFn){
                throw new \Exception("El servicio final es igual al servicio inicial del paciente");
                
            } */

            if ($ClaProFn == 2 && $MPNumC == ''){
                throw new \Exception("El paciente no tiene cama para atención hospitalaria");
            }

            //Condicion si se pasa a un pabellon hospitalario y tiene cama para acostar (VALIDACIONN DE LA CAMA ESCOGIDA)
            if($MPNumC != ' ') {


                ///validacion de reserva de la cama y el paciente
            
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

            }

            //Validacion del paciente acostado en otro ingreso
            $pteAcostado = DB::table('TMPFAC')
                ->leftJoin('MAEPAB', 'MAEPAB.MPCodP','=','TMPFAC.TFcCodPab')
                ->select('TMPFAC.TmCtvIng', 'TMPFAC.TFTDoc', 'TMPFAC.TFCedu', 'TMPFAC.TFcCodCam', 'TMPFAC.SccEmp',
                        'TMPFAC.SCCCod', 'MAEPAB.MPCLAPRO', 'TMPFAC.TFcCodPab')
                ->where('TMPFAC.TFCedu',$MPCedu)
                ->where('TMPFAC.TFTDoc', $MPTDoc)
                ->where('TMPFAC.TmCtvIng','<>',$IngCsc)->get();

            $pteAcostadoIng = DB::table('MAEPAB1')
                ->where('MPUCed',$MPCedu)
                ->where('MPUDoc', $MPTDoc)
                ->where('MPCtvIn','<>',$IngCsc)->get();

             if(!$pteAcostadoIng->isEmpty() ){
                throw new \Exception('El paciente con cedula '.$MPCedu.' esta acostado en otro ingreso');
            }


            $ing = DB::table('INGRESOS')
                ->select('IngEntDx', 'ClaPro', 'IngAtnAct', 'IngUlcMoP')
                ->where('mpcedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $IngCsc)->first();

            //ACTUALIZACION EN LA TABLA INGRESOS
            DB::table('INGRESOS')
                ->where('MPCedu',$MPCedu)
                ->where('MPTDoc',$MPTDoc)
                ->where('IngCsc',$IngCsc)
                ->update([
                    'IngAtnAct' => $ClaProFn,
                    'IngUlcMoP' => $ing->IngUlcMoP + 1,
                    'IngEntDx' => $IngEntDx,
                    'IngEntDx2' => $IngEntDx2,
                    'IngHosTTo' => $IngHosTTo
                ]);

            //ACTUALIZACION TABLA INGRESOMP
            DB::table('INGRESOMP')
                ->where('MPCedu',$MPCedu)
                ->where('MPTDoc',$MPTDoc)
                ->where('IngCsc',$IngCsc)
                ->where('IngCtvMoP',$IngUlcMoP)
                ->update([
                    'IngFecMoE' => Carbon::now('America/Bogota')->format('Ymd H:i:s')
                ]);

            $movimiento = DB::table('INGRESOMP')
                ->select('IngCtvMoP', 'MPCedu', 'MPTDoc')
                ->where('MPCedu',$MPCedu)
                ->where('MPTDoc',$MPTDoc)
                //->where('ClaPro',$ClaPro)
                ->where('IngCsc',$IngCsc)
                ->orderBy('IngCtvMoP', 'desc')->first();

            if($movimiento != NULL ){
                $cscMov = $movimiento->IngCtvMoP + 1;
            }else{
                $cscMov = 1;
            }

            //INSERT DEL MOVIMIENTO DEL PACIENTE
            DB::table('INGRESOMP')
                ->insert([
                    ['MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'ClaPro' => $ing->ClaPro, 'IngCsc' => $IngCsc,'IngCtvMoP' => $ing->IngUlcMoP + 1,
                    'IngCodPab' => $MPCodP, 'IngCodCam' => $MPNumC, 'IngFecMoP' => Carbon::now('America/Bogota')->format('Ymd H:i:s'),
                    'IngUsuMoP' => $IngUsuMoP, 'IngUrgObs' => $IngUrgObs, 'IngFecMoE' => '17530101']
                ]);


            //Validacion si el paciente actualmente esta acostado
            $pteAcostadoMIng = DB::table('MAEPAB1')
                ->where('MPUCed',$MPCedu)
                ->where('MPUDoc', $MPTDoc)
                ->where('MPCtvIn',$IngCsc)->get();

            
            //si el paciente estaba acostado se levanta de la cama
            if(!$pteAcostadoMIng->isEmpty()){

                $camaActual = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFcCodCam', 'TFcCodPab' )
                ->where('TFCedu', $MPCedu)
                ->where('TFTDoc', $MPTDoc)
                ->where('TmCtvIng', $IngCsc)->first();


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
                          'MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => $IngCsc]
                    ]);
            }

            //modificacion temporal de facturación
            //si el paciente va a acostarse
            if($MPNumC != ''){

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
                        'MPCtvIn' => $IngCsc,
                        'MPDisp' => 1,
                        'MPFchI' => Carbon::now('America/Bogota')->format('Ymd'),
                        'MPUdx' => $IngEntDx
                    ]);

                DB::table('MAEPAB11')
                    ->insert([
                        ['MPCodP' => $MPCodP, 'MPNumC' =>  $MPNumC, 'HisCamCtv' => $ultCtvoCamaFn->MpUltCtvo + 1, 'HisCamEdo' => 'O',
                        'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                         'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                          'MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'HisCamUsu' => $IngUsuMoP, 'HisCnsIng' => $IngCsc]
                    ]);

                DB::table('INGRESOS')
                    ->where('mpcedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->where('IngCsc', $IngCsc)
                    ->update([
                        'MPCodP' => $MPCodP,
                        'MPNumC' => $MPNumC
                    ]);
            }

            //actualizacion
            DB::table('TMPFAC')
                ->where('TFCedu',$MPCedu)
                ->where('TFTDoc',$MPTDoc)
                ->where('TmCtvIng',$IngCsc)
                ->update([
                    'ClaPro' => $ClaProFn , 'TFcCodCam' => $MPNumC , 'TFcCodPab' => $MPCodP ,
                    'TFCoMI' => $TFCoMI , 'TFDi1I' => $IngEntDx , 'TFDi2I' => $IngEntDx2,
                    'TFEsMI' => $TFEsMI , 'TFViaI' => $TFViaI , 'SCCCod' => $SCCCod ,
                ]);

            $retorno = [
                'status' => 200,
                'message' => 'Cambio de servicio actualizado correctamente'
            ];




        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //FUNCION PARA ACTUALIZAR LA ADMISION DEL PACIENTE
    public function actAdmision(Request $request){

        $MPCedu = $request->input('MPCedu'); //Cedula C
        $MPTDoc = $request->input('MPTDoc'); //Tipo de Documento C
        $IngCsc = $request->input('IngCsc'); //Consecutivo de ingreso del paciente
        $MPNumC = $request->input('MPNumC'); //Cama del paciente
        $MPCodP = $request->input('MPCodP'); //Pabellon del paciente
        $IngNit = $request->input('IngNit'); //Nit del contrato

        $IngEntDx = $request->input('IngEntDx'); //Dx. Entrada C
        $IngEntDx2 = $request->input('IngEntDx2'); //2do Diagnostico de Entrada C

        $IngDerObs = $request->input('IngDerObs'); //Derechos del paciente L
        $TFViaI = $request->input('TFViaI'); //Via de Ingreso N
        $IngCauE = $request->input('IngCauE'); //Causa Externa N
        $TFCoMI = $request->input('TFCoMI'); //M􀀁dico Ingreso C
        $TFEsMI = $request->input('TFEsMI'); //Especialidad M􀀁dico Ingreso N
        $IngCoMt = $request->input('IngCoMt'); //Codigo del medico tratante C
        $IngEsMt = $request->input('IngEsMt'); //Especialidad del medico tratante N
        $IngHosTTo = $request->input('IngHosTTo'); //Hospitalizacion Tratamiento N

        $IngNmResp = $request->input('IngNmResp'); //Primer Nombre del Responsable del Paciente C
        $IngNmResp2 = $request->input('IngNmResp2'); //Segundo Nombre del Responsable del Paciente C
        $IngApRes = $request->input('IngApRes'); //Primer Apellido Responsable C
        $IngApRes2 = $request->input('IngApRes2'); //Segundo Apellido Responsable C
        $IngDocResp = $request->input('IngDocResp'); //Documeto Responsable C
        $IngTDoResp = $request->input('IngTDoResp'); //Tipo de documento Responsable C
        $IngParResp = $request->input('IngParResp'); //Parentesco del Responsable iniciales C conyuge H hijo
        $IngDirResp = $request->input('IngDirResp'); //Direccion del Responsable C
        $IngTelResp = $request->input('IngTelResp'); //Telefonos del Responsable C
        $IngEmTrR = $request->input('IngEmTrR'); //Empresa Trabajo Responsable C
        $IngTeTrR  =  $request->input('IngTeTrR'); //Telefono trabajo de responsable C
        $IngDptRe = $request->input('IngDptRe'); //Departamento residencia de responsable C
        $IngMunRe = $request->input('IngMunRe'); //Municipio de residencia responsable N

        $IngDoAco = $request->input('IngDoAco'); //Documento de Acompa􀀁ante N
        $IngNoAc = $request->input('IngNoAc'); //Nombre de Acompa􀀁ante C
        $IngTeAc = $request->input('IngTeAc'); //Telefono de Acompa􀀁ante C
        $IngTiDoAc = $request->input('IngTiDoAc'); //Tipo de Documento Acompa􀀁ante C
        $IngParAc = $request->input('IngParAc'); //Parentesco de Acompa􀀁ante C

        $TFNMAU = $request->input('TFNMAU'); //AUTORIZACION C
        $TFcNomAut = $request->input('TFcNomAut'); //Nombre de Quien Autoriza C

        try{

            //Validacion del diagnostico
            $infoPac = DB::table('CAPBAS')
                ->select('MPSexo')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)->first();
        
            

            DB::table('INGRESOS')
            ->where('MPCedu', $MPCedu)
            ->where('MPTDoc', $MPTDoc)
            ->where('IngCsc', $IngCsc)
            ->update([
                'IngNit' => $IngNit,
                'IngDerObs' => $IngDerObs,
                'IngCauE' => $IngCauE,
                'IngCoMt' => $IngCoMt,
                'IngEsMt' => $IngEsMt,
                'IngEntDx' => $IngEntDx,
                'IngEntDx2' => $IngEntDx2,
                'IngHosTTo' => $IngHosTTo,
                'IngNmResp' => $IngNmResp, 
                'IngNmResp2' => $IngNmResp2, 
                'IngApRes' => $IngApRes, 
                'IngApRes2' => $IngApRes2, 
                'IngDocResp' => $IngDocResp,  
                'IngTDoResp' => $IngTDoResp, 
                'IngParResp' => $IngParResp, 
                'IngDirResp' => $IngDirResp, 
                'IngTelResp' => $IngTelResp,
                'IngEmTrR' => $IngEmTrR, 
                'IngTeTrR' => $IngTeTrR,  
                'IngDptRe' => $IngDptRe, 
                'IngMunRe' => $IngMunRe,
                'IngDoAco' => $IngDoAco, 
                'IngNoAc' => $IngNoAc, 
                'IngTeAc' => $IngTeAc, 
                'IngTiDoAc' => $IngTiDoAc,
                'IngParAc' => $IngParAc 
            ]);

            DB::table('TMPFAC')
            ->where('TFCedu',$MPCedu)
            ->where('TFTDoc',$MPTDoc)
            ->where('TmCtvIng',$IngCsc)
            ->update([
                'TFMENi' => $IngNit,
                'TFViaI' => $TFViaI,
                'TFCoMI' => $TFCoMI,
                'TFEsMI' => $TFEsMI,
                'TFCoMt' => $IngCoMt,
                'TFEsMt ' => $IngEsMt,
                'TFDi1I' => $IngEntDx,
                'TFDi2I' => $IngEntDx2,
                'TFNoAc' => $IngNoAc,
                'TFDocAco' => $IngDoAco,
                'TFTiDocAc' => $IngTiDoAc,
                'TFParAc' => $IngParAc,
                'TFTeAc' => $IngTeAc,
                'TFDocRep' => $IngDocResp,
                'TFTDoRep' => $IngTDoResp,
                'TFNoRe' => $IngNmResp,
                'TFNoRe2' => $IngNmResp2,
                'TFApeRes' => $IngApRes,
                'TFApeRes2' => $IngApRes2,
                'TFTeRe' => $IngTelResp,
                'TFDirRep' => $IngDirResp,
                'TFEmTrRe' => $IngEmTrR,
                'TFTeTrRe' => $IngTeTrR,
                'TFDptRes' => $IngDptRe,
                'TFMunRes' => $IngMunRe,
                'TFTiRe' => $IngParResp,
                'TFNMAU' => $TFNMAU,
                'TFcNomAut' => $TFcNomAut
            ]);

            //Validacion si el paciente actualmente esta acostado

            $pteAcostadoMIng = DB::table('MAEPAB1')
                ->where('MPUCed',$MPCedu)
                ->where('MPUDoc', $MPTDoc)
                ->where('MPCtvIn',$IngCsc)->get();

            
            //si el paciente estaba acostado se actualiza el dx
            if(!$pteAcostadoMIng->isEmpty()){
                DB::table('MAEPAB1')
                    ->where('MPCodP', $MPCodP)
                    ->where('MPNumC', $MPNumC)
                    ->update([
                        'MPUdx' => $IngEntDx
                    ]);
            }

            $retorno = [
                'status' => 200,
                'message' => 'Admision actualizada correctamente'
            ];


        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno); 

    }

    //Funcion para calcular fecha de poliza con un 364 dias de vigencia
    public function calcularFechaPoliza(Request $request){

        $fecha = Carbon::parse($request->fecha);
        $fecha->addYear(); // Agrega un año
        $fecha->subDay();
        $fecha = $fecha->format('Y-m-d');

        return response()->json([
            'data' => $fecha]);
    }

    //Info accidente
    public function infoAccidente(Request $request){
        
        $MPCedu = $request->input('MPCedu'); //Cedula PACIENTE
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento PACIENTE
        $CtvIng = $request->input('CtvIng'); //Consecutivo de ingreso del paciente

        $accidente = DB::select(DB::raw("SELECT
                                RTRIM(LTRIM(DATSOAT.CtvIng)) AS CtvIng,
                                CAPBAS.MPFchN,
                                RTRIM(LTRIM(DATSOAT.MPCedu)) AS MPCedu,
                                RTRIM(LTRIM(DATSOAT.MPTDoc)) AS MPTDoc,
                                RTRIM(LTRIM(CAPBAS.MPNOMC)) AS MPNOMC,
                                RTRIM(LTRIM(MAEATE.MPCedu)) AS TFCedu,
                                RTRIM(LTRIM(MAEATE.MPTDoc)) AS TFTDoc,
                                RTRIM(LTRIM(MAEATE.MPMeNi)) AS TFMENi,
                                RTRIM(LTRIM(MAEEMP.MENOMB)) AS MENOMB,
                                RTRIM(LTRIM(MAEATE.MaDocDcl)) AS SODocDcl,
                                RTRIM(LTRIM(MAEATE.MaTiDoDc)) AS SOTiDoDc,
                                RTRIM(LTRIM(MAEATE.MaDecla)) AS SODecla,
                                RTRIM(LTRIM(MAEATE.MaLugExp)) AS SOlugExp,
                                RTRIM(LTRIM(MAEATE.FacSCndAcc)) AS SOCndAcc,
                                RTRIM(LTRIM(MAEATE.FacSSitAcc)) AS SOSitAcc,
                                MAEATE.FacSFchAcc AS SOFchAcc,
                                RTRIM(LTRIM(MAEATE.FacSCodD)) AS SOCodD,
                                RTRIM(LTRIM(MAEDMB.MDNomD)) AS DptoAcc,
                                RTRIM(LTRIM(MAEATE.FacSCodM)) AS FacSCodM,
                                RTRIM(LTRIM(MAEDMB1.MDNomM)) AS MpoAcc,
                                RTRIM(LTRIM(DATSOAT.DaCodB)) AS DaCodB,
                                RTRIM(LTRIM(MAEDMB2.MDNomB)) AS BarrAcc,
                                RTRIM(LTRIM(MAEATE.FacSRulUrb)) AS SORulUrb,
                                RTRIM(LTRIM(MAEATE.MaInfAcc)) AS SOInfAcc,
                                RTRIM(LTRIM(DATSOAT.DaIntAut)) AS DaIntAut,
                                RTRIM(LTRIM(MAEATE.FacSIndAsg)) AS SOIndAsg,
                                RTRIM(LTRIM(MAEATE.FacSVeMa)) AS SOVeMa,
                                RTRIM(LTRIM(MAEATE.FacSVePl)) AS SOVePl,
                                RTRIM(LTRIM(MAEATE.FacSVeTi)) AS SOVeTi,
                                RTRIM(LTRIM(MAEATE.FacSNitAsg)) AS SONitAsg,
                                RTRIM(LTRIM(EMPRESS.EmpDsc)) AS EmpDsc,
                                RTRIM(LTRIM(MAEATE.FacSNomSuc)) AS SONomSuc,
                                RTRIM(LTRIM(DATSOAT.DaNumPol)) AS DaNumPol,
                                DATSOAT.DaFecIniV,
                                DATSOAT.DaFecFinV
                                RTRIM(LTRIM(DATSOAT.DaTipSer)) AS DaTipSer,
                                CASE DaTipSer 
                                    WHEN 'PA' THEN 'PARTICULAR'
                                    WHEN 'PU' THEN 'PUBLICO'
                                    WHEN 'OF' THEN 'OFICIAL'
                                    WHEN 'EM' THEN 'EMERGENCIA'
                                    WHEN 'DI' THEN 'TRANSPORTE MA'
                                    WHEN 'ES' THEN 'ESCOLAR'
                                    WHEN 'NA' THEN 'NO APLICA'
                                    ELSE 'NO APLICA' END AS tipoServicio,
                                RTRIM(LTRIM(DATSOAT.DaCobExc)) AS DaCobExc,
                                RTRIM(LTRIM(DATSOAT.DaRadGlo)) AS DaRadGlo,
                                CASE DaRadGlo
                                    WHEN '0' THEN 'GLOSA TOTAL'
                                    WHEN '1' THEN 'RESPUESTA O PAGO'
                                    ELSE 'NO APLICA' END AS radicacionGlosa,
                                RTRIM(LTRIM(DATSOAT.DaNroTFol)) AS DaNroTFol,
                                RTRIM(LTRIM(DATSOAT.DaNRdGlA)) AS DaNRdGlA,
                                RTRIM(LTRIM(DATSOAT.DaPrApPr)) AS DaPrApPr,
                                RTRIM(LTRIM(DATSOAT.DaSeApPr)) AS DaSeApPr,
                                RTRIM(LTRIM(DATSOAT.DaPrNoPr)) AS DaPrNoPr,
                                RTRIM(LTRIM(DATSOAT.DaSeNoPr)) AS DaSeNoPr,
                                RTRIM(LTRIM(DATSOAT.DaTiDoPr)) AS DaTiDoPr,
                                RTRIM(LTRIM(DATSOAT.DaNuDoPr)) AS DaNuDoPr,
                                RTRIM(LTRIM(DATSOAT.DaLugExp)) AS DaLugExp,
                                RTRIM(LTRIM(DATSOAT.DaDirPr)) AS DaDirPr,
                                RTRIM(LTRIM(DATSOAT.DaCodDPr)) AS DaCodDPr,
                                RTRIM(LTRIM(MAEDMBPR.MDNomD)) AS DptoPr,
                                RTRIM(LTRIM(DATSOAT.DaCodMPr)) AS DaCodMPr,
                                RTRIM(LTRIM(MAEDMB1PR.MDNomM)) AS MpoPr,
                                RTRIM(LTRIM(DATSOAT.DaTelPr)) AS DaTelPr,
                                RTRIM(LTRIM(DATSOAT.DaPrApCo)) AS DaPrApCo,
                                RTRIM(LTRIM(DATSOAT.DaSeApCo)) AS DaSeApCo,
                                RTRIM(LTRIM(DATSOAT.DaPrNoCo)) AS DaPrNoCo,
                                RTRIM(LTRIM(DATSOAT.DaSeNoCo)) AS DaSeNoCo,
                                RTRIM(LTRIM(MAEATE.FacSTpICnd)) AS SOTpICnd,
                                RTRIM(LTRIM(MAEATE.FacSCedCnd)) AS SOCedCnd,
                                RTRIM(LTRIM(MAEATE.MaLuExCnd)) AS SoLgExpCn,
                                RTRIM(LTRIM(MAEATE.MaDirCnd)) AS SODirCnd,
                                RTRIM(LTRIM(MAEATE.FacSCodDCn)) AS SoCodDCnd,
                                RTRIM(LTRIM(MAEDMBCN.MDNomD)) AS DptoCnd,
                                RTRIM(LTRIM(MAEATE.FacSCodMCn)) AS SOCodMCnd,
                                RTRIM(LTRIM(MAEDMB1CN.MDNomM)) AS MpoCdn,
                                RTRIM(LTRIM(MAEATE.MaTelCnd)) AS SoTelCnd
                            FROM DATSOAT
                            LEFT JOIN MAEATE ON DATSOAT.MPCedu = MAEATE.MPCedu and DATSOAT.MPTDoc = MAEATE.MPTDoc 
                                and DATSOAT.CtvIng = MAEATE.MaCtvIng
                            LEFT JOIN MAEEMP ON MAEEMP.MENNIT = MAEATE.MPMeNi
                            LEFT JOIN MAEDMB on MAEDMB.MDCodD = MAEATE.FacSCodD
                            LEFT JOIN MAEDMB1 ON MAEDMB1.MDCodD = MAEATE.FacSCodD and MAEDMB1.MDCodM = MAEATE.FacSCodM
                            LEFT JOIN MAEDMB2 ON MAEDMB2.MDCodD = MAEATE.FacSCodD  and MAEDMB2.MDCodM = MAEATE.FacSCodM 
                                and MAEDMB2.MDCodB = DATSOAT.DaCodB 
                            LEFT JOIN EMPRESS ON EMPRESS.MEcntr = MAEATE.FacSNitAsg
                            LEFT JOIN MAEDMB AS MAEDMBPR  on MAEDMBPR.MDCodD = DATSOAT.DaCodDPr
                            LEFT JOIN MAEDMB1 AS MAEDMB1PR ON MAEDMB1PR.MDCodD = DATSOAT.DaCodDPr and MAEDMB1PR.MDCodM = DATSOAT.DaCodMPr
                            LEFT JOIN MAEDMB AS MAEDMBCN  on MAEDMBCN.MDCodD = MAEATE.FacSCodDCn
                            LEFT JOIN MAEDMB1 AS MAEDMB1CN ON MAEDMB1CN.MDCodD = MAEATE.FacSCodDCn and MAEDMB1CN.MDCodM = MAEATE.FacSCodMCn
                            LEFT JOIN CAPBAS ON CAPBAS.MPCedu = DATSOAT.MPCedu and CAPBAS.MPTDoc = DATSOAT.MPTDoc
                            where DATSOAT.MPCedu = '".$MPCedu."' and DATSOAT.MPTDoc = '".$MPTDoc."' AND DATSOAT.CtvIng = $CtvIng"));
            
        
        return response()->json([
            'status' => 200,
            'data' => $accidente
        ]);
    }

    //Funcion para imprimir FURIPS
    public function docFurips(Request $request){

        $MPCedu = $request->input('MPCedu'); //Cedula PACIENTE
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento PACIENTE
        $CtvIng = $request->input('CtvIng'); //Consecutivo de ingreso del paciente
        $EMPCOD = $request->input('EMPCOD'); //Codigo de la empresa

        $infoEmpresa = DB::table('EMPRESA')
            ->select('EMPCOD', 'EmpRazSoc', 'EmpNit', 'EmpCdIPS')
            ->where('EMPCOD', $EMPCOD)->first();

        $furips = DB::connection('sqlsrv')
            ->select("SELECT  
                DATSOAT.CtvIng, DATSOAT.MPCedu, DATSOAT.MPTDoc, CAPBAS.MPNOMC, CAPBAS.MPNom1, CAPBAS.MPNom2, CAPBAS.MPApe1, CAPBAS.MPApe2, 
                TMPFAC.TFCedu, TMPFAC.TFTDoc, CAPBAS.MPFchN, CAPBAS.MPSexo, CAPBAS.MPDire, CAPBAS.MDCodD dptoAccidentado, MAEDMBRA.MDNomD nomDptoActado,
                CAPBAS.MDCodM mpioAccidentado, MAEDMB1RA.MDNomM nomMpoActado, CAPBAS.MPTele,
                TMPFAC.TFMENi, MAEEMP.MENOMB,
                TMPFAC.SODocDcl, TMPFAC.SOTiDoDc, TMPFAC.SODecla, TMPFAC.SOlugExp,
                TMPFAC.SOCndAcc, TMPFAC.SOSitAcc,TMPFAC.SOFchAcc,TMPFAC.SOCodD, MAEDMB.MDNomD nomDptoActe, TMPFAC.SOCodM,
                MAEDMB1.MDNomM nomMpoActe, DATSOAT.DaCodB, MAEDMB2.MDNomB nomBarActe, TMPFAC.SORulUrb, TMPFAC.SOInfAcc, DATSOAT.DaIntAut,
                TMPFAC.SOIndAsg, TMPFAC.SOVeMa, TMPFAC.SOVePl, TMPFAC.SOVeTi, TMPFAC.SONitAsg, EMPRESS.EmpDsc,
                TMPFAC.SONomSuc, DATSOAT.DaNumPol, DATSOAT.DaFecIniV, DATSOAT.DaFecFinV, DATSOAT.DaTipSer, DATSOAT.DATNUMSIR,
                    CASE DaTipSer 
                        WHEN 'PA' THEN 'PARTICULAR'
                        WHEN 'PU' THEN 'PUBLICO'
                        WHEN 'OF' THEN 'OFICIAL'
                        WHEN 'EM' THEN 'EMERGENCIA'
                        WHEN 'DI' THEN 'TRANSPORTE MA'
                        WHEN 'ES' THEN 'ESCOLAR'
                        WHEN 'NA' THEN 'NO APLICA'
                        ELSE 'NO APLICA' END AS tipoServicio,
                DATSOAT.DaCobExc,  DATSOAT.DaRadGlo, 
                    CASE DaRadGlo
                        WHEN '0' THEN 'GLOSA TOTAL'
                        WHEN '1' THEN 'RESPUESTA O PAGO'
                        ELSE 'NO APLICA' END AS radicacionGlosa,
                DATSOAT.DaNroTFol, DATSOAT.DaNRdGlA,
                DATSOAT.DaPrApPr, DATSOAT.DaSeApPr, DATSOAT.DaPrNoPr, DATSOAT.DaSeNoPr,
                DATSOAT.DaTiDoPr, DATSOAT.DaNuDoPr,DATSOAT.DaLugExp, DATSOAT.DaDirPr,
                DATSOAT.DaCodDPr,MAEDMBPR.MDNomD nomDptoPr, DATSOAT.DaCodMPr, MAEDMB1PR.MDNomM nomMpoPr, DATSOAT.DaTelPr,
                DATSOAT.DaPrApCo, DATSOAT.DaSeApCo, DATSOAT.DaPrNoCo, DATSOAT.DaSeNoCo,
                TMPFAC.SOTpICnd, TMPFAC.SOCedCnd, TMPFAC.SoLgExpCn, TMPFAC.SODirCnd,
                TMPFAC.SoCodDCnd, MAEDMBCN.MDNomD nomDptoCn, TMPFAC.SOCodMCnd, MAEDMB1CN.MDNomM nomMpoCn, TMPFAC.SoTelCnd,
                DatAmb.DatPlaAmb, DatAmb.DatDirIni, DatAmb.DatDirFin, DatAmb.DatTipAmb, DatAmb.DatUR,
                INGRESOS.IngFecAdm, INGRESOS.IngFecEgr, INGRESOS.IngDxCli, INGRESOS.IngEntDx, INGRESOS.IngEntDx2,  
                INGRESOS.IngSalDx, INGRESOS.IngDxSal1, INGRESOS.IngDxSal2,
                REFCREF.RefTip, REFCREF.RefFch, REFCREF.RefIPSRef, MAEIPS.MINomI, MAEIPS.IPSNroNIT, REFCREF.RefNomEnt,
                REFCREF.REFFCHACR, REFCREF.RefIPSRcp, IPSREC.MINomI ipsReceptora, IPSREC.IPSNroNIT inscripReceptora,
                REFCREF.REFMEDREC, MAEMED1.MMNomM, MAEMED1.MMTpoServ, MaeTpP.mtpdsc,
                MEDTRA.MApe1 ape1Medtra, MEDTRA.MApe2 ape2Medtra, MEDTRA.MNom1 nom1Medtra, MEDTRA.MNom2 nom2Medtra,
                MEDTRA.MTipDoc, MEDTRA.MMCedM, MEDTRA.MMRegM
                from DATSOAT
                LEFT JOIN TMPFAC ON DATSOAT.MPCedu = TMPFAC.TFCedu and DATSOAT.MPTDoc = TMPFAC.TFTDoc 
                    and DATSOAT.CtvIng = TMPFAC.TmCtvIng
                LEFT JOIN MAEEMP ON MAEEMP.MENNIT = TMPFAC.TFMENI
                LEFT JOIN MAEDMB on MAEDMB.MDCodD = TMPFAC.SOCodD
                LEFT JOIN MAEDMB1 ON MAEDMB1.MDCodD = TMPFAC.SOCodD and MAEDMB1.MDCodM = TMPFAC.SOCodM
                LEFT JOIN MAEDMB2 ON MAEDMB2.MDCodD = TMPFAC.SOCodD and MAEDMB2.MDCodM = TMPFAC.SOCodM 
                    and MAEDMB2.MDCodB = DATSOAT.DaCodB 
                LEFT JOIN EMPRESS ON EMPRESS.MEcntr = TMPFAC.SONitAsg
                LEFT JOIN MAEDMB AS MAEDMBPR  on MAEDMBPR.MDCodD = DATSOAT.DaCodDPr
                LEFT JOIN MAEDMB1 AS MAEDMB1PR ON MAEDMB1PR.MDCodD = DATSOAT.DaCodDPr and MAEDMB1PR.MDCodM = DATSOAT.DaCodMPr
                LEFT JOIN MAEDMB AS MAEDMBCN  on MAEDMBCN.MDCodD = TMPFAC.SoCodDCnd
                LEFT JOIN MAEDMB1 AS MAEDMB1CN ON MAEDMB1CN.MDCodD = TMPFAC.SoCodDCnd and MAEDMB1CN.MDCodM = TMPFAC.SOCodMCnd
                LEFT JOIN CAPBAS ON CAPBAS.MPCedu = TMPFAC.TFCedu and CAPBAS.MPTDoc = TMPFAC.TFTDoc
                LEFT JOIN MAEDMB AS MAEDMBRA ON MAEDMBRA.MDCodD = CAPBAS.MDCodD
                LEFT JOIN  MAEDMB1 AS MAEDMB1RA ON MAEDMB1RA.MDCodD = CAPBAS.MDCodD and MAEDMB1RA.MDCodM = CAPBAS.MDCodM
                LEFT JOIN DatAmb ON DatAmb.MPCedu = TMPFAC.TFCedu AND DatAmb.MPTDoc = TMPFAC.TFTDoc AND DatAmb.DatCscIng = TMPFAC.TmCtvIng
                INNER JOIN INGRESOS ON INGRESOS.MPCedu = TMPFAC.TFCedu AND INGRESOS.MPTDoc = TMPFAC.TFTDoc and INGRESOS.IngCsc = TMPFAC.TmCtvIng
                LEFT JOIN REFCREF ON REFCREF.MPcedu = TMPFAC.TFCedu and REFCREF.MPTDoc = TMPFAC.TFTDoc and REFCREF.RefCscIng = TMPFAC.TmCtvIng
                LEFT JOIN MAEIPS ON MAEIPS.MICodI = REFCREF.RefIPSRef
                LEFT JOIN MAEIPS AS IPSREC ON IPSREC.MICodI = REFCREF.RefIPSRcp
                LEFT JOIN MAEMED1 ON MAEMED1.MMCODM = REFCREF.REFMEDREC
                LEFT JOIN MaeTpP ON MaeTpP.mtpcod = MAEMED1.MMTpoServ
                LEFT JOIN MAEMED1 AS MEDTRA ON MEDTRA.MMCODM = INGRESOS.IngCoMt 
                where DATSOAT.MPCedu = '".$MPCedu."' and DATSOAT.MPTDoc = '".$MPTDoc."' AND DATSOAT.CtvIng = $CtvIng");
        
        return response()->json([
            'status' => 200,
            'empresa' => $infoEmpresa,
            'data' => $furips
        ]);
    }

    //Funcion para ver accidente en admision
    public function infoAccidenteAdm(Request $request){
        
        $MPCedu = $request->input('MPCedu'); //Cedula PACIENTE
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento PACIENTE
        $CtvIng = $request->input('CtvIng'); //Consecutivo de ingreso del paciente

        $accidente = DB::connection('sqlsrv')
                    ->select(DB::raw("SELECT
                    RTRIM(LTRIM(DATSOAT.CtvIng)) AS CtvIng,
                    CAPBAS.MPFchN,
                    RTRIM(LTRIM(DATSOAT.MPCedu)) AS MPCedu,
                    RTRIM(LTRIM(DATSOAT.MPTDoc)) AS MPTDoc,
                    RTRIM(LTRIM(CAPBAS.MPNOMC)) AS MPNOMC,
                    RTRIM(LTRIM(TMPFAC.TFCedu)) AS TFCedu,
                    RTRIM(LTRIM(TMPFAC.TFTDoc)) AS TFTDoc,
                    RTRIM(LTRIM(TMPFAC.TFMENi)) AS TFMENi,
                    RTRIM(LTRIM(MAEEMP.MENOMB)) AS MENOMB,
                    RTRIM(LTRIM(TMPFAC.SODocDcl)) AS SODocDcl,
                    RTRIM(LTRIM(TMPFAC.SOTiDoDc)) AS SOTiDoDc,
                    RTRIM(LTRIM(TMPFAC.SODecla)) AS SODecla,
                    RTRIM(LTRIM(TMPFAC.SOlugExp)) AS SOlugExp,
                    RTRIM(LTRIM(TMPFAC.SOCndAcc)) AS SOCndAcc,
                    RTRIM(LTRIM(TMPFAC.SOSitAcc)) AS SOSitAcc,
                    TMPFAC.SOFchAcc AS SOFchAcc,
                    RTRIM(LTRIM(TMPFAC.SOCodD)) AS SOCodD,
                    RTRIM(LTRIM(MAEDMB.MDNomD)) AS DptoAcc,
                    RTRIM(LTRIM(TMPFAC.SOCodM)) AS SOCodM,
                    RTRIM(LTRIM(MAEDMB1.MDNomM)) AS MpoAcc,
                    RTRIM(LTRIM(DATSOAT.DaCodB)) AS DaCodB,
                    RTRIM(LTRIM(MAEDMB2.MDNomB)) AS BarrAcc,
                    RTRIM(LTRIM(TMPFAC.SORulUrb)) AS SORulUrb,
                    RTRIM(LTRIM(TMPFAC.SOInfAcc)) AS SOInfAcc,
                    RTRIM(LTRIM(DATSOAT.DaIntAut)) AS DaIntAut,
                    RTRIM(LTRIM(TMPFAC.SOIndAsg)) AS SOIndAsg,
                    RTRIM(LTRIM(TMPFAC.SOVeMa)) AS SOVeMa,
                    RTRIM(LTRIM(TMPFAC.SOVePl)) AS SOVePl,
                    RTRIM(LTRIM(TMPFAC.SOVeTi)) AS SOVeTi,
                    RTRIM(LTRIM(TMPFAC.SONitAsg)) AS SONitAsg,
                    RTRIM(LTRIM(EMPRESS.EmpDsc)) AS EmpDsc,
                    RTRIM(LTRIM(TMPFAC.SONomSuc)) AS SONomSuc,
                    RTRIM(LTRIM(DATSOAT.DaNumPol)) AS DaNumPol,
                    DATSOAT.DaFecIniV,
                    DATSOAT.DaFecFinV,
                    RTRIM(LTRIM(DATSOAT.DaTipSer)) AS DaTipSer,
                        CASE DaTipSer 
                            WHEN 'PA' THEN 'PARTICULAR'
                            WHEN 'PU' THEN 'PUBLICO'
                            WHEN 'OF' THEN 'OFICIAL'
                            WHEN 'EM' THEN 'EMERGENCIA'
                            WHEN 'DI' THEN 'TRANSPORTE MA'
                            WHEN 'ES' THEN 'ESCOLAR'
                            WHEN 'NA' THEN 'NO APLICA'
                            ELSE 'NO APLICA' END AS tipoServicio,
                    RTRIM(LTRIM(DATSOAT.DaCobExc)) AS DaCobExc,
                    RTRIM(LTRIM(DATSOAT.DaRadGlo)) AS DaRadGlo,
                        CASE DaRadGlo
                            WHEN '0' THEN 'GLOSA TOTAL'
                            WHEN '1' THEN 'RESPUESTA O PAGO'
                            ELSE 'NO APLICA' END AS radicacionGlosa,
                    RTRIM(LTRIM(DATSOAT.DaNroTFol)) AS DaNroTFol,
                    RTRIM(LTRIM(DATSOAT.DaNRdGlA)) AS DaNRdGlA,
                    RTRIM(LTRIM(DATSOAT.DaPrApPr)) AS DaPrApPr,
                    RTRIM(LTRIM(DATSOAT.DaSeApPr)) AS DaSeApPr,
                    RTRIM(LTRIM(DATSOAT.DaPrNoPr)) AS DaPrNoPr,
                    RTRIM(LTRIM(DATSOAT.DaSeNoPr)) AS DaSeNoPr,
                    RTRIM(LTRIM(DATSOAT.DaTiDoPr)) AS DaTiDoPr,
                    RTRIM(LTRIM(DATSOAT.DaNuDoPr)) AS DaNuDoPr,
                    RTRIM(LTRIM(DATSOAT.DaLugExp)) AS DaLugExp,
                    RTRIM(LTRIM(DATSOAT.DaDirPr)) AS DaDirPr,
                    RTRIM(LTRIM(DATSOAT.DaCodDPr)) AS DaCodDPr,
                    RTRIM(LTRIM(MAEDMBPR.MDNomD)) AS DptoPr,
                    RTRIM(LTRIM(DATSOAT.DaCodMPr)) AS DaCodMPr,
                    RTRIM(LTRIM(MAEDMB1PR.MDNomM)) AS MpoPr,
                    RTRIM(LTRIM(DATSOAT.DaTelPr)) AS DaTelPr,
                    RTRIM(LTRIM(DATSOAT.DaPrApCo)) AS DaPrApCo,
                    RTRIM(LTRIM(DATSOAT.DaSeApCo)) AS DaSeApCo,
                    RTRIM(LTRIM(DATSOAT.DaPrNoCo)) AS DaPrNoCo,
                    RTRIM(LTRIM(DATSOAT.DaSeNoCo)) AS DaSeNoCo,
                    RTRIM(LTRIM(TMPFAC.SOTpICnd)) AS SOTpICnd,
                    RTRIM(LTRIM(TMPFAC.SOCedCnd)) AS SOCedCnd,
                    RTRIM(LTRIM(TMPFAC.SoLgExpCn)) AS SoLgExpCn,
                    RTRIM(LTRIM(TMPFAC.SODirCnd)) AS SODirCnd,
                    RTRIM(LTRIM(TMPFAC.SoCodDCnd)) AS SoCodDCnd,
                    RTRIM(LTRIM(MAEDMBCN.MDNomD)) AS DptoCnd,
                    RTRIM(LTRIM(TMPFAC.SOCodMCnd)) AS SOCodMCnd,
                    RTRIM(LTRIM(MAEDMB1CN.MDNomM)) AS MpoCnd,
                    RTRIM(LTRIM(TMPFAC.SoTelCnd)) AS SoTelCnd
                FROM DATSOAT
                LEFT JOIN TMPFAC ON DATSOAT.MPCedu = TMPFAC.TFCedu and DATSOAT.MPTDoc = TMPFAC.TFTDoc 
                    and DATSOAT.CtvIng = TMPFAC.TmCtvIng
                LEFT JOIN MAEEMP ON MAEEMP.MENNIT = TMPFAC.TFMENI
                LEFT JOIN MAEDMB on MAEDMB.MDCodD = TMPFAC.SOCodD
                LEFT JOIN MAEDMB1 ON MAEDMB1.MDCodD = TMPFAC.SOCodD and MAEDMB1.MDCodM = TMPFAC.SOCodM
                LEFT JOIN MAEDMB2 ON MAEDMB2.MDCodD = TMPFAC.SOCodD and MAEDMB2.MDCodM = TMPFAC.SOCodM 
                    and MAEDMB2.MDCodB = DATSOAT.DaCodB 
                LEFT JOIN EMPRESS ON EMPRESS.MEcntr = TMPFAC.SONitAsg
                LEFT JOIN MAEDMB AS MAEDMBPR  on MAEDMBPR.MDCodD = DATSOAT.DaCodDPr
                LEFT JOIN MAEDMB1 AS MAEDMB1PR ON MAEDMB1PR.MDCodD = DATSOAT.DaCodDPr and MAEDMB1PR.MDCodM = DATSOAT.DaCodMPr
                LEFT JOIN MAEDMB AS MAEDMBCN  on MAEDMBCN.MDCodD = TMPFAC.SoCodDCnd
                LEFT JOIN MAEDMB1 AS MAEDMB1CN ON MAEDMB1CN.MDCodD = TMPFAC.SoCodDCnd and MAEDMB1CN.MDCodM = TMPFAC.SOCodMCnd
                LEFT JOIN CAPBAS ON CAPBAS.MPCedu = DATSOAT.MPCedu and CAPBAS.MPTDoc = DATSOAT.MPTDoc
                where DATSOAT.MPCedu = '".$MPCedu."' and DATSOAT.MPTDoc = '".$MPTDoc."' AND DATSOAT.CtvIng = $CtvIng"));
        
        return response()->json([
            'status' => 200,
            'data' => $accidente
        ]);
    }   

    //Funcion para furips facturado
    public function docFuripsFac(Request $request){

        $MPNFac = $request->MPNFac; //Numero de factura
        $EMPCOD = $request->EMPCOD; //Codigo de la empresa

        $infoEmpresa = DB::table('EMPRESA')
            ->select('EMPCOD', 'EmpRazSoc', 'EmpNit', 'EmpCdIPS')
            ->where('EMPCOD', $EMPCOD)->first();
        
        $furips = DB::connection('sqlsrv')
            ->select("SELECT
                        MAEATE.MPNFac, ADMGLO11.AglFRdFac, ADMGLO01.AGlRadNr, MAEATE.MATipDoc, DATSOAT.DaNRdGlA,
                        DATSOAT.CtvIng, DATSOAT.MPCedu, DATSOAT.MPTDoc, CAPBAS.MPNOMC, CAPBAS.MPNom1, CAPBAS.MPNom2, CAPBAS.MPApe1, CAPBAS.MPApe2, 
                        CAPBAS.MPFchN, CAPBAS.MPSexo, CAPBAS.MPDire, CAPBAS.MDCodD dptoAccidentado, MAEDMBRA.MDNomD nomDptoActado,
                        CAPBAS.MDCodM mpioAccidentado, MAEDMB1RA.MDNomM nomMpoActado, CAPBAS.MPTele,
                        MAEATE.MPMeNi, MAEEMP.MENOMB,
                        MAEATE.MaDocDcl, MAEATE.MaTiDoDc, MAEATE.MaDecla, MAEATE.MaLugExp,
                        MAEATE.FacSCndAcc, MAEATE.FacSSitAcc, MAEATE.FacSFchAcc, MAEATE.FacSCodD, MAEDMB.MDNomD nomDptoActe, MAEATE.FacSCodM,
                        MAEDMB1.MDNomM nomMpoActe, DATSOAT.DaCodB, MAEDMB2.MDNomB nomBarActe, MAEATE.FacSRulUrb, MAEATE.MaInfAcc, DATSOAT.DaIntAut,
                        MAEATE.FacSIndAsg, MAEATE.FacSVeMa, MAEATE.FacSVePl, MAEATE.FacSVeTi, MAEATE.FacSNitAsg, EMPRESS.EmpDsc,
                        MAEATE.FacSNomSuc, DATSOAT.DaNumPol, DATSOAT.DaFecIniV, DATSOAT.DaFecFinV, DATSOAT.DaTipSer, DATSOAT.DATNUMSIR,
                            CASE DaTipSer 
                                WHEN 'PA' THEN 'PARTICULAR'
                                WHEN 'PU' THEN 'PUBLICO'
                                WHEN 'OF' THEN 'OFICIAL'
                                WHEN 'EM' THEN 'EMERGENCIA'
                                WHEN 'DI' THEN 'TRANSPORTE MA'
                                WHEN 'ES' THEN 'ESCOLAR'
                                WHEN 'NA' THEN 'NO APLICA'
                                ELSE 'NO APLICA' END AS tipoServicio,
                        DATSOAT.DaCobExc,  DATSOAT.DaRadGlo, 
                            CASE DaRadGlo
                                WHEN '0' THEN 'GLOSA TOTAL'
                                WHEN '1' THEN 'RESPUESTA O PAGO'
                                ELSE 'NO APLICA' END AS radicacionGlosa,
                        DATSOAT.DaNroTFol, DATSOAT.DaNRdGlA,
                        DATSOAT.DaPrApPr, DATSOAT.DaSeApPr, DATSOAT.DaPrNoPr, DATSOAT.DaSeNoPr,
                        DATSOAT.DaTiDoPr, DATSOAT.DaNuDoPr,DATSOAT.DaLugExp, DATSOAT.DaDirPr,
                        DATSOAT.DaCodDPr,MAEDMBPR.MDNomD nomDptoPr, DATSOAT.DaCodMPr, MAEDMB1PR.MDNomM nomMpoPr, DATSOAT.DaTelPr,
                        DATSOAT.DaPrApCo, DATSOAT.DaSeApCo, DATSOAT.DaPrNoCo, DATSOAT.DaSeNoCo,
                        MAEATE.FacSTpICnd, MAEATE.FacSCedCnd, MAEATE.MaLuExCnd, MAEATE.MaDirCnd,
                        MAEATE.FacSCodDCn, MAEDMBCN.MDNomD nomDptoCn, MAEATE.FacSCodMCn, MAEDMB1CN.MDNomM nomMpoCn, MAEATE.MaTelCnd,
                        DatAmb.DatPlaAmb, DatAmb.DatDirIni, DatAmb.DatDirFin, DatAmb.DatTipAmb, DatAmb.DatUR,
                        INGRESOS.IngFecAdm, INGRESOS.IngFecEgr, INGRESOS.IngDxCli, INGRESOS.IngEntDx, INGRESOS.IngEntDx2,  
                        INGRESOS.IngSalDx, INGRESOS.IngDxSal1, INGRESOS.IngDxSal2,
                        REFCREF.RefTip, REFCREF.RefFch, REFCREF.RefIPSRef, MAEIPS.MINomI, MAEIPS.IPSNroNIT, REFCREF.RefNomEnt,
                        REFCREF.REFFCHACR, REFCREF.RefIPSRcp, IPSREC.MINomI ipsReceptora, IPSREC.IPSNroNIT inscripReceptora,
                        REFCREF.REFMEDREC, MAEMED1.MMNomM, MAEMED1.MMTpoServ, MaeTpP.mtpdsc,
                        MEDTRA.MApe1 ape1Medtra, MEDTRA.MApe2 ape2Medtra, MEDTRA.MNom1 nom1Medtra, MEDTRA.MNom2 nom2Medtra,
                        MEDTRA.MTipDoc, MEDTRA.MMCedM, MEDTRA.MMRegM, MAEATE.MaUltCci, MAEATE.MATotF
                    FROM DATSOAT
                    LEFT JOIN MAEATE ON DATSOAT.MPCedu = MAEATE.MPCedu and DATSOAT.MPTDoc = MAEATE.MPTDoc 
                        and DATSOAT.CtvIng = MAEATE.MaCtvIng
                    LEFT JOIN MAEEMP ON MAEEMP.MENNIT = MAEATE.MPMeNi
                    LEFT JOIN MAEDMB on MAEDMB.MDCodD = MAEATE.FacSCodD
                    LEFT JOIN MAEDMB1 ON MAEDMB1.MDCodD = MAEATE.FacSCodD and MAEDMB1.MDCodM = MAEATE.FacSCodM
                    LEFT JOIN MAEDMB2 ON MAEDMB2.MDCodD = MAEATE.FacSCodD  and MAEDMB2.MDCodM = MAEATE.FacSCodM 
                        and MAEDMB2.MDCodB = DATSOAT.DaCodB 
                    LEFT JOIN EMPRESS ON EMPRESS.MEcntr = MAEATE.FacSNitAsg
                    LEFT JOIN MAEDMB AS MAEDMBPR  on MAEDMBPR.MDCodD = DATSOAT.DaCodDPr
                    LEFT JOIN MAEDMB1 AS MAEDMB1PR ON MAEDMB1PR.MDCodD = DATSOAT.DaCodDPr and MAEDMB1PR.MDCodM = DATSOAT.DaCodMPr
                    LEFT JOIN MAEDMB AS MAEDMBCN  on MAEDMBCN.MDCodD = MAEATE.FacSCodDCn
                    LEFT JOIN MAEDMB1 AS MAEDMB1CN ON MAEDMB1CN.MDCodD = MAEATE.FacSCodDCn and MAEDMB1CN.MDCodM = MAEATE.FacSCodMCn
                    LEFT JOIN CAPBAS ON CAPBAS.MPCedu = DATSOAT.MPCedu and CAPBAS.MPTDoc = DATSOAT.MPTDoc
                    LEFT JOIN MAEDMB AS MAEDMBRA ON MAEDMBRA.MDCodD = CAPBAS.MDCodD
                    LEFT JOIN  MAEDMB1 AS MAEDMB1RA ON MAEDMB1RA.MDCodD = CAPBAS.MDCodD and MAEDMB1RA.MDCodM = CAPBAS.MDCodM
                    LEFT JOIN DatAmb ON DatAmb.MPCedu = MAEATE.MPCedu AND DatAmb.MPTDoc = MAEATE.MPTDoc AND DatAmb.DatCscIng = MAEATE.MaCtvIng
                    INNER JOIN INGRESOS ON INGRESOS.MPCedu = MAEATE.MPCedu AND INGRESOS.MPTDoc = MAEATE.MPTDoc and INGRESOS.IngCsc = MAEATE.MaCtvIng
                    LEFT JOIN REFCREF ON REFCREF.MPcedu = MAEATE.MPCedu and REFCREF.MPTDoc = MAEATE.MPTDoc and REFCREF.RefCscIng = MAEATE.MaCtvIng
                    LEFT JOIN MAEIPS ON MAEIPS.MICodI = REFCREF.RefIPSRef
                    LEFT JOIN MAEIPS AS IPSREC ON IPSREC.MICodI = REFCREF.RefIPSRcp
                    LEFT JOIN MAEMED1 ON MAEMED1.MMCODM = REFCREF.REFMEDREC
                    LEFT JOIN MaeTpP ON MaeTpP.mtpcod = MAEMED1.MMTpoServ
                    LEFT JOIN MAEMED1 AS MEDTRA ON MEDTRA.MMCODM = INGRESOS.IngCoMt 
                    LEFT JOIN ADMGLO11 ON ADMGLO11.MPNFac = MAEATE.MPNFac AND ADMGLO11.MATipDoc = MAEATE.MATipDoc
                    LEFT JOIN ADMGLO01 ON ADMGLO01.AGlRemNr = ADMGLO11.AGlRemNr
                    where MAEATE.MPNFac = '".$MPNFac."'");
                
        return response()->json([
            'status' => 200,
            'empresa' => $infoEmpresa,
            'data' => $furips
        ]);

    }

    //Funcion para ver ubicacion del paciente
    public function ubicacionPaciente(Request $request){

        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');
        $IngFecMoPI = str_replace('-','',$request->input('IngFecMoPI'));
        $IngFecMoPF = str_replace('-','',$request->input('IngFecMoPF'));
        $IngCsc = $request->input('IngCsc');
        $ClaPro = $request->input('ClaPro');
        $IngCodPab = $request->input('IngCodPab');

        try{

            $data = DB::table('INGRESOMP')
                ->select('IngCodPab', 'ClaPro', 'IngFecMoP', 'IngCsc', 'MPTDoc', 'MPCedu', 'IngCodCam', 
                        'IngUsuMoP', 'IngFecMoE', 'IngUrgObs', 'IngCtvMoP')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->when($IngFecMoPI, function ($query, $IngFecMoPI) {
                    return $query->where('IngFecMoP', '>=', $IngFecMoPI);
                })
                ->when($IngFecMoPF, function ($query, $IngFecMoPF) {
                    return $query->where('IngFecMoP', '<=', $IngFecMoPF);
                })
                ->when($ClaPro, function ($query, $ClaPro) {
                    return $query->where('ClaPro', $ClaPro);
                })
                ->when($IngCodPab, function ($query, $IngCodPab) {
                    return $query->where('IngCodPab', $IngCodPab);
                })
                ->where('IngCsc', $IngCsc)
                ->orderBy('MPCedu')
                ->orderBy('MPTDoc')
                ->orderBy('ClaPro')
                ->orderBy('IngCsc')
                ->orderBy('IngCtvMoP')
                ->get();
    
            
            foreach ($data as $movimiento) {
                $usuarioRegM = DB::table('ADMUSR')
                    ->selectRaw('DBO.DESENCRIPTAR(AUSRID) AS usuario')
                    ->where('AUsrId', $movimiento->IngUsuMoP)
                    ->first();
                
                if ($usuarioRegM) {
                    $movimiento->IngUsuMoPD = $usuarioRegM->usuario;
                } else {
                    $movimiento->IngUsuMoPD = '';
                }
    
                $pabellon = DB::table('MAEPAB')
                    ->select('MPCodP', 'MPNomP')
                    ->where('MPCodP', $movimiento->IngCodPab)
                    ->orderBy('MPCodP')
                    ->first();
    
                if ($pabellon) {
                    $movimiento->IngCodPabD = $pabellon->MPNomP;
                } else {
                    $movimiento->IngCodPabD = '';
                }
    
            }
            return response()->json(['status' => 200, 'data' => $data]);

        }catch(\exception $e){
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }



    }
}
