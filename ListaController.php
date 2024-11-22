<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BD;


class ListaController extends Controller
{
    public function tipDocumento (){

        try{
            $tipoDoc = DB::Connection('sqlsrv')

            ->select("SELECT [MPTDoc], [MPTDesc] FROM [TIPDOCASI] WITH (NOLOCK) ORDER BY [MPTDoc]");
            $status = 200;

        }catch (\exception $e){
            $tipoDoc = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }

        return response()->json($tipoDoc, $status);
    }


    public function tipServicio(Request $request)
    {
        $clapro = $request->input('clapro');
        $EMPCOD = $request->input('EMPCOD');
        $MPMCDpto = $request->input('MPMCDpto');

        try{

            if ($clapro < 0) {
                throw new \Exception('Valor invalido de servicio');
            }

            if ($clapro >= 1){
                $servicio = DB::Connection('sqlsrv')

                ->select("SELECT [MPCLAPRO], [MPMCDpto], [EMPCOD], [MPTraEsp], [MPNomP], [MPCodP]
                            FROM [MAEPAB] WITH (NOLOCK) WHERE ([EMPCOD] =  $EMPCOD  and [MPMCDpto] =  '".$MPMCDpto."' )
                            AND ([MPCLAPRO] =  $clapro) AND [MPActPab] <> 'S' ORDER BY [EMPCOD], [MPMCDpto], [MPCLAPRO]");

            }else{
                $servicio = DB::Connection('sqlsrv')

                ->select("SELECT [MPCLAPRO], [MPMCDpto], [EMPCOD], [MPTraEsp], [MPNomP], [MPCodP]
                            FROM [MAEPAB] WITH (NOLOCK) WHERE ([EMPCOD] =  $EMPCOD  and [MPMCDpto] =  '".$MPMCDpto."' )
                            AND [MPActPab] <> 'S'
                            ORDER BY [EMPCOD], [MPMCDpto], [MPCLAPRO]");
            }

            $status = 200;
            
        }catch(\Exception $e) {
            $servicio = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($servicio, $status);
    }

    public function clasePaciente(Request $request){

        $CLAESTCOD = $request->CLAESTCOD;

        try{

            $clasePaciente = DB::table('CLAESTPAC')
                ->select('CLAESTPRI', 'CLAESTCOD', 'CLAESTCOL', 'CLAESTDSC', 'CLAESTIDE')
                ->when($CLAESTCOD, function($query, $CLAESTCOD){
                    return $query->where('CLAESTCOD', $CLAESTCOD);
                })
                ->orderBy('CLAESTCOD')->get();

            $status=200;
            
        }catch (\exception $e){
            $clasePaciente = [
                'message' => $e->getMessage()
            ];
            $status=404;
        }
        return response()->json($clasePaciente, $status);
    }

    public function discapacidad(){
        try{
            $discapacidad = DB::Connection('sqlsrv')

            ->select("SELECT * FROM DISCPAC");
            $status = 200;
        }catch(\exception $e){
            $discapacidad = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($discapacidad, $status);
    }

    public function etnia(){
        try{
            $etnia = DB::Connection('sqlsrv')

            ->select("SELECT * FROM ETNIAS");
            $status = 200;
        }catch(\exception $e){
            $etnia = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($etnia, $status);
    }

    public function grupoPoblacion(){
        try{
            $grupo = DB::Connection('sqlsrv')

            ->select("SELECT * FROM GRUPOB");
            $status = 200;
        }catch(\exception $e){
            $grupo = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($grupo, $status);
    }

    public function nivelEducativo(){
        try{
            $nivelEducativo = DB::Connection('sqlsrv')

            ->select("SELECT * FROM NIVEDU");
            $status = 200;
        }catch(\exception $e){
            $nivelEducativo = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($nivelEducativo, $status);
    }

    public function atencionEspecial(){
        try{
            $atencionEspecial = DB::Connection('sqlsrv')

            ->select("SELECT * FROM ATEESP");
            $status = 200;

        }catch(\exception $e){
            $atencionEspecial = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($atencionEspecial, $status);
    }

    public function listaSede(){

        $sedes = DB::table('MAESED')
                    ->select('EMPCOD', 'MCDpto', 'MCDnom')->get();

        return response()->json([
            'data' => $sedes
        ], 200);
    }

    public function ocupacion(Request $request){

        $MOCodPri = $request->input('MOCodPri');

        try{

            $ocupacion = DB::table('MAEOCUPRI')
                ->when($MOCodPri, function($query, $MOCodPri){
                    return $query->where('MOCodPri', $MOCodPri)
                                ->orWhere('MODesPri', 'like', '%'.$MOCodPri.'%');
                })
                ->get();
            
            $status = 200;
        }catch(\exception $e){
            $ocupacion = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($ocupacion, $status);
    }

    public function listaDepartamentos(Request $request){

        $MDCodD = $request->input('MDCodD');

        try{


            $departamentos = DB::table('MAEDMB')
                ->when($MDCodD, function($query, $MDCodD){
                    return $query->where('MDCodD', $MDCodD)
                                ->orWhere('MDNomD', 'like', '%'.$MDCodD.'%');
                })
                ->get();
            $status = 200;

        }catch(\exception $e){
            $departamentos = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($departamentos, $status);
    }

    public function listaMunicipios(Request $request){

        $MDCodD = $request->input('iddepartamento');//codigo del departamento
        $MDCodM = $request->input('MDCodM');//codigo del municipio o nombre

        try{


            $municipios = DB::table('MAEDMB1')
                
                ->when($MDCodM, function($query, $MDCodM){
                    return $query->where('MDCodM', $MDCodM)
                                ->orWhere('MDNomM', 'like', '%'.$MDCodM.'%');
                })
                ->where('MDCodD', $MDCodD)
                ->get();

            $status = 200;

        }catch(\exception $e){
            $municipios = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($municipios, $status);
    }

    public function getAllMunicipios() {

        try{
            $municipios = DB::Connection('sqlsrv')

            ->select("SELECT * FROM MAEDMB1");
            $status = 200;

        }catch(\exception $e){
            $municipios = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($municipios, $status);
    }

    public function listaBarrios(Request $request){

        $MDCodD = $request->input('MDCodD'); //Codigo del departamento
        $MDCodM = $request->input('MDCodM'); //Codigo del municipio
        $MDCodB = $request->input('MDCodB'); //Codigo del barrio o nombre

        try{

            $barrios = DB::table('MAEDMB2')
                ->when($MDCodB, function($query, $MDCodB){
                    return $query->where('MDCodB', $MDCodB)
                                ->orWhere('MDNomB', 'like', '%'.$MDCodB.'%');
                })
                ->where('MDCodM', $MDCodM)
                ->where('MDCodD', $MDCodD)
                ->get();
            $status = 200;

        }catch(\exception $e){

            $barrios = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($barrios, $status);
    }

    public function listaPaises(){

        try {

            $listaPaises = DB::Connection('sqlsrv')

            ->select("SELECT [PaisCod], [PaisNom] FROM [PAIS] WITH (NOLOCK)  ORDER BY [PaisCod]");

            $status = 200;

        } catch(\exception $e){

            $listaPaises = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($listaPaises, $status);
    }

    public function listaIps(){

        try {

            $listaIps = DB::Connection('sqlsrv')

            ->select("SELECT [MICodI], [MINomI], [IPSCodMed] FROM [MAEIPS] WITH (NOLOCK) ORDER BY [MICodI]");

            $status = 200;

        } catch(\exception $e){

            $listaIps = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($listaIps, $status);
    }

    public function causaExterna(){

        try {
            $causaExterna = DB::Connection('sqlsrv')

            ->select("SELECT [CEDETALL], [CECODIGO] FROM [MAECAUE] WITH (NOLOCK) ORDER BY [CEDETALL]");

            $status = 200;

        } catch(\exception $e){

            $causaExterna = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($causaExterna, $status);
    }

    public function medico(Request $request){

        $MMNomM = '%'.$request->input('MMNomM').'%';
        $MMNomMc = $request->input('MMNomM');

        try {

            if (!$MMNomM ){
                throw new \Exception("Algo salió mal");
            }

            $medico = DB::Connection('sqlsrv')

            ->select("SELECT [MMEstado], [MMCedM], [MMNomM], [MMRegM], [MMUsuario], [MMCODM]
                        FROM [MAEMED1] WITH (NOLOCK)
                        WHERE ([MMCedM] = '' or '' = '') and ([MMEstado] <> 'I')
                        and ([MMNomM] like '$MMNomM' or [MMCODM] = '$MMNomMc')
                        ORDER BY [MMCODM]");

            $status = 200;

        } catch(\exception $e) {

            $medico = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($medico, $status);
    }

    public function EspecialidadMed(Request $request){

        $MMCODM = $request->input('MMCODM');

        try {


            $listaEsp = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MEEstE], T2.[EspEst], T1.[MMCODM], T1.[MECodE], T2.[MENomE]
                    FROM ([MAEMED] T1 WITH (NOLOCK) INNER JOIN [MAEESP] T2 WITH (NOLOCK) ON T2.[MECodE] = T1.[MECodE])
                    WHERE (T1.[MMCODM] =  '".$MMCODM."' ) AND (T2.[EspEst] = 'S') AND (T1.[MEEstE] = 'S')
                    ORDER BY T1.[MMCODM]");

            $status = 200;

        } catch(\exception $e) {

            $listaEsp = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($listaEsp, $status);
    }

    public function getAllEspecialidadMed(Request $request){
        try {
            $listaEsp = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MEEstE], T2.[EspEst], T1.[MMCODM], T1.[MECodE], T2.[MENomE]
                    FROM ([MAEMED] T1 WITH (NOLOCK) INNER JOIN [MAEESP] T2 WITH (NOLOCK) ON T2.[MECodE] = T1.[MECodE])
                    WHERE (T2.[EspEst] = 'S') AND (T1.[MEEstE] = 'S')
                    ORDER BY T1.[MMCODM]");

            $status = 200;

        } catch(\exception $e) {

            $listaEsp = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($listaEsp, $status);
    }

    public function listaCamasPab(Request $request){

        $MPCodP = $request->input('MPCodP');

        try {

            if (!$MPCodP ){
                throw new \Exception("Algo salió mal");
            }

            $camas = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MPCodC] AS MPCodC, T1.[MPNumC], T1.[MPCodP], T1.[MPActCam], T1.[MPDisp],
                        T2.[PrNomb] AS MPCodN FROM ([MAEPAB1] T1 WITH (NOLOCK)
                        LEFT JOIN [MAEPRO] T2 WITH (NOLOCK) ON T2.[PRCODI] = T1.[MPCodC])
                        WHERE (T1.[MPCodP] =  $MPCodP ) AND (T1.[MPActCam] <> 'S') AND (T1.[MPDisp] = 0)
                        ORDER BY T1.[MPCodP], T1.[MPNumC]");

            $status = 200;

        } catch(\exception $e) {
            $camas = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($camas, $status);
    }

    public function listaDiagnosticos(Request $request){

        $DMNomb = '%'.$request->input('DMNomb').'%';
        $DMNombc = $request->input('DMNomb');

        try {

            if (!$DMNomb ){
                throw new \Exception("Algo salió mal");
            }

            $diagnostico = DB::connection('sqlsrv')
            ->select("SELECT T1.[DgnEst], T1.[DMNomb], T1.[PClCod], T2.[PClArc], T1.[DMCodi]
                    FROM [MAEDIA] T1 WITH (NOLOCK)
                    LEFT JOIN [PRTCLIN] T2 WITH (NOLOCK) ON T2.[PClCod] = T1.[PClCod]
                    WHERE (T1.[DgnEst] = 'A') AND (T1.[DMNomb] LIKE '".$DMNomb."' OR T1.[DMCodi] = '".$DMNombc."')
                    ORDER BY T1.[DgnEst], T1.[DMCodi]");

            $status = 200;

        } catch(\exception $e) {
            $diagnostico = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($diagnostico, $status);
    }

    public function listaTratamiento(){

        try {

            $tto = DB::Connection('sqlsrv')

            ->select("SELECT * FROM MAETTOHOS");

            $status = 200;

        } catch(\exception $e) {
            $tto = [
                'message' => 'Algo salió mal'
            ];

            $status = 404;
        }

        return response()->json($tto, $status);
    }

    //FUNCION QUE TRAE LISTA DE CONTRATOS POR COMODIN EN INSERT
    public function listaContrato(Request $request){

        $EMPCOD = $request->input("EMPCOD");
        $MCDpto = $request->input("MCDpto");
        $MENOMB = $request->input("MENOMB");

        try {

            if(!$EMPCOD || !$MCDpto){
                throw new \Exception("Algo salió mal");
            }

            $contratos = DB::connection('sqlsrv')
                ->table('CNTRXCC AS T1')
                ->select('T1.EMPCOD', 'T1.MCDpto', 'T2.MEestado', 'T2.MENOMB', 'T1.MENNIT')
                ->join('MAEEMP AS T2', 'T2.MENNIT', '=', 'T1.MENNIT')
                ->where('T1.EMPCOD', $EMPCOD)
                ->where('T1.MCDpto', $MCDpto)
                ->when($MENOMB, function($query, $MENOMB){
                    return $query->Where('T2.MENNIT', $MENOMB)
                                ->orwhere('T2.MENOMB', 'like', '%'.$MENOMB.'%');
                })
                ->where('T2.MEestado', 0)
                ->orderBy('T1.EMPCOD')
                ->orderBy('T1.MCDpto')
                ->orderBy('T2.MENOMB')
                ->get();


            $status = 200;
            

        }catch(\exception $e){

            $contratos = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($contratos, $status);
    }

    public function listaRegimen(Request $request){

        $MENNIT = $request->input('MENNIT'); //Contrato del paciente

        try {

            $regimen = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MENNIT], T2.[MTUEstado] AS MTRegEsta,
                        T1.[MTUCo1] AS MTUCo1, T2.[MTUDes] AS MTtiNo
                    FROM ([MAEEMP1] T1 WITH (NOLOCK)
                        INNER JOIN [MAETPA2] T2 WITH (NOLOCK) ON T2.[MTUCod] = T1.[MTUCo1])
                    WHERE (T1.[MENNIT] =  '".$MENNIT."' ) AND (T2.[MTUEstado] = 'A') ORDER BY T1.[MENNIT]");

            $status = 200;

        } catch(\exception $e) {

            $regimen = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($regimen, $status);
    }

    //FUNCION TRAE LISTAS DE REGIMEN DE ACUERDO AL CONTRATO ESCOGIDO
    public function listaRegimenC(Request $request){

        $MENNIT = $request->input('MENNIT'); //NIT DE CONTRATO

        try {

            if(!$MENNIT){
                throw new \Exception("Algo salió mal");
            }

            $regimen = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MENNIT], T2.[MTUEstado] AS MTRegEsta, T1.[MTUCo1] AS MTUCo1, T2.[MTUDes] AS MTtiNo
                    FROM ([MAEEMP1] T1 WITH (NOLOCK)
                        INNER JOIN [MAETPA2] T2 WITH (NOLOCK) ON T2.[MTUCod] = T1.[MTUCo1])
                    WHERE (T1.[MENNIT] =  '".$MENNIT."' ) AND (T2.[MTUEstado] = 'A')
                    ORDER BY T1.[MENNIT]");

            $status = 200;

        } catch(\exception $e){

            $regimen = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($regimen, $status);
    }

    public function listaTipoAfiliadoCR(Request $request){

        $MENNIT = $request->input('MENNIT');
        $MTUCo1 = $request->input('MTUCo1');

        try {

            if (!$MENNIT || !$MTUCo1 ){
                throw new \Exception("Algo salió mal");
            }

            $tipoAfiliado = DB::Connection('sqlsrv')

            ->select("SELECT T2.[MTUCod], T1.[MENNIT], T2.[MTTEstado], T1.[MTUCo1], T2.[MTGenInc],
                        T1.[MTCodP], T2.[MTNomP]
                    FROM ([MAEEMP3] T1 WITH (NOLOCK)
                        INNER JOIN [MAETPA3] T2 WITH (NOLOCK) ON T2.[MTUCod] = T1.[MTUCo1] AND T2.[MTCodP] = T1.[MTCodP])
                    WHERE (T1.[MENNIT] =  '".$MENNIT."'  and T1.[MTUCo1] =  $MTUCo1 ) AND (T2.[MTTEstado] = 'A')
                    ORDER BY T1.[MENNIT]");

            $status = 200;

        } catch(\exception $e){

            $tipoAfiliado = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($tipoAfiliado, $status);
    }

    public function contratoPorPaciente(Request $request){

        $MPCedu = $request->input('MPCedu'); //Cedula paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento paciente
        try{

            if (!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $contrato = DB::Connection('sqlsrv')

            ->select("SELECT PAC.MENNIT, EMP.MENOMB, PAC.MPTDoc, PAC.MPCedu,
                        PAC.MPstatus, PAC.MTCodP, MAE3.MTNomP, PAC.MPNoCa, PAC.MPACMO, PAC.MTUCod,
                        PAC.MpFicSIS, PAC.MpPunSIS, PAC.MPOrd
                    FROM [MAEPAC] AS PAC WITH (NOLOCK)
                        inner join MAEEMP as EMP ON EMP.MENNIT = PAC.MENNIT
                        left join MAETPA3 AS MAE3 ON MAE3.MTUCod = PAC.MTUCod and MAE3.MTCodP = PAC.MTCodP
                    WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."' 
                        AND PAC.MPstatus = 'A' ORDER BY PAC.MPOrd ");

            $status = 200;
        }catch(\Exception $e){

            $contrato = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($contrato, $status);
    }

    public function contratoPorPacienteTabla(Request $request){

        $MPCedu = $request->input('MPCedu'); //Cedula paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento paciente
        try{

            if (!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $contrato = DB::Connection('sqlsrv')

            ->select("SELECT PAC.MENNIT, EMP.MENOMB, PAC.MPTDoc, PAC.MPCedu,
                        PAC.MPstatus, PAC.MTCodP, MAE3.MTNomP, PAC.MPNoCa, PAC.MPACMO, PAC.MTUCod,
                        PAC.MpFicSIS, PAC.MpPunSIS, PAC.MPOrd
                    FROM [MAEPAC] AS PAC WITH (NOLOCK)
                        inner join MAEEMP as EMP ON EMP.MENNIT = PAC.MENNIT
                        left join MAETPA3 AS MAE3 ON MAE3.MTUCod = PAC.MTUCod and MAE3.MTCodP = PAC.MTCodP
                    WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."' 
                       ORDER BY PAC.MPOrd ");

            $status = 200;
        }catch(\Exception $e){

            $contrato = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($contrato, $status);
    }

    //lista de aseguradoras
    public function listaAseguradora(Request $request){

        $EmpDsc = $request->input('EmpDsc'); //Nombre de la aseguradora

        try{

            $listaAseguradora = DB::table('MAEEMP3')
                ->join('MAEEMP', 'MAEEMP.MENNIT', '=', 'MAEEMP3.MENNIT')
                ->leftjoin('EMPRESS','EMPRESS.MEcntr','=', 'MAEEMP.MEcntr')
                ->select('MAEEMP3.MENNIT', 'MAEEMP3.MTUCo1', 'MAEEMP3.MTCodP', 'EMPRESS.EmpDsc', 'MAEEMP.MEcntr')
                ->where('MAEEMP3.MTUCo1','5')
                ->where('MAEEMP3.MTCodP','S')
                ->where('EMPRESS.EmpDsc','LIKE', '%'.$EmpDsc.'%')->get();

            $retorno = [
                'status' => 200,
                'data' => $listaAseguradora
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //lista de Ips Referente
    public function listaIpsReferente (Request $request){

        $MINomI = $request->input('MINomI'); //Nombre IPS
        $MDCodD = $request->input('MDCodD'); //Codigo IPS

        try{
            $listaIpsRef = DB::table('MAEIPS')
                ->leftJoin('MAEDMB','MAEDMB.MDCodD','=','MAEIPS.MDCodD')
                ->select('MAEIPS.IPSIndRef', 'MAEIPS.MDCodM', 'MAEIPS.MDCodD', 'MAEIPS.MINomI', 'MAEIPS.MICodI', 'MAEDMB.MDNomD')
                ->where('MAEIPS.MICodI','>=',0)
                ->where('MAEIPS.MINomI','like','%'.$MINomI.'%')
                ->where('MAEIPS.MDCodD','like','%'.$MDCodD.'%')
                ->where('MAEIPS.MDCodM','>=', 0)
                ->where('MAEIPS.IPSIndRef','S')->get();

            $retorno = [
                'status' => 200,
                'data' => $listaIpsRef
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //lista nivel atencion
    public function listaNivAtencion(){

        try{

            $nivAtn = DB::table('NIVLATE')
                ->select('NivDsc', 'NivCod')->get();
            $retorno = [
                'status' => 200,
                'data' => $nivAtn
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //lista para respuesta de procedimientos
    public function encues (Request $request){

        $EMPCOD = $request->input('EMPCOD'); //Empresa
        $EncTip = $request->input('EncTip'); //tipo de formato viene de lista quemada
        // $EncVig = Carbon::now('America/Bogota')->format('Ymd'); //vigencia
        // $EncVig = "20220201"; //vigencia

        try{
            $encue = DB::Connection('sqlsrv')
            ->select("SELECT T1.[EncVer], T1.[EncCod], T1.[EMPCOD], T2.[EncIndAct], T1.[EncVigFin], 
                        T1.[EncVigIni], T1.[EncTip], T2.[EncDsc], T1.[EncSexAp], T1.[EncRaEdI], 
                        T1.[EncTipEdI], T1.[EncRaEdF], T1.[EncTipEdF], T1.[EncTipFol] 
                    FROM ([ENCUES] T1 WITH (NOLOCK) 
                        INNER JOIN [ENCUESP] T2 WITH (NOLOCK) ON T2.[EMPCOD] = T1.[EMPCOD] AND T2.[EncCod] = T1.[EncCod])
                    WHERE (T1.[EMPCOD] =  '".$EMPCOD."'  and T1.[EncTip] =  '".$EncTip."' ) 
                        AND (T2.[EncIndAct] <> 'S') 
                    ORDER BY T1.[EMPCOD], T1.[EncTip], T1.[EncTipFol]");
            
            $retorno = [
                'status' => 200,
                'data' => $encue
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' =>200,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    public function listaTipPorc(){
        
        try{
            $tipoProc = DB::table('TIPPROC')
                ->select('TiPrDes', 'TiPrCod')->get();
            
            $retorno = [
                'status' => 200,
                'data' => $tipoProc
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //Lista de procedimientos 
    public function listaProcedimientos(Request $request){
        
        $PrNomb = $request->input('PrNomb'); //Nombre del procedimiento
        $PRCODI = $request->input('PRCODI'); //Codigo del procedimiento

        try{

            $procedimientos = DB::connection('sqlsrv')
                ->table('MAEPRO as T1')
                ->leftJoin('PRTCLIN as T2', 'T2.PClCod', '=', 'T1.PClCod')
                ->select('T1.PrSta', 'T1.PrNomb', 'T1.PRCODI', 'T1.PClCod', 'T2.PClDsc', 'T2.PClArc', 'T1.PrMCCodi')
                ->when($PrNomb, function($query, $PrNomb){
                    return $query->where('T1.PRCODI', '=', $PrNomb)
                                ->orWhere('T1.PrNomb', 'like', '%'.$PrNomb.'%');
                })
                ->when($PRCODI, function($query, $PRCODI){
                    return $query->where('T1.PRCODI', '=', $PRCODI);
                })
                ->where('T1.PrSta', 'S')
                ->where('T1.PrNoOp', 0)
                ->orderBy('T1.PRCODI')
                ->get();
                    

            $retorno = [
                'status' => 200,
                'data' => $procedimientos
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'messgae' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    }

    //lista de admisiones
    public function listaAdmisionesAbiertas(Request $request){
        $TFCedu = $request->input('TFCedu'); //Documento del paciente
        $TFTDoc = $request->input('TFTDoc'); //Tipo documento paciente

        try{

            $infoAdm = DB::Connection('sqlsrv')
            ->select("SELECT TMP.TFCedu, TMP.TFTDoc, CAP.MPNOMC, TMP.TmCtvIng, TMP.TFMENi, MAE.MENOMB,
                        TMP.ClaPro, 
                        CASE TMP.ClaPro 
                            WHEN '1' THEN 'AMBULATORIO'
                            WHEN '2' THEN 'HOSPITALARIO'
                            WHEN '3' THEN 'URGENCIAS'
                            WHEN '4' THEN 'TRATAMIENTO ESPECIAL'
                            WHEN '5' THEN 'TRIAGE'
                        END AS TipoAtencion,
                        TMP.TFDi1I, DIA.DMNomb, TMP.TFcCodPab, PAB.mpCncCod 
                    FROM TMPFAC AS TMP
                        LEFT JOIN CAPBAS AS CAP ON CAP.MPCedu = TMP.TFCedu AND CAP.MPTDoc = TMP.TFTDoc 
                        LEFT JOIN MAEEMP AS MAE ON MAE.MENNIT = TMP.TFMENi
                        LEFT JOIN MAEDIA AS DIA ON DIA.DMCodi = TMP.TFDi1I
                        LEFT JOIN MAEPAB AS PAB ON PAB.MPCodP = TMP.TFcCodPab  
                    where TFCedu = '".$TFCedu."' and TFTDoc = '".$TFTDoc."' ");

            $retorno = [
                'status' => 200,
                'data' => $infoAdm
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);

    }

    //Lista de los honorarios de procedimiento con forma de liquidacion
    public function listaHonorarios(Request $request){

        $MENNIT = $request->input('MENNIT'); //Codigo del contrato
        $PRCODI = $request->input('PRCODI'); //Codigo del procedimiento 
        $fechAct = Carbon::now('America/Bogota')->format('Ymd');

        try{
            
            $vigContrato = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig', '<=', $fechAct)
                ->orderByDesc('MEPPVig')->first();
            
            $prtProc = DB::table('PORTAR1')
                ->select('PRCODI', 'PTCodi', 'PTApCo', 'PTApMo', 'PTPorc', 'TrfCod', 'FctoCod', 
                        'PTCntUvr', 'ForLiqCod')
                ->where('PTCodi', $vigContrato->PTCodi)        
                ->where('PRCODI', $PRCODI)->first();

            $infoHonorario = DB::table('FORLIQ1')
                ->leftJoin('HONRIOS','HONRIOS.HnrCod','=','FORLIQ1.HnrCod')
                ->leftJoin('FACTOR','FACTOR.FctoCod','=','FORLIQ1.FctoCod')
                ->select('FORLIQ1.HnrCod', 'HONRIOS.HnrDsc', 'ForLiqVlHn', 'FACTOR.FctoVlr', 
                        DB::raw('ROUND((ForLiqVlHn*FACTOR.FctoVlr), -2) as ValorHonorario'),
                        'ForLiqCod', 'ForLiqcar',  
                        'FORLIQ1.FctoCod', 'FOREXCBAS')
                ->where('ForLiqCod',$prtProc->ForLiqCod)->get();

            $retorno = [
                'status' => 200,
                'data' => $infoHonorario
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    } 

    //lista opcion de procedimiento 
    public function opcionProc (){
        try{
            $opcion = DB::table('PRCMULOPC')
                ->select('PrcMulOpc', 'PrcMulDsc')
                ->orderBy('PrcMulOpc')->get();
            
            $retorno = [
                'status' => 200,
                'data' => $opcion
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
            
        }
        return response()->json($retorno);
    }

    //lista agrupador
    public function agrupadorCir (){
        try{
            $agrupador = DB::table('PROAGR')
                ->select('ProAgCod', 'ProAgDes')
                ->orderBy('ProAgDes')->get();
            
            $retorno = [
                'status' => 200,
                'data' => $agrupador
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
            
        }
        return response()->json($retorno);
    }

    //Lista de formas de pago 
    public function formasPago(){

        try{

            $formPago = DB::table('FMP001')
                ->select('EMPCOD', 'FMESTADO', 'CodTpoPgo', 'CodPago', 'NomPago')
                ->where('EMPCOD', '1')
                ->where('CodTpoPgo', '<>', 'P')
                ->where('FMESTADO', 'S')->get();
            
            $retorno = [
                'status' => 200,
                'data' => $formPago
            ];

        }catch(\Exception $e){

            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];

        }
        return response()->json($retorno);
    }

    //Lista bancos
    public function bancos(){

        try{

            $bancos = DB::table('BANCOS')
                ->select('CODBAN', 'NomBan')
                ->orderBy('NomBan')->get();
            
            $retorno = [
                'status' => 200,
                'data' => $bancos
            ];

        }catch(\Exception $e){

            $retorno = [
                'status' => 200,
                'message' => $e->getMessage()
            ];

        }

        return response()->json($retorno);
        
    }

    //Lista tipo de entidad
    public function listaTipoEntidad(){

        try{

            $tipoEntidad = DB::table('TIPENT')
                ->select('TipEntCod', 'TipEntDsc')
                ->orderBy('TipEntCod')->get();
            
            $retorno  = [
                'status' => 200,
                'data' => $tipoEntidad
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //Lista de tipo de tercero
    public function listaTipoTercero(){

        try{

            $tipoTercero = DB::table('TIPTER')
                ->select('TipCodTer', 'TipDscTer')
                ->orderBy('TipDscTer')->get();
            
            $retorno  = [
                'status' => 200,
                'data' => $tipoTercero
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);

    }

    //Lista tipo contribuyente 
    public function listaTipoContribuyente(){

        try{

            $tipoContribuyente = DB::table('TPOCONT')
                ->select('TCoCod', 'TCoDsc')
                ->orderBy('TCoCod')->get();
            
            $retorno  = [
                'status' => 200,
                'data' => $tipoContribuyente
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);

    }

    //Funcion trae lista de empresas
    public function listaEmpresas(Request $request){

        $MEcntr = $request->input('MEcntr'); //clave

        $listaEmpresas = DB::table('EMPRESS')
            ->select('MEcntr', 'EmpDsc', 'MENNid')
            ->where('MEcntr', $MEcntr)
            ->orWhere('EmpDsc', 'like', '%'.$MEcntr.'%')
            ->get();
        
        return response()->json($listaEmpresas);

    }

    //Funcion para traer lista de contratos por empresa
    public function listaContratos(Request $request){

        $contratos = DB::table('MAEEMP')
            ->select('MEcntr', 'MENNIT', 'MENOMB')
            ->where('MEcntr', $request->MEcntr)
            ->get();
        
        return response()->json($contratos);

    }    

    //Funcion que trae la lista de las tarifas
    public function listaTarifas(){
                
        $tarifas = DB::table('TARIFAS')
            ->select('TrfCod', 'TrfDsc')
            ->get();
        
        return response()->json($tarifas);
        
    }

    //Funcion para traer la lista de las especialidades
    public function listaEspecialidades(Request $request){

        $MECodE = $request->input('MECodE'); //clave

        $especialidades = DB::table('MAEESP')
            ->select('MECodE', 'MENomE')
            ->when($MECodE, function($query, $MECodE){
                return $query->where('MECodE', $MECodE)
                            ->orWhere('MENomE', 'like', '%'.$MECodE.'%');
            })
            ->get();
        return response()->json($especialidades);
    }

    //Funcion para traer los puntos de ruta
    public function puntosRuta(){
            
            $puntosRuta = DB::table('PUNRUT')
                ->select('EMPCOD', 'MCDpto', 'PunRutCod', 'PunRutDes')
                ->get();
            
            return response()->json($puntosRuta);
    }



}
