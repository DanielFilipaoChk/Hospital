<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;

class CensoController extends Controller
{
    public function censo (Request $request){

        $MPCodP = $request->MPCodP; //Codgio de pabellon
        $ClaPro = $request->ClaPro; //Clase de procedimiento
        $EMPCOD = $request->EMPCOD; //Codigo de la empresa
        $MPMCDpto = $request->MPMCDpto; //Codigo de la sede

        try{

            if($MPCodP == null){
                $MPCodPV = "''";
                $MPCodP = "''";
            }
            else{
                $MPCodPV = 'T1.[MPCodP]';
                $MPCodP = $MPCodP;
            }

            if($ClaPro == null){
                $ClaProV = "''";
                $ClaPro = "''";
            }
            else{
                $ClaProV = 'T2.[MPCLAPRO]';
                $ClaPro = $ClaPro;
            }

            $censo = DB::Connection('sqlsrv')
            ->select("SELECT T2.[EMPCOD], T2.[MPMCDpto], T2.[MPCLAPRO], T2.[MPActPab], 
                        T1.[MPActCam], T1.[MPCodP], LTRIM(RTRIM(T2.[MPNomP])) as MPNomP, T1.[MPNumC], T1.[MPUDoc], T1.[MPUced], T1.[MPCtvIn], T1.[MPDisp],
                        CASE T1.[MPDisp]
                            WHEN 0 THEN 'LIBRE'
                            WHEN 1 THEN 'OCUPADA'
                            WHEN 8 THEN 'MANTENIMIENTO'
                            WHEN 9 THEN 'DESINFECCIÓN'
                            WHEN 10 THEN 'BLOQUEADA'
                        END AS desEstadoCama,
                        UPPER(LTRIM(RTRIM(T3.MPNOMC))) as MPNOMC,
                        DATEDIFF(YEAR,T3.MPFchN,GETDATE())
                        -(CASE
                        WHEN DATEADD(YY,DATEDIFF(YEAR,T3.MPFchN,GETDATE()),T3.MPFchN)>GETDATE() THEN
                            1
                        ELSE
                            0 
                        END) as Edad, 
                        T1.MPUdx, LTRIM(RTRIM(T4.DMNomb)) as DMNomb, T5.IngFecAdm, DATEDIFF (DAY, T5.IngFecAdm, GETDATE()) AS DIAS_ESTANCIA, T5.IngNit,
                        T6.MENOMB, 
                        CASE T6.MECApi 
                        WHEN 1 THEN 'CAPITADO'
                        WHEN 0 THEN 'EVENTO' 
                        END AS CONTRATO,
                        T7.MENomE, T8.MTCodP AS NIVEL
                        FROM ([MAEPAB1] T1 WITH (NOLOCK) 
                        
                        left join [MAEPAB] T2 WITH (NOLOCK) ON T2.[MPCodP] = T1.[MPCodP]) 
                        left join CAPBAS T3 ON T3.MPCedu = T1.MPUced AND T3.MPTDoc = T1.MPUDoc
                        left join MAEDIA T4 ON T4.DMCodi = T1.MPUdx
                        left join INGRESOS T5 ON T5.MPCedu = T1.MPUced AND T5.MPTDoc = T1.MPUDoc AND T5.IngCsc = T1.MPCtvIn
                        left join MAEEMP T6 ON T6.MENNIT = T5.IngNit
                        left join MAEESP T7 ON T7.MECodE = T5.IngEsMt
                        left join MAEPAC T8 ON T8.MPCedu = T1.MPUced AND T8.MPTDoc = T1.MPUDoc AND T8.MENNIT = T5.IngNit
                        
                        WHERE (T2.[EMPCOD] = '".$EMPCOD."' and T2.[MPMCDpto] = '".$MPMCDpto."') and (T1.[MPActCam] <> 'S') 
                            and (T2.[MPActPab] <> 'S') and (T2.[MPCLAPRO] = '2' or T2.[MPCLAPRO] = '3') 
                            and $MPCodPV = $MPCodP and $ClaProV = $ClaPro
                        ORDER BY T1.[MPCodP], T1.[MPNumC]
                        ");

            foreach($censo as $cens){
                $diagnostico = DB::table('HCDIAGN')
                    ->where ('HISCKEY', $cens->MPUced)
                    ->where('HISTipDoc', $cens->MPUDoc)
                    ->where('HCDXCLS', '1')
                    ->orderBy('HISCSEC', 'desc')->first();
                
                if($diagnostico){
                    $nomD=DB::table('MAEDIA')
                        ->select('MAEDIA.DMNomb')
                        ->where('DMCodi', $diagnostico->HCDXCOD)
                        ->lock('WITH(NOLOCK)')->first();

                    $cens->MPUdx = $diagnostico->HCDXCOD;

                    if($nomD){
                        $cens->DMNomb = $nomD->DMNomb;
                    }else{
                        $cens->DMNomb = '';
                    }
                }else{

                    $cens->MPUdx = '';
                    $cens->DMNomb = '';
                }


            }

            $retorno = [
                'status' => 200,
                'data' => $censo
            ];
        }catch(\Exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }
        return $retorno;
    }
}
