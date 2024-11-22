<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;


class infoInicialController extends Controller
{

    /*FUNCION QUE TRAE DE LA TABLA ORGANIZ: 
        forma de documento
        asiento contable caja
        activadores de indicadores 1
        activadores de indicadores 2
        codgo de la organizacion    
    */
    public function organizacion()
    {
        try{
            $organizacion = DB::Connection('sqlsrv')

            ->select("SELECT [MCFDOC], [mcacaja], [MCFLAGs], [McFlags2], [ORGCOD] 
                        FROM [ORGANIZ] WITH (NOLOCK) ORDER BY [ORGCOD]");
            $status = 200;
        }catch(\exception $e){
            $organizacion = [
                'message' => 'Algo salió mal'
            ];
            $status = 404;
        }
        return response()->json($organizacion, $status);
    }

    // VALIDA EL USUSARIO EN LA TABL ADMUSR1 CON LA EMPRESA
    public function validaUsuario(Request $request){
        $AUsrId = $request->input('AUsrId'); //id del usuario
        $EMPCOD = $request->input('EMPCOD'); //codigo de la emoresa

        try{
            if (!$AUsrId || !$EMPCOD){
                throw new \Exception('Falta información requerida');
            }
            $usuario = DB::Connection('sqlsrv')

            ->select("SELECT [AUsrEstDp], [EMPCOD], [AUsrId], [MCDpto] FROM [ADMUSR1] WITH (NOLOCK) 
                        WHERE ([AUsrId] = '".$AUsrId."'  and [EMPCOD] =  $EMPCOD ) AND ([AUsrEstDp] = 'S') 
                        ORDER BY [AUsrId], [EMPCOD], [MCDpto]");
            $status = 200;
        }catch (\exception $e){
            $usuario = [
                'message' => $e->getMessage() 
            ];
            $status = 404;
        }
        return response()->json($usuario, $status);
    }

    //Trae información de la sede 
    public function sede(Request $request){
        $EMPCOD = $request->input('EMPCOD');
        $MCDpto = $request->input('MCDpto');

        try{
            if(!$EMPCOD || !$MCDpto){
                throw new \Exception('Falta información requerida');
            }

            $sede = DB::Connection('sqlsrv')

            ->select("SELECT [MCDpto], [EMPCOD], [MCDnom] FROM [MAESED] WITH (NOLOCK)
                        WHERE [EMPCOD] =  $EMPCOD  and [MCDpto] =  '".$MCDpto."'  ORDER BY [EMPCOD], [MCDpto]");
            $status = 200;

        }catch(\exception $e){
            $sede = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($sede, $status);
    }

    //Información del periodo escogido al ingresar a hosvital
    public function periodo (Request $request){
        $ANO001 = $request->input('ANO001');
        $MES002 = $request->input('MES002');

        try{
            if(!$ANO001 || !$MES002){
                throw new \Exception('Falta información requerida');
            }

            $periodo = DB::Connection('sqlsrv')

            ->select("SELECT [MES002], [ANO001], [INI002], [FIN002] FROM [CTR002] WITH (NOLOCK) 
                        WHERE [ANO001] =  $ANO001  and [MES002] =  $MES002  ORDER BY [ANO001], [MES002]");
            $status = 200;

        }catch(\exception $e){
            $periodo = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($periodo, $status);
    }

    //Información de los servicios de la clinica 
    public function servicios(Request $request){
        $EMPCOD = $request->input('EMPCOD');
        try{
            $servicios = DB::Connection('sqlsrv')

            ->select("SELECT [EMPCOD], [MPTraEsp], [MPCodP], [MPCLAPRO], [MPMCDpto] FROM [MAEPAB] 
                    WITH (NOLOCK) WHERE [EMPCOD] =  $EMPCOD  ORDER BY [EMPCOD], [MPMCDpto], [MPCLAPRO]");
            $status = 200;
        }catch(\exception $e){
            $servicios = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($servicios, $status);
    }

    //Informacion de la emrpesa

    public function empresa(Request $request){
        
        $EMPCOD = $request->input('EMPCOD');

        try{

            if (!$EMPCOD){
                throw new \Exception("Algo salió mal");
                
            }
            $empresa = DB::Connection('sqlsrv')
            ->select("SELECT [EMPCOD], [EmpDVer], [EmpNit], [PaisCod] 
                        FROM [EMPRESA] WITH (NOLOCK) 
                        WHERE [EMPCOD] =  $EMPCOD  ORDER BY [EMPCOD]");
            $status = 200;

        }catch(\exception $e){

            $empresa = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($empresa, $status);
    }

    public  function ruta(Request $request){

        $EMPCOD = $request->input('EMPCOD');
        $MCDpto = $request->input('MCDpto');

        try{

            if(!$EMPCOD || !$MCDpto){
                throw new \Exception("Algo salio mal");   
            }

            $ruta = DB::Connection('sqlsrv')

            ->select("SELECT [MDCns], [MDTipo], [MCDpto], [EMPCOD], [MDRuta], [MDPue], [MDIP] 
                        FROM [MAEDIR] WITH (NOLOCK) 
                        WHERE [EMPCOD] =  $EMPCOD  and [MCDpto] = '".$MCDpto."'  
                        and [MDTipo] = 'URL' and [MDCns] = 81 ORDER BY [EMPCOD], [MCDpto], [MDTipo], [MDCns]");
            $status = 200;

        }catch(\exception $e){
            
            $ruta = [
                'message' => $e->getMessage()
            ];
            $status = 404;
        }

        return  response()->json($ruta, $status);
    }

}
