<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BD;


class IngresaAtencionController extends Controller
{
    //FUNCION PARA ASIGNAR EL PACIENTE A UNA CAMA
    public function acuestaPaciente(Request $request){
       $MpUltCtv = $request->input('MpUltCtv'); //ultimo consecutivo de la cama estra desde validación ruta (Route::get('validaciones/infoCama',[ValidacionesController::class, 'infoCama']);)
       $MPDisp = $request->input('MPDisp'); //estado de la cama ocupada 1
       $MPUced = $request->input('MMPUced'); //cedula del paciente
       $MPUDoc = $request->input('MPUDoc'); //tipo de documento del paciente
       $MPCtvIn = $request->input('MPCtvIn'); //consecutivo de ingreso del paciente el utlimo consecutivo mas 1 es autoincrmentable
       $MPFchI = $request->input('MPFchI'); //fecha de ingreso fecha actual
       $MPCodP = $request->input('MPCodP'); //codigo del pabellon pabellon escogido
       $MPNumC = $request->input('MPNumC'); //numero de la cama cama escogida

       try{
            $acuestaPaciente = DB::Connection('sqlsrv')
            ->update("UPDATE [MAEPAB1] SET [MpUltCtvo]= $MpUltCtv + 1, [MPDisp] = $MPDisp , [MPUced]= '".$MPUced."' ,
                        [MPUDoc]= '".$MPUDoc."' , [MPCtvIn]= $MPCtvIn , [MPFchI]= '".$MPFchI."'
                    WHERE [MPCodP] =  $MPCodP  AND [MPNumC] =  '".$MPNumC."'");
            $status=200;

       }catch(\exception $e){
            $acuestaPaciente = [
                'message' => $e->getMessage()
            ];
            $status = 400;
       }

       return response()->jason($acuestaPaciente, $status);

    }

    public function insertMovCama(Request $request){
        $MPCodP = $request->input('MPCodP'); //codigo del pabellon escogido
        $MPNumC = $request->input('MPNumC'); //cama escogida
        $HisCamCtv = $request->input('HisCamCtv'); //consecutivo de la cama viene de validacion (Route::get('validaciones/infoCama',[ValidacionesController::class, 'infoCama']);)
        $HisCamEdo = $request->input('HisCamEdo'); //estado en el que queda la cama O ocupada
        $HisCamFec = $request->input('HisCamFec'); //fecha actual sin hora
        $HisCamHor = $request->input('HisCamHor'); //hora de movimiento de la cama
        $MPCedu = $request->input('MPCedu'); //cedula del paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo de documento
        $HisCamUsu = $request->input('HisCamUsu'); //usuario que hace el movimiento
        $HisCnsIng = $request->input('HisCnsIng'); //consecutivo de ingreso del paciente

        try{
            $movimientoCama = DB::Connection('sqlsrv')
            ->insert("INSERT INTO [MAEPAB11] ([MPCodP], [MPNumC], [HisCamCtv], [HisCamEdo],
                        [HisCamFec], [HisCamHor], [MPCedu], [MPTDoc], [HisCamUsu], [HisCnsIng])
                    VALUES ( $MPCodP,  '".$MPNumC."' ,  $HisCamCtv ,  '".$HisCamEdo."' ,
                        '".$HisCamFec."',  '".$HisCamHor."',  '".$MPCedu."',  '".$MPTDoc."',  '".$HisCamUsu."',  $HisCnsIng )");
            $status = 200;
        }catch(\exception $e){
            $movimientoCama = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($movimientoCama, $status);
    }

    //INGRESO DE ATENCIONES 
    public function insertAtencion(Request $request){

        $fechAct = Carbon::now('America/Bogota')->format('Ymd');
        $MPCedu = $request->input('MPCedu'); //Cedula C
        $MPTDoc = $request->input('MPTDoc'); //Tipo de Documento C
        $ClaPro = $request->input('ClaPro'); //Via de ingreso del paceinte, 1amb 2 hospi .... C

        $result = $this->admAbierta($MPCedu, $MPTDoc, $ClaPro);

        if($result["status"] == 200){
            $TFMENi  =  $request->input('TFMENi'); //C􀀁digo Contrato
            $TFDi1S  = ' ';
            $SONume  = ' ';
            $SONitAsg  = ' ';
            $SOVePl  = ' ';
            $SOVeMa  = ' ';
            $SOVeTi  = ' ';
            $SOVeMo  = ' ';
            $SOVeCl  = ' ';
            $SOCndAcc  = ' ';
            $SOSitAcc  = ' ';
            $SOFchAcc  = ' ';
            $SOCodD  = ' ';
            $SOCodM  = 0;
            $SORulUrb  = ' ';
            $SOInfAcc  = ' ';
            $SOIndAsg  = ' ';
            $SOTpICnd  = ' ';
            $SOCedCnd  = ' ';
            $SODirCnd  = ' ';
            $SoCodDCnd  = ' ';
            $SOCodMCnd  = 0;
            $SoTelCnd  = ' ';
            $SONomEmp  = ' ';
            $SOMCodFCD  = ' ';
            $SONomSuc  = ' ';
            $SOTpoEC  = 0;
            $SODesEC  = ' ';
            $SOCodDE  = ' ';
            $SOCodME  = 0;
            $SODecla  = ' ';
            $SOTiDoDc  = ' ';
            $SODocDcl  = ' ';
            $SOlugExp  = ' ';
            $SoLgExpCn  = ' ';
            $SCCCod  = $request->input('SCCCod'); //C􀀁digo de la sede
            $TFViaI  = 0;
            $TFCoMI  =  '';
            $TFEsMI  =  0;
            $MPFEsH  = ' ';
            $TFNoRe  =  $request->input('TFNoRe'); //Primer Nombre Responsable C
            $TFNoRe2  =  $request->input('TFNoRe2'); //Segundo Nombre Responsable C
            $TFTeRe  =  $request->input('TFTeRe'); //Tel􀀁fono Responsable C
            $TFTiRe  =  $request->input('TFTiRe'); //Tipo de Responsable C
            $TFUIng  =  $request->input('TFUIng'); //Usuario Tramita Ingreso C
            $TFNMAU  = $request->input('TFNMAU'); //Nombre Medico Autoriza C
            $TFcNomAut  = $request->input('TFcNomAut'); //Nombre Medico Autoriza C
            $SccEmp  =  $request->input('SccEmp'); //C􀀁digo de la empresa 
            $TFVAPU  = 0;
            $TmCtvAct  = 0;
            $TFIPSENT  = 0;
            $TFCoMt  = ' ';
            $TFEsMt  = 0;
            $TFCODPAQ  = ' ';
            $TFTiDocAc  =  $request->input('TFTiDocAc'); //Tipo Docuemento Acompa􀀁ante C
            $TFParAc  =  $request->input('TFParAc'); //Parentesco de Acompa􀀁ante C
            $MICodI  = 0;
            $SOFchVIni  = ' ';
            $SOFchVFin  = ' ';
            $SONomCnd  = ' ';
            $SOMNroReg  = ' ';
            $SOInfEC  = ' ';
            $TFCMEg  = ' ';
            $TFCMAD  = ' ';
            $MPCedu  =  $request->input('MPCedu'); //Cedula C
            $MPTDoc  =  $request->input('MPTDoc'); //Tipo de Documento C
            $ClaPro  =  $request->input('ClaPro'); //Via de ingreso del paceinte, 1amb 2 hospi .... C
            $IngNit  =  $request->input('IngNit'); //Nit-contrato C
            $IngEntDx  =  $request->input('IngEntDx'); //Dx. Entrada C
            $IngUsrReg  =  $request->input('IngUsrReg'); //Usuario que Registra Ingreso C
            $IngIPS  =  0;
            $IngEntDx2  =  $request->input('IngEntDx2'); //2do Diagnostico de Entrada C
            $IngNmResp  =  $request->input('IngNmResp'); //Primer Nombre del Responsable del Paciente C
            $IngNmResp2  =  $request->input('IngNmResp2'); //Segundo Nombre del Responsable del Paciente C
            $IngDocResp  =  $request->input('IngDocResp'); //Documeto Responsable C
            $IngTDoResp  =  $request->input('IngTDoResp'); //Tipo de documento Responsable C
            $IngParResp  =  $request->input('IngParResp'); //Parentesco del Responsable iniciales C conyuge H hijo
            $IngDirResp  =  $request->input('IngDirResp'); //Direccion del Responsable C
            $IngTelResp  =  $request->input('IngTelResp'); //Telefonos del Responsable C
            $MPCodP  =  $request->input('MPCodP'); //C􀀁digo Pabell􀀁n N
            $MPNumC  = ' ';
            $IngDoAco  =  $request->input('IngDoAco'); //Documento de Acompa􀀁ante N
            $IngNoAc  =  $request->input('IngNoAc'); //Nombre de Acompa􀀁ante C
            $IngTeAc  =  $request->input('IngTeAc'); //Telefono de Acompa􀀁ante C
            $IngTeTrR  =  $request->input('IngTeTrR'); //Telefono trabajo de responsable C
            $IngEmTrR  =  $request->input('IngEmTrR'); //Empresa Trabajo Responsable C
            $IngDptRe  =  $request->input('IngDptRe'); //Departamento residencia de responsable C
            $IngMunRe  =  $request->input('IngMunRe'); //Municipio de residencia responsable N
            $IngCoMt  =  $request->input('IngCoMt'); //Codigo del medico tratante C
            $IngEsMt  = 0;
            $IngApRes  =  $request->input('IngApRes'); //Primer Apellido Responsable C
            $IngApRes2  =  $request->input('IngApRes2'); //Segundo Apellido Responsable C
            $IngCtvAc  = 0;
            $IngDxCli  = ' ';
            $IngDerObs  = ' ';
            $IngAtnAct  = $ClaPro;
            $IngCauE  = 13;
            $IngResExe  = ' ';
            $IngHosTTo  = 0;
            $IngIndCap  = ' ';
            $IngIPSAtn  = 0;
            $IngSoAdTr  = ' ';
            $IngMeSAT  = ' ';
            $IngEsSAT  = 0;
            $IngFSAdTr  = ' ';
            $IngIndReN  = ' ';
            $IngIndAte  = ' ';
            $IngNumTur  = ' ';
            $IngFecTur  = ' ';
            $INGCODPAQ  = ' ';
            $IngAteEs  = ' ';
            $IngGruPo  = ' ';
            $IngCarnet  = ' ';
            $IngTiDoAc  =  $request->input('IngTiDoAc'); //Tipo de Documento Acompa􀀁ante C
            $IngParAc  =  $request->input('IngParAc'); //Parentesco de Acompa􀀁ante C
            $INGREIPAC  = ' ';
            $IngCodEnt  = ' ';
            $IngMEdEsp  = ' ';
            $IngUrgObs  = 0;
            $TFFCES =  Carbon::now('America/Bogota')->format('Ymd'); //Cierre de la Estancia D

            $TFFchI = Carbon::now('America/Bogota')->format('Ymd');
            $IngFecAdm = Carbon::now('America/Bogota')->format('Ymd H:i:s');
            $TFHorI = Carbon::now('America/Bogota')->format('H:i:s');
        

        $IngFecMoP = Carbon::now('America/Bogota')->format('Ymd H:i:s');

        if($IngDoAco < 1){
            $IngDoAco = 0;
        }

        if($IngMunRe < 1){
            $IngMunRe = 0;   
        }

        try{

            //Validacion del parametro
            $flagHvt = DB::table('MAEFLAG')
            ->where('FlgCod', 2)->first();
        
            if($flagHvt != null && $flagHvt->FlgEst == 1){
                //Todas las variables del request son obligatorias
                try{
                    $variables = [
                        'ClaPro' => [$ClaPro, 'Tipo de ingreso'],
                        'MPTDoc' => [$MPTDoc, 'Tipo de documento'],
                        'MPCedu' => [$MPCedu, 'Documento'],
                        'IngNit' => [$IngNit, 'Contrato'],
                        'MPCodP' => [$MPCodP, 'Pabellon'],
                        'TFViaI' => [$TFViaI, 'Via de ingreso'],
                    ];

                    foreach ($variables as $valor) {
                        if ($valor[0] === null) {
                            // Aquí puedes manejar el caso de una variable en null
                            throw new \Exception("El campo " .$valor[1]. " es requerido");
                        }
                    }


                }catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'message' => $e->getMessage()
                    ], 400);
                }
                
            }else{


                if(!$MPCodP){
                    throw new \Exception("El pabellon es obligatorio");   
                }

                if(!$IngNit){
                    throw new \Exception("El contrato es obligatorio");   
                }

                if(!$TFMENi){
                    throw new \Exception("El contrato es obligatorio");   
                }
            }

            //validacion estado del paciente
            $estadoPaciente = DB::table('CAPBAS')
                ->select('MPCedu', 'MPTDoc', 'MPEstPac')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)->first();
                
            if($estadoPaciente->MPEstPac == 'N'){
                throw new \Exception("El paciente se encuentra inactivo");
            }

            $vigCnc = DB::table('MAECTOS')
                ->select('MENNIT', 'MeCfcha1', 'CtoFchIni', 'MeCnsCnt')
                ->where('MENNIT', $IngNit)
                ->where('CtoFchIni','<=', $fechAct)
                ->where('MeCfcha1','>=', $fechAct)->first();
            

            $vigencia = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $IngNit)
                ->where('MEPPVig','<=', $fechAct)
                ->orderByDesc('MEPPVig')->first();
        
            if ($vigencia == null){
                throw new \Exception("No hay vigencia para el contrato " .$IngNit);
            }

            //consulta el siguiennte consecutivo de ingreso
            $nextIngreso = DB::table('CAPBAS')
            ->select('MPTDoc', 'MPCedu', 'MpCtvoAtn', 'MpCtvoActe')
            ->where('MPCedu', $MPCedu)
            ->where('MPTDoc', $MPTDoc)->first();

            $nextCscIng = $nextIngreso->MpCtvoAtn;

            if($nextCscIng == 0){
                $nextCscIng = 1;
            }

            $ingresoAn = DB::table('INGRESOS')
                ->select('MPCedu', 'MPTDoc', 'IngCsc')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $nextCscIng)->first();
            
            if($ingresoAn != null){
                
                $ultimoCsv = DB::table('INGRESOS')
                    ->select('IngCsc')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->orderByDesc('IngCsc', 'desc')->first();
                
                $nextCscIng = $ultimoCsv->IngCsc + 1;
                
            }
            

            //insert en TMPFAC  
            DB::Connection('sqlsrv')
            ->insert("INSERT INTO [TMPFAC]
                        ([TFCedu], [TFTDoc], [TmCtvIng], [TFMENi], [TFFchI],
                        [TFFCES], [TFHorI], [TFDi1I], [TFDi2I], [TFDi1S], [TFDi3I], [SONume],
                        [SONitAsg], [SOVePl], [SOVeMa], [SOVeTi], [SOVeMo], [SOVeCl], [SOCndAcc],
                        [SOSitAcc], [SOFchAcc], [SOCodD], [SOCodM], [SORulUrb], [SOInfAcc],
                        [SOIndAsg], [SOTpICnd], [SOCedCnd], [SODirCnd], [SoCodDCnd], [SOCodMCnd],
                        [SoTelCnd], [SONomEmp], [SOMCodFCD], [SONomSuc], [SOTpoEC], [SODesEC],
                        [SOCodDE], [SOCodME], [SODecla], [SOTiDoDc], [SODocDcl], [SOlugExp],
                        [SoLgExpCn], [SCCCod], [TFViaI], [TFCoMI], [TFEsMI], [MPFEsH], [TFNoRe],
                        [TFNoRe2], [TFTeRe], [TFTiRe], [TFCauE], [TFDi2S], [TFDi3S], [TFUIng],
                        [TFNMAU], [TFcNomAut], [TFcCodCam], [TFcCodPab], [SccEmp], [ClaPro],
                        [TFVAPU], [TmCtvAct], [TFDocRep], [TFTDoRep], [TFDirRep], [TFIPSENT],
                        [TFDocAco], [TFNoAc], [TFTeAc], [TFTeTrRe], [TFEmTrRe], [TFDptRes],
                        [TFMunRes], [TFCoMt], [TFEsMt], [TFApeRes], [TFApeRes2], [ClaproI],
                        [TFCoCamI], [TFCODPAQ], [TFTiDocAc], [TFParAc], [MICodI], [SOFchVIni],
                        [SOFchVFin], [SONomCnd], [SOMNroReg], [SOInfEC], [TFEsMS], [TFCMEg],
                        [TFEstS], [TFMotS], [TFCMAD], [TFSeGe], [TFCoPr], [TFTiPa], [TFTiAn],
                        [TFFchM], [TFCaMu], [TFQuia], [TFDiCp], [TFHorO], [TFFchS], [TFVNPU],
                        [TFUSal], [TFUscP], [TFNrCerD], [TFUlcAC], [TFEstP], [TFTotP], [TFTotS],
                        [TFTotF], [TFValS], [TFVaAb], [TFVPaU], [TFVDsc], [TFUscS], [TFUcsA], [TFUcsN],
                        [TFTpeAut], [TFVlrAut], [TFCpgPgo], [TFCpgLqd], [TFcCodCns], [TFVlrImpt],
                        [TFIndCrt], [TFCnTQx], [ReFatMat], [ReFatNum], [TmEdoCue], [TFObsFac],
                        [TFAdmFac], [TFUsuFac], [TFVPOCo], [TFVlrTIv], [TFDI4S], [TFNUMPGP])
                    VALUES (
                        '".$MPCedu."' ,  '".$MPTDoc."' ,  $nextCscIng,  '".$IngNit."' ,  '".$TFFchI."' , '".$TFFCES."'  ,
                        '".$TFHorI."' ,  '".$IngEntDx."' ,  '".$IngEntDx2."' ,  ' ' ,  '".$IngDxCli."' ,  '".$SONume."' ,  '".$SONitAsg."' ,
                        '".$SOVePl."' ,  '".$SOVeMa."' ,  '".$SOVeTi."' ,  '".$SOVeMo."' ,  '".$SOVeCl."' ,  '".$SOCndAcc."' ,
                        '".$SOSitAcc."' ,  '".$SOFchAcc."' ,  '".$SOCodD."' ,  $SOCodM ,  '".$SORulUrb."' ,  '".$SOInfAcc."' ,
                        '".$SOIndAsg."' ,  '".$SOTpICnd."' ,  '".$SOCedCnd."' ,  '".$SODirCnd."' ,  '".$SoCodDCnd."' ,  $SOCodMCnd,
                        '".$SoTelCnd."' ,  '".$SONomEmp."' ,  '".$SOMCodFCD."' ,  '".$SONomSuc."' ,  $SOTpoEC,  '".$SODesEC."',
                        '".$SOCodDE."' ,  $SOCodME,  '".$SODecla."' ,  '".$SOTiDoDc."' ,  '".$SODocDcl."' ,  '".$SOlugExp."' ,
                        '".$SoLgExpCn."' ,  '".$SCCCod."' ,  $TFViaI ,  '".$TFCoMI."' ,  $TFEsMI ,  '".$MPFEsH."' ,
                        '".$TFNoRe."' ,  '".$TFNoRe2."' ,  '".$TFTeRe."' ,  '".$TFTiRe."' ,  $IngCauE ,  ' ' ,
                        ' ' ,  '".$TFUIng."' ,  '".$TFNMAU."' ,  '".$TFcNomAut."' , '".$MPNumC."'  ,  $MPCodP,
                        $SccEmp ,  $ClaPro , $TFVAPU,  $TmCtvAct ,  '".$IngDocResp."' ,  '".$IngTDoResp."' ,
                        '".$IngDirResp."' ,  $TFIPSENT ,  '".$IngDoAco."' ,  '".$IngNoAc."' ,  '".$IngTeAc."' ,  '".$IngTeTrR."' ,
                        '".$IngEmTrR."' ,  '".$IngDptRe."' ,  ".$IngMunRe.",  '".$TFCoMt."' ,  ".$TFEsMt.",  '".$IngApRes."' ,
                        '".$IngApRes2."' ,  $ClaPro ,  '".$MPNumC."' ,  '".$TFCODPAQ."' ,  '".$TFTiDocAc."' ,  '".$TFParAc."' ,
                        convert(int, $MICodI), convert( DATETIME, '".$SOFchVIni."', 112 ),
                        convert( DATETIME, '".$SOFchVFin."', 112 ), '".$SONomCnd."', '".$SOMNroReg."', '".$SOInfEC."', ' ',
                        '".$TFCMEg."', convert(int, 0), ' ', '".$TFCMAD."', convert(int, 0), '', '', '',
                        convert( DATETIME, '17530101', 112 ), '', '', '',
                        convert(int, 0), convert( DATETIME, '17530101', 112 ),
                        convert(int, 0), '', convert(int, 0), '', convert(int, 0),
                        convert(int, 0), convert(int, 0), convert(int, 0),
                        convert(int, 0), convert(int, 0), convert(int, 0),
                        convert(int, 0), convert(int, 0), convert(int, 0),
                        convert(int, 0), convert(int, 0), convert(int, 0),
                        convert(int, 0), '', '', convert(int, 0), convert(int, 0),
                        '', convert(int, 0), convert(int, 0), convert(int, 0),
                        '', '', '', '', convert(int, 0), convert(int, 0), '', '')"); 

            //INSERT INGRESOS
            DB::Connection('sqlsrv')
            ->insert("INSERT INTO [INGRESOS]
                        ([MPCedu], [MPTDoc], [ClaPro], [IngCsc], [IngFecAdm], [IngNit], [IngEntDx],
                        [IngUsrReg], [IngIPS], [IngEntDx2], [IngNmResp], [IngNmResp2], [IngDocResp],
                        [IngTDoResp], [IngParResp], [IngDirResp], [IngTelResp], [IngMedSal], [IngMEdEsp],
                        [IngDxSal1], [IngDxSal2], [IngDxSal3], [MPCodP], [MPNumC], [IngDoAco], [IngNoAc],
                        [IngTeAc], [IngTeTrR], [IngEmTrR], [IngDptRe], [IngMunRe], [IngCoMt], [IngEsMt],
                        [IngApRes], [IngApRes2], [IngCtvAc], [IngDxCli], [IngInSlC], [IngDerObs], [IngAtnAct],
                        [IngUlcMoP], [IngCauE], [IngTip], [IngResExe], [IngHosTTo], [IngIndCap], [IngIPSAtn],
                        [IngSoAdTr], [IngMeSAT], [IngEsSAT], [IngFSAdTr], [IngIndReN], [IngIndAte], [IngNumTur],
                        [IngFecTur], [INGCODPAQ], [IngAteEs], [IngGruPo], [IngCarnet], [IngTiDoAc], [IngParAc],
                        [INGREIPAC], [IngCodEnt], [IngFac], [IngDoc], [IngHsp], [IngExtEst], [IngEstSld],
                        [IngFecEgr], [IngSalDx], [IngDxTip], [IngNotObl], [InsIpsSal], [IngFchM], [IngCauM],
                        [IngMedDef], [InCerDef], [IngDxTip1], [IngDxTip2], [IngComp], [IngMotSal], [IngHorObs],
                        [IngUsrSal], [IngCodPEg], [IngAteEgr], [IngNumCit], [IngCscN], [IngSege], [IngCoPr],
                        [IngTiPa], [IngTiAn], [IngQuiA], [IngUCtvEp], [IngCodCEg], [IngUsuAnu], [IngFchAnu],
                        [IngObsAnu], [IngReaUrg], [IngEmpPlt], [IngSedPlt], [IngCodPlt], [IngCnsPlt],
                        [IngFeHAtU], [IngIndApB], [IngNroAn1], [IngNroAn2], [AlumCod], [ProTerCod], [IngReligi],
                        [IngAcudie], [IngDesAlm], [IngIndUF], [IngActAla], [IngObsAla], [IngCodMed], [IngEsDepo],
                        [IngRieCod], [IngRiCnDe], [IngRiCoDe], [IngDxRie], [INGSALML], [INGDXTIP3], [INGDXSAL4],
                        [INGDEPPLA], [IngIndCamT])
                    VALUES (
                        '".$MPCedu."' ,  '".$MPTDoc."' ,  $ClaPro , $nextCscIng,  '".$IngFecAdm."' ,  '".$IngNit."' ,  '".$IngEntDx."',
                        '".$IngUsrReg."' ,  $IngIPS,  '".$IngEntDx2."' ,  '".$IngNmResp."' ,  '".$IngNmResp2."' ,  '".$IngDocResp."' ,
                        '".$IngTDoResp."' ,  '".$IngParResp."' ,  '".$IngDirResp."' ,  '".$IngTelResp."' ,  '' ,  '".$IngMEdEsp."' ,
                        '' ,  '' ,  '' ,  $MPCodP ,  '".$MPNumC."' ,  $IngDoAco ,
                        '".$IngNoAc."' ,  '".$IngTeAc."' ,  '".$IngTeTrR."' ,  '".$IngEmTrR."' ,  '".$IngDptRe."' ,  $IngMunRe ,
                        '".$IngCoMt."' ,  $IngEsMt ,  '".$IngApRes."' ,  '".$IngApRes2."' ,  $IngCtvAc ,  '".$IngDxCli."' ,
                        'N' ,  '".$IngDerObs."' ,  '".$ClaPro."' ,  1 ,  $IngCauE ,  'GN' ,
                        '".$IngResExe."' ,  $IngHosTTo ,  '".$IngIndCap."' ,  '".$IngIPSAtn."' ,  '".$IngSoAdTr."' ,  '".$IngMeSAT."' ,
                        $IngEsSAT ,  '".$IngFSAdTr."' ,  '".$IngIndReN."' ,  '".$IngIndAte."' ,  '".$IngNumTur."' ,  '".$IngFecTur."' ,
                        '".$INGCODPAQ."' ,  '".$IngAteEs."' ,  '".$IngGruPo."' ,  '".$IngCarnet."' ,  '".$IngTiDoAc."' ,  '".$IngParAc."' ,
                        '".$INGREIPAC."' ,  '".$IngCodEnt."'  , convert(int, 0), convert(int, 0),
                        '', convert(int, 0), convert(int, 0),
                        convert( DATETIME, '17530101', 112 ), '',
                        convert(int, 0), '', '', convert( DATETIME, '17530101', 112 ),
                        '', '', '', convert(int, 0), convert(int, 0), '', '',
                        convert(int, 0), '', convert(int, 0), '', convert(int, 0),
                        convert(int, 0), convert(int, 0), '', '', '', '',
                        convert(int, 0), '', '', convert( DATETIME, '17530101', 112 ),
                        '', '', '', '', '', convert(int, 0),
                        convert( DATETIME, '17530101', 112 ),
                        '', convert(int, 0), convert(int, 0),
                        '', '', '', '', '',
                        '', '', '', '', '',
                        convert(int, 0), convert(int, 0), convert(int, 0),
                        '', '', convert(int, 0), '', '', '')");
            

            //INSERT INGRESOMP
            DB::Connection('sqlsrv')
            ->insert("INSERT INTO [INGRESOMP]
                        ([MPCedu], [MPTDoc], [ClaPro], [IngCsc], [IngCtvMoP], [IngCodPab],
                        [IngCodCam], [IngFecMoP], [IngUsuMoP], [IngUrgObs], [IngFecMoE])
                    VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$ClaPro."' , $nextCscIng ,  1 ,
                         $MPCodP,  '".$MPNumC."' ,  '".$IngFecMoP."' ,  '".$TFUIng."' ,  $IngUrgObs , convert( DATETIME, '17530101', 112 ))");
            
            //UPDATE CAPBAS 
            DB::table('CAPBAS')
            ->where('MPCedu', $MPCedu)
            ->where('MPTDoc', $MPTDoc)
            ->update([
                'MpCtvoAtn' => $nextCscIng + 1
            ]);

            DB::table('LOGINGR')->insert([
                'MPCedu' => $MPCedu,
                'MPTDoc' => $MPTDoc,
                'IngCsc' => $nextCscIng,
                'IngFec' => Carbon::now('America/Bogota')->format('Ymd H:i:s'), // Utiliza la función now() para obtener la fecha y hora actual
                'UsrIng' => $IngUsrReg,
            ]);


            $retorno = [
                'status' => 200,
                'message' => 'Admision creada correctamente para el paciente '.$MPCedu.' en el ingreso ' .$nextCscIng
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);  
        }
        else {
            $respuesta = [
                'status' => $result["status"],
                'message' => $result["message"]
             ];

            return response()->json($respuesta);
        }
    }

    public function admAbierta($MPCedu, $MPTDoc, $ClaPro){
            /* $MPCedu = $request->input('MPCedu'); //Cedula C
        $MPTDoc = $request->input('MPTDoc'); //Tipo de Documento C
        $ClaPro = $request->input('ClaPro'); //Via de ingreso del paceinte, 1amb 2 hospi .... C */
        $mensaje = '';
        try{

            switch ($ClaPro) {
                case 1:
                    $tipoIng = "AMBULATORIA";
                    break;
                
                case 2:
                    $tipoIng = "HOSPITALARIA";
                    break;

                case 3:
                    $tipoIng = "de URGENCIAS";
                    break;

                case 4:
                    $tipoIng = "de TRATAMIENTO ESPECIAL";
                    break;
            
                case 5:
                    $tipoIng = "de TRIAGE";
                    break;
            
            }

            //VERIFIACION DE ADMISION ABIERTA
            $admAbierta = DB::table('TMPFAC')
                ->select('TmCtvIng', 'ClaPro', 'TFTDoc', 'TFCedu', 'TFHorI', 
                    'TFFchI', 'TFcCodPab', 'TFcCodCam')
                ->where('TFCedu', $MPCedu)
                ->where('TFTDoc', $MPTDoc)
                ->where('ClaPro', $ClaPro)->first();
            
                $ingreso = DB::table('INGRESOS')
                    ->select('MPCedu', 'MPTDoc', 'IngCsc', 'ClaPro', 'IngFecEgr', 'IngFchAnu')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->where('IngFecEgr', '1753-01-01')
                    ->where('ClaPro','!=', 1)
                    ->where('IngFchAnu', '1753-01-01' )
                    ->first();


            if($admAbierta !== null ){
                throw new \Exception('Paciente con admision ' .$tipoIng.' sin salida en el ingreso ' . $admAbierta->TmCtvIng.' no se puede seguir con el proceso');
            }

            if($ingreso !== null ){
                throw new \Exception('El paciente ' .$MPCedu . ' tiene una admisión sin salida en el ingreso ' .$ingreso->IngCsc. '. No se puede continuar con el proceso');  
            }  

            if ($admAbierta !== null and $ClaPro !== 2){
                $mensaje ='Paciente con admision ' .$tipoIng.' sin salida en el ingreso ' . $admAbierta->TmCtvIng .' ¿desea realizar la admisión?';
                           
            }

            $retorno= [
                'status' => 200,
                'message' => $mensaje,
                'data' => $admAbierta
            ];

        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage(),
                'data' => $ingreso
                
            ];
        }
        return $retorno;

    }

    public function insertAtencionR(Request $request){

        $fechAct = Carbon::now('America/Bogota')->format('Ymd');
        $MPCedu = $request->input('MPCedu'); //Cedula C
        $MPTDoc = $request->input('MPTDoc'); //Tipo de Documento C
        $ClaPro = $request->input('ClaPro'); //Via de ingreso del paceinte, 1amb 2 hospi .... C
        $indCtvoActe = $request->input('indCtvoActe'); //indicador si se asigna un nuevo consecutivo de accidente
        
        $result = $this->admAbierta($MPCedu, $MPTDoc, $ClaPro); 
        
        if($result["status"] == 200){ 
            
            //DATOS PARA TMPFAC 
            $TFFchI = Carbon::now('America/Bogota')->format('Ymd'); //Fecha de Ingreso D
            $TFFCES = Carbon::now('America/Bogota')->format('Ymd'); //Cierre de la Estancia D
            $TFHorI = Carbon::now('America/Bogota')->format('H:i:s'); //Hora de Ingreso C
            $SONume = $request->input('SONume'); //SOAT No. C
            $SONitAsg = $request->input('SONitAsg'); //SOAT Nit Aseguradora C
            $SOVePl = $request->input('SOVePl'); //Placas Vehiculo C
            $SOVeMa = $request->input('SOVeMa'); //Marca Vehiculo C
            $SOVeTi = $request->input('SOVeTi'); //Tipo Vehiculo C
            $SOVeMo = $request->input('SOVeMo'); //Modelo Vehiculo C
            $SOVeCl = $request->input('SOVeCl'); //Clase de Vehiculo C
            $SOCndAcc = $request->input('SOCndAcc'); //SOAT Condici􀀁n del accidentado C
            $SOSitAcc = $request->input('SOSitAcc'); //SOAT Sitio del accidente C
            $SOFchAcc = $request->input('SOFchAcc'); //SOAT Fecha del accidente T
            $SOCodD = $request->input('SOCodD'); //SOAT Dpto del accidente C
            $SOCodM = $request->input('SOCodM'); //SOAT Municipio del accidente N
            $SORulUrb = $request->input('SORulUrb'); //SOAT Indica Rulal o Urbano C
            $SOInfAcc = $request->input('SOInfAcc'); //SOAT Informe del accidente L
            $SOIndAsg = $request->input('SOIndAsg'); //SOAT Indica asegurado Si No C
            $SOTpICnd = $request->input('SOTpICnd'); //SOAT Tipo Ident Conductor C
            $SOCedCnd = $request->input('SOCedCnd'); //SOAT Numero Ident Conductor C
            $SODirCnd = $request->input('SODirCnd'); //SOAT Direcci􀀁n del conductor C
            $SoCodDCnd = $request->input('SoCodDCnd'); //SOAT Dpto del conductor C
            $SOCodMCnd = $request->input('SOCodMCnd'); //SOAT Ciudad del Conductor N
            $SoTelCnd = $request->input('SoTelCnd'); //SOAT Telefono del conductor C
            $SONomEmp = $request->input('SONomEmp'); //SOAT Nombre de Empresa Trabaja C
            $SOMCodFCD = $request->input('SOMCodFCD'); //SOATMedico Firma Cert Difunsio C
            $SONomSuc = $request->input('SONomSuc'); //SOAT Nombre Sucursal Asegurado C
            $SOTpoEC = $request->input('SOTpoEC'); //SOAT Tipo Evento Catastrofico N
            $SODesEC = $request->input('SODesEC'); //SOAT Descripci􀀁n Evento Catast C
            $SOCodDE = $request->input('SOCodDE'); //SOAT Codigo Dpto Empresa C
            $SOCodME = $request->input('SOCodME'); //SOAT Codigo Ciudad Empresa N
            $SODecla = $request->input('SODecla'); //Nombre del declarante C
            $SOTiDoDc = $request->input('SOTiDoDc'); //Tipo de Documento de Declarante C
            $SODocDcl = $request->input('SODocDcl'); //Documento del declarante C
            $SOlugExp = $request->input('SOlugExp'); //Lugar de expedici􀀁n del documento del declarante C
            $SoLgExpCn = $request->input('SoLgExpCn'); //lugar de expedici􀀁n de la cedula del conductor C
            $SCCCod = $request->input('SCCCod'); //Subtipo Centro de Costos C
            $TFViaI = $request->input('TFViaI'); //Via de Ingreso N
            $TFCoMI = $request->input('TFCoMI'); //M􀀁dico Ingreso C
            $TFEsMI = $request->input('TFEsMI'); //Especialidad M􀀁dico Ingreso N
            $TFNoRe = $request->input('TFNoRe'); //Primer Nombre Responsable C
            $TFNoRe2 = $request->input('TFNoRe2'); //Segundo Nombre Responsable C
            $TFTeRe = $request->input('TFTeRe'); //Tel􀀁fono Responsable C
            $TFTiRe = $request->input('TFTiRe'); //Tipo de Responsable C
            $TFUIng = $request->input('TFUIng'); //Usuario Tramita Ingreso C
            $TFNMAU = $request->input('TFNMAU'); //AUTORIZACION C
            $TFcNomAut = $request->input('TFcNomAut'); //Nombre de Quien Autoriza C
            $SccEmp = $request->input('SccEmp'); //Empresa C
            $ClaPro = $request->input('ClaPro'); //Clase de Procedimiento C
            $TFVAPU = $request->input('TFVAPU'); //Valor a Pagar X el Usuario N
            $TFIPSENT = $request->input('TFIPSENT'); //IPS de Entrada N
            $TFCoMt = $request->input('TFCoMt'); //Codigo del medico tratante C
            $TFEsMt = $request->input('TFEsMt'); //Especialidad de medico tratante N
            $TFCODPAQ = $request->input('TFCODPAQ'); //Codigo paquete C
            $TFTiDocAc = $request->input('TFTiDocAc'); //Tipo Docuemento Acompa􀀁ante C
            $TFParAc = $request->input('TFParAc'); //Parentesco de Acompa􀀁ante C
            $MICodI = $request->input('MICodI'); //C􀀁digo I.P.S. N
            $SOFchVIni = $request->input('SOFchVIni'); //SOAT Fecha Inicial Vigencia D
            $SOFchVFin = $request->input('SOFchVFin'); //SOAT Fecha fin vigencia poliza D
            $SONomCnd = $request->input('SONomCnd'); //SOAT Nombre Conductor C
            $SOMNroReg = $request->input('SOMNroReg'); //SOAT Nro Reg Med Firma Cert Di C
            $SOInfEC = $request->input('SOInfEC'); //SOAT Informe Evento Catastr􀀁fico L
            $TFCMEg = $request->input('TFCMEg'); //C􀀁digo M􀀁dico Egreso C
            $TFCMAD = $request->input('TFCMAD'); //Usuario que Autoriza Descuento C

            //DATOS PARA INGRESOS
            $IngFecAdm = Carbon::now('America/Bogota')->format('Ymd H:i:s'); //Fecha Adminisi􀀁n fecha y hora actual
            $IngNit = $request->input('IngNit'); //Nit-contrato C
            $IngEntDx = $request->input('IngEntDx'); //Dx. Entrada C
            $IngUsrReg = $request->input('IngUsrReg'); //Usuario que Registra Ingreso C
            $IngIPS = $request->input('IngIPS'); //Cod IPS Ingreso N
            $IngEntDx2 = $request->input('IngEntDx2'); //2do Diagnostico de Entrada C
            $IngNmResp = $request->input('IngNmResp'); //Primer Nombre del Responsable del Paciente C
            $IngNmResp2 = $request->input('IngNmResp2'); //Segundo Nombre del Responsable del Paciente C
            $IngDocResp = $request->input('IngDocResp'); //Documeto Responsable C
            $IngTDoResp = $request->input('IngTDoResp'); //Tipo de documento Responsable C
            $IngParResp = $request->input('IngParResp'); //Parentesco del Responsable iniciales C conyuge H hijo
            $IngDirResp = $request->input('IngDirResp'); //Direccion del Responsable C
            $IngTelResp = $request->input('IngTelResp'); //Telefonos del Responsable C
            $MPCodP = $request->input('MPCodP'); //C􀀁digo Pabell􀀁n N
            $MPNumC = $request->input('MPNumC'); //N􀀁mero de Cama C
            $IngDoAco = $request->input('IngDoAco'); //Documento de Acompa􀀁ante N
            $IngNoAc = $request->input('IngNoAc'); //Nombre de Acompa􀀁ante C
            $IngTeAc = $request->input('IngTeAc'); //Telefono de Acompa􀀁ante C
            $IngTeTrR = $request->input('IngTeTrR'); //Telefono trabajo de responsable C
            $IngEmTrR = $request->input('IngEmTrR'); //Empresa Trabajo Responsable C
            $IngDptRe = $request->input('IngDptRe'); //Departamento residencia de responsable C
            $IngMunRe = $request->input('IngMunRe'); //Municipio de residencia responsable N
            $IngCoMt = $request->input('IngCoMt'); //Codigo del medico tratante C
            $IngEsMt = $request->input('IngEsMt'); //Especialidad del medico tratante N
            $IngApRes = $request->input('IngApRes'); //Primer Apellido Responsable C
            $IngApRes2 = $request->input('IngApRes2'); //Segundo Apellido Responsable C
            $IngCtvAc = $request->input('IngCtvAc'); //Consecutivo de Accidente N
            $IngDxCli = $request->input('IngDxCli'); //Diagnostico Clinico C
            $IngDerObs = $request->input('IngDerObs'); //Derechos del paciente L
            $IngAtnAct = $ClaPro; //Atenci􀀁n actual de admision C
            $IngCauE = $request->input('IngCauE'); //Causa Externa N
            $IngResExe = $request->input('IngResExe'); //Contrato Responsable Exedente C
            $IngHosTTo = $request->input('IngHosTTo'); //Hospitalizacion Tratamiento N
            $IngIndCap = $request->input('IngIndCap'); //Indicador de admisi􀀁n de capitaci􀀁n C
            $IngIPSAtn = $request->input('IngIPSAtn'); //IPS de Atenci􀀁n
            $IngSoAdTr = $request->input('IngSoAdTr'); //Tipo Solicitud Env􀀁o Admisi􀀁n/Traslado C
            $IngMeSAT = $request->input('IngMeSAT'); //M􀀁dico registra env􀀁o solicitud Admisi􀀁n/Traslado C
            $IngEsSAT = $request->input('IngEsSAT'); //Especialidad M􀀁dico registra env􀀁o solicitud Admisi􀀁n/Traslado N
            $IngFSAdTr = $request->input('IngFSAdTr'); //Fecha Hora registro Solicitud Env􀀁o Admisi􀀁n/Traslado T
            $IngIndReN = $request->input('IngIndReN'); //Indicador de registro de datos de parto en HC C
            $IngIndAte = $request->input('IngIndAte'); //Indica si el paciente se encuentra en atenci􀀁n C
            $IngNumTur = $request->input('IngNumTur'); //Numero Turno Triage C
            $IngFecTur = $request->input('IngFecTur'); //Fecha Turno T
            $INGCODPAQ = $request->input('INGCODPAQ'); //Codigo Paquete C
            $IngGruPo = $request->input('IngGruPo'); //Codigo Grupo Poblacional C
            $IngCarnet = $request->input('IngCarnet'); //Carnet C
            $IngTiDoAc = $request->input('IngTiDoAc'); //Tipo de Documento Acompa􀀁ante C
            $IngParAc = $request->input('IngParAc'); //Parentesco de Acompa􀀁ante C
            $INGREIPAC = $request->input('INGREIPAC'); //Indicador de reingreso de paciente
            $IngCodEnt = $request->input('IngCodEnt'); //: c􀀁digo del departamento C
            $IngMEdEsp =  $request->input('IngMEdEsp'); //espacialidad medico ingreso

            $IngFecMoP = Carbon::now('America/Bogota')->format('Ymd H:i:s');
            $IngUrgObs = $request->input('IngUrgObs'); //ingreso observacion 
            $IngFecMoP = Carbon::now('America/Bogota')->format('Ymd H:i:s');
            $IngNumCit = $request->input('IngNumCit') ?? "";
            $TmCtvAct = 0;

            if($ClaPro == 5){
                $IngCauE = 0;
                $TFEsMI = 0;
            }

            if($IngDoAco < 1){
                $IngDoAco = 0;
            }

            if($IngMunRe < 1){
                $IngMunRe = 0;   
            }


            try{

                //Validacion del parametro
                $flagHvt = DB::table('MAEFLAG')
                    ->where('FlgCod', 2)->first();
                
                if($flagHvt != null && $flagHvt->FlgEst == 1){
                    //Todas las variables del request son obligatorias
                    try{
                        $variables = [
                            'ClaPro' => [$ClaPro, $ClaPro],
                            'MPTDoc' => [$MPTDoc, 'Tipo de documento'],
                            'MPCedu' => [$MPCedu, 'Documento'],
                            'IngNit' => [$IngNit, 'Contrato'],
                            'TFIPSENT' => [$TFIPSENT, 'IPS de Entrada'],
                            'IngCauE' => [$IngCauE, 'Causa externa'],
                            'IngEsMt' => [$IngEsMt, 'Especialidad médico tratante'],
                            'IngCoMt' =>  [$IngCoMt, 'Médico tratante'],
                            'IngMEdEsp' => [$IngMEdEsp, 'Especialidad médico de ingreso'],
                            'MPCodP' => [$MPCodP, 'Pabellon'],
                            'TFViaI' => [$TFViaI, 'Via de ingreso'],
                            'IngMEdEsp' => [$IngMEdEsp, 'Especialidad médico de ingreso'],
                            //Datos del responsable
                            'IngNmResp' => [$IngNmResp, 'Primer nombre del responsable'],
                            'IngApRes' => [$IngApRes, 'Primer apellido del responsable'],
                            'IngDocResp' => [$IngDocResp, 'Documento del responsable'],
                            'IngTDoResp' => [$IngTDoResp, 'Tipo de documento del responsable'],
                            'IngParResp' => [$IngParResp, 'Parentesco del responsable'],
                            'IngDirResp' => [$IngDirResp, 'Direccion del responsable'],
                            'IngTelResp' => [$IngTelResp, 'Telefono del responsable'],
                            'IngTeTrR' => [$IngTeTrR, 'Telefono trabajo del responsable'],
                            'IngEmTrR' => [$IngEmTrR, 'Empresa del responsable'],
                            'IngDptRe' => [$IngDptRe, 'Departamento del responsable'],
                            'IngMunRe' => [$IngMunRe, 'Municipio del responsable'],
                            //Datos del acompañante
                            'TFTiDocAc' => [$TFTiDocAc, 'Tipo de documento del acompañante'],
                            'IngDoAco' => [$IngDoAco, 'Documento del acompañante'],
                            'IngNoAc' => [$IngNoAc, 'Nombre del acompañante'],
                            'TFParAc' => [$TFParAc, 'Parentesco del acompañante'],
                            'IngTeAc' => [$IngTeAc, 'Telefono del acompañante'],
                            'IngHosTTo' => [$IngHosTTo, 'Tratamiento'],
                        ];

                        foreach ($variables as $valor) {
                            if ($valor[0] === null) {
                                // Aquí puedes manejar el caso de una variable en null
                                throw new \Exception("El campo " .$valor[1]. " es requerido");
                            }
                        }

                    }catch (\Exception $e) {
                        return response()->json([
                            'message' => $e->getMessage()
                        ]);
                    }
                     
                }else{

                    if(!$IngHosTTo){
                        throw new \Exception("Campo Tratamiento es requerido");   
                    }
    
                    if(!$IngNit){
                        throw new \Exception("Contrato requerido");   
                    }
    
                    if(!$IngCauE){
                        throw new \Exception("Causa externa requerida");   
                    }
    
                    if(!$IngEsMt){
                        throw new \Exception("Especialidad médico tratante requerida");   
                    }
    
                    if(!$IngCoMt){
                        throw new \Exception("Médico tratante requerido");   
                    }
    
                    if(!$IngMEdEsp){
                        throw new \Exception("Especialidad médico de ingreso de ingreso");   
                    }
    
                    if(!$MPCodP){
                        throw new \Exception("El pabellon es obligatorio");   
                    }
    
                    if(!$TFViaI ){
                        throw new \Exception("La via de ingreso es obligatoria");   
                    }
                }

                //validacion estado del paciente
                $estadoPaciente = DB::table('CAPBAS')
                    ->select('MPCedu', 'MPTDoc', 'MPEstPac')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)->first();
                    
                if($estadoPaciente->MPEstPac == 'N'){
                    throw new \Exception("El paciente se encuentra inactivo");
                }


                $vigCnc = DB::table('MAECTOS')
                    ->select('MENNIT', 'MeCfcha1', 'CtoFchIni', 'MeCnsCnt')
                    ->where('MENNIT', $IngNit)
                    ->where('CtoFchIni','<=', $fechAct)
                    ->where('MeCfcha1','>=', $fechAct)->first();
                

                $vigencia = DB::table('MAEEMP31')
                    ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                    ->where('MENNIT', $IngNit)
                    ->where('MEPPVig','<=', $fechAct)
                    ->orderByDesc('MEPPVig')->first();
            
                if ($vigencia == null){
                    throw new \Exception("No hay vigencia para el contrato " .$IngNit);
                }

                //consulta el siguiennte consecutivo de ingreso
                $nextIngreso = DB::table('CAPBAS')
                    ->select('MPTDoc', 'MPCedu', 'MpCtvoAtn', 'MpCtvoActe', 'MPGrEs', 'MPSexo')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)->first();

                $nextCscIng = $nextIngreso->MpCtvoAtn;

                if($nextCscIng == 0){
                    $nextCscIng = 1;
                }
    
                $ingresoAn = DB::table('INGRESOS')
                    ->select('MPCedu', 'MPTDoc', 'IngCsc')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->where('IngCsc', $nextCscIng)->first();
                
                
                if($ingresoAn != null){
                    
                    $ultimoCsv = DB::table('INGRESOS')
                        ->select('IngCsc')
                        ->where('MPCedu', $MPCedu)
                        ->where('MPTDoc', $MPTDoc)
                        ->orderByDesc('IngCsc', 'desc')->first();
                    
                    $nextCscIng = $ultimoCsv->IngCsc + 1;
                    
                }

                //VALIDACIONES INICIALES ANTES DE HACER EL INSERT O UPDATE

                //Entrada hospi sin cama
                if($ClaPro == 2 && $MPNumC == ''){
                    throw new \Exception('Admisión hospitalaria debe tener cama asignada');
                }

                //Validacion del dx
                $diagnostico = DB::table('MAEDIA')
                    ->select('DMCodi', 'DMSEXO', 'DgnTpoEFn', 'DgnTpoEIn', 'DMEDADFI', 'DMEDADIN', 'DgnEst', 'DMNomb')
                    ->where('DMCodi', $IngEntDx)->first();
                


                //Validacion de cama
                if($MPNumC != ''){

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

                //Validacion paciente acostado en otra cama en un ingreso diferente
                $pteAcostado = DB::table('TMPFAC')
                    ->leftJoin('MAEPAB', 'MAEPAB.MPCodP','=','TMPFAC.TFcCodPab')
                    ->select('TMPFAC.TmCtvIng', 'TMPFAC.TFTDoc', 'TMPFAC.TFCedu', 'TMPFAC.TFcCodCam', 'TMPFAC.SccEmp',
                            'TMPFAC.SCCCod', 'MAEPAB.MPCLAPRO', 'TMPFAC.TFcCodPab')
                    ->where('TMPFAC.TFCedu',$MPCedu)
                    ->where('TMPFAC.TFTDoc', $MPTDoc)
                    ->where('TMPFAC.TmCtvIng','<>',$nextCscIng)->get();

                $acostado = DB::table('MAEPAB1')
                    ->where('MPUced', $MPCedu)
                    ->where('MPUDoc', $MPTDoc)
                    ->first();

                if($acostado != null){
                    throw new \Exception('El paciente con cedula '.$MPCedu.' esta acostado en otro ingreso');
                }

                //SI SE AUMENTA EL CONSECUTIVO DE ACCIDENTE
                if($indCtvoActe == 1){

                    DB::table('CAPBAS')
                        ->where('MPCedu', $MPCedu)
                        ->where('MPTDoc', $MPTDoc)
                        ->update([
                            'MpCtvoActe' => $nextIngreso->MpCtvoActe + 1
                        ]);
                    $TmCtvAct  = $nextIngreso->MpCtvoActe + 1;
                }else{
                    $TmCtvAct  = $request->input('TmCtvActEs');//se envia el consecutivo de accidente

                    if($TmCtvAct > 0){
                        //Busca los ingresos facturados con ese consecutivo para validacion del tope de atencion
                        $sumaVFac = DB::table('MAEATE')
                            ->selectRaw("SUM(MATotF) as total")
                            ->where('MPCedu', $MPCedu)
                            ->where('MPTDoc', $MPTDoc)
                            ->where('MaCtvAct', $TmCtvAct)->first();
                        
                        //Buscar el tope de atencion de ese contrtato
                        $valorFac = DB::table('MAECTOS')
                            ->select('MESalMi')
                            ->where('MENNIT', $IngNit)->first();
                        
                        
                        //Comparamos los valores 
                        if($sumaVFac != null && $valorFac != null){
                            if($sumaVFac->total >= $valorFac->MESalMi){
                                throw new \Exception('El paciente supera el tope de atención');
                            }
                        }
                    }
                }

                //insert en TMPFAC
                DB::Connection('sqlsrv')
                ->insert("INSERT INTO [TMPFAC]
                            ([TFCedu], [TFTDoc], [TmCtvIng], [TFMENi], [TFFchI],
                            [TFFCES], [TFHorI], [TFDi1I], [TFDi2I], [TFDi1S], [TFDi3I], [SONume],
                            [SONitAsg], [SOVePl], [SOVeMa], [SOVeTi], [SOVeMo], [SOVeCl], [SOCndAcc],
                            [SOSitAcc], [SOFchAcc], [SOCodD], [SOCodM], [SORulUrb], [SOInfAcc],
                            [SOIndAsg], [SOTpICnd], [SOCedCnd], [SODirCnd], [SoCodDCnd], [SOCodMCnd],
                            [SoTelCnd], [SONomEmp], [SOMCodFCD], [SONomSuc], [SOTpoEC], [SODesEC],
                            [SOCodDE], [SOCodME], [SODecla], [SOTiDoDc], [SODocDcl], [SOlugExp],
                            [SoLgExpCn], [SCCCod], [TFViaI], [TFCoMI], [TFEsMI], [MPFEsH], [TFNoRe],
                            [TFNoRe2], [TFTeRe], [TFTiRe], [TFCauE], [TFDi2S], [TFDi3S], [TFUIng],
                            [TFNMAU], [TFcNomAut], [TFcCodCam], [TFcCodPab], [SccEmp], [ClaPro],
                            [TFVAPU], [TmCtvAct], [TFDocRep], [TFTDoRep], [TFDirRep], [TFIPSENT],
                            [TFDocAco], [TFNoAc], [TFTeAc], [TFTeTrRe], [TFEmTrRe], [TFDptRes],
                            [TFMunRes], [TFCoMt], [TFEsMt], [TFApeRes], [TFApeRes2], [ClaproI],
                            [TFCoCamI], [TFCODPAQ], [TFTiDocAc], [TFParAc], [MICodI], [SOFchVIni],
                            [SOFchVFin], [SONomCnd], [SOMNroReg], [SOInfEC], [TFEsMS], [TFCMEg],
                            [TFEstS], [TFMotS], [TFCMAD], [TFSeGe], [TFCoPr], [TFTiPa], [TFTiAn],
                            [TFFchM], [TFCaMu], [TFQuia], [TFDiCp], [TFHorO], [TFFchS], [TFVNPU],
                            [TFUSal], [TFUscP], [TFNrCerD], [TFUlcAC], [TFEstP], [TFTotP], [TFTotS],
                            [TFTotF], [TFValS], [TFVaAb], [TFVPaU], [TFVDsc], [TFUscS], [TFUcsA], [TFUcsN],
                            [TFTpeAut], [TFVlrAut], [TFCpgPgo], [TFCpgLqd], [TFcCodCns], [TFVlrImpt],
                            [TFIndCrt], [TFCnTQx], [ReFatMat], [ReFatNum], [TmEdoCue], [TFObsFac],
                            [TFAdmFac], [TFUsuFac], [TFVPOCo], [TFVlrTIv], [TFDI4S], [TFNUMPGP])
                        VALUES (
                            '".$MPCedu."' ,  '".$MPTDoc."' ,  $nextCscIng,  '".$IngNit."' ,  '".$TFFchI."' , '".$TFFCES."'  ,
                            '".$TFHorI."' ,  '".$IngEntDx."' ,  '".$IngEntDx2."' ,  ' ' ,  '".$IngDxCli."' ,  '".$SONume."' ,  '".$SONitAsg."' ,
                            '".$SOVePl."' ,  '".$SOVeMa."' ,  '".$SOVeTi."' ,  '".$SOVeMo."' ,  '".$SOVeCl."' ,  '".$SOCndAcc."' ,
                            '".$SOSitAcc."' ,  '".$SOFchAcc."' ,  '".$SOCodD."' ,  $SOCodM ,  '".$SORulUrb."' ,  '".$SOInfAcc."' ,
                            '".$SOIndAsg."' ,  '".$SOTpICnd."' ,  '".$SOCedCnd."' ,  '".$SODirCnd."' ,  '".$SoCodDCnd."' ,  $SOCodMCnd,
                            '".$SoTelCnd."' ,  '".$SONomEmp."' ,  '".$SOMCodFCD."' ,  '".$SONomSuc."' ,  $SOTpoEC,  '".$SODesEC."',
                            '".$SOCodDE."' ,  $SOCodME,  '".$SODecla."' ,  '".$SOTiDoDc."' ,  '".$SODocDcl."' ,  '".$SOlugExp."' ,
                            '".$SoLgExpCn."' ,  '".$SCCCod."' ,  $TFViaI ,  '".$TFCoMI."' ,  $TFEsMI ,  '17530101' ,
                            '".$TFNoRe."' ,  '".$TFNoRe2."' ,  '".$TFTeRe."' ,  '".$TFTiRe."' ,  $IngCauE ,  ' ' ,
                            ' ' ,  '".$TFUIng."' ,  '".$TFNMAU."' ,  '".$TFcNomAut."' , '".$MPNumC."'  ,  $MPCodP,
                            '".$SccEmp."' ,  $ClaPro , $TFVAPU,  $TmCtvAct ,  '".$IngDocResp."' ,  '".$IngTDoResp."' ,
                            '".$IngDirResp."' ,  $TFIPSENT ,  $IngDoAco ,  '".$IngNoAc."' ,  '".$IngTeAc."' ,  '".$IngTeTrR."' ,
                            '".$IngEmTrR."' ,  '".$IngDptRe."' ,  ".$IngMunRe.",  '".$TFCoMt."' ,  ".$TFEsMt.",  '".$IngApRes."' ,
                            '".$IngApRes2."' ,  $ClaPro ,  '".$MPNumC."' ,  '".$TFCODPAQ."' ,  '".$TFTiDocAc."' ,  '".$TFParAc."' ,
                            convert(int, $MICodI), convert( DATETIME, '".$SOFchVIni."', 112 ),
                            convert( DATETIME, '".$SOFchVFin."', 112 ), '".$SONomCnd."', '".$SOMNroReg."', '".$SOInfEC."', ' ',
                            '".$TFCMEg."', convert(int, 0), ' ', '".$TFCMAD."', convert(int, 0), '', '', '',
                            convert( DATETIME, '17530101', 112 ), '', '', '',
                            convert(int, 0), convert( DATETIME, '17530101', 112 ),
                            convert(int, 0), '', convert(int, 0), '', convert(int, 0),
                            convert(int, 0), convert(int, 0), convert(int, 0),
                            convert(int, 0), convert(int, 0), convert(int, 0),
                            convert(int, 0), convert(int, 0), convert(int, 0),
                            convert(int, 0), convert(int, 0), convert(int, 0),
                            convert(int, 0), '', '', convert(int, 0), convert(int, 0),
                            '', convert(int, 0), convert(int, 0), convert(int, 0),
                            '', '', '', '', convert(int, 0), convert(int, 0), '', '')"); 

                //INSERT INGRESOS
                DB::Connection('sqlsrv')
                ->insert("INSERT INTO [INGRESOS]
                            ([MPCedu], [MPTDoc], [ClaPro], [IngCsc], [IngFecAdm], [IngNit], [IngEntDx],
                            [IngUsrReg], [IngIPS], [IngEntDx2], [IngNmResp], [IngNmResp2], [IngDocResp],
                            [IngTDoResp], [IngParResp], [IngDirResp], [IngTelResp], [IngMedSal], [IngMEdEsp],
                            [IngDxSal1], [IngDxSal2], [IngDxSal3], [MPCodP], [MPNumC], [IngDoAco], [IngNoAc],
                            [IngTeAc], [IngTeTrR], [IngEmTrR], [IngDptRe], [IngMunRe], [IngCoMt], [IngEsMt],
                            [IngApRes], [IngApRes2], [IngCtvAc], [IngDxCli], [IngInSlC], [IngDerObs], [IngAtnAct],
                            [IngUlcMoP], [IngCauE], [IngTip], [IngResExe], [IngHosTTo], [IngIndCap], [IngIPSAtn],
                            [IngSoAdTr], [IngMeSAT], [IngEsSAT], [IngFSAdTr], [IngIndReN], [IngIndAte], [IngNumTur],
                            [IngFecTur], [INGCODPAQ], [IngAteEs], [IngGruPo], [IngCarnet], [IngTiDoAc], [IngParAc],
                            [INGREIPAC], [IngCodEnt], [IngFac], [IngDoc], [IngHsp], [IngExtEst], [IngEstSld],
                            [IngFecEgr], [IngSalDx], [IngDxTip], [IngNotObl], [InsIpsSal], [IngFchM], [IngCauM],
                            [IngMedDef], [InCerDef], [IngDxTip1], [IngDxTip2], [IngComp], [IngMotSal], [IngHorObs],
                            [IngUsrSal], [IngCodPEg], [IngAteEgr], [IngNumCit], [IngCscN], [IngSege], [IngCoPr],
                            [IngTiPa], [IngTiAn], [IngQuiA], [IngUCtvEp], [IngCodCEg], [IngUsuAnu], [IngFchAnu],
                            [IngObsAnu], [IngReaUrg], [IngEmpPlt], [IngSedPlt], [IngCodPlt], [IngCnsPlt],
                            [IngFeHAtU], [IngIndApB], [IngNroAn1], [IngNroAn2], [AlumCod], [ProTerCod], [IngReligi],
                            [IngAcudie], [IngDesAlm], [IngIndUF], [IngActAla], [IngObsAla], [IngCodMed], [IngEsDepo],
                            [IngRieCod], [IngRiCnDe], [IngRiCoDe], [IngDxRie], [INGSALML], [INGDXTIP3], [INGDXSAL4],
                            [INGDEPPLA], [IngIndCamT])
                        VALUES (
                            '".$MPCedu."' ,  '".$MPTDoc."' ,  $ClaPro , $nextCscIng,  '".$IngFecAdm."' ,  '".$IngNit."' ,  '".$IngEntDx."',
                            '".$IngUsrReg."' ,  $IngIPS,  '".$IngEntDx2."' ,  '".$IngNmResp."' ,  '".$IngNmResp2."' ,  '".$IngDocResp."' ,
                            '".$IngTDoResp."' ,  '".$IngParResp."' ,  '".$IngDirResp."' ,  '".$IngTelResp."' ,  '' ,  '".$IngMEdEsp."' ,
                            '' ,  '' ,  '' ,  $MPCodP ,  '".$MPNumC."' ,  $IngDoAco ,
                            '".$IngNoAc."' ,  '".$IngTeAc."' ,  '".$IngTeTrR."' ,  '".$IngEmTrR."' ,  '".$IngDptRe."' ,  $IngMunRe ,
                            '".$IngCoMt."' ,  $IngEsMt ,  '".$IngApRes."' ,  '".$IngApRes2."' ,  $TmCtvAct ,  '".$IngDxCli."' ,
                            'N' ,  '".$IngDerObs."' ,  '".$ClaPro."' ,  1 ,  $IngCauE ,  'GN' ,
                            '".$IngResExe."' ,  $IngHosTTo ,  '".$IngIndCap."' ,  '".$IngIPSAtn."' ,  '".$IngSoAdTr."' ,  '".$IngMeSAT."' ,
                            $IngEsSAT ,  '".$IngFSAdTr."' ,  '".$IngIndReN."' ,  '".$IngIndAte."' ,  '".$IngNumTur."' ,  '".$IngFecTur."' ,
                            '".$INGCODPAQ."' ,  '".$nextIngreso->MPGrEs."' ,  '".$IngGruPo."' ,  '".$IngCarnet."' ,  '".$IngTiDoAc."' ,  '".$IngParAc."' ,
                            '".$INGREIPAC."' ,  '".$IngCodEnt."'  , convert(int, 0), convert(int, 0),
                            '', convert(int, 0), convert(int, 0),
                            convert( DATETIME, '17530101', 112 ), '',
                            convert(int, 0), '', '', convert( DATETIME, '17530101', 112 ),
                            '', '', '', convert(int, 0), convert(int, 0), '', '',
                            convert(int, 0), '', convert(int, 0), '',  '".$IngNumCit."' ,convert(int, 0),

                            convert(int, 0), '', '', '', '',
                            convert(int, 0), '', '', convert( DATETIME, '17530101', 112 ),
                            '', '', '', '', '', convert(int, 0),
                            convert( DATETIME, '17530101', 112 ),
                            '', convert(int, 0), convert(int, 0),
                            '', '', '', '', '',
                            '', '', '', '', '',
                            convert(int, 0), convert(int, 0), convert(int, 0),
                            '', '', convert(int, 0), '', '', '')");
                
                //INSERT INGRESOMP
                $movimiento = DB::table('INGRESOMP')
                            ->select('IngCtvMoP', 'MPCedu', 'MPTDoc')
                            ->where('MPCedu',$MPCedu)
                            ->where('MPTDoc',$MPTDoc)
                            ->where('ClaPro',$ClaPro)
                            ->orderBy('IngCtvMoP', 'desc')->first();
                if($movimiento != NULL ){
                    $cscMov = $movimiento->IngCtvMoP + 1;
                }else{
                    $cscMov = 1;
                }

                DB::Connection('sqlsrv')
                ->insert("INSERT INTO [INGRESOMP]
                            ([MPCedu], [MPTDoc], [ClaPro], [IngCsc], [IngCtvMoP], [IngCodPab],
                            [IngCodCam], [IngFecMoP], [IngUsuMoP], [IngUrgObs], [IngFecMoE])
                        VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$ClaPro."' , $nextCscIng ,  1 ,
                            $MPCodP,  '".$MPNumC."' ,  '".$IngFecMoP."' ,  '".$TFUIng."' ,  $IngUrgObs , convert( DATETIME, '17530101', 112 ))");
                
                //UPDATE CAPBAS 
                DB::table('CAPBAS')
                    ->where('MPCedu', $MPCedu)
                    ->where('MPTDoc', $MPTDoc)
                    ->update([
                        'MpCtvoAtn' => $nextCscIng + 1
                    ]);

                
                //ACOSTAR EL PACIENTE CUANDO ENTRA CAMA
                if($MPNumC != ''){
                    //Acuesta el paciente en la cama
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
                            'MPCtvIn' => $nextCscIng,
                            'MPDisp' => 1,
                            'MPFchI' => Carbon::now('America/Bogota')->format('Ymd'),
                            'MPUdx' => $IngEntDx
                        ]);

                    DB::table('MAEPAB11')
                        ->insert([
                            ['MPCodP' => $MPCodP, 'MPNumC' =>  $MPNumC, 'HisCamCtv' => $ultCtvoCamaFn->MpUltCtvo + 1, 'HisCamEdo' => 'O',
                            'HisCamFec' => Carbon::now('America/Bogota')->format('Ymd') ,
                            'HisCamHor' => Carbon::now('America/Bogota')->format('H:i:s') ,
                            'MPCedu' => $MPCedu, 'MPTDoc' => $MPTDoc, 'HisCamUsu' => $IngUsrReg, 'HisCnsIng' => $nextCscIng]
                        ]);
                }

                DB::table('LOGINGR')->insert([
                    'MPCedu' => $MPCedu,
                    'MPTDoc' => $MPTDoc,
                    'IngCsc' => $nextCscIng,
                    'IngFec' => Carbon::now('America/Bogota')->format('Ymd H:i:s'), // Utiliza la función now() para obtener la fecha y hora actual
                    'UsrIng' => $IngUsrReg,
                ]);



                $retorno = [
                    'status' => 200,
                    'message' => 'Admisión creada correctamente para el paciente '.$MPCedu.' en el ingreso ' .$nextCscIng,
                    'data' => [
                        'ingreso' => $nextCscIng,
                        'accidente' => $TmCtvAct
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
        else {
           return  $respuesta = [
              'status' => $result["status"],
              'message' => $result["message"]
           ];
        }
    }

    //Funcion para ingresar datos de accidente de transito
    public function ingresaAccidente(Request $request){

        $mensaje = "";

        $indEdicion = $request->indEdicion; //Indica si se va a editar el furips o no 1 si es edicion 0 si es nuevo

        $EmpCodA = $request->input('EmpCodA'); //Codigo de la empresa 
        $MCDptoA = $request->input('MCDptoA'); //Codigo del departamento
        $TFCedu = $request->input('TFCedu'); //Cedula del paciente N
        $TFTDoc = $request->input('TFTDoc'); //Tipo de documento del paciente C
        $TmCtvIng = $request->input('TmCtvIng'); //Consecutivo de ingreso N
        $DatCscTra = 0;
        
        //Datos del formulario 
        $SONomCndN1 = trim($request->input('SONomCndN1')); //Primer Nombre conductor 
        $SONomCndN2 = trim($request->input('SONomCndN2')); //Segundo Nombre conductor
        $SONomCndA1 = trim($request->input('SONomCndA1')); //Primer Apellido conductor
        $SONomCndA2 = trim($request->input('SONomCndA2')); //Segundo Apellido conductor
        $SOTpICnd = $request->input('SOTpICnd'); //Tipo de identificacion conductor 
        $SOCedCnd = $request->input('SOCedCnd'); //Cedula conductor
        $SoLgExpCn = $request->input('SoLgExpCn'); //Lugar de expedicion de la cedula del conductor 
        $SODirCnd = trim($request->input('SODirCnd')); //Direccion de conductor
        $SoCodDCnd = $request->input('SoCodDCnd'); //Codigo del departamento del conductor
        $SOCodMCnd = $request->input('SOCodMCnd'); //Codigo del municipio del conductor
        $SoTelCnd = $request->input('SoTelCnd'); //Telefono del conductor
        $SODecla = $request->input('SODecla'); //Nombre del declarante
        $SOTiDoDc = $request->input('SOTiDoDc'); //Tipo de documento del declarante
        $SODocDcl = $request->input('SODocDcl'); //Documento del declarante
        $SOlugExp = $request->input('SOlugExp'); //Lugar de expedicion del declarante
        $SOCndAcc = $request->input('SOCndAcc'); //Condicion del accidentado
        $SOSitAcc = $request->input('SOSitAcc'); //Sitio del accidente
        $SOFchAcc = str_replace('-','',$request->input('SOFchAcc')). ' '. $request->horAcc; //Fecha del accidente
        $SOCodD = $request->input('SOCodD'); //Codigo del departamento del accidente
        $SOCodM = $request->input('SOCodM'); //Codigo del municipio del accidente
        $SORulUrba = $request->input('SORulUrb'); //Indicador rural:R/urbana:U 
        $SOInfAcc = $request->input('SOInfAcc'); //Informe del accidente
        $SOIndAsg = $request->input('SOIndAsg'); //Indicador de asegurado o no S/N
        $SOVeMa = $request->input('SOVeMa'); //Vehiculo marca
        $SOVePl = $request->input('SOVePl'); //Vehiculo placa
        $SOVeTi = $request->input('SOVeTi'); //Vehiculo tipo
        $SONitAsg = $request->input('SONitAsg'); //Nit aseguradora
        $SONomSuc = $request->input('SONomSuc'); //Nombre sucursal
        $TmCtvAct = $request->input('TmCtvAct'); //Consecutivo de accidente
        $DATNUMSIR = $request->input('DATNUMSIR'); //Numero de siras

        $DaPrApPr = trim($request->input('DaPrApPr')); //Primer apellido del propietario
        $DaSeApPr = trim($request->input('DaSeApPr')); //Segundo apellido del propietario
        $DaPrNoPr = trim($request->input('DaPrNoPr')); //Primer nombre del propietario
        $DaSeNoPr = trim($request->input('DaSeNoPr')); //Segundo nombre del propietario
        $DaTiDoPr = $request->input('DaTiDoPr'); //Tipo de documento del propietario
        $DaNuDoPr = $request->input('DaNuDoPr'); //Numero de documento del propietario
        $DaCodDPr = $request->input('DaCodDPr'); //Codigo del departamento del propietario
        $DaCodMPr = $request->input('DaCodMPr'); //Codigo del municipio del propietario
        $DaLugExp = $request->input('DaLugExp'); //Lugar de expedicion del propietario
        $DaDirPr = $request->input('DaDirPr'); //Direccion del propietario
        $DaTelPr = $request->input('DaTelPr'); //Telefono del propietario
        $DaPrApCo = trim($request->input('DaPrApCo')); //Primer apellido del conductor
        $DaSeApCo = trim($request->input('DaSeApCo')); //Segundo apellido del conductor
        $DaPrNoCo = trim($request->input('DaPrNoCo')); //Primer nombre del conductor
        $DaSeNoCo = trim($request->input('DaSeNoCo')); //Segundo nombre del conductor
        $DaTipSer = $request->input('DaTipSer'); //Tipo de servicio vehiculo
        $DaIntAut = $request->input('DaIntAut'); //Indicador de autoridad S/N
        $DaCobExc = $request->input('DaCobExc'); //Cobro excedente S/N
        $DaNumPol = $request->input('DaNumPol'); //Numero de poliza
        $DaFecIniV = str_replace('-','',$request->input('DaFecIniV')); //Fecha inicio vigencia poliza
        $DaFecFinV = str_replace('-','',$request->input('DaFecFinV')); //Fecha fin vigencia poliza
        $DaCodB = $request->input('DaCodB'); //Codigo del barrio
        $DaNRdGlA = $request->input('DaNRdGlA'); //Numero de radicado anterior glosa
        $DaRadGlo = $request->input('DaRadGlo'); //Radicado glosa
        $DaNroTFol = $request->input('DaNroTFol'); //Numero de folios de la factura


        try{

            /* if($SOIndAsg == 'S'){
                if($SONitAsg == null){
                    throw new \Exception("El nit de la aseguradora es obligatorio");
                }
                if($SONomSuc == null){
                    throw new \Exception("El nombre de la sucursal es obligatorio");
                }
                if($DaFecFinV == null){
                    throw new \Exception("La fecha fin de vigencia es obligatoria");
                }
                if($DaFecIniV == null){
                    throw new \Exception("La fecha inicio de vigencia es obligatoria");
                }

            }

            if($DaFecIniV){
                if($DaFecIniV > $DaFecFinV){
                    throw new \Exception("La fecha inicio de vigencia no puede ser mayor a la fecha fin de vigencia");
                }
                if($DaFecIniV > Carbon::now('America/Bogota')->format('Ymd')){
                    throw new \Exception("La fecha inicio de vigencia no puede ser mayor a la fecha actual");
                }
                if($DaFecFinV == null){
                    throw new \Exception("La fecha fin de vigencia es obligatoria");
                }
            }

            if($DaFecFinV){
                if($DaFecFinV < Carbon::now('America/Bogota')->format('Ymd')){
                    throw new \Exception("La fecha fin de vigencia no puede ser menor a la fecha actual");
                }
            } */


            $valida = DB::table('DATSOAT')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('CtvIng', $TmCtvIng)
                ->first();
            
            if($valida == null){
                $indEdicion = 0;
            }else{
                $indEdicion = 1;
            }


            //Validacion del parametro del FURIPS
            $doc = DB::table('DOCUCONA')
                ->select('DocCodA', 'EmpCodA', 'DocDscA', 'DocCscEmA', 'DocCscLA')
                ->where('EmpCodA', $EmpCodA)
                ->where('DocCodA', 'FUR')->first();
            
            if($doc == null){
                throw new \Exception("No está parametrizado el doc FURIPS");
            }

            //validacion de fecha de accidente menor a fecha y hora actual
            if($SOFchAcc > Carbon::now('America/Bogota')->format('Ymd H:i:s')){
                throw new \Exception("La fecha del accidente no puede ser mayor a la fecha actual");
            }


            $ctvoDoc = DB::table('DOCUCON1A')
                ->select('DocNumEsA', 'DocCodA', 'EmpCodA', 'DocNumAcA', 'DocNumBqA',
                        'DocFchReA', 'DocNumInA', 'DocNumFiA', 'DocConA', 'MCDptoA')
                ->where('EmpCodA', $EmpCodA)
                ->where('MCDptoA', $MCDptoA)
                ->where('DocCodA', 'FUR')
                ->where('DocConA', '>=', 1)->first();
            


            DB::table('TMPFAC')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)
                ->update([
                    'SONomCnd' => $SONomCndA1.' '.$SONomCndA2.' '.$SONomCndN1.' '.$SONomCndN2,
                    'SOTpICnd' => $SOTpICnd,
                    'SOCedCnd' => $SOCedCnd,
                    'SoLgExpCn' => $SoLgExpCn,
                    'SODirCnd' => $SODirCnd,
                    'SoCodDCnd' => $SoCodDCnd,
                    'SOCodMCnd' => $SOCodMCnd,
                    'SoTelCnd' => $SoTelCnd,
                    'SODecla' => $SODecla,
                    'SOTiDoDc' => $SOTiDoDc,
                    'SODocDcl' => $SODocDcl,
                    'SOlugExp' => $SOlugExp,
                    'SOCndAcc' => $SOCndAcc,
                    'SOSitAcc' => $SOSitAcc,
                    'SOFchAcc' => $SOFchAcc,
                    'SOCodD' => $SOCodD,
                    'SOCodM' => $SOCodM,
                    'SORulUrb' => $SORulUrba,
                    'SOInfAcc' => $SOInfAcc,
                    'SOIndAsg' => $SOIndAsg,
                    'SOVeMa' => $SOVeMa,
                    'SOVePl' => $SOVePl,
                    'SOVeTi' => $SOVeTi,
                    'SONitAsg' => $SONitAsg,
                    'SONomSuc' => $SONomSuc,
                    'TmCtvAct' => $TmCtvAct
                ]);

                            

            DB::table('DOCUCONA')
                ->where('EmpCodA', $EmpCodA)
                ->where('DocCodA', 'FUR')
                ->update([
                    'DocDscA' => 'ACCIDENTES DE TRANSITO'
                ]);
            
            if($ctvoDoc != null){
                DB::table('DOCUCON1A')
                    ->where('EmpCodA', $EmpCodA)
                    ->where('MCDptoA', $MCDptoA)
                    ->where('DocCodA', 'FUR')
                    ->update([
                        'DocNumAcA' => $ctvoDoc->DocNumAcA + 1,
                    ]);
                $DatCscTra = $ctvoDoc->DocNumAcA;
            }

            if($indEdicion === 0){

                DB::table('DATSOAT')
                    ->insert([
                        [
                            'MPCedu' => $TFCedu,
                            'MPTDoc' => $TFTDoc,
                            'CtvIng' => $TmCtvIng,
                            'DaSitAcc' => $SOSitAcc,
                            'DaInfAcc' => $SOInfAcc,
                            'DaFecAcc' => $SOFchAcc,
                            'DaPrApPr' => $DaPrApPr,
                            'DaSeApPr' => $DaSeApPr,
                            'DaPrNoPr' => $DaPrNoPr,
                            'DaSeNoPr' => $DaSeNoPr,
                            'DaTiDoPr' => $DaTiDoPr,
                            'DaNuDoPr' => $DaNuDoPr,
                            'DaCodDPr' => $DaCodDPr,
                            'DaCodMPr' => $DaCodMPr,
                            'DaLugExp' => $DaLugExp,
                            'DaDirPr' => $DaDirPr,
                            'DaTelPr' => $DaTelPr,
                            'DaPrApCo' => $DaPrApCo,
                            'DaSeApCo' => $DaSeApCo,
                            'DaPrNoCo' => $DaPrNoCo,
                            'DaSeNoCo' => $DaSeNoCo,
                            'DaTipSer' => $DaTipSer,
                            'DaIntAut' => $DaIntAut,
                            'DaCobExc' => $DaCobExc,
                            'DaNumPol' => $DaNumPol,
                            'DaFecIniV' => $DaFecIniV,
                            'DaFecFinV' => $DaFecFinV,
                            'DatEmpCod' => $EmpCodA,
                            'DatMCDPto' => $MCDptoA,
                            'DatDocTra' => 'FUR',
                            'DatCscTra' => $DatCscTra,
                            'DaCodB' => $DaCodB,
                            'DaNRdGlA' => $DaNRdGlA,
                            'DaRadGlo' => $DaRadGlo,
                            'DaNroTFol' => $DaNroTFol,
                            'DaMedMov' => '',
                            'DaARP' => '',
                            'DaNomCto' =>  '',
                            'DaCodOcu' => 0,
                            'DaDesOcu' =>  '',
                            'DaTmpCar' =>  '',
                            'DaNitEmp' =>  '',
                            'DaNomEmp' =>  '',
                            'DaDFEmp' =>  '',
                            'DaTipLes' =>  '',
                            'DaParCue' =>  '',
                            'DaAgnAcc' =>  '',
                            'DaMecAcc' =>  '',
                            'DaLabDes' =>  '',
                            'DaComOcu' =>  '',
                            'DaDirEmp' =>  '',
                            'DaTelEmp' =>  '',
                            'DATNUMSIR' => $DATNUMSIR,
                        ]
                    ]);
                $mesnaje = "Accidente de transito ingresado correctamente";
                    
            }else{
                DB::table('DATSOAT')
                    ->where('MPCedu', $TFCedu)
                    ->where('MPTDoc', $TFTDoc)
                    ->where('CtvIng', $TmCtvIng)
                    ->update([
                            'DaSitAcc' => $SOSitAcc,
                            'DaInfAcc' => $SOInfAcc,
                            'DaFecAcc' => $SOFchAcc,
                            'DaPrApPr' => $DaPrApPr,
                            'DaSeApPr' => $DaSeApPr,
                            'DaPrNoPr' => $DaPrNoPr,
                            'DaSeNoPr' => $DaSeNoPr,
                            'DaTiDoPr' => $DaTiDoPr,
                            'DaNuDoPr' => $DaNuDoPr,
                            'DaCodDPr' => $DaCodDPr,
                            'DaCodMPr' => $DaCodMPr,
                            'DaLugExp' => $DaLugExp,
                            'DaDirPr' => $DaDirPr,
                            'DaTelPr' => $DaTelPr,
                            'DaPrApCo' => $DaPrApCo,
                            'DaSeApCo' => $DaSeApCo,
                            'DaPrNoCo' => $DaPrNoCo,
                            'DaSeNoCo' => $DaSeNoCo,
                            'DaTipSer' => $DaTipSer,
                            'DaIntAut' => $DaIntAut,
                            'DaCobExc' => $DaCobExc,
                            'DaNumPol' => $DaNumPol,
                            'DaFecIniV' => $DaFecIniV,
                            'DaFecFinV' => $DaFecFinV,
                            'DatEmpCod' => $EmpCodA,
                            'DatMCDPto' => $MCDptoA,
                            'DatCscTra' => $DatCscTra,
                            'DaCodB' => $DaCodB,
                            'DaNRdGlA' => $DaNRdGlA,
                            'DaRadGlo' => $DaRadGlo,
                            'DaNroTFol' => $DaNroTFol,
                            'DATNUMSIR' => $DATNUMSIR
                    ]);
                
                $mesnaje = "Accidente de transito actualizado correctamente";

            }
            return response()->json([
                'status' => 200,
                'message' => $mesnaje
            ]);
               

        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
            
    }

    //Lista de accidentes a escoger en la admison
    public function accidentesPaciente(Request $request){
        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');

        return response()->json([
            'data' => DB::table('DATSOAT')
                            ->join('INGRESOS', function ($join) {
                                $join->on('DATSOAT.MPCedu', '=', 'INGRESOS.MPCedu')
                                     ->on('DATSOAT.MPTDoc', '=', 'INGRESOS.MPTDoc')
                                     ->on('DATSOAT.CtvIng', '=', 'INGRESOS.IngCsc');
                            })
                            ->select('DATSOAT.CtvIng', 'DATSOAT.MPTDoc', 'DATSOAT.MPCedu', 'DATSOAT.DaFecAcc',
                                    'INGRESOS.IngFecAdm', 'INGRESOS.IngCtvAc')
                            ->selectRaw("
                                        CASE INGRESOS.ClaPro 
                                            WHEN 1 THEN 'AMBULATORIO'
                                            WHEN 2 THEN 'HOSPITALARIO'
                                            WHEN 3 THEN 'URGENCIAS'
                                            WHEN 4 THEN 'TRATAMIENTO ESPECIAL'
                                            WHEN 5 THEN 'TRIAGE' END AS claseIngreso")
                            ->where('DATSOAT.MPCedu', $MPCedu)
                            ->where('DATSOAT.MPTDoc', $MPTDoc)
                            ->orderBy('DATSOAT.DaFecAcc', 'ASC')->get()
        ], 200);
    }
    
   
}
