<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;
use Carbon\Carbon;
use PDF;
use Dompdf\Options;
use Illuminate\Support\Facades\App;

class PacientesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //------------------------------------FUNCIONES PARA TODOS LOS TIPOS DE ATENCION

     //FUNCION QUE TRAE LA INFORMACIÓN GENERAL DEL PACIENTE GRILLA PRINCIPAL DE ADMISIONES

     public function infoPacienteGeneral(Request $request){

        //VALORES DE ENTRADA PARA FILTROS

        $salida = $request->input('salida');  //SELECT DE SALIDA (S:SI N:NO)
        $TFFchI = $request->input('TFFchI');  //FECHA INICIAL
        $TFFchF = $request->input('TFFchF').' 23:59:00.000';  //FECHA FINAL
        $SccEmp = $request->input('SccEmp');  //EMPRESA = 1
        $SCCCod = $request->input('SCCCod');  //SUBTIPO CENTRO DE COSTOS = 001
        $ClaPro = $request->input('ClaPro');  //TIPO DE INGRESO
        $MPNOMC = $request->input('MPNOMC');  //NOMBRE DEL PACIENTE
        $MENOMB = $request->input('MENOMB');  //NOMBRE DEL CONTRATO
        $TFcCodPab = $request->input('TFcCodPab');  //CODIGO DEL PABELLÓN
        $TFCedu = $request->input('TFCedu');  //CEDULA DEL PACIENTE
        $TFTDoc = $request->input('TFTDoc');  //TIPO DE DOCUMENTO

        $valorS = "'' = ''";

        //CLAVES PARA FILTRO

        $TFFchI_v = 'T1.TFFchI';
        $ClaPro_v = 'T1.ClaPro';
        $MPNOMC_v = 'T2.MPNOMC';
        $MENOMB_v = 'T3.MENOMB';
        $TFcCodPab_v = 'T1.TFcCodPab';
        $TFCedu_v = 'T1.TFCedu';
        $TFTDoc_v = 'T1.TFTDoc';

        if($salida == 'S'){
            $valorS = " T4.[IngFecEgr] > '19000101' ";
        }elseif($salida == 'N'){
            $valorS = "T4.[IngFecEgr] <  '19000101' ";
        }

        try{

            if (!$TFFchF){
                throw new \Exception('Fecha final requerida');
            }

            if (!$TFFchI){
                $TFFchI = ' ';
                $TFFchI_v = "' '";
            }

            if (!$ClaPro){
                $ClaPro = "' '";
                $ClaPro_v = "' '";
            }

            if (!$MPNOMC){
                $MPNOMC = ' ';
                $MPNOMC_v = "' '";
            }

            if (!$MENOMB){
                $MENOMB = ' ';
                $MENOMB_v = "' '";
            }

            if (!$TFcCodPab){
                $TFcCodPab = "' '";
                $TFcCodPab_v = "' '";
            }

            if (!$TFCedu){
                $TFCedu = ' ';
                $TFCedu_v = "' '";
            }

            if (!$TFTDoc){
                $TFTDoc = ' ';
                $TFTDoc_v = "' '";
            }

            $infoPaciente = DB::Connection('sqlsrv')
            ->select("SELECT T4.[IngFecEgr], T1.[TFTDoc] AS TFTDoc, T1.[TFCedu] AS TFCedu, T1.[TFViaI],
                        T1.[TFcCodPab], T5.[MPNomP], T3.[MENOMB] AS TFNomCto, T1.[TFMENi] AS TFMENi,
                        T2.[MPNOMC] AS TFNomC, T1.[TFFchI], T1.[ClaPro], T1.[SCCCod],
                        T1.[SccEmp], T6.[MCDnom], T1.[TmCtvIng], T1.[TFMotS], T1.[TFEstP], T1.[TmCtvAct],
                        T1.[TFNMAU], T1.[TFCauE], T1.[TFHorO], T1.[TFHorI], T1.[TFcCodCam]
                        FROM (([TMPFAC] T1 WITH (NOLOCK)
                            LEFT JOIN [CAPBAS] T2 WITH (NOLOCK) ON T2.[MPCedu] = T1.[TFCedu]
                                                    AND T2.[MPTDoc] = T1.[TFTDoc])
                            LEFT JOIN [MAEEMP] T3 WITH (NOLOCK) ON T3.[MENNIT] = T1.[TFMENi])
                            LEFT JOIN [INGRESOS] T4 WITH (NOLOCK) ON T4.MPCedu = T1.TFCedu AND T4.MPTDoc = T1.TFTDoc
                                                AND T4.IngCsc = T1.TmCtvIng
                            LEFT JOIN [MAEPAB] T5 ON T5.MPCodP = T1.[TFcCodPab]
                            LEFT JOIN [MAESED] T6 ON T6.[EMPCOD] = T1.[SccEmp] AND T6.[MCDpto] = T1.[SCCCod]
                        WHERE ($TFFchI_v >= convert( DATETIME,'".$TFFchI."',102))
                            and (T1.[TFFchI] <= convert( DATETIME,'".$TFFchF."',102))
                            and (T1.[SccEmp] = $SccEmp)
                            and (T1.[SCCCod] = '".$SCCCod."')
                            and ($ClaPro_v = $ClaPro)
                            and ($MPNOMC_v like '".$MPNOMC."')
                            and ($MENOMB_v like '".$MENOMB."')
                            and ($TFcCodPab_v =   $TFcCodPab)
                            and ($TFCedu_v = '".$TFCedu."')
                            and ($TFTDoc_v = '".$TFTDoc."')
                            and $valorS

                        ORDER BY T1.[TFCedu], T1.[TFTDoc], T1.[ClaPro], T1.[TFcCodCam], T1.[TFcCodPab]");
            
            foreach($infoPaciente as $info){

                $atencion = DB::connection('sqlsrv')
                    ->table('CLAESTPAC AS A')
                    ->join('PREATE AS B', 'A.CLAESTCOD', '=', 'B.CLAESTCOD')
                    ->select('A.CLAESTPRI', 'A.CLAESTCOD', 'A.CLAESTCOL', 'A.CLAESTDSC', 'A.CLAESTIDE',
                            'B.MPTDoc', 'B.MPCedu', 'B.PAFecReg', 'B.PAUsuReg', 'B.PADesc', 'B.CLAESTCOD AS B_CLAESTCOD',
                            'B.PAESTACT', 'B.PAESTOBS', 'B.PAUSUINA', 'B.PAFECINA', 'B.PACtvo')
                    ->where('B.MPCedu', $info->TFCedu)
                    ->where('B.MPTDoc', $info->TFTDoc)
                    ->where('B.PAESTACT', 'A')
                    ->orderBy('B.PACtvo', 'DESC')
                    ->first();
                
                if($atencion){
                    $info->tipo = $atencion->CLAESTIDE;
                    $info->color = $atencion->CLAESTCOL;
                }else{
                    $info->tipo = '';
                    $info->color = '';
                }
                
            }
            $status = 200;

        }catch(\exception $e){
            $infoPaciente = [
                'message' => $e->getMessage()
            ];
            $status = 404;

        }
        return response()->json($infoPaciente, $status);
     }

     public function observacion(Request $request){

        $MPCedu = $request->input('MPCedu'); //CEDULA DEL PACIENTE
        $MPTDoc = $request->input('MPTDoc'); //TIPO DE DOCUMENTO
        $IngCsc = $request->input('IngCsc'); //CONSECUTIVO DE INGRESO

        try{

            if (!$MPCedu) {
                throw new \Exception('Falta información requerida');
            }

            if (!$MPTDoc) {
                throw new \Exception('Falta información requerida');
            }

            if (!$IngCsc) {
                throw new \Exception('Falta información requerida');
            }

            $observacion = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [ClaPro], [MPCedu], [MPTDoc], [IngCsc], [IngUrgObs], [IngCtvMoP]
                    FROM [INGRESOMP] WITH (NOLOCK)
                    WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."'
                    and [IngCsc] =  $IngCsc  ORDER BY [MPCedu], [MPTDoc], [IngCsc], [IngCtvMoP] DESC ");
            $status = 200;

        }catch(\exception $e){
            $observacion = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($observacion, $status);
     }

     public function atencion(Request $request){
        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');

        try{

            $atencion = DB::Connection('sqlsrv')
            ->select("SELECT    A.[CLAESTPRI], A.[CLAESTCOD], A.[CLAESTCOL], A.[CLAESTDSC], A.[CLAESTIDE],
                                B.[MPTDoc], B.[MPCedu], B.[PAFecReg], B.[PAUsuReg], B.[PADesc], B.[CLAESTCOD] AS B_CLAESTCOD,
                                B.[PAESTACT], B.[PAESTOBS], B.[PAUSUINA], B.[PAFECINA], B.[PACtvo]
                        FROM [CLAESTPAC] A WITH (NOLOCK)
                        JOIN [PREATE] B WITH (NOLOCK) ON A.[CLAESTCOD] = B.[CLAESTCOD]
                        WHERE (B.[MPCedu] =  '".$MPCedu."' ) AND (B.[MPTDoc] =  '".$MPTDoc."' ) ORDER BY [PACtvo] DESC");
            $status=200;

        }catch (\exception $e){
            $atencion = [
                'message' => $e->getMessage()
            ];
            $status=404;
        }
        return response()->json($atencion, $status);
     }

     //-----------------------------------ADICIONAL DE TIPO ATENCION URGENCIAS

     public function movAdmision (Request $request){

        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');
        $IngCsc = $request->input('IngCsc');

        try{

            if (!$MPCedu || !$MPTDoc || !$IngCsc){
                throw new \Exception("Algo salió mal");
            }

            $movimiento = DB::Connection('sqlsrv')

            ->select("SELECT [MPCedu], [MPTDoc], [IngCsc], [IngUrgObs], [IngFecMoP], [IngCodPab], [IngFecMoE],
                        [IngCtvMoP], [ClaPro]
                        FROM [INGRESOMP] WITH (NOLOCK)
                        WHERE [MPCedu] =  '".$MPCedu."' and [MPTDoc] =  '".$MPTDoc."'  and [IngCsc] =  $IngCsc
                         ORDER BY [MPCedu], [MPTDoc], [IngCsc], [IngCtvMoP]");
            $status = 200;
        }catch(\exception $e){
            $movimiento = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($movimiento, $status);
     }

     public function infoResponsable(Request $request){
        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');
        $IngCsc = $request->input('IngCsc');

        try{
            if(!$MPCedu || !$MPTDoc || !$IngCsc){
                throw new \Exception("Falta información requerida");
            }

            $infoResponsable = DB::Connection('sqlsrv')
            ->select("SELECT [MPCedu], [MPTDoc], [IngDocResp], [IngTDoResp], [IngNmResp], [IngNmResp2],
                        [IngApRes], [IngApRes2], [IngTelResp], [IngDirResp], [IngDptRe], [IngMunRe], [IngEmTrR],
                        [IngTeTrR], [IngParResp], [IngCsc], [ClaPro], IngTiDoAc TIPODOCACOMPA, IngDoAco DOCACOMPA, IngNoAc NOMACOMPA,
	                    IngTeAc TELACOMPA, IngParAc PARENTESCOAC
                    FROM [INGRESOS] WITH (NOLOCK) WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."' and [IngCsc] = $IngCsc
                    ORDER BY [MPCedu], [MPTDoc], [IngCsc] DESC");
            $status = 200;
        }catch(\exception $e){
            $infoResponsable = [
                'message' => $e->getMessage()
            ];
            $status=404;
        }
        return response()->json($infoResponsable, $status);
    }

    public function infoGenPte(Request $request){
        $MPCedu = $request->input('MPCedu'); //cedula paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo documento paciente
        $IngCsc = $request->input('IngCsc'); //Consecutivo de ingreso

        try{
            $info = DB::Connection('sqlsrv')
            ->select("SELECT ING.MPTDoc, ING.MPCedu, CAP.MPNOMC, ING.IngCsc,
                        CASE ING.ClaPro
                            WHEN 1 THEN 'AMBULATORIO'
                            WHEN 2 THEN 'HOSPITALARIO'
                            WHEN 3 THEN 'URGENCIAS'
                            WHEN 4 THEN 'TRATAMIENTO ESPECIAL'
                            WHEN 5 THEN 'TRIAGE' END AS TIPO_ATENCION
                    FROM INGRESOS as ING
                        INNER JOIN CAPBAS AS CAP ON CAP.MPCedu = ING.MPCedu AND CAP.MPTDoc = ING.MPTDoc
                    WHERE ING.MPCedu = '".$MPCedu."' AND ING.MPTDoc = '".$MPTDoc."' AND ING.IngCsc = $IngCsc");
            $retorno = [
                'status' => 200,
                'data' => $info
            ];
        }catch(\Exception $e){
            $retorno =[
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return $retorno;
    }

    //INFORMACION DEL INGRESO DEL PACIENTE
    public function infoIngreso(Request $request){

        $MPCedu = $request->input('MPCedu'); //cedula del paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo de documento del paciente
        $IngCsc = $request->input('IngCsc'); //consecutivo de ingreso

        try{
            $info = DB::connection('sqlsrv')
            ->select("SELECT 
                        LTRIM(RTRIM(ING.IngCsc)) AS IngCsc, 
                        LTRIM(RTRIM(ING.MPTDoc)) AS MPTDoc, 
                        LTRIM(RTRIM(ING.MPCedu)) AS MPCedu, 
                        LTRIM(RTRIM(CAP.MPNOMC)) AS MPNOMC,
                        LTRIM(RTRIM(ING.IngAtnAct)) AS IngAtnAct, 
                        LTRIM(RTRIM(ING.ClaPro)) AS ClaPro, 
                        LTRIM(RTRIM(ING.IngUlcMoP)) AS IngUlcMoP, 
                        LTRIM(RTRIM(ING.IngEntDx)) AS IngEntDx, 
                        LTRIM(RTRIM(DIA1.DMNomb)) AS diagEntrada1,
                        LTRIM(RTRIM(ING.IngEntDx2)) AS IngEntDx2, 
                        LTRIM(RTRIM(DIA2.DMNomb)) AS diagEntrada2, 
                        LTRIM(RTRIM(ING.IngDxCli)) AS IngDxCli, 
                        LTRIM(RTRIM(ING.IngHosTTo)) AS IngHosTTo, 
                        LTRIM(RTRIM(ING.IngCauE)) AS IngCauE, 
                        LTRIM(RTRIM(ING.IngSoAdTr)) AS IngSoAdTr, 
                        ING.IngFecEgr, 
                        LTRIM(RTRIM(ING.INGREIPAC)) AS INGREIPAC, 
                        LTRIM(RTRIM(ING.IngCoMt)) AS IngCoMt, 
                        LTRIM(RTRIM(MED.MMNomM)) AS MMNomM,
                        LTRIM(RTRIM(CAP.MPSexo)) AS MPSexo, 
                        LTRIM(RTRIM(ING.ingEsMt)) AS ingEsMt, 
                        LTRIM(RTRIM(ESP.MENomE)) AS MENomE, 
                        LTRIM(RTRIM(TMP.TFViaI)) AS TFViaI, 
                        LTRIM(RTRIM(TMP.TFcCodCam)) AS TFcCodCam, 
                        LTRIM(RTRIM(TMP.TFcCodPab)) AS TFcCodPab, 
                        LTRIM(RTRIM(TMP.TFCoMt)) AS MEDICOTRATANTE, 
                        LTRIM(RTRIM(TMP.TFEsMt)) AS ESPMEDTRATANTE,
                        LTRIM(RTRIM(TMP.TFCoMI)) AS MEDICOINGRESO, 
                        LTRIM(RTRIM(TMP.TFEsMI)) AS ESPMEDINGRESO, 
                        LTRIM(RTRIM(TMP.TFNMAU)) AS TFNMAU, 
                        LTRIM(RTRIM(TMP.TFcNomAut)) AS TFcNomAut, 
                        LTRIM(RTRIM(ING.IngDerObs)) AS IngDerObs, 
                        LTRIM(RTRIM(TMP.TFDi1I)) AS TFDi1I, 
                        LTRIM(RTRIM(TMP.TFDi2I)) AS TFDi2I
                    FROM [INGRESOS] AS ING WITH (UPDLOCK) 
                        LEFT JOIN MAEMED1 AS MED ON MED.MMCODM = ING.IngCoMt
                        LEFT JOIN MAEDIA AS DIA1 ON DIA1.DMCodi = ING.IngEntDx
                        LEFT JOIN MAEDIA AS DIA2 ON DIA2.DMCodi = ING.IngEntDx2
                        INNER JOIN CAPBAS AS CAP ON CAP.MPCedu = ING.MPCedu AND CAP.MPTDoc = ING.MPTDoc
                        LEFT JOIN MAEESP AS ESP ON ESP.MECodE = ING.IngEsMt
                        LEFT JOIN TMPFAC AS TMP ON TMP.TFCedu = ING.MPCedu AND TMP.TFTDoc = ING.MPTDoc AND TMP.TmCtvIng = ING.IngCsc
                    WHERE (ING.[MPCedu] =  '".$MPCedu."'  AND ING.[MPTDoc] =  '".$MPTDoc."'  AND ING.[IngCsc] =  $IngCsc ) 
                    ORDER BY ING.[MPCedu], ING.[MPTDoc], ING.[IngCsc]");

                //agregar dx
                foreach ($info as $value) {

                    $dx = DB::table('MAEMED AS T1')
                        ->select('T1.MMCODM', 'T1.MECodE', 'T2.MMNomM')
                        ->join('MAEMED1 AS T2', 'T2.MMCODM', '=', 'T1.MMCODM')
                        ->where('T1.MMCODM', $value->TFDi1I)
                        ->orderBy('T1.MMCODM')
                        ->first();
                
                    $sdx = DB::table('MAEMED AS T1')
                        ->select('T1.MMCODM', 'T1.MECodE', 'T2.MMNomM')
                        ->join('MAEMED1 AS T2', 'T2.MMCODM', '=', 'T1.MMCODM')
                        ->where('T1.MMCODM', $value->TFDi2I)
                        ->orderBy('T1.MMCODM')
                        ->first();

                    if($dx){
                        $value->dx1N = $dx->MMNomM;
                    }
                    if($sdx){
                        $value->dx2N = $sdx->MMNomM;
                    }
                }
                    
            $retorno = [
                'status' => 200,
                'message' => true,
                'data' => $info
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    }

    public function infoManilla (Request $request){
        $MPCedu = $request->input('MPCedu'); //Documento del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo documento del paciente

        try{
            $infoManilla = DB::Connection('sqlsrv')
            ->select("SELECT CAP.MPCedu cedula, CAP.MPTDoc tipoDoc, CAP.MPNOMC NOMBRE, HISTSANG TIPOSANGRE, HCAlergia,
                        DATEDIFF(YEAR,CAP.MPFchN,GETDATE())
                        -(CASE
                        WHEN DATEADD(YY,DATEDIFF(YEAR,CAP.MPFchN,GETDATE()),CAP.MPFchN)>GETDATE() THEN
                            1
                        ELSE
                            0 
                        END) as Edad, CAP.MPDire, CAP.MPTele
                    FROM CAPBAS AS CAP
                        LEFT JOIN HCCOM AS HCC ON HCC.HISCKEY = CAP.MPCedu AND HCC.HISTipDoc = CAP.MPTDoc
                    WHERE CAP.MPCedu = '".$MPCedu."' AND CAP.MPTDoc = '".$MPTDoc."'");
            
            $retorno = [
                'status' => 200,
                'data' => $infoManilla,
                'message' => ""
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    }

    //Funcion para traer el historico de los paciente 
    public function historicoIngresos(Request $request){

        $indSalida = $request->input('indSalida'); //Indicador de salida (1:con salida, 0:sin salida)
        $ClaPro = $request->input('ClaPro'); //Tipo de ingreso
        $MPCodP = $request->input('MPCodP'); //Codigo del pabellon
        $MPNOMC = $request->input('MPNOMC'); //Nombre del paciente
        $IngNit = $request->input('IngNit'); //Contrato de ingreso
        $IngNitL = $request->input('IngNitL'); //Nombre de ingreso por nombre
        $indFacturado = $request->input('indFacturado'); //Indicador de facturado (1:facturado, 0:no facturado)
        
        $IngFecAdmI = str_replace('-','',$request->input('IngFecAdmI')); //Fecha inicial de ingreso
        $IngFecAdmF = str_replace('-','',$request->input('IngFecAdmF')); //Fecha final de ingreso
        $MPCedu = $request->input('MPCedu'); //Documento del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo documento del paciente
      
        if($IngFecAdmF == null){
            $fechaFinal = $IngFecAdmF;
        }else{
            $fechaFinal = $IngFecAdmF . ' 23:59:00.000';
        }

            
            $historico = DB::table('INGRESOS')
                ->leftJoin('CAPBAS', function($join){
                    $join->on('INGRESOS.MPCedu', '=', 'CAPBAS.MPCedu')
                        ->on('INGRESOS.MPTDoc', '=', 'CAPBAS.MPTDoc');
                })
                ->leftJoin('MAEEMP', 'INGRESOS.IngNit', '=', 'MAEEMP.MENNIT')
                ->leftJoin('MAEPAB', 'INGRESOS.MPCodP', '=', 'MAEPAB.MPCodP')
                ->leftJoin('MAEPAB as MAEPAB2', 'INGRESOS.IngCodPEg', '=', 'MAEPAB2.MPCodP')
                ->select('INGRESOS.IngFecAdm', 'INGRESOS.IngFac', 'MAEEMP.MEcntr', 'INGRESOS.IngNit', 
                        'INGRESOS.IngInSlC', 'INGRESOS.MPCodP', 'INGRESOS.ClaPro', 'INGRESOS.MPTDoc', 'INGRESOS.MPCedu', 'CAPBAS.MPNOMC', 'INGRESOS.IngAteEgr',
                        'INGRESOS.IngCsc', 'INGRESOS.IngDoc', 'INGRESOS.IngEntDx', 'INGRESOS.IngEstSld', 'INGRESOS.IngUsrReg', 'INGRESOS.IngIPS', 
                        'INGRESOS.IngFecEgr', 'INGRESOS.IngSalDx', 'INGRESOS.IngDxTip', 'INGRESOS.IngFchM', 'INGRESOS.IngCauM', 'INGRESOS.IngMedSal', 
                        'INGRESOS.IngMEdEsp', 'INGRESOS.IngMedDef', 'INGRESOS.InCerDef', 'INGRESOS.IngDxSal1', 'INGRESOS.IngDxTip1', 'INGRESOS.IngMotSal', 
                        'INGRESOS.IngUsrSal', 'INGRESOS.IngCodPEg', 'INGRESOS.MPNumC', 'MAEPAB.MPNomP', 'INGRESOS.IngUsuAnu', 'INGRESOS.IngFchAnu', 
                        'INGRESOS.IngObsAnu', 'INGRESOS.IngAtnAct')
                ->selectraw('DBO.DESENCRIPTAR(INGRESOS.IngUsrReg) AS usuario, MAEPAB2.MPNomP AS pabEgr')
                ->where(function ($query) use ($MPCedu) {
                    if($MPCedu != null){
                        return $query->where('INGRESOS.MPCedu',$MPCedu);  
                    }
                })
                ->where( function ($query) use ($IngFecAdmI) {
                    if($IngFecAdmI != null){
                        return $query->where('INGRESOS.IngFecAdm','>=',$IngFecAdmI);  
                    }
                })
                ->where(function ($query) use ($IngFecAdmF, $fechaFinal) {
                    if($IngFecAdmF != null){
                        return $query->where('INGRESOS.IngFecAdm','<=', $fechaFinal);  
                    }
                })
                ->where(function ($query) use ($MPTDoc) {
                    if($MPTDoc != null){
                        return $query->where('INGRESOS.MPTDoc',$MPTDoc);  
                    }
                })
                ->where( function ($query) use ($indSalida) {
                    if($indSalida != null){
                        if($indSalida == 1){
                            return $query->where('INGRESOS.IngFecEgr','>=','19000101');
                        }elseif($indSalida == 0){
                            return $query->where('INGRESOS.IngFecEgr','17530101');  
                        }
                    }
                })
                ->where(function ($query) use ($ClaPro) {
                    if($ClaPro != null){
                        return $query->where('INGRESOS.ClaPro',$ClaPro);  
                    } 
                })
                ->where(function ($query) use ($MPCodP) {
                    if($MPCodP != null){
                        return $query->where('INGRESOS.MPCodP',$MPCodP);  
                    }
                })
                ->where(function ($query) use ($MPNOMC) {
                    if($MPNOMC != null){
                        return $query->where('CAPBAS.MPNOMC','like','%'.$MPNOMC.'%');  
                    }
                })
                ->where(function ($query) use ($IngNit) {
                    if($IngNit != null){
                        return $query->where('INGRESOS.IngNit',$IngNit);  
                    }
                })
                ->where(function ($query) use ($IngNitL) {
                    if($IngNitL != null){
                        return $query->where('INGRESOS.IngNit','like','%'.$IngNitL.'%');  
                    } 
                })
                ->where(function ($query) use ($indFacturado) {
                    if($indFacturado != null){
                        if($indFacturado == 1){
                            return $query->where('INGRESOS.IngFac','>=','1');
                        }else{
                            return $query->where('INGRESOS.IngFac','0');  
                        }
                    }
                })
                ->orderby('INGRESOS.IngCsc','asc')
                ->get();

                return response()->json([
                    'status' => 200,
                    'data' => $historico,
                    'fecha' => $IngFecAdmF
                ]);
    }

    //Funcion informacion de anulacion del ingreso 
    public function infoAnulacion(Request $request){

        $MPCedu = $request->input('MPCedu'); //Documento del paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo documento del paciente
        $IngCsc = $request->input('IngCsc'); //Consecutivo de ingreso
        $ClaPro = $request->input('ClaPro'); //Tipo de ingreso

        try{
            $infoIngreso = DB::table('INGRESOS')
                ->select('IngCsc', 'ClaPro', 'MPTDoc', 'MPCedu', 'IngFecAdm', 'MPCodP', 'IngUsuAnu',
                        'IngFchAnu', 'IngObsAnu')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngCsc', $IngCsc)
                ->where('ClaPro', $ClaPro)->first();

            if($infoIngreso == null){
                throw new \Exception("No se encontró información del ingreso");
            }

            //switch case con el ClaPro del infoIngreso
            switch($infoIngreso->ClaPro){
                case 1:
                    $atencion = 'AMBULATORIO';
                    break;
                case 2:
                    $atencion = 'HOSPITALARIO';
                    break;
                case 3:
                    $atencion = 'URGENCIAS';
                    break;
                case 4:
                    $atencion = 'TRATAMIENTO ESPECIAL';
                    break;
                case 5:
                    $atencion = 'TRIAGE';
                    break;
            }

            $paciente = DB::table('CAPBAS')
                ->where('MPCedu', $infoIngreso->MPCedu)
                ->where('MPTDoc', $infoIngreso->MPTDoc)->first();

            $infoPabellon = DB::table('MAEPAB')
                ->select('MPCodP', 'MPNomP')
                ->where('MPCodP', $infoIngreso->MPCodP)->first();

            if($infoPabellon == null){
                throw new \Exception("No se encontró información del pabellón");
            }

            //Desencriptar el usuario
            $usuario = DB::table('ADMUSR')
                ->selectraw("AUsrId,DBO.DESENCRIPTAR(AUsrDsc) AS usuario")
                ->where('AUsrId', $infoIngreso->IngUsuAnu)->first();
            
            if($usuario == null){
                $usuarioAnu = '';
            }else{
                $usuarioAnu = $usuario->usuario;
            }

            return response()->json([
                'status' => 200,
                'data' => [
                    'cedulaPaciente' => $infoIngreso->MPCedu,
                    'tipoDocumentoPaciente' => $infoIngreso->MPTDoc,
                    'consecutivoIngreso' => $infoIngreso->IngCsc,
                    'nombrePaciente' => [
                        'primerNombre' => $paciente->MPNom1,
                        'segundoNombre' => $paciente->MPNom2,
                        'primerApellido' => $paciente->MPApe1,
                        'segundoApellido' => $paciente->MPApe2
                    ],
                    'tipoAtn' => $infoIngreso->ClaPro,
                    'atencion' => $atencion,
                    'codPabellon' => $infoPabellon->MPCodP,
                    'nombrePabellon' => $infoPabellon->MPNomP,
                    'idUsuario' => $infoIngreso->IngUsuAnu,
                    'usuarioAnula' => $usuarioAnu,
                    'fechaAnulacion' => $infoIngreso->IngFchAnu,
                    'fechaIngreso' => $infoIngreso->IngFecAdm,
                    'observacionAnulacion' => $infoIngreso->IngObsAnu
                ]
            ]);


        }catch(\Exception $e){
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
        
    }

  
    public function HCRangoFolios(Request $request){
        
        $pdfContentTop = "";
        $pdfContent = "";
        $fechActH = Carbon::now('America/Bogota')->format('Ymd H:i:s');
        $LOGESTTRA = gethostname();
        $escalaRes = [];
        $procOrdenados = [];
        $datosTriage =[];

        $pdf = App::make('dompdf.wrapper');
       
        //Arrays que entregan datos
   
        $MPCedu = $request->MPCedu; //Documento del paciente
        $MPTDoc = $request->MPTDoc; //Tipo de documento del paciente  
        $MSUser = $request->MSUser; //Usuario que realiza la consulta
        $EMPCOD = $request->EMPCOD; //Codigo de la empresa
        $MENNIT = $request->MENNIT; //Codigo del contrato
        $IngCsc = $request->IngCsc; //Codigo del ingreso
        $MCDpto = $request->MCDpto; //Codigo de la sede
        $HISCSECIn = $request->HISCSECIn; //Codigo del folio inicial
        $HISCSECFn = $request->HISCSECFn; //Codigo del folio final


        //Valida registro de descarga de la historia clinica
        $regDownload = DB::table('LOGCONHC')
            ->select('FECHORCON', 'MPTDoc', 'MPCedu')
            ->where('MPTDoc', $MPTDoc)
            ->where('MPCedu', $MPCedu)
            ->where('FECHORCON', $fechActH)->first();

         if($regDownload == null){
            //inserta registro de descarga de la historia clinica
            DB::table('LOGCONHC')->insert([
                'FECHORCON' => $fechActH,
                'MPTDoc' => $MPTDoc,
                'MPCedu' => $MPCedu,
                'LOGMOTCON' => '',
                'MSUser' => $MSUser,
                'LOGESTTRA' => $LOGESTTRA,
                'LOGNOMREP' => 'RANGO DE FOLIOS'
            ]);
        } 

        //INICIA BUSQUEDA DE INFORMACION DE LA HISTORIA CLINICA
        //Informacion de la empresa
        $infoEmpresa = DB::table('EMPRESA')
            ->select('EMPCOD', 'EmpDVer', 'EmpNit', 'EmpRazSoc')
            ->where('EMPCOD', $EMPCOD)->first();
        
        //Informcion del paciente 
        $paciente = DB::table('MAEPAC')
            ->leftJoin('MAEEMP', 'MAEPAC.MENNIT', '=', 'MAEEMP.MENNIT')
            ->leftJoin('EMPRESS', 'EMPRESS.MEcntr','=','MAEEMP.MEcntr')
            ->leftJoin('CAPBAS', function ($join) {
                $join->on('CAPBAS.MPCEDU', '=', 'MAEPAC.MPCEDU')
                    ->on('CAPBAS.MPTDOC', '=', 'MAEPAC.MPTDOC');
            })
            ->leftJoin('MAEOCUPRI', 'MAEOCUPRI.MOCodPri', '=', 'CAPBAS.MOCodPri')
            ->leftJoin('MAEOCU', 'MAEOCU.MOCodi', '=', 'MAEOCUPRI.MOCodi')
            ->leftJoin('MAEDMB', 'MAEDMB.MDCodD', '=', 'CAPBAS.MDCodD')
            ->leftJoin('MAEDMB1', function ($join) {
                $join->on('MAEDMB1.MDCodD', '=', 'CAPBAS.MDCodD')
                    ->on('MAEDMB1.MDCodM', '=', 'CAPBAS.MDCodM');
            })
            ->leftJoin('MAETPA3', function ($join) {
                $join->on('MAETPA3.MTCodP', '=', 'MAEPAC.MTCodP')
                    ->on('MAETPA3.MTUCod', '=', 'MAEPAC.MTUCod');
            })
            ->leftJoin('MAEDMB2', function ($join) {
                $join->on('MAEDMB2.MDCodD', '=', 'CAPBAS.MDCodD')
                    ->on('MAEDMB2.MDCodM', '=', 'CAPBAS.MDCodM')
                    ->on('MAEDMB2.MDCodB', '=', 'CAPBAS.MDCodB');
            })
            ->leftJoin('ETNIAS', 'ETNIAS.MPCodEt', '=', 'CAPBAS.MPCodEtn')
            ->leftJoin('ETNIAS1', function ($join) {
                $join->on('ETNIAS1.MPCodEt', '=', 'CAPBAS.MPCodEtn')
                    ->on('ETNIAS1.MPCNEtn', '=', 'CAPBAS.MPSbGrPo');
            })
            ->leftJoin('NIVEDU', 'NIVEDU.NivEdCo', '=', 'CAPBAS.MPNivEdu')
            ->leftJoin('ATEESP', 'ATEESP.ATEESPCOD', '=', 'CAPBAS.MPGrEs')
            ->leftJoin('DISCPAC', 'DISCPAC.DiscCod', '=', 'CAPBAS.MPCodDisc')
            ->leftJoin('GRUPOB', 'GRUPOB.GRUPOBCOD', '=', 'CAPBAS.MPGrPo')
            ->select('CAPBAS.MPTDoc', 'CAPBAS.MPCedu', 'CAPBAS.MPNHiC', 
                    'CAPBAS.MOCodPri', 'MAEPAC.MENNIT', 'MAEPAC.MPTDoc', 'MAEPAC.MPCedu', 'MAEPAC.MTUCod', 'CAPBAS.MPSexo',
                    'MAEPAC.MTCodP', 'MAETPA3.MTNomP', 'EMPRESS.MEDire', 'MAEPAC.MPNoCa', 'MAEEMP.MEcntr', 'EMPRESS.EmpDsc', 'MAEEMP.MENOMB', 'CAPBAS.MPNHiC', 
                    'CAPBAS.MPDire', 'CAPBAS.MPTele', 'CAPBAS.MPNOMC', 'CAPBAS.MPFchN', 'MAEEMP.MEPARt', 'CAPBAS.MpLgExp', 'CAPBAS.MPEstC',
                    'CAPBAS.MDCodD', 'MAEDMB.MDNomD', 'CAPBAS.MDCodM', 'MAEDMB1.MDNomM', 'CAPBAS.MDCodB', 'MAEDMB2.MDNomB', 'MAEOCUPRI.MOCodi', 'MAEOCUPRI.MODesPri','MAEOCU.MODesc',
                    'CAPBAS.MPCodEtn', 'ETNIAS.MPDscEt', 'ETNIAS1.MPDNEtn','CAPBAS.MPCPEtn', 'CAPBAS.MPNivEdu', 'NIVEDU.NivEdDsc','CAPBAS.MPCodDisc', 'DISCPAC.DiscDsc',
                    'CAPBAS.MPGrPo', 'GRUPOB.GRUPOBDES','CAPBAS.MPGrEs', 'ATEESP.ATEESPDES', 'CAPBAS.MPEmpTra')
            ->selectRaw("DATEDIFF(DAY, CAPBAS.MPFchN, GETDATE()) / 365 AS Anios, 
                    DATEDIFF(DAY, CAPBAS.MPFchN, GETDATE()) % 365 / 30 AS Meses, 
                    DATEDIFF(DAY, CAPBAS.MPFchN, GETDATE()) % 365 % 30 AS Dias,
                    CASE CAPBAS.MPSexo 
                        WHEN 'M' THEN 'MASCULINO'
                        WHEN 'F' THEN 'FEMENINO'
                    END AS sexo,
                    CASE CAPBAS.MPEstC
                        WHEN 'S' THEN 'SOLTERO'
                        WHEN 'C' THEN 'CASADO'
                        WHEN 'V' THEN 'VIUDO'
                        WHEN 'U' THEN 'UNION LIBRE'
                        WHEN 'M' THEN 'MENOR'
                    END AS estadoCivil
                    ")
            ->where('CAPBAS.MPTDoc', $MPTDoc)
            ->where('CAPBAS.MPCedu', $MPCedu)
            ->where('MAEPAC.MENNIT', $MENNIT)
            ->first();
    

        //Info del responsable
        $responsable = DB::table('INGRESOS')    
            ->select('IngCsc', 'MPTDoc', 'MPCedu', 'IngDoAco', 'IngNoAc', 'IngTeAc', 'IngNmResp',
                    'IngNmResp2', 'IngApRes', 'IngApRes2', 'IngTelResp', 'IngParResp', 'ClaPro', 'IngCodPEg')
            ->selectRaw("CASE IngParResp
                            WHEN 'P' THEN 'Padre o madre'
                            WHEN 'H' THEN 'Hijo'
                            WHEN 'C' THEN 'Conyuge'
                            WHEN 'F' THEN 'Familiar'
                            WHEN 'A' THEN 'Amigo'
                            WHEN 'O' THEN 'Otro'
                        END AS parentescoResp")
            ->where('MPTDoc', $MPTDoc)
            ->where('MPCedu', $MPCedu)
            ->where('IngCsc', $IngCsc)->first();
        
        //Pabellon egreso
        $pabEgreso = DB::table('MAEPAB')
            ->select('MPCodP', 'MPNomP')
            ->where('MPCodP', $responsable->IngCodPEg)->first();
        
        if($pabEgreso != null){
            $pabeEgr = $pabEgreso->MPNomP;
        }else{
            $pabeEgr = '';
        }

        //Info de la sede
        $sede = DB::table('MAESED')
            ->select('MCDpto', 'EMPCOD', 'MCDIntLab', 'MCDIntIma', 'MCDnom')
            ->where('EMPCOD' , $EMPCOD)
            ->where('MCDpto', $MCDpto)->first();

        //Ingresa datos al pdf
        $pdfContentTop = '<p style="text-align: center;
                        font-weight:bold;
                        font-family: Arial, sans-serif;
                        font-size: 12px">'.$infoEmpresa->EmpRazSoc.'</p>';
        $pdfContentTop = $pdfContentTop . '<p style="text-align: center;
                            font-weight:bold;
                            font-family: Arial, sans-serif;
                            font-size: 12px">'.$infoEmpresa->EmpNit.'</p>';
        $pdfContentTop = $pdfContentTop . '<p style="text-align: left;
                            font-weight:bold;
                            font-family: Arial, sans-serif;
                            font-size: 12px">
                                HISTORIA CLINICA No '.$paciente->MPTDoc.' '.$paciente->MPCedu.' -- '.$paciente->MPNOMC.'</p>';
        $pdfContentTop = $pdfContentTop . '<div style="width: 50%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0; padding: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">Empresa: '.$infoEmpresa->EmpRazSoc.' </p>'.
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Fecha Nacimiento: '.Carbon::parse($paciente->MPFchN)->format('Y-m-d').'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Telefono: '.$paciente->MPTele.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Barrio: '.$paciente->MDNomB.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Municipio: '.$paciente->MDNomM.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Etnia: '.$paciente->MPDscEt.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Nivel Educativo: '.$paciente->NivEdDsc.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Discapacidad: '.$paciente->DiscDsc.'</p>' . 
                                        '</div>' . 
                                        '<div style="width: 30%; float: left;">' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Afiliado: '.$paciente->MTNomP .'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Sexo: '.$paciente->sexo.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Dirección: '.$paciente->MPDire.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Departamento: '.$paciente->MDNomD.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Ocupación: '.$paciente->MODesc.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Grupo Etnico: '.$paciente->MPDNEtn.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Atención Especial: '.$paciente->ATEESPDES.'</p>' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Grupo Poblacional: '.$paciente->GRUPOBDES.'</p>' . 
                                        '</div>'.
                                        '<div style="width: 20%; float: left;">' . 
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Estado civil: '.$paciente->estadoCivil .'</p>' . 
                                        '</div>'.

                                        '<hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">'.
                                        '<div style="width: 50%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0; padding: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">Responsable: '.$responsable->IngNmResp.' '.$responsable->IngNmResp2.' '.$responsable->IngApRes.' '.$responsable->IngApRes2.'</p>'.
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Acompañante: '.$responsable->IngNoAc.'</p>
                                        </div>'.
                                        '<div style="width: 25%; float: left;">' .
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Telefono: '.$responsable->IngTelResp.'</p>'.
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Telefono: '.$responsable->IngTeAc.'</p>'.
                                        '</div>'.
                                        '<div style="width: 25%; float: left;">' .
                                            '<p style = "font-weight:bold;
                                                font-family: Arial, sans-serif; margin: 0; padding: 0;
                                                font-size: 12px ">Parentesco: '.$responsable->parentescoResp.'</p>
                                        </div>';
                                    
        
        //Busca folios en el rango de folios que entro 
        $folios = DB::table('HCCOM1')
            ->select('hiscfk', 'MCDpto', 'HISCMMED', 'HISCSEC', 'HISTipDoc', 'HISCKEY', 'TCnCod', 
                    'HISCCIE', 'HCtvIn1', 'HisFHorAt', 'HISCLPR', 'HisGCtv', 'HCEsp', 'EMPCOD', 'HisCitNum', 
                    'RxSDscPlm', 'EFsDscPlm', 'HISFECSAL', 'FHCIndEsp', 'HISCLTR', 'FHCCodCto', 'HCCONCOD', 
                    'HISCENFACT')
            ->selectRaw("CASE HISCLPR 
                            WHEN 1 THEN 'AMBULATORIO'
                            WHEN 2 THEN 'HOSPITALARIO'
                            WHEN 3 THEN 'URGENCIAS'
                            WHEN 4 THEN 'TRATAMIENTO ESP'
                            WHEN 5 THEN 'TRIAGE'
                            END AS ClaPro")
            ->where('HISCKEY', $MPCedu)
            ->where('HISTipDoc', $MPTDoc)
            ->whereBetween('HISCSEC', [$HISCSECIn, $HISCSECFn])
            ->where('HCtvIn1', $IngCsc)
            ->where('HISCCIE', '1')
            ->where('TCnCod', '<>', 'S')
            ->orderBy('HISCSEC')->get();
        
        
        //recorre folios
        foreach($folios as $value){
            $pdfContentTriage = "";
            $pdfContentPr = "";
            $pdfContentLab = "";
            $pdfContentImDx = "";
            $pdfContentPrQx = "";
            $pdfContentCx = "";
            $pdfContentTer = "";
            $pdfContentPrH = "";
            $pdfContentLabH = "";
            $pdfContentImDxH = "";
            $pdfContentPrQxH = "";
            $pdfContentCxH = "";
            $pdfContentTerH = "";
            $relacionador = "";
            $medicoN = "";
            $medicoR = "";
            $especialidadN = "";

            //Datos iniciales del folio 
            $pdfContent = $pdfContent . '<hr style="border: none; border-top: 3px solid #000; margin-top: 1px; clear: both;">'.
                                        '<div style="width: 30%; float: left; ">' . 
                                            '<p style = "font-weight:bold; margin: 0; 
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">SEDE DE ATENCIÓN: </p>'.
                                        '</div>'.
                                        '<div style="width: 80%; float: left;margin:0">' . 
                                            '<p style = "margin: 0; padding: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">'.$sede->MCDpto.'  '.$sede->MCDnom.' </p>'.
                                        '</div>'.
                                        '<hr style="border: none; border-top: 3px solid #000; margin-top: 1px; clear: both;">'.
                                        '<div style="width: 30%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">FOLIO: '.$value->HISCSEC.' </p>'.
                                        '</div>'.
                                        '<div style="width: 35%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">FECHA: '.$value->HisFHorAt.' </p>'.
                                        '</div>'.
                                        '<div style="width: 35%; float: left;">' . 
                                            '<p style = " font-weight:bold;margin: 0; 
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">TIPO DE ATENCIÓN:  '.$value->ClaPro.' </p>'.
                                        '</div>'.
                                        '<hr style="border: none; border-top: 4px solid #000; margin-top: 1px; clear: both;">';

            //medico del folio 
            $medico = DB::table('MAEMED1')
                ->select('MMCODM', 'MMNomM','MMRegM', 'MMFIRMAS', 'MMFIRMA')
                ->where('MMCODM', $value->HISCMMED)->first();
            
            if($medico != null){
                $medicoN = $medico->MMNomM;
                $medicoR = $medico->MMRegM;
            }
            
            //Especialidad del medico del folio
            $especialidad = DB::table('MAEESP')
                ->select('MECodE', 'MENomE')
                ->where('MECodE', $value->HCEsp)->first();
            
            if($especialidad != null){
                $especialidadN = $especialidad->MENomE;
            }

            //Valida si es folio de triage 
            $flTria = DB::table('HCTRIAGE')
                ->join('DIATRIA', 'DIATRIA.DiaCodDia', '=', 'HCTRIAGE.DiaCodDia')
                ->select('HCTRIAGE.DiaCodSis', 'HCTRIAGE.HISCKEY', 'HCTRIAGE.HISTipDoc', 'HCTRIAGE.HISCSEC', 'HCTRIAGE.DiaCodDia',
                        'DIATRIA.DiaDescri')
                ->where('HCTRIAGE.HISCKEY', $value->HISCKEY)
                ->where('HCTRIAGE.HISTipDoc', $value->HISTipDoc)
                ->where('HCTRIAGE.HISCSEC', $value->HISCSEC)->first();

            //Si entra en un folio de triage 
            if($flTria != null){

                //Busca la prioridad del triage
                $prioridadTr = DB::table('GPOTRIA')
                    ->select('DiaCodGru', 'DiaDscGru')
                    ->where('DiaCodGru', $value->HISCLTR)->first();

                //Valida la prioridad del triage con el convenio
                $prioridadTrC = DB::table('EPSTRIA')
                    ->select('DiaCodGru', 'MENNIT')
                    ->where('MENNIT', $MENNIT)
                    ->where('DiaCodGru', $value->HISCLTR)->first();
                
                if($prioridadTrC == null){
                    $aceptaPr = 'EPS no acepta este Grupo';
                }else{
                    $aceptaPr = 'EPS acepta este Grupo';
                }

                //Informcion general de folio de triage
                $datosTriage = [
                    'prioridad' => $prioridadTr,
                    'aceptaPr' => $aceptaPr
                ];   


                $pdfContentTriage = $pdfContentTriage.'
                                        <div style="width: 50%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0; padding: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">CLASIFICACION DE TRIAGE: '.$prioridadTr->DiaDscGru.' </p>'.
                                        '</div>'.
                                        '<div style="width: 50%; float: left;">' . 
                                            '<p style = "font-weight:bold; margin: 0; padding: 0;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">'.$aceptaPr.' </p>'.
                                        '</div>';
            }

            //Descripcion del folio en HCCOM1DES
            $deshc1 = DB::table('HCCOM1DES')
                ->select('HISDesAtr', 'HISCSEC', 'HISTipDoc', 'HISCKEY', 'HISDesDet')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HISCSEC', $value->HISCSEC)->get();
            
            foreach ($deshc1 as $desHC1) {

                $pdfContent = $pdfContent. '
                                <div style="width: 100%; float: left; padding:0px">' . 
                                    '<p style = "font-weight:bold; padding: 0px;
                                        font-family: Arial, sans-serif;
                                        font-size: 14px ">'.$desHC1->HISDesAtr.' </p>'.
                                '</div>
                                <div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($desHC1->HISDesDet).'
                                </div>'. $pdfContentTriage;                

                $desHCC1 [] = [
                    $desHC1->HISDesAtr => $desHC1
                ];
            }

            

            $pteAudiometria = DB::table('AUDPACIEN')
                ->select('AudForCod', 'HISCSEC', 'HISTipDoc', 'HISCKEY', 'AudSchOD', 'AudSchOI', 'AudSchAte')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HISCSEC', $value->HISCSEC)
                ->where('AudForCod', 'OT12')->get();

            //Info de la evolucion tipo SOAP
            $dataSOAP = DB::table('HCCOMEVOS')
                ->select('EvoCnSoAp', 'HISCSEC', 'HISTipDoc', 'HISCKEY', 'EvoHoSoAp', 'EvoFeSoAp')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HISCSEC', $value->HISCSEC)->get();
            

            //Validacion de antecedentes del paciente 
            $antecedentes = DB::table('HCANTE')
                ->select('HISCSEC', 'HISTipDoc', 'HISCKEY', 'OBSERANTE', 'CALANTEC', 'AntAyuCod',
                        'AntTipCod')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HISCSEC', $value->HISCSEC)->get();

            
                
            //Validacion datos por gineco
            //Valores del PntCodi
            $PntCodi = [27, 43, 45];

            foreach($PntCodi as $valuePntCodi){
                
                $gineco = DB::table('HCCOM61')
                    ->leftJoin('MAESNT', 'MAESNT.SntCod', '=', 'HCCOM61.SntCod')
                    ->select('HCCOM61.SntCod', 'HCCOM61.HISCKEY', 'HCCOM61.HISTipDoc', 'HCCOM61.PntCodi', 'HCCOM61.HISCSEC', 'HCCOM61.AntObs',
                            'HCCOM61.AntObsFlu', 'HCCOM61.RNQCod', 'MAESNT.SntNom')
                    ->where('HCCOM61.HISCKEY', $MPCedu)
                    ->where('HCCOM61.HISTipDoc', $MPTDoc)
                    ->where('HCCOM61.HISCSEC', $value->HISCSEC)
                    ->where('HCCOM61.PntCodi' , $valuePntCodi)->first();
                
                if($gineco != null){  

                    $pdfContent = $pdfContent. '<div style="width: 100%; float: left; padding:0px">' . 
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">GINECO </p>'.
                                                '</div>'.
                                                '</div>
                                                <div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($gineco->AntObs).'</div>';
                }
                
                $gineco = [
                    'gineco' => $gineco
                ];
            }

            //Signos vitales por hora
            $SGNVTLH = DB::table('SGNVTLH')
                ->select('HISCSEC', 'HISTipDoc', 'HISCKEY', 'HISHIDR', 'HISViaTem', 'HISDPRES', 'HISDPRED',
                        'HISDTMP', 'HISDPUL', 'HISSATO', 'HISDFRC', 'HISDFRR', 'HISDPES', 'HISPERCEF', 'HISDTAL',
                        'HISPERABD', 'HISEscDoA', 'HISGLIS', 'HISDGLA', 'HISRichRa', 'HISRams', 'HISPunRC', 
                        'SVEstReCo', 'HISDPVC', 'HISPRIC', 'HISTAPUPI', 'HISTAPUP', 'HISPRPC', 'HISPRIA', 
                        'SVPPulSis', 'SVPPulDia', 'SVPPsoIde', 'SVPPulMed', 'HISREALU', 'HISREALUI', 'HisFrCaFe',
                        'HISPERS', 'SVPValPTr', 'HISTipEsD', 'HISPATRE', 'HISPIEL', 'HISRESP', 'HISNEUR', 'SVtObs',
                        'HISCHOR', 'SgVVlrPVC')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HISCSEC', $value->HISCSEC)->first();
            
            //si encuentra datos de signos vitales
            if($SGNVTLH != null){
                $pdfContent = $pdfContent. '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">SIGNOS VITALES </p>'.
                                             
                                                    '<p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION SISTOLICA: '.$SGNVTLH->HISDPRES.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION DIASTOLICA: '.$SGNVTLH->HISDPRED.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION VENOSA CENTRAL: '.$SGNVTLH->SgVVlrPVC.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">TEMPERATURA: '.$SGNVTLH->HISDTMP.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">FRECUENCIA RESPIRATORIA: '.$SGNVTLH->HISDFRR.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">FRECUENCIA CARDIACA: '.$SGNVTLH->HISDFRC.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PULSO: '.$SGNVTLH->HISDPUL.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">GLASGOW: '.$SGNVTLH->HISDGLA.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PESO: '.$SGNVTLH->HISDPES.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">TALLA: '.$SGNVTLH->HISDTAL.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">HIDRATACIÓN: '.$SGNVTLH->HISHIDR.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">VIA TEMPERATURA: '.$SGNVTLH->HISViaTem.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">SATURACION: '.$SGNVTLH->HISSATO.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PERIMETRO CEFALICIO: '.$SGNVTLH->HISPERCEF.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PERIMETRO ABDOMINAL: '.$SGNVTLH->HISPERABD.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">ESCALA DE DOLOR: '.$SGNVTLH->HISEscDoA.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">GLEASON: '.$SGNVTLH->HISGLIS.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">RICHMOND RASS: '.$SGNVTLH->HISRichRa.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">RAMSAY: '.$SGNVTLH->HISRams.' </p>
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">RIESGO CARDIOVASCULAR: '.$SGNVTLH->HISPunRC.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">ENFERMEDAD RENAL CRÓNICA: '.$SGNVTLH->SVEstReCo.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION VENOSA CENTRAL: '.$SGNVTLH->HISDPVC.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION INTRACRANEAL: '.$SGNVTLH->HISPRIC.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">TAMAÑO PUPILAR: Ojo Izq:' .$SGNVTLH->HISTAPUPI.', Ojo Der: '.$SGNVTLH->HISTAPUP.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION PERFUSION CEREBRAL: '.$SGNVTLH->HISPRPC.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION INTRA ABDOMINAL: '.$SGNVTLH->HISPRIA.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">PRESION PULMONAR: Sistolica: '.$SGNVTLH->SVPPulSis.' Diastolica: '.$SGNVTLH->SVPPulDia.' Media: '.$SGNVTLH->SVPPulMed.'</p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">REACCIONA A LA LUZ: Ojo Der'.$SGNVTLH->HISREALU.', Ojo Izq: '.$SGNVTLH->HISREALUI.'</p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">FRECUENCIA CARDIACA FETAL: '.$SGNVTLH->HisFrCaFe.' </p>                            
                                                    <p style = "padding: 0px; margin:0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES: '.$SGNVTLH->SVtObs.' </p>                            
                                            </div>';
            }
            //Evolucion con profesional
            $evolucion = DB::table('HCCOM33')
                ->leftJoin('HCCOM1', function ($join){
                    $join->on('HCCOM1.HISCKEY', '=', 'HCCOM33.HISCKEY')
                        ->on('HCCOM1.HISTipDoc', '=', 'HCCOM33.HISTipDoc')
                        ->on('HCCOM1.HISCSEC', '=', 'HCCOM33.HISCSEC');
                })
                ->leftJoin('MAEMED1', 'MAEMED1.MMCODM', '=', 'HCCOM1.HISCMMED')
                ->select('HCCOM33.HISCKEY', 'HCCOM33.HISTipDoc', 'HCCOM33.HISCSEC', 'HCCOM33.EvoIndMoE', 'HCCOM1.HISCMMED',
                        'MAEMED1.MMNomM', 'MAEMED1.MMRegM','HCCOM33.EVOHOR', 'HCCOM33.EVOFEC', 'HCCOM33.EVODES', 'HCCOM33.EVONum')
                ->where('HCCOM33.HISCKEY', $MPCedu)
                ->where('HCCOM33.HISTipDoc', $MPTDoc)
                ->where('HCCOM33.HISCSEC', $value->HISCSEC)->first();

            if($evolucion != null){
                if($evolucion->EvoIndMoE == 'E'){
                    $pdfContent = $pdfContent. '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">NOTAS ENFERMERIA </p>'.
                                                '</div>';
                    $ind = 'Nota';
                }else{
                    $pdfContent = $pdfContent. '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">EVOLUCIÓN MEDICO </p>'.
                                                '</div>';
                    $ind = 'Evolución';
                }

                $pdfContent = $pdfContent . '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'.nl2br($evolucion->EVODES).'</div>'.
                                            '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 12px ">'.$ind.' realizada por: '.trim($evolucion->MMNomM).' Fecha: '.Carbon::parse($evolucion->EVOFEC)->format('Y-m-d').' '.$evolucion->EVOHOR.'</p>'.
                                            '</div>';
            }
            
            
            //Escala de valoracion de respues
            $escalas = DB::table('ESCPAR')
                ->select('ESCNOM', 'ESCCOD')->get();

            //Valida si tiene escalas en el folio 
            $validacionE = DB::table('ESCPARRES')
                ->select('ESCCODRES')
                ->where('ESCPARRES.HISCKEY', $MPCedu)
                ->where('ESCPARRES.HISTipDoc', $MPTDoc)
                ->where('ESCPARRES.HISCSEC', $value->HISCSEC)->get();
            
            if(count($validacionE)>0){
                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">ESCALA DE VALORACION DE RESPUESTAS </p>'.
                                            '</div>';
            }
            
            foreach($escalas as $escalaCod){

                $escalaRespuesta = DB::table('ESCPARRES')
                    ->leftJoin('ESCPAR', 'ESCPAR.ESCCOD', '=', 'ESCPARRES.ESCCODRES')
                    ->leftJoin('ESCPAR1', function ($join){
                        $join->on('ESCPAR1.ESCCOD', '=', 'ESCPARRES.ESCCODRES')
                            ->on('ESCPAR1.ESCCODDET', '=', 'ESCPARRES.ESCDETRES');
                    })
                    ->leftJoin('ESCPAR2', function ($join){
                        $join->on('ESCPAR2.ESCCOD', '=', 'ESCPARRES.ESCCODRES')
                            ->on('ESCPAR2.ESCCODDET', '=', 'ESCPARRES.ESCDETRES')
                            ->on('ESCPAR2.ESCCODIND', '=', 'ESCPARRES.ESCINDRES');
                    })
                    ->select('ESCPARRES.ESCCODRES', 'ESCPAR.ESCNOM', 'ESCPARRES.ESCDETRES','ESCPAR1.ESCCAT', 
                            'ESCPARRES.ESCINDRES', 'ESCPAR2.ESCDESIND', 'ESCPAR2.ESCPUNIND', 'ESCPARRES.ESCPUNINR',
                            'ESCPARRES.HISCSEC', 'ESCPARRES.HISTipDoc', 'ESCPARRES.HISCKEY')
                    ->where('ESCPARRES.HISCKEY', $MPCedu)
                    ->where('ESCPARRES.HISTipDoc', $MPTDoc)
                    ->where('ESCPARRES.HISCSEC',  $value->HISCSEC) 
                    ->where('ESCPARRES.ESCCODRES', $escalaCod->ESCCOD)->get();

                if(count($escalaRespuesta)>0){   
                    
                    $pdfContent = $pdfContent .
                                                '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">'.$escalaRespuesta[0]->ESCNOM.'</p>'.
                                                '</div>';
                    
                    foreach($escalaRespuesta as $escala){
                        $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight:bold; padding: 0px; margin:0;
                                                            font-family: Arial, sans-serif;text-indent: 20px;
                                                            font-size: 14px ">'.$escala->ESCDETRES.'-'.$escala->ESCCAT.'</p>'.
                                                    '<p style = "padding: 0px; margin:0 ;
                                                            font-family: Arial, sans-serif; text-indent: 40px;
                                                            font-size: 12px ">'.$escala->ESCDESIND.': '.$escala->ESCPUNINR.'</p>'.
                                                    '</div>';
                    }

                    $escalaRes [] = [
                        $escalaCod->ESCNOM => $escalaRespuesta
                    ];
                }
            }

            //Diagnostico del folio 
            $dx = DB::table('HCDIAGN')
                ->leftJoin('MAEDIA', 'MAEDIA.DMCodi', '=', 'HCDIAGN.HCDXCOD')
                ->select('HCDIAGN.HCDXCLS', 'HCDIAGN.HISCSEC', 'HCDIAGN.HISTipDoc', 'HCDIAGN.HISCKEY', 'HCDIAGN.HCDXObs', 
                        'HCDIAGN.HCDXCOD', 'MAEDIA.DMNomb', 'HCDIAGN.HCDXApto')
                ->where('HCDIAGN.HISCKEY', $MPCedu)
                ->where('HCDIAGN.HISTipDoc', $MPTDoc)
                ->where('HCDIAGN.HISCSEC', $value->HISCSEC)->get();
            
            if(count($dx)>0){

                foreach($dx as $diagnostico){

                    //switch case de $dx->HCDXCLS
                    switch ($diagnostico->HCDXCLS) {
                        case 0:
                            $tipoDx = 'RELACIONADO';
                            $relacionador = $diagnostico->DMNomb;
                            break;
                        case 1:
                            $tipoDx = 'PRINCIPAL';
                            break;
                        case 2:
                            $tipoDx = 'DESCARTADO';
                            break;
                        case 3:
                            $tipoDx = 'ANATOFARMACOLOGICO';
                            break;
                        default:
                            $tipoDx = '';
                            break;
                    }
                
                    $pdfContent = $pdfContent . '<div style="width: 20%; float: left; ">' . 
                                                    '<p style = "font-weight:bold; margin: 0; 
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">DIAGNÓSTICO: </p>'.
                                                '</div>'.
                                                '<div style="width: 60%; float: left;margin:0">' . 
                                                    '<p style = "margin: 0; padding: 0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">'.$diagnostico->HCDXCOD.'  '.$diagnostico->DMNomb.' </p>'.
                                                '</div>'.
                                                '<div style="width: 20%; float: left;margin:0">' . 
                                                    '<p style = "margin: 0; padding: 0;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px "> TIPO: '.$tipoDx.' </p>'.
                                                '</div><br><div style="clear: both;"></div>';
                }
            }

            //Gestaciones del paciente
            $gestaciones = DB::table('HISGEACT')
                ->select('HisGeCtv', 'HISTipDoc', 'HISCKEY', 'HisGFchRg', 'HisGFPP', 'HisGFUM', 'HISTIPTOM', 'HisGFoli',
                        'HISESTGES', 'HISOBSANU')
                ->where('HISCKEY', $MPCedu)
                ->where('HISTipDoc', $MPTDoc)
                ->where('HisGFoli', $value->HISCSEC)->get();

            if(count($gestaciones)>0){
                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">GESTACIONES DEL PACIENTE </p>'.
                                            '</div>'.
                                            '<div style = " text-indent: 10px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 33%; float: left;">' . 
                                                        '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Consecutivo de gestación</p>' .
                                                        
                                                    '</div>' .

                                                    '<div style="width: 33%; float: left;">' . 
                                                        '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Ultima menstruación</p>' .
                                                        
                                                    '</div>' .

                                                    '<div style="width: 33%; float: left;">' . 
                                                        '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Fecha probable de parto</p>' .
                                                        
                                                    '</div>' .
                                                '</div><br><div style="clear: both;"></div>';
                
                foreach($gestaciones as $gestacion){

                    $pdfContent = $pdfContent . '<div style = " text-indent: 10px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 33%; float: left;">' . 
                                                        
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.number_format($gestacion->HisGeCtv,0).'</p>' .
                                                    '</div>' .

                                                    '<div style="width: 33%; float: left;">' . 
                                                        
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.Carbon::parse($gestacion->HisGFUM)->format('Y-m-d').'</p>' .
                                                    '</div>' .

                                                    '<div style="width: 33%; float: left;">' . 
                                                        
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.Carbon::parse($gestacion->HisGFPP)->format('Y-m-d').'</p>' .
                                                    '</div>' .
                                                '</div><br><div style="clear: both;"></div>';
                }
            }

            //Procedimientos de enfermeria 
            $procEnf = DB::table('HCCOM44')
                ->leftJoin('RSPNOQX', 'RSPNOQX.RNQCod', '=', 'HCCOM44.HCDIETCD')
                ->select('HCCOM44.HISCKEY', 'HCCOM44.HISTipDoc', 'RSPNOQX.RNQTip', 'HCCOM44.HISCSEC', 
                        'RSPNOQX.RNQClv', 'HCCOM44.HCDieDsc', 'HCCOM44.EvOdsEf', 'HCCOM44.HCDIETCD')
                ->where('HCCOM44.HISCKEY', $MPCedu)
                ->where('HCCOM44.HISTipDoc', $MPTDoc)
                ->where('HCCOM44.HISCSEC', $value->HISCSEC)->get();
            
            if(count($procEnf)>0){
                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">RECOMENDACIONES </p>'.
                                            '</div>';
                
                foreach($procEnf as $proc){
                    $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 14px ">'.$proc->RNQClv.'</p>'.
                                                '</div>'.
                                                '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($proc->HCDieDsc);
                }
            }

            //Procedimientos 
            $procedimientosOrd = DB::table('HCCOM51')
                ->leftJoin('HCCOM5', function ($join){
                    $join->on('HCCOM5.HISCKEY', '=', 'HCCOM51.HISCKEY')
                        ->on('HCCOM5.HISTipDoc', '=', 'HCCOM51.HISTipDoc')
                        ->on('HCCOM5.HISCSEC', '=', 'HCCOM51.HISCSEC')
                        ->on('HCCOM5.HCPrcCod', '=', 'HCCOM51.HCPrcCod');
                })
                ->leftJoin('MAEPRO', 'MAEPRO.PRCODI', '=', 'HCCOM51.HCPrcCod')
                ->leftJoin('MAEMED1', 'MAEMED1.MMCODM', '=', 'HCCOM51.HCCODMED')
                ->leftJoin('MAEMED1 AS MEDCAN', 'MEDCAN.MMUsuario', '=', 'HCCOM51.HCPrUsCaD')
                ->select('HCCOM51.HISCKEY', 'HCCOM51.HISTipDoc', 'HCCOM51.HISCSEC', 'HCCOM5.HCPrcTip', 'HCCOM51.HCPrcEst',
                        'HCCOM51.RPrUsrRgs', 'HCCOM51.HCPrcCod', 'MAEPRO.PrNomb', 'HCCOM51.HCPrcCns', 'HCCOM51.HCConclu', 'HCCOM51.HCResult', 'HCCOM5.HCCarIntL',
                        'HCCOM51.HCMedInt', 'HCCOM51.HCIntRes', 'HCCOM51.HCFeHInt', 'HCCOM51.HCFcHrAp', 'HCCOM5.HCDiaPT', 'HCCOM51.HCFcHrOrd', 
                        'HCCOM51.HCCODESP', 'HCCOM51.HCCODMED', 'MAEMED1.MMNomM', 'HCCOM5.HCPrStGr', 'HCCOM5.HCDosCod', 'HCCOM5.HCDosLot', 'HCCOM5.HisCPCan',
                        'HCCOM51.HCMoCnTp', 'HCCOM51.HCObsCan', 'HCCOM51.HCPrUsCaD', 'MEDCAN.MMNomM AS medCancela', 'HCCOM51.HCPrFhCaD', 'HCCOM5.HisCpObs')
                ->selectRaw("case HCCOM51.HCPrcEst
                                WHEN 'A' THEN 'REALIZADO'
                                WHEN 'C' THEN 'CANCELADO'
                                WHEN 'E' THEN 'EN PROCESO'
                                WHEN 'O' THEN 'PENDIENTE'
                                END AS estadoProc,
                            case HCCOM51.HCMoCnTp 
                                WHEN 3 THEN 'Medico'
                                WHEN 2 THEN 'Paciente'
                                WHEN 1 THEN 'Administrativo' 
                                END AS tipoCanc")
                ->where('HCCOM51.HISCKEY', $MPCedu)
                ->where('HCCOM51.HISTipDoc', $MPTDoc)
                ->where('HCCOM51.HISCSEC', $value->HISCSEC)
                ->where('HCCOM5.HCPrcTip', '!=', 5)
                ->orderBy('HCCOM5.HCPrcTip')->get();
            
            if(count($procedimientosOrd)>0){
            
                //recorre procedimientos
                foreach($procedimientosOrd as $procedimiento){
                    //Descripcion de procedimientos
                    $procedimientos = DB::table('HCCOM2DES')
                        ->select('HCCOM2DES.HISCKEY', 'HCCOM2DES.HISTipDoc', 'HCCOM2DES.HISCSEC', 'HCCOM2DES.HCPrcCod', 'HCCOM2DES.HCPrcCns',
                                'HCCOM2DES.HCDesAtr', 'HCCOM2DES.HCDscAtr')
                        ->where('HCCOM2DES.HISCKEY', $MPCedu)
                        ->where('HCCOM2DES.HISTipDoc', $MPTDoc)
                        ->where('HCCOM2DES.HISCSEC', $value->HISCSEC)
                        ->where('HCCOM2DES.HCPrcCod', $procedimiento->HCPrcCod)
                        ->where('HCCOM2DES.HCPrcCns', $procedimiento->HCPrcCns)
                        ->orderByDesc('HCDesAtr')->get();
                    
                        if($procedimiento->HCPrcTip == 4 || $procedimiento->HCPrcTip == 10){
                            $pdfContentPrH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE PROCEDIMIENTOS NO QUIRURGICO</p>'.
                                                        '</div>';

                            $pdfContentPr = $pdfContentPr . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';
                            if(count($procedimientos)>0){
                                $pdfContentPr .= '<div style="width: 100%; padding:0px">' . 
                                                    '<br><div style=" text-indent: 20px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[0]->HCDscAtr).'</div>'.
                                                    '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">FECHA Y HORA DE APLICACIÓN: '.Carbon::parse($procedimiento->HCFcHrAp)->format('Y-m-d H:i:s').' REALIZADO POR: '.$procedimiento->MMNomM .'</p>'.
                                                    '</div>'.
                                                    '<hr style="border: none; border-top: 2px solid #000; margin-top: 1px; clear: both;">'.
                                                    '<div style="width: 20%;">' . 
                                                        '<p style=" text-indent: 20px; font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">CONCLUSIONES</p>' .
                                                    '</div>' .
                                                    '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[1]->HCDscAtr).'</div>';
                            }

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentPr .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = " text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }            
                    
                    $procOrdenados [] = [
                        trim($procedimiento->HCPrcCod).'-'.trim($procedimiento->HCPrcCns) => [
                            'ordenamiento' => $procedimiento,
                            'descripcion' => $procedimientos
                        ]
                    ] ;
                }
            }


            $pdfContent = $pdfContent . $pdfContentPrH. $pdfContentPr;

            //Formulacion de medicamentos
            $formulacion = DB::table('FRMSMNS')
                ->leftJoin('MAECONC', 'MAECONC.CncCd', '=', 'FRMSMNS.CncCd')
                ->leftJoin('UNDMEDI', 'UNDMEDI.UnMdCod', '=', 'FRMSMNS.HCSmUndCd')
                ->leftJoin('MAEVIAPL', 'MAEVIAPL.ViAplCod', '=', 'FRMSMNS.HCFSVia')
                ->select('FRMSMNS.FRMCONGEN', 'FRMSMNS.CncCd', 'FRMSMNS.HCSmUndCd', 'FRMSMNS.HISCKEY',
                        'FRMSMNS.HISTipDoc', 'FRMSMNS.HCSmStGr', 'FRMSMNS.HISCSEC', 'FRMSMNS.HCFSVia', 'MAEVIAPL.ViAplDSc', 'FRMSMNS.HCFSFrH', 'MAECONC.CncDes', 
                        'FRMSMNS.FsmDscMdc', 'FRMSMNS.FSmCntDia', 'UNDMEDI.UnMdDes' , 'FRMSMNS.hisCanSum', 'FRMSMNS.HISCOBSFOR',
                        'FRMSMNS.MSForm', 'FRMSMNS.MSPrAc', 'FRMSMNS.MSCodi')
                ->selectRaw("CASE
                                WHEN FRMSMNS.HCFSFrH =74 THEN '7 Dias'
                                WHEN FRMSMNS.HCFSFrH =91 THEN '15 Minutos'
                                WHEN FRMSMNS.HCFSFrH =92 THEN '30 Minutos'
                                WHEN FRMSMNS.HCFSFrH =78 THEN '14 Dias'
                                WHEN FRMSMNS.HCFSFrH =12 THEN '12 horas'
                                WHEN FRMSMNS.HCFSFrH =6 THEN '6 Horas'
                                WHEN FRMSMNS.HCFSFrH =8 THEN '8 Horas'
                                WHEN FRMSMNS.HCFSFrH =2 THEN '2 Horas'
                                WHEN FRMSMNS.HCFSFrH =1 THEN '1 Hora'
                                WHEN FRMSMNS.HCFSFrH =95 THEN 'Ahora'
                                WHEN FRMSMNS.HCFSFrH =90 THEN 'Bolo'
                                WHEN FRMSMNS.HCFSFrH =94 THEN '5 Minutos'
                                WHEN FRMSMNS.HCFSFrH =24 THEN '24 Horas'
                                WHEN FRMSMNS.HCFSFrH =93 THEN 'Dosis Unica'
                                WHEN FRMSMNS.HCFSFrH =79 THEN '21 Dias'
                                WHEN FRMSMNS.HCFSFrH =80 THEN '28 Dias'
                                WHEN FRMSMNS.HCFSFrH =99 THEN 'Inf. Continua'
                                WHEN FRMSMNS.HCFSFrH =3 THEN '3 Horas'
                                WHEN FRMSMNS.HCFSFrH =4 THEN '4 Horas'
                            END AS frecuencia
                            ")
                ->where('FRMSMNS.HISCKEY', $MPCedu)
                ->where('FRMSMNS.HISTipDoc', $MPTDoc)
                ->where('FRMSMNS.HISCSEC', $value->HISCSEC)
                ->where('FRMSMNS.HCSmStGr', '!=', 'X')->get();

            
            if(count($formulacion)>0){
            
                //Switch case de $formulacion->HCSmStGr
                switch ($formulacion[0]->HCSmStGr) {
                    case 'O':
                        $estado = 'Nuevo';
                        break;
                    case 'C':
                        $estado = 'Continuar';
                        break;
                    case 'N':
                        $estado = 'Sin Cambios';
                        break;
                    case 'M':
                        $estado = 'Modificado';
                        break;
                        break;
                    case 'S':
                        $estado = 'Suspendido';
                        break;
                    default:
                        $estado = '';
                        break;
                }

                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">FORMULA MÉDICA</p>'.
                                            '</div>'.
                                            '<div style = " text-indent: 10px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 10%; float: left;">' . 
                                                        '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 11px;">Cantidad</p>' .
                                                    '</div>' .
                                                    '<div style="width: 20%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Dosis</p>' .
                                                    '</div>' .
                                                    '<div style="width: 25%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Descripción</p>' .
                                                    '</div>' .
                                                    '<div style="width: 20%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Vía</p>' .
                                                    '</div>' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Frecuencia</p>' .
                                                    '</div>' .
                                                    '<div style="width: 10%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Acción</p>' .
                                                    '</div>' .
                                                '</div><br><div style="clear: both;"></div>';

                foreach($formulacion as $form){
                    $pdfContent = $pdfContent . '<div style = " text-indent: 10px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 10%; float: left;">' . 
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 11px;">'.number_format($form->hisCanSum,0).'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 20%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">'.number_format($form->FSmCntDia,0).' '.$form->UnMdDes.'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 25%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">'.trim($form->FsmDscMdc).'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 20%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">'.trim($form->ViAplDSc).'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">'.$form->frecuencia.'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 10%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">'.$estado.'</p>' .
                                                    '</div>' .
                                                '</div><br><div style="clear: both;"></div>';
                }
            }


            //Demas procedimientos im dx, lab, pro qx, consultas y terapias
            if(count($procedimientosOrd)>0){
            
                //recorre procedimientos
                foreach($procedimientosOrd as $procedimiento){
                    //Descripcion de procedimientos
                    $procedimientos = DB::table('HCCOM2DES')
                        ->select('HCCOM2DES.HISCKEY', 'HCCOM2DES.HISTipDoc', 'HCCOM2DES.HISCSEC', 'HCCOM2DES.HCPrcCod', 'HCCOM2DES.HCPrcCns',
                                'HCCOM2DES.HCDesAtr', 'HCCOM2DES.HCDscAtr')
                        ->where('HCCOM2DES.HISCKEY', $MPCedu)
                        ->where('HCCOM2DES.HISTipDoc', $MPTDoc)
                        ->where('HCCOM2DES.HISCSEC', $value->HISCSEC)
                        ->where('HCCOM2DES.HCPrcCod', $procedimiento->HCPrcCod)
                        ->where('HCCOM2DES.HCPrcCns', $procedimiento->HCPrcCns)
                        ->orderByDesc('HCDesAtr')->get();
                    
                        if($procedimiento->HCPrcTip == 2){
                            $pdfContentLabH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE LABORATORIO</p>'.
                                                        '</div>';

                            $pdfContentLab = $pdfContentLab . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';
                            if(count($procedimientos)>0){
                                $pdfContentLab .= '<div style="width: 100%; padding:0px">' . 
                                                    '<br><div style=" text-indent: 20px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[0]->HCDscAtr).'</div>'.
                                                    '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">FECHA Y HORA DE APLICACIÓN: '.Carbon::parse($procedimiento->HCFcHrAp)->format('Y-m-d H:i:s').' REALIZADO POR: '.$procedimiento->MMNomM .'</p>'.
                                                    '</div>'.
                                                    '<hr style="border: none; border-top: 2px solid #000; margin-top: 1px; clear: both;">'.
                                                    '<div style="width: 20%;">' . 
                                                        '<p style=" text-indent: 20px; font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">CONCLUSIONES</p>' .
                                                    '</div>' .
                                                    '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[1]->HCDscAtr).'</div>';
                            }

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentLab .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }            

                        if($procedimiento->HCPrcTip == 1){
                            $pdfContentImDxH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE IMAGENES DIAGNÓSTICAS</p>'.
                                                        '</div>';

                            $pdfContentImDx = $pdfContentImDx . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';
                            if(count($procedimientos)>0){
                                $pdfContentImDx .= '<div style="width: 100%; padding:0px">' . 
                                                    '<br><div style=" text-indent: 20px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[0]->HCDscAtr).'</div>'.
                                                    '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">FECHA Y HORA DE APLICACIÓN: '.Carbon::parse($procedimiento->HCFcHrAp)->format('Y-m-d H:i:s').' REALIZADO POR: '.$procedimiento->MMNomM .'</p>'.
                                                    '</div>'.
                                                    '<hr style="border: none; border-top: 2px solid #000; margin-top: 1px; clear: both;">'.
                                                    '<div style="width: 20%;">' . 
                                                        '<p style=" text-indent: 20px; font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">CONCLUSIONES</p>' .
                                                    '</div>' .
                                                    '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[1]->HCDscAtr).'</div>';
                            }

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentImDx .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }  

                        if($procedimiento->HCPrcTip == 3){
                            $pdfContentTerH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE TERAPIAS</p>'.
                                                        '</div>';

                            $pdfContentTer = $pdfContentTer . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';
                            if(count($procedimientos)>0){
                                $pdfContentTer .= '<div style="width: 100%; padding:0px">' . 
                                                    '<br><div style=" text-indent: 20px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[0]->HCDscAtr).'</div>'.
                                                    '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">FECHA Y HORA DE APLICACIÓN: '.Carbon::parse($procedimiento->HCFcHrAp)->format('Y-m-d H:i:s').' REALIZADO POR: '.$procedimiento->MMNomM .'</p>'.
                                                    '</div>'.
                                                    '<hr style="border: none; border-top: 2px solid #000; margin-top: 1px; clear: both;">'.
                                                    '<div style="width: 20%;">' . 
                                                        '<p style=" text-indent: 20px; font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">CONCLUSIONES</p>' .
                                                    '</div>' .
                                                    '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimientos[1]->HCDscAtr).'</div>';
                            }

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentTer .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }  
                        
                        if($procedimiento->HCPrcTip == 5){
                            $pdfContentPrQxH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE CIRUGIA</p>'.
                                                        '</div>';

                            $pdfContentPrQx = $pdfContentPrQx . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentPrQx .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }
                        
                        if($procedimiento->HCPrcTip == 8){
                            $pdfContentCxH =  '<div style="width: 100%; padding:0px">' . 
                                                            '<p style = "font-weight:bold; padding: 0px;
                                                                font-family: Arial, sans-serif;
                                                                font-size: 14px ">ORDENES DE CONSULTAS</p>'.
                                                        '</div>';

                            $pdfContentCx = $pdfContentCx . '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                                '<div style="width: 20%; float: left;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Cantidad</p>' .
                                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->HisCPCan.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">Descripción</p>' .
                                                                    '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->PrNomb.'</p>' .
                                                                '</div>' .
                                                                '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                    '<p style="font-weight: bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$procedimiento->estadoProc.'</p>' .
                                                                
                                                                '</div>' .
                                                            '</div><br><div style="clear: both;"></div>';

                            if($procedimiento->HCPrcEst == 'C'){
                                $pdfContentTer .= '<div style="width: 100%; padding:0px">' . 
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$procedimiento->tipoCanc.'</p>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($procedimiento->HCObsCan).'</div>'.
                                                    '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">CANCELADO POR: '.$procedimiento->medCancela.' FECHA Y HORA: '.Carbon::parse($procedimiento->HCPrFhCaD)->format('Y-m-d H:i:s').'</p>'.
                                                '</div>';
                            }
                        }
                }
            }

            $pdfContent = $pdfContent . $pdfContentLabH. $pdfContentLab. $pdfContentImDxH. $pdfContentImDx. $pdfContentTerH. $pdfContentTer . $pdfContentPrQxH. $pdfContentPrQx . $pdfContentCxH. $pdfContentCx;

            //Informacion de interconsultas 
            $interconsultas = DB::table('INTERCN')
                ->join('MAEESP', 'MAEESP.MECodE', '=', 'INTERCN.MECodE')
                ->leftJoin('MAEMED1', 'MAEMED1.MMUsuario', '=', 'INTERCN.IntUsrRsp')
                ->leftJoin('MAEMED1 AS MEDCAN', 'MEDCAN.MMUsuario', '=', 'INTERCN.IntUsrCan')
                ->select('INTERCN.HISCKEY', 'INTERCN.HISTipDoc', 'INTERCN.HISCSEC', 'INTERCN.IntDiaPT', 'INTERCN.IntEst',
                        'MAEESP.MENomE', 'INTERCN.IntObsOrd', 'INTERCN.IntFchRsl', 'INTERCN.IntUsrRsp', 'MAEMED1.MMNomM', 'INTERCN.IntDscRsl',
                        'INTERCN.IntMoCnTp', 'INTERCN.IntObsCan', 'INTERCN.IntUsrCan', 'INTERCN.IntFchCan', 'INTERCN.MECodE', 'MEDCAN.MMNomM AS medCancela')
                ->selectRaw("CASE INTERCN.IntEst
                                WHEN 'A' THEN 'Atendido'
                                WHEN 'O' THEN 'Pendiente'
                                ELSE '' END AS estadoDsc,
                            CASE INTERCN.IntMoCnTp 
                                WHEN 3 THEN 'Medico'
                                WHEN 2 THEN 'Paciente'
                                WHEN 1 THEN 'Administrativo' 
                                END AS tipoCanc ")
                ->where('INTERCN.HISCKEY', $MPCedu)
                ->where('INTERCN.HISTipDoc', $MPTDoc)
                ->where('INTERCN.HISCSEC', $value->HISCSEC)->get();
            
            if(count($interconsultas)>0){
                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">INTERCONSULTAS</p>'.
                                            '</div>';

                foreach($interconsultas as $interconsulta){
                    $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                    '<div style="width: 60%; float: left;">' . 
                                                        '<p style = " padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">INTERCONSULTA POR '.$interconsulta->MENomE.'</p>'.
                                                    '</div>' .
                                                    '<div style="width: 40%; float: left;">' . 
                                                        '<p style = " padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px ">Fecha de la orden: '.Carbon::parse($value->HisFHorAt)->format('Y-m-d').' '.'<strong>'.$interconsulta->estadoDsc.'</strong></p>'.
                                                    '</div>' .
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif; text-indent: 20px;
                                                        font-size: 12px ">OBSERVACIONES</p>'.
                                                    '<p style = "padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">'.$interconsulta->IntObsOrd.'</p>'.
                                                    '<p style = "font-weight:bold; padding: 0px;
                                                        font-family: Arial, sans-serif; text-indent: 20px;
                                                        font-size: 12px ">RESULTADOS</p>'.
                                                    '<p style = "padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">'.$interconsulta->IntDscRsl.'</p>'.
                                                '</div>';

                    if($interconsulta->IntEst == 'A' || $interconsulta->IntEst == 'O'){
                        $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "padding: 0px;
                                                        font-family: Arial, sans-serif;
                                                        font-size: 12px ">FECHA Y HORA DE APLICACIÓN: '.Carbon::parse($interconsulta->IntFchRsl)->format('Y-m-d H:i:s').' REALIZADO POR: '.$interconsulta->MMNomM.'</p>'.
                                                    '</div>';
                    }else{
                        $pdfContent .= '<div style="width: 100%; padding:0px">' . 
                                            '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">MOTIVO DE CANCELACIÓN: '.$interconsulta->tipoCanc.'</p>'.
                                            '<p style = "font-weight: bold; text-indent: 20px; padding: 0px;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">OBSERVACIONES</p>'.
                                            '<div style="text-indent: 20px; margin:0px; padding: 0px; white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($interconsulta->IntObsCan).'</div>'.
                                            '<p style = " text-indent: 20px; padding: 0px;
                                                font-family: Arial, sans-serif;
                                                font-size: 12px ">CANCELADO POR: '.$interconsulta->medCancela.' FECHA Y HORA: '.Carbon::parse($interconsulta->IntFchCan)->format('Y-m-d H:i:s').'</p>'.
                                        '</div>';
                    }
                }
            }

            //Informacion de cirugias 
            $infoQX = DB::table('PROCIR')
                ->select('MPCedu', 'MPTDoc', 'ProFliCx', 'ProMCDpto', 'ProEmpCod', 'ProEPS', 'ProCirCod', 
                        'ProEsta')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('ProFliCx', $value->HISCSEC)
                ->whereIn('ProEsta', [4,5])->get();

            if(count($infoQX)>0){
                $pdfContent = $pdfContent . '<div style="width: 100%; padding:0px">' . 
                                                '<p style = "font-weight:bold; padding: 0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 12px "> <span style="border-bottom: 2px solid;">CIRUGÍAS</span></p>'.
                                            '</div>';
                foreach($infoQX as $info){
                    $cirugias = DB::table('PROCIR')
                        ->join('PROCIR1', 'PROCIR1.ProCirCod', '=', 'PROCIR.ProCirCod')
                        ->join('MAEPRO', 'MAEPRO.PRCODI', '=', 'PROCIR1.CrgCod')
                        ->leftJoin('MAEMED1', 'MAEMED1.MMCODM', '=', 'PROCIR1.MedCod')
                        ->leftJoin('MAEESP', 'MAEESP.MECodE', '=', 'PROCIR1.EspcCod')
                        ->leftJoin('VIAS', 'VIAS.ViaCod', '=', 'PROCIR1.ViaCod')
                        ->select('PROCIR.MPCedu', 'PROCIR.MPTDoc', 'PROCIR.ProFliCx', 'PROCIR.ProEsta', 'PROCIR.ProCirCod', 'PROCIR1.CrgPrcCns', 'PROCIR1.CrgCod', 'MAEPRO.PRNOMB',
                                'PROCIR.ProEmpCod', 'PROCIR.ProMCDpto', 'PROCIR1.CrgCnt',
                                'PROCIR.ProSit', 'PROCIR.ProFec', 'PROCIR1.MedCod', 'MAEMED1.MMNomM', 'PROCIR1.CrgHnrCi', 'PROCIR1.EspcCod', 'MAEESP.MENomE', 'PROCIR1.ViaCod', 'VIAS.ViaDsc', 'PROCIR.ProSit')
                        ->selectRaw("CASE PROCIR.ProSit
                                        WHEN 1 THEN 'PROGRAMADA'
                                        WHEN 2 THEN 'URGENTE'
                                        ELSE '' END AS TipoQX")
                        ->where('PROCIR.ProCirCod', $info->ProCirCod)->get();

                    if(count($cirugias) > 0){
                        foreach($cirugias as $cirugia){
                            $pdfContent = $pdfContent . '<div style = "width: 100%; padding:0px;" >' .
                                                            '<div style="width: 10%; float: left;">' . 
                                                                '<p style="font-weight: bold; margin: 1px; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">CANT</span></p><br>' .
                                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$cirugia->CrgCnt.'</p>' .
                                                            '</div>' .
                                                            '<div style="width: 10%; float: left; margin: 0;">' . 
                                                                '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">CÓDIGO</span></p><br>' .
                                                                '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$cirugia->CrgCod.'</p>' .
                                                            '</div>' .
                                                            '<div style="width: 60%; float: left; margin: 0;">' . 
                                                                '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">DESCRIPCIÓN</span></p><br>' .
                                                                '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$cirugia->PRNOMB.'</p>' .
                                                            '</div>' .
                                                            '<div style="width: 20%; float: left; margin: 0;">' . 
                                                                '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">Grupo Quirúrgico</span></p><br>' .
                                                                '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$cirugia->CrgHnrCi.'</p>' .
                                                            '</div>' .
                                                        '</div><br><div style="clear: both;"></div><br>'.

                                                        '<div style = " text-indent: 20px; width: 100%; padding:0px;" >' .
                                                            '<div style="width: 40%; float: left;">' . 
                                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Médico: </Strong>'.$cirugia->MMNomM.'</p>' .
                                                            '</div>' .
                                                            '<div style="width: 40%; float: left;">' . 
                                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Especialidad: </Strong>'.$cirugia->MENomE.'</p>' .
                                                            '</div>' .
                                                            '<div style="width: 20%; float: left;">' . 
                                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Vía:</Strong>'.$cirugia->ViaDsc.'</p>' .
                                                            '</div>' .
                                                        '</div><br><div style="clear: both;"></div>'.
                                                        '<hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">';
                        }
                    }

                    //DESCRIPCION QX
                    $desQx = DB::table('DESCIRMED')
                        ->leftJoin('MAEMED1', 'MAEMED1.MMCODM', '=', 'DESCIRMED.CodMed')
                        ->leftJoin('MAEESP', 'MAEESP.MECodE', '=', 'DESCIRMED.CodEsp')
                        ->leftJoin('MAEDIA as DIAENT', 'DIAENT.DMCodi', '=', 'DESCIRMED.DiaEnt')
                        ->leftJoin('MAEDIA as DIASAL', 'DIASAL.DMCodi', '=', 'DESCIRMED.DiaSal')
                        ->leftJoin('MAETPANS', 'MAETPANS.TpAnsCod', '=', 'DESCIRMED.TipAne')
                        ->select('DESCIRMED.CodMed', 'MAEMED1.MMNomM', 'MAEESP.MENomE', 'DESCIRMED.DiaEnt', 'DIAENT.DMNomb AS DIAEntr', 'DESCIRMED.DiaSal', 'DIASAL.DMNomb AS DIASali',
                                'DESCIRMED.TipHer', 'MAETPANS.TpAnsDsc', 'DESCIRMED.CanSan', 'DESCIRMED.ViaIng', 
                                'DESCIRMED.FECFINCIR', 'DESCIRMED.HorFinCir', 'DESCIRMED.FECINICIR', 'DESCIRMED.HorIniCir', 'DESCIRMED.TiePer', 'DESCIRMED.TieClamp',
                                'DESCIRMED.DesCir', 'DESCIRMED.DesCom', 'DESCIRMED.DesIndCom', 'DESCIRMED.DesIndTej')
                        ->selectRaw("CASE DESCIRMED.TipHer
                                        WHEN 1 THEN 'LIMPIA' 
                                        WHEN 2 THEN 'LIMPIA CONTAMINADA' 
                                        WHEN 3 THEN 'CONTAMINADA' 
                                        WHEN 4 THEN 'SUCIA' END AS TipHerida,
                                    CASE DESCIRMED.ViaIng
                                        WHEN 'U' THEN 'UNICA VIA'
                                        WHEN 'D' THEN 'DIFERENTE'
                                        WHEN 'B' THEN 'BILATERAL' END AS ViaI")
                        ->where('DESCIRMED.CodCir', $info->ProCirCod)->first();
                    //AYUDANTES 
                    $ayudantes = DB::table('PROCIR2')
                        ->leftJoin('HONRIOS', 'HONRIOS.HnrCod', '=', 'PROCIR2.PersTip')
                        ->leftJoin('MAEMED1', 'MAEMED1.MMCODM', '=', 'PROCIR2.PersCod')
                        ->select('PROCIR2.PersTip', 'PROCIR2.ProCirCod', 'PROCIR2.ProMCDpto', 'PROCIR2.ProEmpCod', 'PROCIR2.PersEst', 'HONRIOS.HnrDsc', 'MAEMED1.MMNomM',
                                'PROCIR2.PersCod')
                        ->where('PROCIR2.ProCirCod', $info->ProCirCod)->get();
                            
                    
                    if($desQx != null){
                        $pdfContent = $pdfContent . '<hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">'.
                                                    '<div style="width: 100%; padding:0px">' . 
                                                        '<p style = "font-weight:bold; padding: 0px;
                                                            font-family: Arial, sans-serif;
                                                            font-size: 12px "> <span style="border-bottom: 2px solid;">DESCRIPCÓN CIRUGÍA</span></p>'.
                                                    '</div>';
                        
                        $pdfContent = $pdfContent . '<div style = " text-indent: 30px; width: 100%; padding:0px;" >' .
                                                        '<div style="width: 40%; float: left;">' . 
                                                            '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                                    <strong>Médico: </Strong>'.$desQx->CodMed.' '.$desQx->MMNomM.'</p>' .
                                                        '</div>' .
                                                        '<div style="width: 40%; float: left;">' . 
                                                            '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Especialidad: </Strong>'.$desQx->MENomE.'</p>' .
                                                            '<i class="far fa-check-square"></i> '.
                                                        '</div>' .
                                                    '</div><br><div style="clear: both;"></div>';

                        $pdfContent = $pdfContent . '<div style = "width: 100%; padding:0px;" >' .
                                            '<div>' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Diagnostico Preoperatorio: </Strong>'.$desQx->DiaEnt.' '.$desQx->DIAEntr.'</p>' .
                                            '</div>' .
                                            '<div >' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Diagnostico Postoperatorio:: </Strong>'.$desQx->DiaSal.' '.$desQx->DIASali.'</p>' .
                                            '</div><div style="clear: both;"></div>' .

                                            '<div>
                                            <div style="width: 40%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tipo de Herida: </Strong>'.$desQx->TipHerida.'</p>' .
                                                '</div>' .
                                                '<div style="width: 30%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tipo de Anestesia: </Strong>'.$desQx->TpAnsDsc.'</p>' .
                                                '</div>' .
                                                '<div style="width: 30%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tipo de Cirugia: </Strong>'.$cirugia->TipoQX.'</p>' .
                                            '   </div>' . 
                                            '</div><div style="clear: both;"></div>'.

                                            '<div>'.
                                                '<div style="width: 40%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Realizacion Acto Quirurgico: </Strong>'.Carbon::parse($desQx->FECINICIR)->format('Y-m-d').'</p>' .
                                                '</div>' .
                                                '<div style="width: 30%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Hora Inicio: </Strong>'.$desQx->HorIniCir.'</p>' .
                                                '</div>' .
                                                '<div style="width: 30%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Hora Final: </Strong>'.$desQx->HorFinCir.'</p>' .
                                                '</div>' . 
                                            '</div><div style="clear: both;"></div>'.

                                            '<div>'.
                                                '<div style="width: 40%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tiempo de Perfusión: </Strong>'.$desQx->TiePer.' <strong> minuto</strong></p>' .
                                                '</div>' .
                                                '<div style="width: 30%; float: left;">' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tiempo de Clamp: </Strong>'.$desQx->TieClamp.'<strong> minuto</strong></p>' .
                                                '</div>' .
                                            '</div><div style="clear: both;"></div>'.

                                            '<div>'.
                                                '<div>' . 
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Descripción quirurgica</strong></p>' .
                                                '</div>' .
                                                '<div style="white-space: pre-wrap; font-family: Arial, sans-serif; font-size: 12px;">'. nl2br($desQx->DesCir).'</div>' .
                                            
                                            '</div><div style="clear: both;"></div>'.
                                    '</div><br><div style="clear: both;"></div>';

                        if($desQx->DesIndCom == 'N'){
                            $pdfContent .= '<div>'.
                                            '<div style="width: 20%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Complicaciones: </p>'.
                                            '</div>' .
                                            
                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>SI</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                </div>' .
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>NO</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                    X
                                                </div>' .
                                            '</div>' .
                                        '</div><div style="clear: both;"></div>';
                        }else{
                            $pdfContent .= '<div>'.
                                            '<div style="width: 20%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Complicaciones: </p>'.
                                            '</div>' .
                                            
                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>SI</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                    X
                                                </div>' .
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>NO</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                </div>' .
                                            '</div>' .
                                        '</div><div style="clear: both;"></div>';
                        }

                        if($desQx->DesIndTej == 'S'){
                            $pdfContent .= '<div>'.
                                            '<div style="width: 20%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tejidos enviados a patología: </p>'.
                                            '</div>' .
                                            
                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>SI</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                X
                                                </div>' .
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>NO</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                </div>' .
                                            '</div>' .
                                        '</div><div style="clear: both;"></div>';
                        }else{
                            $pdfContent .= '<div>'.
                                            '<div style="width: 20%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>Tejidos enviados a patología:</p>'.
                                            '</div>' .
                                            
                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>SI</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                </div>' .
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>NO</p>'.
                                            '</div>' .

                                            '<div style="width: 5%; float: left;">' . 
                                                '<div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;">
                                                X
                                                </div>' .
                                            '</div>' .
                                        '</div><div style="clear: both;"></div>';
                        }

                        if(count($ayudantes)>0){
                            $pdfContent .= '<br><div style = "width: 100%; padding:0px;" >' .
                                                '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                    <span style="border-bottom: 2px solid;"><strong>OTROS PARTICIPANTES</strong></span></p>' .
                                            '</div>'.
                                            '<div style = "text-indent: 20px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 1px; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">CÓDIGO</span></p>' .
                                                    '</div>' .
                                                    '<div style="width: 35%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">NOMBRE</span></p>' .
                                                    '</div>' .
                                                    '<div style="width: 35%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">TIPO</span></p>' .
                                                    '</div>' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="font-weight: bold; margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px;"> <span style="border-bottom: 2px solid;">PARTICIPÓ?</span></p>' .
                                                    '</div>' .
                                                '</div><div style="clear: both;"></div><br>';
                            foreach($ayudantes as $ayudante){
                                $pdfContent .= '<div style = "text-indent: 20px; width: 100%; padding:0px;" >' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">'.trim($ayudante->PersCod).'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 35%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.trim($ayudante->MMNomM).'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 35%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$ayudante->HnrDsc.'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 15%; float: left; margin: 0;">' . 
                                                        '<p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">'.$ayudante->PersEst.'</p>' .
                                                    '</div>' .
                                                '</div><div style="clear: both;"></div><br>';
                            }
                        }
                        
                    }
                }
            }

            
            //Incapacidades
            $incapacidades = DB::table('INCPAC')
                ->leftJoin('CONAFI', 'CONAFI.ConCodAfi', '=', 'INCPAC.ConCodAfi')
                ->leftJoin('MAECAUE', 'MAECAUE.CECODIGO', '=', 'INCPAC.IncCauE')
                ->select('INCPAC.IncSedPad', 'INCPAC.ConCodAfi', 'INCPAC.IncConIng', 'INCPAC.IncTipDoc', 'INCPAC.IncDocAfi', 
                        'INCPAC.IncCodFol', 'INCPAC.IncEmpPac', 'INCPAC.IncDocCod', 'INCPAC.IncConPac', 'INCPAC.IncFecReg', 
                        'INCPAC.IncDiaPri', 'CONAFI.ConDesAfi', 'INCPAC.IncFecIni', 'INCPAC.IncFecFin', 'INCPAC.IncDiaInc', 
                        'INCPAC.IncConAcc', 'INCPAC.IncCodMed', 'INCPAC.IncUsuReg', 'INCPAC.IncCodEsp', 'INCPAC.IncSOAT', 'INCPAC.IncCauE', 'MAECAUE.CEDETALL',
                        'INCPAC.IncObsMed', 'INCPAC.IncProAfi', 'INCPAC.IncLugAte', 'INCPAC.IncTipAte', 'INCPAC.IncFchAcc')
                ->where('INCPAC.IncTipDoc', $MPTDoc)
                ->where('INCPAC.IncDocAfi', $MPCedu)
                ->where('INCPAC.IncCodFol', $value->HISCSEC)->get();
            
            if(count($incapacidades)>0){
                $pdfContent = $pdfContent . '<hr style="border: none; border-top: 1px solid #000; margin-top: 0px; clear: both;"></hr>'.
                                            '<div style="width: 100%; padding:0px;">' . 
                                                '<p style = "font-weight:bold; padding: 0px; margin:0px;
                                                    font-family: Arial, sans-serif;
                                                    font-size: 14px ">CERTIFICADO DE INCAPACIDAD</p>'.
                                            '</div><br>';

                foreach($incapacidades as $incapacidad){

                    if($incapacidad->IncProAfi == 'N'){
                        $prorrog = 'NO';
                    }else{
                        $prorrog = 'SI';
                    }

                    if($incapacidad->IncFchAcc < '1900-01-01'){
                        $fechaAcc = "   // 00:00";
                    }else{
                        $fechaAcc = Carbon::parse($incapacidad->IncFchAcc)->format('Y-m-d');
                    }
                    
                    $pdfContent .= '<div style="width: 100%; padding: 0px;">
                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                            <tr>
                                                <td style="width: 60%; padding: 5px; border: 1px solid #000; font-weight:bold; font-size: 10px;">Nombre:</td>
                                                <td style="width: 20%; padding: 5px; border: 1px solid #000; font-weight:bold; font-size: 10px;">Diagnostico</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000; font-weight:bold; font-size: 10px;">Día</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000; font-weight:bold; font-size: 10px;">Mes</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000; font-weight:bold; font-size: 10px;">Año</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 60%; padding: 5px; border: 1px solid #000;font-weight:bold;">'.
                                                    '<div style="width: 70%; float: left;">' . 
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                                <strong></Strong>'.$paciente->MPNOMC.'</p>' .
                                                    '</div>' .
                                                    '<div style="width: 30%; float: left;">' . 
                                                        '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;"><strong>'.$paciente->MPTDoc.' '.$paciente->MPCedu.'</Strong></p>' .
                                                        '<i class="far fa-check-square"></i> '.
                                                    '</div>' .
                                                '</td>
                                                <td style="width: 20%; padding: 5px; border: 1px solid #000; font-weight:bold;">'.trim($incapacidad->IncDiaPri).'</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000;font-weight:bold;">'.Carbon::parse($incapacidad->IncFecReg)->format('d').'</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000;font-weight:bold;">'.Carbon::parse($incapacidad->IncFecReg)->format('m').'</td>
                                                <td style="width: 3.3%; padding: 5px; border: 1px solid #000;font-weight:bold;">'.Carbon::parse($incapacidad->IncFecReg)->format('Y').'</td>
                                            </tr>
                                        </table>
                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                            <tr>
                                                <td style="width: 15%; padding: 5px; border: 1px solid #000; font-weight:bold">Ocupacion: '.$paciente->MODesPri.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 15%; padding: 5px; border: 1px solid #000; font-weight:bold">Empresa: '.$paciente->EmpDsc.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 15%; padding: 5px; border: 1px solid #000; font-weight:bold">Tipo de incapacidad: '.$incapacidad->ConDesAfi.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 15%; padding: 5px; border: 1px solid #000;">'.
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Fecha Inicia: </Strong>'.Carbon::parse($incapacidad->IncFecIni)->format('Y-m-d').'  '.
                                                        '<strong>Fecha Fin: </strong>'.Carbon::parse($incapacidad->IncFecFin)->format('Y-m-d').' '.
                                                        '<strong>Días De Incapacidad O Licencia :</strong>'.$incapacidad->IncDiaInc.'</p><br>' .

                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Causa Externa: </Strong>'.$incapacidad->CEDETALL.'  '.
                                                        '<strong>Tipo de Atención: </strong>'.$value->ClaPro.' '.
                                                        '<strong>Procedimiento :</strong></p><br>' .

                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Diagnóstico Relacionador: </Strong> '.$relacionador.'</p><br>' .

                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Fecha de Accidente: </Strong>'.$fechaAcc.'  '.
                                                        '<strong>Prórroga: </strong>'.$prorrog.' '.
                                                        '<strong>Expedida En :</strong>'.$sede->MCDnom.'-'.$pabeEgr.'</p><br>' .
                                                    
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Empresa Donde Trabaja: </Strong>'.$paciente->MPEmpTra.'</p><br>' .
                                                    
                                                    '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">
                                                        <strong>Observaciones del profesional: </Strong>'.$incapacidad->IncObsMed.'</p><br>' .
                                                '</td>
                                            </tr>
                                        </table>
        
                                    </div>';

                }
            }

            //Firma

            if(count($incapacidades) > 0){
                
                $pdfContent = $pdfContent . '<div style="width: 50%; float: left;">' . 
                                                '<hr style="border: none; border-top: 1px solid #000; width: 80%; margin-top: 1px; clear: both;">'.
                                                '<p style = " padding: 0px; margin:0px;
                                                    font-family: Arial, sans-serif; text-align: center; 
                                                    font-size: 12px ">'.$medicoN.'</p>'
                                            ;
                                            if(strlen(trim($medicoR)) > 0){
                                                $pdfContent = $pdfContent .  '<p style = " padding: 0px; margin:0px;
                                                                    font-family: Arial, sans-serif; text-align: center; 
                                                                    font-size: 12px ">Reg. '.$medicoR.'</p>';
                                            }
                                            $pdfContent = $pdfContent . 
                                                                '<p style = "padding: 0px; margin:0px;
                                                                    font-family: Arial, sans-serif; text-align: center; 
                                                                    font-size: 12px ">'.$especialidadN.'</p>'.
                                                            '</div><br>';

                    $pdfContent = $pdfContent . '<div style="width: 50%; float: left;">' . 
                                                    '<hr style="border: none; border-top: 1px solid #000; width: 80%; margin-top: 1px; clear: both;">'.
                                                    '<p style = " padding: 0px; margin:0px;
                                                        font-family: Arial, sans-serif; text-align: center; 
                                                        font-size: 12px ">Firma Y Sello De Presta. Economicas</p>'.
                                            '</div>';

            }else{

                $pdfContent = $pdfContent . '<br><div style="width: 30%; padding:0px;">' . 
                                                '<img src="D:\Escritorio\firma.jpg" style="max-width: 100%; height: auto; display: block;">' .
                                                '<hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">'.
                                                '<p style = " padding: 0px; margin:0px;
                                                    font-family: Arial, sans-serif; text-align: center; 
                                                    font-size: 12px ">'.$medicoN.'</p>';
                                if(strlen(trim($medicoR)) > 0){
                                    $pdfContent = $pdfContent .  '<p style = " padding: 0px; margin:0px;
                                                        font-family: Arial, sans-serif; text-align: center; 
                                                        font-size: 12px ">Reg. '.$medicoR.'</p>';
                                }
                                $pdfContent = $pdfContent . 
                                                    '<p style = "padding: 0px; margin:0px;
                                                        font-family: Arial, sans-serif; text-align: center; 
                                                        font-size: 12px ">'.$especialidadN.'</p>'.
                                                '</div><br>';
 
            }
 


            // Agrega la información del médico al arreglo de datos
            $datos [] = [
                'folio'. $value->HISCSEC => [
                    'infoFolio' => $value,
                    'infoTriage' => $datosTriage,
                    'evoSOAP' => $desHCC1,
                    'pteAudiometri' => $pteAudiometria,
                    'dataEvoSOAP' => $dataSOAP,
                    'antecedentes' => $antecedentes,
                    'infoGineco' => $gineco,
                    'signosVitalesH' => $SGNVTLH,
                    'evolucion' => $evolucion,
                    'escalaRespusta' => $escalaRes,
                    'diagnosticos' => $dx,
                    'procedimientosEnf' => $procEnf,
                    'ordenamientoProc' => $procOrdenados,
                    'formulaMedica' => $formulacion,
                    'interconsultas' => $interconsultas,
                    //'programacionCirugias' => $cirugias,
                    'incapacidad' => $incapacidades
                ]
            ];

            //Limpia el arreglos
            $datosTriage = [];
            $desHCC1 = []; 
            $gineco = [];
            $escalaRes = [];
            $procOrdenados = []; 
        
        }     

        $pdf->loadHTML($pdfContentTop.'<br>'.$pdfContent);
        return $pdf->stream();

        return response()->json([
            'encabezado' => [
                'empresa' => $infoEmpresa,
                'paciente' => $paciente,
                'responsable' => $responsable,
                'sedeAtn' => $sede
            ],
            'folio' => $datos,
        ],200);

    }

}
