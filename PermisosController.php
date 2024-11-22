<?php

namespace App\Http\Controllers\ApiAdmisiones;
use \Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;


class PermisosController extends Controller
{

   //FUNCION QUE CONSULTA EL GRUPO DEL USUARIO
    public function grupoUsuario(Request $request){
        $AUsrId = $request->input('AUsrId');

        try{
            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $grupoUsr = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [AUsrId], [AGrpId], [AUsrTip]
                        FROM [ADMUSR] WITH (NOLOCK) WHERE [AUsrId] =  '".$AUsrId."'  ORDER BY [AUsrId]");

            $status = 200;
        }catch(\exception $e){
            $grupoUsr = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($grupoUsr, $status);
    }

    //FUNCION DE VALIDACION DE USUARIO CON LA SEDE estado de la sede en 'S'
    public function usuarioSede(Request $request){

        $AUsrId = $request->input('AUsrId');

        try{

            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $usuarioSede = DB::Connection('sqlsrv')

            ->select("SELECT T1.[AUsrEstDp], T1.[AUsrId], T2.[MCDnom], T1.[EMPCOD], T1.[MCDpto]
                        FROM ([ADMUSR1] T1 WITH (NOLOCK)
                        INNER JOIN [MAESED] T2 WITH (NOLOCK) ON T2.[EMPCOD] = T1.[EMPCOD] AND T2.[MCDpto] = T1.[MCDpto])
                        WHERE (T1.[AUsrId] =  '".$AUsrId."' ) AND (T1.[AUsrEstDp] = 'S')
                        ORDER BY T1.[AUsrId]");
            $status = 200;

        }catch(\exception $e){

            $usuarioSede = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }

        return response()->json($usuarioSede, $status);
    }

    //PERMISO PARA EL INGRESO A DATOS DEL AFILIADO
    public function datosAfiliado(Request $request){

        $AUsrId = $request->input('AUsrId');

        try{

            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $datosAfiliado = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter]
                FROM [PEREXC] WITH (NOLOCK)
                WHERE [AUsrId] =  '".$AUsrId."'  and [ASysId] =  'ADMISIONES'  and [APgmId] =  'WMAEPAC'
                and [AEvnId] =  'DATOSAFILIADOS'  ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");
            $status = 200;

        }catch(\exception $e){

            $datosAfiliado = [
                'mesasge' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($datosAfiliado,$status);
    }

    //PERMISO PARA INGRESAR A MANEJO DE CAMAS
    public function manejoCamas(Request $request){

        $AUsrId = $request->input('AUsrId');

        try{

            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $manejoCamas = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter]
                        FROM [PEREXC] WITH (NOLOCK)
                        WHERE [AUsrId] =  '".$AUsrId."'  and [ASysId] =  'ADMISIONES'  and [APgmId] =  'WHABILITAR'
                        and [AEvnId] =  'MANEJODECAMAS'  ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");

            $status = 200;

        }catch(\exception $e){

            $manejoCamas = [
                'mesasge' => $e->getMessage()
            ];
            $status = 404;
        }

        return response()->json($manejoCamas, $status);

    }

    //PERMISO PARA CONSULTAR PACIENTES
    public function consultarMCamas(Request $request){

        $AUsrId = $request->input('AUsrId');

        try{

            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $consultarMCamas = DB::Connection('sqlsrv')

            ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter]
                        FROM [PEREXC] WITH (NOLOCK)
                        WHERE [AUsrId] =  '".$AUsrId."'  and [ASysId] =  'WTMAEPABRE'  and [APgmId] =  'WTMAEPABRE'
                        and [AEvnId] =  'CONSULTAR'  ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");

            $status = 200;

        }catch(\exception $e){

            $consultarMCamas = [
                'mesasge' => $e->getMessage()
            ];
            $status = 404;

        }

        return response()->json($consultarMCamas, $status);
    }

    //PERMISO PARA ANULACION DE INGRESOS
    public function anulaIngresos(Request $request){

        $AUsrId = $request->input('AUsrId'); //id del usuario que debe tener el permiso

        try{
            if(!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $permiso = DB::Connection('sqlsrv')
            ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter]
                    FROM [PEREXC] WITH (NOLOCK)
                    WHERE [AUsrId] =  '". $AUsrId."'  and [ASysId] =  'ADMISIONES'  and [APgmId] =  'WMNUADMI'
                        and [AEvnId] =  'ANULARINGRESO'
                    ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");

            if($permiso == null){
                
                throw new \Exception('Permiso no concedido');
                
            }else{

                $retorno = [
                    'status' => 200,
                    'message' =>'permiso concedido',
                    'data' => $permiso
                ];
            }


        }catch(\exception $e){
            $retorno = [
                'status' => 404,
                'message' => $e->getMessage(),
                'data' => ''
            ];
        }
        return response()->json($retorno);
    }

    //PERMISO PARA CAMBIO DE SERVICIO DE PACIENTE
    public function cambioServicio(Request $request){
        $AUsrId = $request->input('AUsrId'); //id del usuario que debe tener el permiso

        try{

            if (!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $cambioServicio = DB::Connection('sqlsrv')
            ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter]
                    FROM [PEREXC] WITH (NOLOCK)
                    WHERE [AUsrId] =  '".$AUsrId."'  and [ASysId] =  'ADMISIONES'
                        and [APgmId] =  'WCAMSERV'  and [AEvnId] =  'CAMBIODESERVICIO'
                    ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");
            $retorno = [
                'status' => 200,
                'message' => 'permiso concedido',
                'data' => $cambioServicio
            ];

        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);
    }

    //PERMISO PARA CREAR UNA ADMISION EXTERNA
    public function creaAutoExterna (Request $request){
        $AUsrId = $request->input('AUsrId'); //id del usuario que debe tener el permiso

        try{
            if (!$AUsrId){
                throw new \Exception("Algo salió mal");
            }

            $autoExt = DB::connection('sqlsrv')
                ->select("SELECT TOP 1 [AEvnId], [APgmId], [ASysId], [AUsrId], [ACriter] 
                            FROM [PEREXC] WITH (NOLOCK) 
                            WHERE [AUsrId] =  '".$AUsrId."'  and [ASysId] =  'AUDITORIA'  and 
                            [APgmId] =  'WAUTRATN'  and [AEvnId] =  'CREAR'  
                            ORDER BY [AUsrId], [ASysId], [APgmId], [AEvnId]");
            
            $retorno = [
                'status' => 200,
                'data' => $autoExt
            ];

        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'messgae' => $e->getMessage()
            ];
        }
        return response()->json($retorno);
    }
}
