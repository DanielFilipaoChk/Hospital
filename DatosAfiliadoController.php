<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use BD;

class DatosAfiliadoController extends Controller
{
    public function infoGeneral(Request $request){

        $MPCedu = $request->input('MPCedu');
        $MPTDoc = $request->input('MPTDoc');

        try{

            if(!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $infoGeneral = DB::Connection('sqlsrv')
                ->select("SELECT LTRIM(RTRIM(T1.[MPTDoc])) AS MPTDoc, 
                                LTRIM(RTRIM(T1.[MPCedu])) AS MPCedu, 
                                LTRIM(RTRIM(T1.[MPNHiC])) AS MPNHiC, 
                                LTRIM(RTRIM(T1.[MPNumHCIn])) AS MPNumHCIn, 
                                LTRIM(RTRIM(T1.[MpLgExp])) AS MpLgExp, 
                                LTRIM(RTRIM(T1.[MPNom1])) AS MPNom1, 
                                LTRIM(RTRIM(T1.[MPNom2])) AS MPNom2, 
                                LTRIM(RTRIM(T1.[MPApe1])) AS MPApe1, 
                                LTRIM(RTRIM(T1.[MPApe2])) AS MPApe2, 
                                MPFchN, 
                                T1.[MPFchDef], 
                                LTRIM(RTRIM(T1.[MPEstC])) AS MPEstC, 
                                LTRIM(RTRIM(T1.[MPSexo])) AS MPSexo, 
                                LTRIM(RTRIM(T1.[MPGrEs])) AS MPGrEs, 
                                LTRIM(RTRIM(T4.[ATEESPDES])) AS ATEESPDES, 
                                LTRIM(RTRIM(T1.[MPTele])) AS MPTele, 
                                LTRIM(RTRIM(T1.[MpTele1])) AS MpTele1, 
                                LTRIM(RTRIM(T1.[MpTele2])) AS MpTele2, 
                                LTRIM(RTRIM(T1.[MPDire])) AS MPDire, 
                                LTRIM(RTRIM(T1.[MPEmpTra])) AS MPEmpTra, 
                                LTRIM(RTRIM(T1.[MPOtrAfl])) AS MPOtrAfl, 
                                LTRIM(RTRIM(T1.[MPOtTiAf])) AS MPOtTiAf, 
                                LTRIM(RTRIM(T2.[MOCodi])) AS MOCodi, 
                                LTRIM(RTRIM(T1.[MOCodPri])) AS MOCodPri, 
                                LTRIM(RTRIM(T7.[MODesPri])) AS MODesPri, 
                                LTRIM(RTRIM(T1.[MPSmCtCm])) AS MPSmCtCm, 
                                LTRIM(RTRIM(T1.[MPIpsAtn])) AS MPIpsAtn, 
                                LTRIM(RTRIM(T6.[IPSDsc])) AS IPSDsc, 
                                LTRIM(RTRIM(T1.[MpMail])) AS MpMail, 
                                LTRIM(RTRIM(T1.[MDCodD])) AS MDCodD, 
                                LTRIM(RTRIM(T8.[MDNomD])) AS MDNomD, 
                                LTRIM(RTRIM(T1.[MDCodM])) AS MDCodM, 
                                LTRIM(RTRIM(T9.[MDNomM])) AS MDNomM, 
                                LTRIM(RTRIM(T1.[MDCodB])) AS MDCodB, 
                                LTRIM(RTRIM(T10.[MDNomB])) AS MDNomB, 
                                LTRIM(RTRIM(T1.[MPCodPai])) AS MPCodPai, 
                                LTRIM(RTRIM(T3.[PaisNom])) AS PaisNom, 
                                LTRIM(RTRIM(T1.[MpUsrPrf])) AS MpUsrPrf, 
                                LTRIM(RTRIM(T1.[MPPacNN])) AS MPPacNN, 
                                LTRIM(RTRIM(T1.[MPNivEdu])) AS MPNivEdu, 
                                LTRIM(RTRIM(T12.[NivEdDsc])) AS NivEdDsc, 
                                LTRIM(RTRIM(T1.[MPNivEEs])) AS MPNivEEs, 
                                LTRIM(RTRIM(T1.[MdCodDNac])) AS MdCodDNac, 
                                LTRIM(RTRIM(T1.[MdCodMNac])) AS MdCodMNac, 
                                LTRIM(RTRIM(T1.[MPEstPac])) AS MPEstPac, 
                                LTRIM(RTRIM(T1.[MPDere])) AS MPDere, 
                                LTRIM(RTRIM(T1.[MPBECar])) AS MPBECar, 
                                LTRIM(RTRIM(T1.[MPBEIps])) AS MPBEIps, 
                                LTRIM(RTRIM(T1.[MPCodPai])) AS MPCodPai, 
                                LTRIM(RTRIM(T1.[MPTDocMa])) AS MPTDocMa, 
                                LTRIM(RTRIM(T1.[MPCedMa])) AS MPCedMa, 
                                LTRIM(RTRIM(T1.[MPCscInM])) AS MPCscInM, 
                                LTRIM(RTRIM(T1.[MPConNac])) AS MPConNac, 
                                LTRIM(RTRIM(T1.[MCARNET])) AS MCARNET, 
                                LTRIM(RTRIM(T1.[MPGrPo])) AS MPGrPo, 
                                LTRIM(RTRIM(T5.[GRUPOBDES])) AS GRUPOBDES, 
                                LTRIM(RTRIM(T1.[MPViveS])) AS MPViveS, 
                                LTRIM(RTRIM(T1.[MPPEREXPU])) AS MPPEREXPU, 
                                LTRIM(RTRIM(T1.[MPCEDTIT])) AS MPCEDTIT, 
                                LTRIM(RTRIM(T1.[MPINDIS])) AS MPINDIS, 
                                LTRIM(RTRIM(T1.[MPINDEC])) AS MPINDEC, 
                                LTRIM(RTRIM(T1.[MPPARTIT])) AS MPPARTIT, 
                                LTRIM(RTRIM(T1.[MPNOMTIT])) AS MPNOMTIT, 
                                LTRIM(RTRIM(T1.[MPAMTIT])) AS MPAMTIT, 
                                LTRIM(RTRIM(T1.[MPAPTIT])) AS MPAPTIT, 
                                LTRIM(RTRIM(T1.[MPSbGrPo])) AS MPSbGrPo, 
                                LTRIM(RTRIM(T1.[MPCodDisc])) AS MPCodDisc, 
                                LTRIM(RTRIM(T1.[MPCodEtn])) AS MPCodEtn, 
                                LTRIM(RTRIM(T11.[MPDscEt])) AS MPDscEt, 
                                LTRIM(RTRIM(T1.[MPCPEtn])) AS MPCPEtn 
                        FROM ([CAPBAS] T1 WITH (NOLOCK) 
                            LEFT JOIN [MAEOCUPRI] T2 WITH (NOLOCK) ON T2.[MOCodPri] = T1.[MOCodPri]) 
                            LEFT JOIN [PAIS] T3 WITH (NOLOCK) ON T3.[PaisCod] = T1.[MPCodPai]
                            LEFT JOIN [ATEESP] T4 WITH (NOLOCK) ON T4.[ATEESPCOD] = T1.[MPGrEs]
                            LEFT JOIN [GRUPOB] T5 WITH (NOLOCK) ON T5.[GruPobCod] = T1.[MPGrPo]
                            LEFT JOIN [MAEIPSATE] T6 WITH (NOLOCK) ON T6.[IPSCod] = T1.[MPIpsAtn]
                            LEFT JOIN [MAEOCUPRI] T7 WITH (NOLOCK) ON T7.[MOCodPri] = T1.[MOCodPri]
                            LEFT JOIN [MAEDMB] T8 WITH (NOLOCK) ON T8.[MDCodD] = T1.[MDCodD]
                            LEFT JOIN [MAEDMB1] T9 WITH (NOLOCK) ON T9.[MDCodD] = T1.[MDCodD] AND T9.[MDCodM] = T1.[MDCodM]
                            LEFT JOIN [MAEDMB2] T10 WITH (NOLOCK) ON T10.[MDCodD] = T1.[MDCodD] AND T10.[MDCodM] = T1.[MDCodM] AND T10.[MDCodB] = T1.[MDCodB]
                            LEFT JOIN [ETNIAS] T11 WITH (NOLOCK) ON T11.[MPCodEt] = T1.[MPCodEtn]
                            LEFT JOIN [NIVEDU] T12 WITH (NOLOCK) ON T12.[NivEdCo] = T1.[MPNivEdu]
                        WHERE LTRIM(RTRIM(T1.[MPCedu])) = '".$MPCedu."' AND LTRIM(RTRIM(T1.[MPTDoc])) = '".$MPTDoc."' 
                        ORDER BY T1.[MPCedu], T1.[MPTDoc]");


            $infoPac = json_encode($infoGeneral);

            if($infoPac == "[]"){
          
                throw new \Exception("Paciente no se encuentra registrado en la base de datos");
                
            }else{

                $retorno = [
                    'status' => 200,
                    'message' => 'Paciente encontrado',
                    'data' => $infoGeneral
                ];
            }
        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
            $status = 404;
        }
        return response()->json($retorno);
    }
}
