<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Dompdf\Options;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class FormatosController extends Controller
{
    //funcion para generar las cosultas de pdf de ingreso
    public function generarPdfIngreso(Request $request)
    {
        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //departamento de la sede
        $TFCedu = $request->input('TFCedu'); //cedula del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //tipo de movimiento de ingreso

        $pdfContent = "";

        $pdf = App::make('dompdf.wrapper');

        //Instancia de controlador RenderFormatosController
        $renderF = new RenderFormatosController();

        try{

            //Validaicon de la admision
            $admision = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFMENi', 'TFFchI', 'TFHorI')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)
                ->orderBy('TFCedu')
                ->orderBy('TFTDoc')
                ->orderBy('TmCtvIng')
                ->first();
            
            if($admision == null){
                throw new \Exception('No se encontraron datos de la admision para el paciente '.$TFCedu.'-'.$TFTDoc.' en el consecutivo de ingreso '.$TmCtvIng);
            }

            //datos de la organizacion
            $organizacion = DB::table('EMPRESA')
                    ->select('EMPCOD', 'EmpNit', 'EmpRazSoc', 'EmpCodD', 'EmpCodC', 'EmpLogo', 'EmpDVer')
                    ->where('EMPCOD', $EMPCOD)
                    ->orderBy('EMPCOD')
                    ->first();
                
            if($organizacion == null){
                throw new \Exception('No se encontraron datos de la organizacion');
            }

            //Datos de la sede
            $sede = DB::table('MAESED')
                ->select('MCDpto', 'EMPCOD', 'MCDnom')
                ->where('EMPCOD', $EMPCOD)
                ->where('MCDpto', $MCDpto)
                ->orderBy('EMPCOD')
                ->orderBy('MCDpto')
                ->first();
            
            if($sede == null){
                throw new \Exception('No se encontraron datos de la sede');
            }
    
            //infomracion del paciente
            $paciente =  DB::table('TMPFAC as T1')
                    ->select('T1.TmCtvIng', 'T1.TFTDoc', 'T1.TFCedu', 'T1.ClaproI', 'T1.TFTiRe', 'T2.MPTele as TFTele', 
                            'T2.MPDire as TFDire', 'T2.MPSexo as TFMPSexo', 'T1.TFMENi', 'T1.TFcCodPab', 'T1.TFFchI', 'T1.TFHorI', 
                            'T1.TFTeRe', 'T2.MPFchN as TFMPFchN', 'T1.TFEmTrRe', 'T1.TFTeTrRe', 'T1.TFNoAc', 'T1.TFTeAc', 
                            'T1.TFDptRes', 'T1.TFMunRes', 'T3.MENOMB as TFNomCto', 'T1.TFCoMt', 'T1.TFEsMt', 'T1.TFDi1I', 
                            'T1.TFDocRep', 'T1.TFTDoRep', 'T1.TFDirRep', 'T1.MICodI', 'T1.TFNoRe', 'T1.TFNoRe2', 'T1.TFApeRes', 
                            'T1.TFApeRes2', 'T1.TFNMAU', 'T1.TFCoCamI', 'T4.MTUCod as TFMTUCod', 'T4.MTCodP as TFMTCodP', 
                            'T1.SCCCod', 'T1.SONume', 'T4.MPNoCa as TFMPNoCa', 'T1.TFUIng', 'T2.MPApe2', 'T2.MPApe1', 'T2.MPNom2', 
                            'T2.MPNom1', 'T2.MPEstC', 'T1.TFDocAco', 'T1.TFNoAc', 'T1.TFTeAc', 'T1.TFParAc')
                    ->selectRaw("CASE 
                                    WHEN T1.ClaproI = 1 THEN 'AMBULATORIO'
                                    WHEN T1.ClaproI = 2 THEN 'HOSPITALARIO'
                                    WHEN T1.ClaproI = 3 THEN 'URGENCIAS'
                                    WHEN T1.ClaproI = 4 THEN 'TTO ESPECIAL'
                                    WHEN T1.ClaproI = 5 THEN 'TRIAGE'
                                    ELSE 'OTRO' END AS ClaproIN,
                                CASE 
                                    WHEN T2.MPEstC = 'M' THEN 'Menor'
                                    WHEN T2.MPEstC = 'S' THEN 'Soltero(a)'
                                    WHEN T2.MPEstC = 'C' THEN 'Casado(a)'
                                    WHEN T2.MPEstC = 'V' THEN 'Viudo(a)'
                                    WHEN T2.MPEstC = 'U' THEN 'Union libre'
                                    WHEN T2.MPEstC = 'SE' THEN 'Separado(a)'
                                    ELSE ''
                                END AS MPEstCD,
                                CASE 
                                    WHEN T1.TFParAc = 'P' THEN 'PADRE O MADRE'
                                    WHEN T1.TFParAc = 'H' THEN 'HIJO'
                                    WHEN T1.TFParAc = 'C' THEN 'CONYUGE'
                                    WHEN T1.TFParAc = 'F' THEN 'FAMILIAR'
                                    WHEN T1.TFParAc = 'A' THEN 'AMIGO'
                                    WHEN T1.TFParAc = 'O' THEN 'OTRO'
                                    ELSE ''
                            END AS parentescoAc ")
                    ->join('CAPBAS as T2', function ($join) {
                        $join->on('T2.MPCedu', '=', 'T1.TFCedu')
                            ->on('T2.MPTDoc', '=', 'T1.TFTDoc');
                    })
                    ->leftJoin('MAEEMP as T3', 'T3.MENNIT', '=', 'T1.TFMENi')
                    ->leftJoin('MAEPAC as T4', function ($join) {
                        $join->on('T4.MPCedu', '=', 'T1.TFCedu')
                            ->on('T4.MPTDoc', '=', 'T1.TFTDoc')
                            ->on('T4.MENNIT', '=', 'T1.TFMENi');
                    })
                    ->where('T1.TFCedu', $TFCedu)
                    ->where('T1.TFTDoc', $TFTDoc)
                    ->where('T1.TmCtvIng', $TmCtvIng)
                    ->orderBy('T1.TFCedu')
                    ->orderBy('T1.TFTDoc')
                    ->orderBy('T1.TmCtvIng')
                    ->first();

            if($paciente == null){
                throw new \Exception('No se encontraron datos del ingreso del paciente');
            }

            //Info adicional general del paciente
            $infoGral = DB::table('CAPBAS AS T1')
                ->select('T1.MDCodD', 'T1.MDCodM', 'T1.MOCodPri', 'T3.MOCodi', 'T1.MPTDoc', 'T1.MPCedu', 'T4.MODesc',
                    'T1.MPEmpTra', 'T2.MDNomM', 'T1.MPEstC', 'T1.MPNOMC', 'T1.MPCodDisc', 'T1.MPGrEs', 'T1.MPCodEtn',
                    'T1.MPGrPo', 'T1.MpMail', 'T1.MPNivEdu', 'T1.MPNivEEs', 'T1.MpTele1', 'T1.MPOtTiAf', 'T1.MPOtrAfl')
                ->selectRaw(" CASE 
                                WHEN T1.MPNivEEs = 'C' THEN 'Completa'
                                WHEN T1.MPNivEEs = 'I' THEN 'Incompleta'
                                ELSE '' END AS MPNivEEsD")
                ->leftJoin('MAEDMB1 AS T2', function($join) {
                    $join->on('T2.MDCodD', '=', 'T1.MDCodD')
                        ->on('T2.MDCodM', '=', 'T1.MDCodM');
                })
                ->leftJoin('MAEOCUPRI AS T3', 'T3.MOCodPri', '=', 'T1.MOCodPri')
                ->leftJoin('MAEOCU AS T4', 'T4.MOCodi', '=', 'T3.MOCodi')
                ->where('T1.MPCedu', $TFCedu)
                ->where('T1.MPTDoc', $TFTDoc)
                ->orderBy('T1.MPCedu')
                ->orderBy('T1.MPTDoc')
                ->first();
            
            //Info de discapacidad
            $discapacidad = DB::table('DISCPAC')
                ->select('DiscCod', 'DiscDsc')
                ->where('DiscCod', '=', $infoGral->MPCodDisc)
                ->orderBy('DiscCod')
                ->first();
            
            if($discapacidad != null){
                $infoGral->discapacidad = $discapacidad->DiscDsc;
            }else {
                $infoGral->discapacidad = '';
            }

            //Informacion de la atencion especial del paciente
            $atnEspecial = DB::table('ATEESP')
                ->select('AteEspCod', 'AteEspDes')
                ->where('AteEspCod', '=', $infoGral->MPGrEs)
                ->orderBy('AteEspCod')
                ->first();

            if($atnEspecial != null){
                $infoGral->atnEspD = $atnEspecial->AteEspDes;
            }else {
                $infoGral->atnEspD = '';
            }

            //Grupo cultural
            $grupoCultural = DB::table('ETNIAS')
                ->select('MPCodEt', 'MPDscEt')
                ->where('MPCodEt', '=', $infoGral->MPCodEtn)
                ->orderBy('MPCodEt')
                ->first();
            
            if($grupoCultural != null){
                $infoGral->grupoCultural = $grupoCultural->MPDscEt;
            }else {
                $infoGral->grupoCultural = '';
            }

            //Grupo poblacional
            $grupoPoblacion = DB::table('GRUPOB')
                ->select('GRUAPTADM', 'GruPobCod', 'GruPobDes')
                ->where('GruPobCod', '=', $infoGral->MPGrPo)
                ->where('GRUAPTADM', '=', 'S')
                ->orderBy('GruPobCod')
                ->first();
            
            if($grupoPoblacion != null){
                $infoGral->grupoPoblacional = $grupoPoblacion->GruPobDes;
            }else {
                $infoGral->grupoPoblacional = '';
            }

            //Nivel educativo
            $nivEducativo = DB::table('NIVEDU')
                ->select('NivEdCo', 'NivEdDsc')
                ->where('NivEdCo', '=', $infoGral->MPNivEdu)
                ->orderBy('NivEdCo')
                ->first();

            if($nivEducativo != null){
                $infoGral->nivEducativo = $nivEducativo->NivEdDsc;
            }else {
                $infoGral->nivEducativo = '';
            }

            //Diagnostico
            $diagnostico = DB::table('MAEDIA')
                ->select('DMCodi', 'DMNomb')
                ->where('DMCodi', '=', $paciente->TFDi1I)
                ->orderBy('DMCodi')
                ->first();
            
            if($diagnostico != null){
                $paciente->diagnostico = $diagnostico->DMNomb;
            }else {
                $paciente->diagnostico = '';
            }

            //info observaciones
            $observaciones = DB::table('INGRESOS')
                ->select('ClaPro', 'IngCsc', 'MPTDoc', 'MPCedu', 'IngDerObs')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->where('IngCsc', $TmCtvIng)
                ->orderBy('MPCedu')
                ->orderBy('MPTDoc')
                ->orderBy('IngCsc')
                ->first();
            if($observaciones != null){
                $paciente->observaciones = $observaciones->IngDerObs;
            }else {
                $paciente->observaciones = '';
            }

            //Informacion de departamento y municipio del responsable 
            $departamento = DB::table('MAEDMB')
                ->select('MDCodD', 'MDNomD')
                ->where('MDCodD', $infoGral->MDCodD)
                ->orderBy('MDCodD')
                ->first();
            
            if ($departamento != null){
                $infoGral->dptoResponsable = $departamento->MDNomD;
            }else{
                $infoGral->dptoResponsable = '';
            }

            $municipio = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $infoGral->MDCodD)
                ->where('MDCodM', $infoGral->MDCodM)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($municipio != null){
                $infoGral->municipioResponsable = $municipio->MDNomM;
            }else{
                $infoGral->municipioResponsable = '';
            }

            $eps = DB::table('MAEEMP AS T1')
                ->select('T1.MENNIT', 'T1.MENOMB', 'T2.MeTele', 'T1.MEcntr', 'T1.MEPARt')
                ->leftJoin('EMPRESS AS T2', 'T2.MEcntr', '=', 'T1.MEcntr')
                ->where('T1.MENNIT', $admision->TFMENi)
                ->orderBy('T1.MENNIT')
                ->first();
            
            if($eps == null){
                throw new \Exception('No se encontraron datos de la EPS para el contrato '. $admision->TFMENi);
            }


            $tipoAfiliacion = DB::table('MAETPA3')
                ->select('MTCodP', 'MTUCod', 'MTNomP')
                ->where('MTUCod', $paciente->TFMTUCod)
                ->where('MTCodP', $paciente->TFMTCodP)
                ->orderBy('MTUCod')
                ->orderBy('MTCodP')
                ->first();

            if($tipoAfiliacion != null){
                $paciente->tipoAfiliacion = $tipoAfiliacion->MTNomP;
                
            }else{
                $paciente->tipoAfiliacion = '';
            }

            $medico = DB::table('MAEMED1')
                ->select('MMCODM', 'MMNomM', 'MMCedM')
                ->where('MMCODM', $paciente->TFCoMt)
                ->orderBy('MMCODM')
                ->first();

            if($medico != null){
                $paciente->medico = $medico->MMNomM;
                $paciente->ccMed = $medico->MMCedM;
            }else{
                $paciente->medico = '';
                $paciente->ccMed = '';
            }

            $espMedico = DB::table('MAEESP')
                ->select('MECodE', 'MENomE')
                ->where('MECodE', $paciente->TFEsMt)
                ->orderBy('MECodE')
                ->first();

            
            if($espMedico != null){
                $paciente->espMedico = $espMedico->MENomE;
            }else{
                $paciente->espMedico = '';
            }

            
            //--------------------------------------------------------------------------------------

            // Fecha de nacimiento del usuario
            $fechaNacimiento = $paciente->TFMPFchN;

            // Calcular la edad en años
            $edad = Carbon::parse($fechaNacimiento)->age .' AÑOS';

            $fechaActual = Carbon::now();

            if($edad < 1){
                // Calcular la edad en meses
                $edad = $fechaActual->diffInMonths($fechaNacimiento) . ' MESES';
                
            }
            if ($fechaActual->diffInMonths($fechaNacimiento) < 1){
                // Calcular la edad en días
                $edad = $fechaActual->diffInDays($fechaNacimiento). ' DIAS';
            }

            $paciente->edad = $edad;
                    
            
            $pdfContent = $renderF->formatoIngreso($organizacion, $sede, $paciente, $infoGral, $eps);
    
            
            $pdf->loadHTML($pdfContent);
            return $pdf->stream();

            /* 
                    return response()->json([
                        'org' =>    $organizacion,  
                        'pac' =>    $paciente,
                    ]); */

        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
        
        
    }

    //Funcion para generar las consultas de carta de instrucciones
    public function generaCartaInstrucciones(Request $request){
        $pdfContent = "";

        $pdf = App::make('dompdf.wrapper');
        
        //Instancia de controlador RenderFormatosController
        $renderF = new RenderFormatosController();

        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //departamento de la sede
        $TFCedu = $request->input('TFCedu'); //cedula del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //consecutivo de ingreso

        try{

            //datos de la organizacion
            $organizacion = DB::table('EMPRESA')
                    ->select('EMPCOD', 'EmpNit', 'EmpRazSoc', 'EmpCodD', 'EmpCodC', 'EmpLogo', 'EmpDVer')
                    ->where('EMPCOD', $EMPCOD)
                    ->orderBy('EMPCOD')
                    ->first();
                
            if($organizacion == null){
                throw new \Exception('No se encontraron datos de la organizacion');
            }

            $sede = DB::table('MAESED')
                ->select('MCDpto', 'EMPCOD', 'MCCiuCod', 'MCDptCod')
                ->where('EMPCOD', $EMPCOD)
                ->where('MCDpto', $MCDpto)
                ->orderBy('EMPCOD')
                ->orderBy('MCDpto')
                ->first();
            
            if($sede == null){
                throw new \Exception('No se encontraron datos de la sede');
            }

            $municipioSede = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $sede->MCDptCod)
                ->where('MDCodM', $sede->MCCiuCod)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($municipioSede != null){
                $sede->municipioSede = $municipioSede->MDNomM;
            }else{
                $sede->municipioSede = '';
            }

            //Informacion del paciente
            $paciente = DB::table('CAPBAS AS T1')
                ->select('T1.MDCodD', 'T1.MDCodM', 'T1.MOCodPri', 'T3.MOCodi', 'T1.MPTDoc',
                        'T1.MPCedu', 'T4.MODesc', 'T1.MPApe2', 'T1.MPApe1', 'T1.MPNom2',
                        'T1.MPNom1', 'T1.MpTele1', 'T2.MDNomM')
                ->leftJoin('MAEDMB1 AS T2', function ($join) {
                    $join->on('T2.MDCodD', '=', 'T1.MDCodD')
                        ->on('T2.MDCodM', '=', 'T1.MDCodM');
                })
                ->leftJoin('MAEOCUPRI AS T3', 'T3.MOCodPri', '=', 'T1.MOCodPri')
                ->leftJoin('MAEOCU AS T4', 'T4.MOCodi', '=', 'T3.MOCodi')
                ->where('T1.MPCedu', '=', $TFCedu)
                ->where('T1.MPTDoc', '=', $TFTDoc)
                ->orderBy('T1.MPCedu')
                ->orderBy('T1.MPTDoc')
                ->first();
            
            if($paciente == null){
                throw new \Exception("No se encontraron datos para el paciente");
            }

            //Informacion del responsable
            $responsable = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFNoRe', 'TFNoRe2', 'TFApeRes',
                        'TFApeRes2', 'TFDptRes', 'TFMunRes', 'TFDocRep', 'TFTDoRep',
                        'TFDirRep', 'TFTeRe', 'TFUIng')
                ->where('TFCedu', '=', $TFCedu)
                ->where('TFTDoc', '=', $TFTDoc)
                ->where('TmCtvIng', '=', $TmCtvIng)
                ->orderBy('TFCedu')
                ->orderBy('TFTDoc')
                ->orderBy('TmCtvIng')
                ->first();
            
            if($responsable == null){
                throw new \Exception("No se encontraron datos de la admision del paciente");
            }

            $responsableMunicipio = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', '=', $responsable->TFDptRes)
                ->where('MDCodM', '=', $responsable->TFMunRes)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($responsableMunicipio != null){
                $responsable->municipio = $responsableMunicipio->MDNomM;
            }else{
                $responsable->municipio = '';
            }

            $responsableDepartamento = DB::table('MAEDMB')
                ->select('MDCodD', 'MDNomD')
                ->where('MDCodD', '=', $responsable->TFDptRes)
                ->orderBy('MDCodD')
                ->first();
            
            if ($responsableDepartamento != null){
                $responsable->departamento = $responsableDepartamento->MDNomD;
            }else{
                $responsable->departamento = '';
            }



            $pdfContent = $renderF->cartaInstrucciones($organizacion, $sede, $paciente, $responsable);


            $pdf->loadHTML($pdfContent);
            return $pdf->stream();



        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }

        


    }

    //Funcion para generar las consultas del pagare de la orden
    public function generarPagareOrden(Request $request){

        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //departamento de la sede
        $TFCedu = $request->input('TFCedu'); //cedula del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //tipo de movimiento de ingreso

        $pdfContent = "";

        $pdf = App::make('dompdf.wrapper');

        //Instancia de controlador RenderFormatosController
        $renderF = new RenderFormatosController();

        try{

            //Validaicon de la admision
            $admision = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFMENi', 'TFFchI', 'TFHorI')
                ->where('TFCedu', $TFCedu)
                ->where('TFTDoc', $TFTDoc)
                ->where('TmCtvIng', $TmCtvIng)
                ->orderBy('TFCedu')
                ->orderBy('TFTDoc')
                ->orderBy('TmCtvIng')
                ->first();
            
            if($admision == null){
                throw new \Exception('No se encontraron datos de la admision para el paciente '.$TFCedu.'-'.$TFTDoc.' en el consecutivo de ingreso '.$TmCtvIng);
            }

            //datos de la organizacion
            $organizacion = DB::table('EMPRESA')
                    ->select('EMPCOD', 'EmpNit', 'EmpRazSoc', 'EmpCodD', 'EmpCodC', 'EmpLogo', 'EmpDVer')
                    ->where('EMPCOD', $EMPCOD)
                    ->orderBy('EMPCOD')
                    ->first();
                
            if($organizacion == null){
                throw new \Exception('No se encontraron datos de la organizacion');
            }

            //Datos de la sede
            $sede = DB::table('MAESED')
                ->select('MCDpto', 'EMPCOD', 'MCDnom', 'MCDptCod', 'MCCiuCod')
                ->where('EMPCOD', $EMPCOD)
                ->where('MCDpto', $MCDpto)
                ->orderBy('EMPCOD')
                ->orderBy('MCDpto')
                ->first();
            
            if($sede == null){
                throw new \Exception('No se encontraron datos de la sede');
            }

            $municipioSede = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $sede->MCDptCod)
                ->where('MDCodM', $sede->MCCiuCod)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($municipioSede != null){
                $sede->municipioSede = $municipioSede->MDNomM;
            }else{
                $sede->municipioSede = '';
            }
              

            //Informacion del paciente
            $paciente = DB::table('CAPBAS AS T1')
                ->select('T1.MDCodD', 'T1.MDCodM', 'T1.MOCodPri', 'T3.MOCodi', 'T1.MPTDoc',
                        'T1.MPCedu', 'T4.MODesc', 'T1.MPApe2', 'T1.MPApe1', 'T1.MPNom2',
                        'T1.MPNom1', 'T1.MpTele1', 'T2.MDNomM')
                ->leftJoin('MAEDMB1 AS T2', function ($join) {
                    $join->on('T2.MDCodD', '=', 'T1.MDCodD')
                        ->on('T2.MDCodM', '=', 'T1.MDCodM');
                })
                ->leftJoin('MAEOCUPRI AS T3', 'T3.MOCodPri', '=', 'T1.MOCodPri')
                ->leftJoin('MAEOCU AS T4', 'T4.MOCodi', '=', 'T3.MOCodi')
                ->where('T1.MPCedu', '=', $TFCedu)
                ->where('T1.MPTDoc', '=', $TFTDoc)
                ->orderBy('T1.MPCedu')
                ->orderBy('T1.MPTDoc')
                ->first();
            
            if($paciente == null){
                throw new \Exception("No se encontraron datos para el paciente");
            }

            //Informacion del responsable
            $responsable = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFNoRe', 'TFNoRe2', 'TFApeRes',
                        'TFApeRes2', 'TFDptRes', 'TFMunRes', 'TFDocRep', 'TFTDoRep',
                        'TFDirRep', 'TFTeRe', 'TFUIng')
                ->where('TFCedu', '=', $TFCedu)
                ->where('TFTDoc', '=', $TFTDoc)
                ->where('TmCtvIng', '=', $TmCtvIng)
                ->orderBy('TFCedu')
                ->orderBy('TFTDoc')
                ->orderBy('TmCtvIng')
                ->first();
            
            if($responsable == null){
                throw new \Exception("No se encontraron datos de la admision del paciente");
            }

            $responsableMunicipio = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', '=', $responsable->TFDptRes)
                ->where('MDCodM', '=', $responsable->TFMunRes)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($responsableMunicipio != null){
                $responsable->municipio = $responsableMunicipio->MDNomM;
            }else{
                $responsable->municipio = '';
            }
            

            
            $pdfContent = $renderF->pagareOrden($organizacion, $sede, $paciente, $responsable, $admision);


            $pdf->loadHTML($pdfContent);
            return $pdf->stream();



        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    } 

    //Funcion para generar consultas del evento catastrofico de la admision 
    public function generarEventoCatastrofico(Request $request){

        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //departamento de la sede
        $TFCedu = $request->input('TFCedu'); //cedula del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //tipo de movimiento de ingreso
        $ClaPro = $request->input('ClaPro'); //clapro atencion

        $pdfContent = "";

        $pdf = App::make('dompdf.wrapper');

        //Instancia de controlador RenderFormatosController
        $renderF = new RenderFormatosController();

        try{

            //datos de la organizacion
            $organizacion = DB::table('EMPRESA')
                    ->select('EMPCOD', 'EmpNit', 'EmpRazSoc', 'EmpCodD', 'EmpCodC', 'EmpLogo', 'EmpDVer', 'EmpDir',
                            'EmpTlf')
                    ->where('EMPCOD', $EMPCOD)
                    ->orderBy('EMPCOD')
                    ->first();
                
            if($organizacion == null){
                throw new \Exception('No se encontraron datos de la organizacion');
            }

            $departamentoEmpresa = DB::table('MAEDMB')
                ->select('MDCodD', 'MDNomD')
                ->where('MDCodD', $organizacion->EmpCodD)
                ->orderBy('MDCodD')
                ->first();
            if ($departamentoEmpresa != null){
                $organizacion->departamentoEmpresa = $departamentoEmpresa->MDNomD;
            }else{
                $organizacion->departamentoEmpresa = '';
            }

            $municipioEmpresa = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $organizacion->EmpCodD)
                ->where('MDCodM', $organizacion->EmpCodC)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($municipioEmpresa != null){
                $organizacion->municipioEmpresa = $municipioEmpresa->MDNomM;
            }else{
                $organizacion->municipioEmpresa = '';
            }

            //Info general del ingreso
            $ingreso = DB::table('TMPFAC AS T1')
                ->select(
                    'T1.TFDi1I AS TFDi1I', 'T1.TFDi1S AS TFDi1S', 'T1.TFCaMu AS TFCaMu',
                    'T1.SOCodD AS SOCodD', 'T1.SOCodDE AS SOCodDE', 'T1.SOCodM AS SOCodM',
                    'T1.SOCodME AS SOCodME', 'T1.SOMCodFCD AS SOMCodFCD', 'T1.ClaPro', 'T1.TmCtvIng',
                    'T1.TFTDoc AS TFTDoc', 'T1.TFCedu AS TFCedu', 'T10.MPFchN AS TFMPFchN',
                    'T10.MPSexo AS TFMPSexo', 'T1.SORulUrb', 'T1.SOIndAsg', 'T1.SOTpoEC',
                    'T4.DMNomb AS TFcDscCMu', 'T6.MDNomD AS SONomDE', 'T8.MDNomM AS SONomME',
                    'T2.DMNomb AS TFDN1I', 'T1.TFFchM', 'T3.DMNomb AS TFcDscDS1', 'T10.MPDire AS TFDire',
                    'T1.TFFchI', 'T7.MDNomM AS SONomM', 'T5.MDNomD AS SONomD', 'T1.SOMNroReg',
                    'T9.MMNomM AS SOMNomFCD', 'T1.SOFchAcc', 'T1.SOSitAcc', 'T10.MPTele AS TFTele',
                    'T10.MPNOMC AS TFNomC', 'T10.MPEmpTra', 'T1.TFFchS')
                ->selectRaw("CASE 
                                WHEN T10.MPSexo = 'F' THEN 'FEMENINO'
                                WHEN T10.MPSexo = 'M' THEN 'MASCULINO'
                                ELSE '' END AS MPSexoD,
                            DATEDIFF(year, T10.MPFchN, GETDATE()) AS edad,
                            CASE 
                                WHEN T1.ClaPro = 1 THEN 'AMBULATORIO'
                                WHEN T1.ClaPro = 2 THEN 'HOSPITALIZACION'
                                WHEN T1.ClaPro = 3 THEN 'URGENCIAS'
                                WHEN T1.ClaPro = 4 THEN 'TTO ESPECIAL'
                                WHEN T1.ClaPro = 5 THEN 'TRIAGE'
                                ELSE '' END AS ClaProD")
                ->leftJoin('MAEDIA AS T2', 'T2.DMCodi', '=', 'T1.TFDi1I')
                ->leftJoin('MAEDIA AS T3', 'T3.DMCodi', '=', 'T1.TFDi1S')
                ->leftJoin('MAEDIA AS T4', 'T4.DMCodi', '=', 'T1.TFCaMu')
                ->leftJoin('MAEDMB AS T5', 'T5.MDCodD', '=', 'T1.SOCodD')
                ->leftJoin('MAEDMB AS T6', 'T6.MDCodD', '=', 'T1.SOCodDE')
                ->leftJoin('MAEDMB1 AS T7', function ($join) {
                    $join->on('T7.MDCodD', '=', 'T1.SOCodD')
                        ->whereColumn('T7.MDCodM', '=', 'T1.SOCodM');
                })
                ->leftJoin('MAEDMB1 AS T8', function ($join) {
                    $join->on('T8.MDCodD', '=', 'T1.SOCodDE')
                        ->whereColumn('T8.MDCodM', '=', 'T1.SOCodME');
                })
                ->leftJoin('MAEMED1 AS T9', 'T9.MMCODM', '=', 'T1.SOMCodFCD')
                ->join('CAPBAS AS T10', function ($join) {
                    $join->on('T10.MPCedu', '=', 'T1.TFCedu')
                        ->whereColumn('T10.MPTDoc', '=', 'T1.TFTDoc');
                })
                ->where('T1.TFCedu', '=', $TFCedu)
                ->where('T1.TFTDoc', '=', $TFTDoc)
                ->where('T1.TmCtvIng', '=', $TmCtvIng)
                ->where('T1.ClaPro', '=', $ClaPro)
                ->orderBy('T1.TFCedu')
                ->orderBy('T1.TFTDoc')
                ->orderBy('T1.TmCtvIng')
                ->first();
            
            if($ingreso == null){
                throw new \Exception('No se encontraron datos del ingreso del paciente');
            }

            //Info adicional general del paciente
            $ciudad = DB::table('CAPBAS as T1')
                ->select('T1.MDCodD', 'T1.MDCodM', 'T1.MPTDoc', 'T1.MPCedu', 'T1.MPNom1', 'T2.MDNomM')
                ->leftJoin('MAEDMB1 as T2', function ($join) {
                    $join->on('T2.MDCodD', '=', 'T1.MDCodD')
                        ->on('T2.MDCodM', '=', 'T1.MDCodM');
                })
                ->where('T1.MPCedu', $TFCedu)
                ->where('T1.MPTDoc', $TFTDoc)
                ->orderBy('T1.MPCedu')
                ->orderBy('T1.MPTDoc')
                ->first();
            
            if($ciudad != null){
                $ingreso->ciudad = $ciudad->MDNomM;
            }else{
                $ingreso->ciudad = '';
            }

            
            if(Carbon::parse($ingreso->SOFchAcc)->format('Y-m-d') == '1753-01-01'){
                $ingreso->SOFchAcc = ' / / 00:00:00';
            }

            if(Carbon::parse($ingreso->TFFchM)->format('Y-m-d') == '1753-01-01'){
                $ingreso->TFFchM = ' / / 00:00:00';
            }

            if($ingreso->SORulUrb == 'R'){
                $ingreso->SORulUrb = 'RURAL';
            }else{
                $ingreso->SORulUrb = 'URBANO';
            }

            //Consultas de la naturaleza del evento catastrofico
            $naturaleza = DB::table('MAECAUE1')
                ->select('CECODIGO', 'CESUBDET', 'CESUBTIP', 'CESUBCOD')
                ->where('CECODIGO', 6)
                ->orderBy('CECODIGO')
                ->orderBy('CESUBCOD')
                ->orderBy('CESUBTIP')
                ->get();

            $pdfContent = $renderF->eventoCatastrofico($organizacion, $ingreso, $naturaleza);


            $pdf->loadHTML($pdfContent);
            return $pdf->stream();
            


        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    //Funcion para generar consultas de anexo 1
    public function generarAnexo1(Request $request){
            
            $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
            $MCDpto = $request->input('MCDpto'); //departamento de la sede
            $TFCedu = $request->input('TFCedu'); //cedula del paciente
            $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
            $TmCtvIng = $request->input('TmCtvIng'); //tipo de movimiento de ingreso
            $AUsrId = $request->input('AUsrId'); //Usuario que reporta
    
            $pdfContent = "";
    
            $pdf = App::make('dompdf.wrapper');
    
            //Instancia de controlador RenderFormatosController
            $renderF = new RenderFormatosController();
    
            try{


                //datos de la organizacion
                $organizacion = DB::table('MAESED as T1')
                    ->select(
                        'T1.MCDpto','T1.EMPCOD','T1.MCDnom','T1.MCDNIT','T2.EmpNom1','T1.MCDRazSoc',
                        'T1.MCDptCod','T1.MCCiuCod','T1.MCDDir','T1.MCDCodIPS','T1.MCDIndTel','T1.MCDExtTel',
                        'T1.MCDCel','T1.MCDPie1','T1.MCDPie2','T1.MCDPie3','T1.MCDPie4','T1.MCDFax','T1.MCDTele',
                        'T1.MCDLogoB', 'T1.MCDLogo'
                    )
                    ->join('EMPRESA as T2', 'T2.EMPCOD', '=', 'T1.EMPCOD')
                    ->where('T1.EMPCOD', '=', $EMPCOD)
                    ->where('T1.MCDpto', '=', $MCDpto)
                    ->orderBy('T1.EMPCOD')
                    ->orderBy('T1.MCDpto')
                    ->first();
                
                if($organizacion == null){
                    throw new \Exception('No se encontraron datos de la organizacion');
                }

                //Informacion del ingreso del paciente                
                $ingreso = DB::table('INGRESOS as T1')
                    ->select('T1.MPCodP', 'T1.IngCsc', 'T1.MPTDoc', 'T1.MPCedu', 'T1.IngNit', 'T1.ClaPro', 'T1.IngNroAn1', 'T2.MPMCDpto')
                    ->leftJoin('MAEPAB as T2', 'T2.MPCodP', '=', 'T1.MPCodP')
                    ->where('T1.MPCedu', $TFCedu)
                    ->where('T1.MPTDoc', $TFTDoc)
                    ->where('T1.IngCsc', $TmCtvIng)
                    ->orderBy('T1.MPCedu')
                    ->orderBy('T1.MPTDoc')
                    ->orderBy('T1.IngCsc')
                    ->first();
                
                if($ingreso == null){
                    throw new \Exception('No se encontraron datos del ingreso del paciente');
                }

                //Info del contrato
                $contratoIngreso = DB::table('MAEEMP')
                    ->select('MENNIT', 'MENOMB', 'MeCodRip')
                    ->where('MENNIT', $ingreso->IngNit)
                    ->orderBy('MENNIT')
                    ->first();
                
                if($contratoIngreso == null){
                    throw new \Exception('No se encontraron datos del contrato del ingreso');
                }

                $paciente = DB::table('CAPBAS')
                    ->select('MPTDoc', 'MPCedu', 'MPApe1', 'MPApe2', 'MPNom1', 'MPNom2', 'MPSexo', 'MPFchN', 'MPDire', 'MDCodD', 'MDCodM', 'MPTele')
                    ->where('MPCedu', $TFCedu)
                    ->where('MPTDoc', $TFTDoc)
                    ->orderBy('MPCedu')
                    ->orderBy('MPTDoc')
                    ->first();

                if($paciente == null){
                    throw new \Exception('No se encontraron datos del paciente');
                }

                $usuReporta = DB::table('ADMUSR')
                    ->select('AUsrId', 'AUsrDsc', 'AGrpId', 
                        DB::raw("LTRIM(RTRIM(dbo.desencriptar(AUsrDsc))) AS nombreReporta"))
                    ->where('AUsrId', $AUsrId)
                    ->orderBy('AUsrId')
                    ->first();

                if($usuReporta == null){
                    throw new \Exception('No se encontraron datos del usuario que reporta');
                }
            
                //Asignaicon de nuevas variables a los objetos 

                $departamentoSede = DB::table('MAEDMB')
                    ->select('MDCodD', 'MDNomD')
                    ->where('MDCodD', $organizacion->MCDptCod)
                    ->orderBy('MDCodD')
                    ->first();
                
                if ($departamentoSede != null){
                    $organizacion->departamentoSede = $departamentoSede->MDNomD;
                }else{
                    $organizacion->departamentoSede = '';
                }
               
                $municipioSede = DB::table('MAEDMB1')
                    ->select('MDCodM', 'MDCodD', 'MDNomM')
                    ->where('MDCodD', $organizacion->MCDptCod)
                    ->where('MDCodM', $organizacion->MCCiuCod)
                    ->orderBy('MDCodD')
                    ->orderBy('MDCodM')
                    ->first();
                
                if ($municipioSede != null){
                    $organizacion->municipioSede = $municipioSede->MDNomM;
                }else{
                    $organizacion->municipioSede = '';
                }

                $departamentoPaciente = DB::table('MAEDMB')
                    ->select('MDCodD', 'MDNomD')
                    ->where('MDCodD', $paciente->MDCodD)
                    ->orderBy('MDCodD')
                    ->first();
                
                if ($departamentoPaciente != null){
                    $paciente->departamentoPaciente = $departamentoPaciente->MDNomD;
                }else{
                    $paciente->departamentoPaciente = '';
                }

                $municipioPaciente = DB::table('MAEDMB1')
                    ->select('MDCodM', 'MDCodD', 'MDNomM')
                    ->where('MDCodD', $paciente->MDCodD)
                    ->where('MDCodM', $paciente->MDCodM)
                    ->orderBy('MDCodD')
                    ->orderBy('MDCodM')
                    ->first();  

                if ($municipioPaciente != null){
                    $paciente->municipioPaciente = $municipioPaciente->MDNomM;
                }else{
                    $paciente->municipioPaciente = '';
                }
                

                $pdfContent = $renderF->anexo1($organizacion, $ingreso, $contratoIngreso, $paciente, $usuReporta);


                $pdf->loadHTML($pdfContent);
                return $pdf->stream();



            }catch(\Exception $e){
                return response()->json([
                    'error' => $e->getMessage()
                ]);
            }
    }

    //Funcion para generar consultas de anexo 2
    public function generarAnexo2(Request $request){
        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //departamento de la sede
        $TFCedu = $request->input('TFCedu'); //cedula del paciente
        $TFTDoc = $request->input('TFTDoc'); //tipo de documento del paciente
        $TmCtvIng = $request->input('TmCtvIng'); //tipo de movimiento de ingreso
        $AUsrId = $request->input('AUsrId'); //Usuario que reporta

        $pdfContent = "";
        $dx = [];

        $pdf = App::make('dompdf.wrapper');

        //Instancia de controlador RenderFormatosController
        $renderF = new RenderFormatosController();

        try{

            //datos de la organizacion
            $organizacion = DB::table('MAESED as T1')
                ->select(
                    'T1.MCDpto','T1.EMPCOD','T1.MCDnom','T1.MCDNIT','T2.EmpNom1','T1.MCDRazSoc',
                    'T1.MCDptCod','T1.MCCiuCod','T1.MCDDir','T1.MCDCodIPS','T1.MCDIndTel','T1.MCDExtTel',
                    'T1.MCDCel','T1.MCDPie1','T1.MCDPie2','T1.MCDPie3','T1.MCDPie4','T1.MCDFax','T1.MCDTele',
                    'T1.MCDLogoB', 'T1.MCDLogo'
                )
                ->join('EMPRESA as T2', 'T2.EMPCOD', '=', 'T1.EMPCOD')
                ->where('T1.EMPCOD', '=', $EMPCOD)
                ->where('T1.MCDpto', '=', $MCDpto)
                ->orderBy('T1.EMPCOD')
                ->orderBy('T1.MCDpto')
                ->first();
            
            if($organizacion == null){
                throw new \Exception('No se encontraron datos de la organizacion');
            }

            //Informacion del ingreso del paciente                
            $ingreso = DB::table('INGRESOS as T1')
                ->select('T1.MPCodP', 'T1.IngCsc', 'T1.MPTDoc', 'T1.MPCedu', 'T1.IngNit', 'T1.ClaPro', 'T1.IngNroAn2', 'T2.MPMCDpto',
                        'T1.IngFeHAtU', 'T1.IngEntDx')
                ->leftJoin('MAEPAB as T2', 'T2.MPCodP', '=', 'T1.MPCodP')
                ->where('T1.MPCedu', $TFCedu)
                ->where('T1.MPTDoc', $TFTDoc)
                ->where('T1.IngCsc', $TmCtvIng)
                ->orderBy('T1.MPCedu')
                ->orderBy('T1.MPTDoc')
                ->orderBy('T1.IngCsc')
                ->first();
            
            if($ingreso == null){
                throw new \Exception('No se encontraron datos del ingreso del paciente');
            }

            //Info del contrato
            $contratoIngreso = DB::table('MAEEMP')
                ->select('MENNIT', 'MENOMB', 'MeCodRip')
                ->where('MENNIT', $ingreso->IngNit)
                ->orderBy('MENNIT')
                ->first();
            
            if($contratoIngreso == null){
                throw new \Exception('No se encontraron datos del contrato del ingreso');
            }

            $paciente = DB::table('CAPBAS')
                ->select('MPTDoc', 'MPCedu', 'MPApe1', 'MPApe2', 'MPNom1', 'MPNom2', 'MPSexo', 'MPFchN', 'MPDire', 'MDCodD', 'MDCodM', 'MPTele')
                ->where('MPCedu', $TFCedu)
                ->where('MPTDoc', $TFTDoc)
                ->orderBy('MPCedu')
                ->orderBy('MPTDoc')
                ->first();

            if($paciente == null){
                throw new \Exception('No se encontraron datos del paciente');
            }

            $admision = DB::table('TMPFAC')
                ->select('TmCtvIng', 'TFTDoc', 'TFCedu', 'TFCauE', 'TFEstP', 'TFMotS', 'TFHorO')
                ->where('TFCedu', '=', $TFCedu)
                ->where('TFTDoc', '=', $TFTDoc)
                ->where('TmCtvIng', '=', $TmCtvIng)
                ->orderBy('TFCedu')
                ->orderBy('TFTDoc')
                ->orderBy('TmCtvIng')
                ->first();

            if($admision == null){
                throw new \Exception('No se encontraron datos de la admision del paciente');
            }

            $referencia = DB::table('REFCREF as T1')
                ->leftJoin('MAEIPS as T2', 'T2.MICodI', '=', 'T1.RefIPSRef')
                ->leftJoin('MAEIPS as T3', 'T3.MICodI', '=', 'T1.RefIPSRcp')
                ->select(
                    'T1.REFCOD',
                    'T1.RefCscIng',
                    'T1.MPCedu',
                    'T1.MCDpto',
                    'T1.DOCCOD',
                    'T1.EMPCOD',
                    'T1.RefFch',
                    'T1.RefIPSRef AS RefIPSRef',
                    'T1.RefIPSRcp AS RefIPSRcp',
                    'T2.IPSNroNIT AS RefIPSNRf',
                    'T1.RefNom',
                    'T3.MINomI AS RefMiNomI',
                    'T3.IPSNroNIT AS RefIPSNRc',
                    'T1.RefNomEnt',
                    'T1.RefNomRec'
                )
                ->where('T1.EMPCOD', $EMPCOD)
                ->where('T1.DOCCOD', 'RCR')
                ->where('T1.MCDpto', $MCDpto)
                ->where('T1.MPCedu', $TFCedu)
                ->where('T1.RefCscIng', $TmCtvIng)
                ->orderBy('T1.EMPCOD')
                ->orderBy('T1.DOCCOD')
                ->first();
            
            if($referencia != null){
                $ipsRef = DB::table('MAEIPS')
                    ->select('MICodI', 'MINomI', 'MDCodD', 'MDCodM')
                    ->where('MICodI', '=', $referencia->RefIPSRef)
                    ->first();
                
                if($ipsRef != null){
                    $referencia->ipsRef = $ipsRef->MINomI;
                }else{
                    $referencia->ipsRef = '';
                }

                //Municipio de la ips de referencia
                $municipioIpsRef = DB::table('MAEDMB1')
                    ->select('MDCodM', 'MDCodD', 'MDNomM')
                    ->where('MDCodD', '=', $ipsRef->MDCodD)
                    ->where('MDCodM', '=', $ipsRef->MDCodM)
                    ->first();
                
                if($municipioIpsRef != null){
                    $referencia->municipioIpsRef = $municipioIpsRef->MDNomM;
                    $referencia->codMunicipioIpsRef = $municipioIpsRef->MDCodM;
                }else{
                    $referencia->municipioIpsRef = '';
                    $referencia->codMunicipioIpsRef = '';
                }

                //Departamento de la ips de referencia
                $departamentoIpsRef = DB::table('MAEDMB')
                    ->select('MDCodD', 'MDNomD')
                    ->where('MDCodD', '=', $ipsRef->MDCodD)
                    ->first();

                if($departamentoIpsRef != null){
                    $referencia->departamentoIpsRef = $departamentoIpsRef->MDNomD;
                    $referencia->codDepartamentoIpsRef = $departamentoIpsRef->MDCodD;
                }else{
                    $referencia->departamentoIpsRef = '';
                    $referencia->codDepartamentoIpsRef = '';
                }

            }

            $folioP = DB::table('HCCOM1')
                ->select('HCtvIn1', 'HISTipDoc', 'HISCKEY', 'HISCSEC')
                ->where('HISCKEY', '=', $TFCedu)
                ->where('HISTipDoc', '=', $TFTDoc)
                ->where('HCtvIn1', '=', $TmCtvIng)
                ->orderBy('HISCKEY')
                ->orderBy('HISTipDoc')
                ->orderBy('HISCSEC')
                ->first();
            
            if($folioP == null){
                throw new \Exception('No se encontraron datos del folio del paciente. El paciente no ha sido atendido');
            }         

            $folios =  DB::table('HCCOM1')
                ->select('HISCSEC', 'HCtvIn1', 'HISTipDoc', 'HISCKEY', 'FHCIndEsp')
                ->where('HISCKEY', '=', $TFCedu)
                ->where('HISTipDoc', '=',  $TFTDoc)
                ->where('HISCSEC', '>', $folioP->HISCSEC) 
                ->where('HCtvIn1', '=', $TmCtvIng)
                ->orderBy('HISCKEY')
                ->orderBy('HISTipDoc')
                ->orderBy('HISCSEC')
                ->get();
        
            if($folios->count() > 0){
                $motConsulta = DB::table('HCCOM1DES')
                    ->select('HISDesAtr', 'HISCSEC', 'HISTipDoc', 'HISCKEY', 'HISDesDet')
                    ->where('HISCKEY', '=', $TFCedu)
                    ->where('HISTipDoc', '=', $TFTDoc)
                    ->where('HISCSEC', '=', $folios->first()->HISCSEC)
                    ->where('HISDesAtr', 'HISCMOTCON')
                    ->orderBy('HISCKEY')
                    ->orderBy('HISTipDoc')
                    ->orderBy('HISCSEC')
                    ->orderBy('HISDesAtr')
                    ->first();
                
                //agrega a ingreso
                if($motConsulta != null){
                    $ingreso->motConsulta = $motConsulta->HISDesDet;
                }else{
                    $ingreso->motConsulta = '';
                }

            }else{
                $ingreso->motConsulta = '';
            }

            //folios lista
            $listaFoliosD = DB::table('HCCOM1')
                ->select('HISCKEY', 'HISTipDoc', 'HCtvIn1', 'HISCSEC')
                ->where('HISCKEY', $TFCedu)
                ->where('HISTipDoc', $TFTDoc)
                ->where('HCtvIn1', $TmCtvIng)
                ->orderBy('HISCKEY')
                ->orderBy('HISTipDoc')
                ->orderByDesc('HCtvIn1')
                ->orderByDesc('HISCSEC')
                ->get();
            
            foreach($listaFoliosD as $folio){
                $dx = DB::table('HCDIAGN as T1')
                    ->select('T1.HISCSEC', 'T1.HISTipDoc', 'T1.HISCKEY', 'T1.HCDXCLS', 'T1.HCDXCOD as HCDXCOD', 'T2.DMNomb as HCDXNOM')
                    ->join('MAEDIA as T2', 'T2.DMCodi', '=', 'T1.HCDXCOD')
                    ->where('T1.HISCKEY', $folio->HISCKEY)
                    ->where('T1.HISTipDoc', $folio->HISTipDoc)
                    ->where('T1.HISCSEC', $folio->HISCSEC)
                    ->where('T1.HCDXCLS', 0)
                    ->orderBy('T1.HISCKEY')
                    ->orderBy('T1.HISTipDoc')
                    ->orderBy('T1.HISCSEC')
                    ->take(3) // Limitar a 3 resultados
                    ->get();
                
                // Verificar si se encontraron registros en $dx
                if ($dx->isNotEmpty()) {
                    // Romper el bucle foreach
                    break;
                }

            }

            $usuReporta = DB::table('ADMUSR')
                ->select('AUsrId', 'AUsrDsc', 'AGrpId', 
                    DB::raw("LTRIM(RTRIM(dbo.desencriptar(AUsrDsc))) AS nombreReporta"))
                ->where('AUsrId', $AUsrId)
                ->orderBy('AUsrId')
                ->first();

            if($usuReporta == null){
                throw new \Exception('No se encontraron datos del usuario que reporta');
            }

            //Asignaicon de nuevas variables a los objetos 

            $departamentoSede = DB::table('MAEDMB')
                ->select('MDCodD', 'MDNomD')
                ->where('MDCodD', $organizacion->MCDptCod)
                ->orderBy('MDCodD')
                ->first();
            
            if ($departamentoSede != null){
                $organizacion->departamentoSede = $departamentoSede->MDNomD;
            }else{
                $organizacion->departamentoSede = '';
            }
        
            $municipioSede = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $organizacion->MCDptCod)
                ->where('MDCodM', $organizacion->MCCiuCod)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();
            
            if ($municipioSede != null){
                $organizacion->municipioSede = $municipioSede->MDNomM;
            }else{
                $organizacion->municipioSede = '';
            }

            $departamentoPaciente = DB::table('MAEDMB')
                ->select('MDCodD', 'MDNomD')
                ->where('MDCodD', $paciente->MDCodD)
                ->orderBy('MDCodD')
                ->first();
            
            if ($departamentoPaciente != null){
                $paciente->departamentoPaciente = $departamentoPaciente->MDNomD;
            }else{
                $paciente->departamentoPaciente = '';
            }

            $municipioPaciente = DB::table('MAEDMB1')
                ->select('MDCodM', 'MDCodD', 'MDNomM')
                ->where('MDCodD', $paciente->MDCodD)
                ->where('MDCodM', $paciente->MDCodM)
                ->orderBy('MDCodD')
                ->orderBy('MDCodM')
                ->first();  

            if ($municipioPaciente != null){
                $paciente->municipioPaciente = $municipioPaciente->MDNomM;
            }else{
                $paciente->municipioPaciente = '';
            }

            $dxPpal = DB::table('MAEDIA')
                ->select('DMCodi', 'DMNomb')
                ->where('DMCodi', '=', $ingreso->IngEntDx)
                ->first();
            
            if($dxPpal != null){
                $ingreso->dxPpal = $dxPpal->DMNomb;
                $ingreso->dxPpalCod = $dxPpal->DMCodi;
            }else{
                $ingreso->dxPpal = '';
                $ingreso->dxPpalCod = '';
            }



            $pdfContent = $renderF->anexo2($organizacion, $contratoIngreso, $paciente, $admision, $ingreso, $referencia, $dx, $usuReporta);


            $pdf->loadHTML($pdfContent);
            return $pdf->stream();



        }catch(\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    //Formaro mensaje
    public function formato(Request $request){

        $mensaje = $request->input('mensaje'); //mensaje

        $pdfContent = "";
    
        $pdf = App::make('dompdf.wrapper');

        $pdfContent = '<p>'.$mensaje.'</p>';


        $pdf->loadHTML($pdfContent);
        return $pdf->stream();


    }
}
