<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BD;
use Illuminate\Support\Facades\Redis;

class ValidacionesController extends Controller
{

    //FUNCION VALIDA SI EL DOCUMENTO ES UN DOCUMENTO EXTRANEJERO
    public function documentoExtranjero(Request $request){

        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento

        try {

            if (!$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $extranjeria = DB::Connection('sqlsrv')

            ->select("SELECT [MPTDoc], [MPIndExt], [MPTEstado] FROM [TIPDOCASI] WITH (NOLOCK)
                    WHERE [MPTDoc] =  '".$MPTDoc."'  ORDER BY [MPTDoc]");

            $status = 200;
        }catch(\exception $e) {

            $extranjeria = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($extranjeria, $status);
    }

    //VALIDA SI EL PACIENTE YA ESTA CREADO EN LA BASE DE DATOS
    public function existePaciente(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento

        try {

            if(!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $paciente = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [MPTDoc], [MPCedu], [MPNOMC]
                        FROM [CAPBAS] WITH (NOLOCK)
                        WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."'  ORDER BY [MPCedu], [MPTDoc]");

            $status = 200;
        } catch(\exception $e){

            $paciente = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($paciente, $status);
    }

    //FUNCION VERIFICA CARGOS PENDIENTES EN TEMPORAL DE FACTURACION (admision abierta)
    public function cargosTMP(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento

        try{

            if(!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $cargos = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [TFCedu], [TFTDoc], [TFFchM], [TFEstS], [TmCtvIng]
                    FROM [TMPFAC] WITH (NOLOCK)
                    WHERE [TFCedu] =  '".$MPCedu."'  and [TFTDoc] =  '".$MPTDoc."'
                    ORDER BY [TFCedu], [TFTDoc], [TmCtvIng] DESC");

            $status = 200;

        }catch(\exception $e){
            $cargos = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($cargos, $status);
    }

    //FUNCION VALIDA SI EL PACIENTE TIENE UN INGRESO EN LA CLINICA
    public function existeIngreso(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento

        try {

            if(!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $ingresos = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [MPCedu], [MPTDoc], [IngFchM], [IngEstSld], [IngCsc], [ClaPro]
                    FROM [INGRESOS] WITH (NOLOCK)
                    WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."'
                    ORDER BY [MPCedu], [MPTDoc], [IngCsc] DESC");

            $status = 200;

        }catch(\exception $e){

            $ingresos = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($ingresos, $status);
    }

    //FUNCION VERIFICA QUE EL PACIENTE YA TENGA ESA PREFERENCIA DE ATENCION
    public function preferenciaPaciente(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento
        $PACtvo = $request->input('PACtvo'); //Consecutivo de preferencia
        $CLAESTCOD = $request->input('CLAESTCOD'); //Codigo de la preferencia para la verificacion

        try {

            if (!$MPCedu || !$MPTDoc || !$PACtvo || !$CLAESTCOD){
                throw new \Exception("Algo salió mal");
            }

            $preferencia = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [CLAESTCOD], [PACtvo], [MPTDoc], [MPCedu], [PADesc], [PAFecReg],
                        [PAUsuReg], [PAESTACT]
                    FROM [PREATE] WITH (UPDLOCK)
                    WHERE ([MPCedu] =  '".$MPCedu."'  AND [MPTDoc] =  '".$MPTDoc."'
                        AND [PACtvo] =  $PACtvo  AND [CLAESTCOD] =  '".$CLAESTCOD."' ) ");

            $status = 200;

        } catch(\exception $e){

            $preferencia = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($preferencia, $status);
    }

    public function contratoPaciente(Request $request){

        $MPCedu = $request->input('MPCedu'); //Cedula paciente
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento paciente
        $MENNIT = $request->input('MENNIT'); //Nit de contrato a validar

        try{

            if (!$MPCedu || !$MPTDoc || !$MENNIT){
                throw new \Exception("Algo salió mal");
            }

            $validaContrato = DB::Connection('sqlsrv')

            ->select("SELECT [MENNIT], [MPTDoc], [MPCedu] FROM [MAEPAC] WITH (NOLOCK)
                    WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."'  and [MENNIT] =  '".$MENNIT."'
                    ORDER BY [MPCedu], [MPTDoc], [MENNIT]");

            $status = 200;
        }catch(\Exception $e){

            $validaContrato = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($validaContrato, $status);
    }

    //VALIDA ANTES DE ACOSTAR EL PACIENTE EL ESTADO DE LA CAMA Y SE LLEVA EL CONSECUTIVO DE LA CAMA PARA EL UPDATE
    public function infoCama(Request $request){
        $MPCodP = $request->input('MPCodP');
        $MPNumC = $request->input('MPNumC');

        try{
            $infoCama = DB::Connection('sqlsrv')
            ->select("SELECT [MPNumC], [MPCodP], [MpUltCtvo], [MPDisp], [MPUced], [MPUDoc],
                        [MPCtvIn], [MPFchI]
                    FROM [MAEPAB1] WITH (UPDLOCK)
                    WHERE ([MPCodP] =   $MPCodP AND [MPNumC] =  '".$MPNumC."' ) ");
            $status = 200;
        }catch(\exception $e){
            $infoCama = [
                'message' => $e->getMessage()
            ];
            $status  = 404;
        }
        return response()->json($infoCama,$status);
    }

    //DEVUELVE EL SIGUIENTE INGRESO DEL PACIENTE
    public function nextIngreso(Request $request){

        $MPCedu = $request->input('MPCedu'); //numero de documento de paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo de documento
        try{
            $nextIngreso = DB::Connection('sqlsrv')
            ->select("SELECT TOP 1 [MPTDoc], [MPCedu], [MpCtvoAtn], [MpCtvoActe]
                    FROM [CAPBAS] WITH (NOLOCK) WHERE [MPCedu] =  '".$MPCedu."'  and [MPTDoc] =  '".$MPTDoc."'
                    ORDER BY [MPCedu], [MPTDoc]");
            $status = 200;
        }catch(\exception $e){
            $nextIngreso = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($nextIngreso, $status);
    }

    //consulta el ultimo consecutivo de movimiento de la cama
    public function ultCtvoCama(Request $request){

        $MPCodP = $request->input('MPCodP'); //pabellon de la cama
        $MPNumC = $request->input('MPNumC'); //cama

        try{
            $ultCtvo = DB::table('MAEPAB11')
                ->select('MPCodP', 'MPNumC', 'HisCamCtv')
                ->where('MPCodP',$MPCodP)
                ->where('MPNumC',$MPNumC)->max('HisCamCtv');

            $retorno = [
                'status' => 200,
                'data' =>  Carbon::now('America/Bogota')->format('Ymd H:i:s')
            ];
        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return $retorno;
    }
}
