<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use BD;
use DateTime;

class CreaPacientesController extends Controller
{
    //FUNCION TRAE LOS DATOS DE LOS CONTRATOS DEL PACIENTE
    public function infoContrato(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento

        try {
            if(!$MPCedu || !$MPTDoc){
                throw new \Exception("Algo salió mal");
            }

            $contratos = DB::Connection('sqlsrv')

            ->select("SELECT T1.[MPTDoc], T1.[MPCedu], T4.[MENOMB], T1.[MPNoCa],
                        T1.[MPACMO], T1.[MTUCod], T2.[MTUDes], T1.[MTCodP], T3.[MTNomP],
                        T1.[MPOrd], T1.[MPstatus], T1.[MPResExe], T1.[MpFicSIS], T1.[MpPunSIS],
                        T1.[MPPopla], T4.[MEAseg], T1.[MENNIT], T4.[MECApi]
                    FROM ((([MAEPAC] T1 WITH (NOLOCK)
                        LEFT JOIN [MAETPA2] T2 WITH (NOLOCK) ON T2.[MTUCod] = T1.[MTUCod])
                        LEFT JOIN [MAETPA3] T3 WITH (NOLOCK) ON T3.[MTUCod] = T1.[MTUCod] AND T3.[MTCodP] = T1.[MTCodP])
                        INNER JOIN [MAEEMP] T4 WITH (NOLOCK) ON T4.[MENNIT] = T1.[MENNIT])
                    WHERE T1.[MPCedu] =  '".$MPCedu."'  and T1.[MPTDoc] =  '".$MPTDoc."'
                    ORDER BY T1.[MPCedu], T1.[MPTDoc], T1.[MENNIT]");

            $status = 200;
        } catch(\exception $e){

            $contratos = [
                'message' => $e->getMessage()
            ];

            $status = 404;
        }
        return response()->json($contratos, $status);
    }

    public function insertAtencion(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento
        $PACtvo = $request->input('PACtvo'); //Consecutivo de atencion
        $CLAESTCOD = $request->input('CLAESTCOD'); //Codigo clase de atencion
        $PADesc = $request->input('PADesc'); //Descripcion
        $PAESTACT = $request->input('PAESTACT'); //Estado preferencia
        $PAFecReg = str_replace('-','',$request->input('PAFecReg')); //Fecha de registro
        $PAUsuReg = $request->input('PAUsuReg'); //Usuario registra preferencia
        $PAESTOBS = $request->input('PAESTOBS'); //Observacion inactivacion
        $PAFECINA = str_replace('-','',$request->input('PAFECINA')); //Fecha de inactivacion
        $PAUSUINA = $request->input('PAUSUINA'); //Usuario inactiva

        try {

            //busca si la atencion ya la tiene el paciente
            $atencion = DB::table('PREATE')
                ->where('MPCedu', $MPCedu) 
                ->where('MPTDoc', $MPTDoc)
                ->where('CLAESTCOD', $CLAESTCOD)->get();
            
            if(!$atencion->isEmpty()){
                throw new \Exception("El paciente ya tiene una atencion con el mismo codigo de clase de atencion");
            }
            
            //busca en la tabla preate si hay atencion en estado activo 
            $atenciones = DB::table('PREATE')
                ->where('MPCedu', $MPCedu) 
                ->where('MPTDoc', $MPTDoc)
                ->where('PAESTACT', 'A')->get();
            
            if(!$atenciones->isEmpty()){
                throw new \Exception("El paciente tiene una atencion preferencial activa, para crear una inactivar las atenciones activas");
            }


            $insertAtencion = DB::Connection('sqlsrv')

            ->insert("INSERT INTO [PREATE] ([MPCedu], [MPTDoc], [PACtvo], [CLAESTCOD], [PADesc], [PAESTACT],
                        [PAFecReg], [PAUsuReg], [PAESTOBS], [PAFECINA], [PAUSUINA])
                    VALUES ( '".$MPCedu."' ,  '". $MPTDoc."' ,  $PACtvo ,  '".$CLAESTCOD."' , '".$PADesc."' ,
                    '".$PAESTACT."' ,  '".$PAFecReg."' ,'".$PAUsuReg."','".$PAESTOBS."',
                    convert( DATETIME, '".$PAFECINA."', 112 ), '".$PAUSUINA."')");

            return response()->json([
                'status' => 200,
                'message' => 'Atención creada correctamente'
            ]);

        }catch(\exception $e){

            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateAtencion(Request $request){

        $MPCedu = $request->input('MPCedu'); //Numero de documento
        $MPTDoc = $request->input('MPTDoc'); //Tipo de documento
        $PAESTACT = $request->input('PAESTACT'); //Estado preferencia editado
        $CLAESTCOD = $request->input('CLAESTCOD'); //Codigo clase de atencion

        DB::connection('sqlsrv')->table('PREATE')
            ->where('MPCedu', $MPCedu)
            ->where('MPTDoc', $MPTDoc)
            ->where('CLAESTCOD', $CLAESTCOD) //Consecutivo de atencion
            ->update([
                'PAESTACT' => $PAESTACT 
            ]);
        
        return response()->json([
            'status' => 200,
            'message' => 'Atención actualizada correctamente'
        ]);


    }

    public function insertPaciente(Request $request){

        $fechAct = Carbon::now('America/Bogota')->format('Ymd');
        $MPCedu = $request->input('MPCedu'); //numero documento paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo documento paciente
        $MPNom1 = trim($request->input('MPNom1')); //primer nombre
        $MPNom2 = trim($request->input('MPNom2')); //segundo nombre
        $MPApe1 = trim($request->input('MPApe1')); //primer apellido
        $MPApe2 = trim($request->input('MPApe2')); //segunndo apellido
        $MPFchN = $request->input('MPFchN');  //fecha de nacimiento
        $fechanac = str_replace('-','',$MPFchN);
        $MPSexo = $request->input('MPSexo'); //sexo(F/M)
        $MPDire = $request->input('MPDire'); //direccion
        $MPTele = $request->input('MPTele'); //telefono
        $MDCodD = $request->input('MDCodD'); //codigo departamento
        $MDCodM = $request->input('MDCodM'); //codigo municipio
        $MDCodB = $request->input('MDCodB'); //codigo barrio
        $MPEstC = $request->input('MPEstC'); //estado del paciente
        $MPGrEs = $request->input('MPGrEs'); //codigo grupo atencion ATEEESP atencion especial
        $MOCodPri = $request->input('MOCodPri'); //codigo de la ocupacion
        $MPNHiC = $request->input('MPNHiC'); //numero historia clinica
        $MPUCod = $request->input('MPUCod'); //id usuario registra
        $MPFchA = Carbon::now('America/Bogota')->format('Ymd'); //Fecha de Ingreso D; //fecha de creacion
        $MPNOMC = $MPNom1 . ' ' . $MPNom2 . ' ' .$MPApe1 . ' ' . $MPApe2; //nombre completo
        $MPNivEdu = $request->input('MPNivEdu'); //nivel educativo
        $MpLgExp = $request->input('MpLgExp'); //lugar de expedicion
        $MpTele1 = $request->input('MpTele1'); //telefono 1
        $MpTele2 = $request->input('MpTele2'); //telefono 2
        $MpMail = $request->input('MpMail'); //correo electronico
        $MPNumHCIn = $request->input('MPNumHCIn'); //numero historia clinica institucional
        $MPNivEEs = $request->input('MPNivEEs'); //estado nivel educativo (lista quemada)
        $MPOtrAfl = $request->input('MPOtrAfl'); //otra afiliacion
        $MpCtvoAtn = $request->input('MpCtvoAtn'); //consecutivo de atencion
        $MpUsrPrf = $request->input('MpUsrPrf'); //indicador de usuario preferencial (S/N)
        $MPEmpTra = $request->input('MPEmpTra'); //empresa donde trabaja
        $MPOtTiAf = $request->input('MPOtTiAf'); //tipo de afiliacion
        $MPPacNN = $request->input('MPPacNN'); //indicador tipo numero documento
        $MPSemCSis = $request->input('MPSemCSis'); //semanas cotizadas salud
        $MPSmCtCm = $request->input('MPSmCtCm'); //semanas cotizadas comfenalco
        $MPIpsAtn = $request->input('MPIpsAtn'); //ips atencion
        $MPTipAfi = $request->input('MPTipAfi'); //tipo afiliacion comfenalco
        $MPCalAfi = $request->input('MPCalAfi'); //calidad afiliado comfenalco
        $MdCodMNac = $request->input('MdCodMNac'); //municipio nacimiento
        $MdCodDNac = $request->input('MdCodDNac'); //departamento de nacimiento
        $MPEstPac = $request->input('MPEstPac'); //estado paciente
        $MPDere = $request->input('MPDere'); //derechos comfenalco
        $MPBECar = $request->input('MPBECar'); //bebe estrella carne
        $MPBEIps = $request->input('MPBEIps'); //bebe estrella IPS
        $MPCodPai = $request->input('MPCodPai'); //codigo de pais documento
        $MPCodDisc = $request->input('MPCodDisc'); //codigo de discapacidad
        $MPCodEtn = $request->input('MPCodEtn'); //codigo de la etnia
        $MPTDocMa = $request->input('MPTDocMa'); //tipo de documento de la madre
        $MPCedMa = $request->input('MPCedMa'); //documento de la madre
        $MPCscInM = $request->input('MPCscInM'); //ingreso registra nacimiento
        $MPConNac = $request->input('MPConNac'); //consecutivo de nacimiento
        $MPGrPo = $request->input('MPGrPo'); //grupo poblacional
        $MPViveS = $request->input('MPViveS'); //indicador vives solo
        $MCARNET = $request->input('MCARNET'); //numero de carnet
        $MPSbGrPo = $request->input('MPSbGrPo'); //sub grupo poblacional
        $MPIndEtr = $request->input('MPIndEtr'); //indicador de extranjeria
        $MPPEREXPU = $request->input('MPPEREXPU'); //indicador de persona expuesta publicamente
        $MPCEDTIT = $request->input('MPCEDTIT'); //cedula titular
        $MPINDIS = $request->input('MPINDIS'); //indicador de discapacidad
        $MPINDEC = $request->input('MPINDEC'); //indicador enfermedad catastrofica
        $MPPARTIT = $request->input('MPPARTIT'); //parentesco titular
        $MPNOMTIT = $request->input('MPNOMTIT'); //nombre titular
        $MPAMTIT = $request->input('MPAMTIT'); //apellido materno titular
        $MPAPTIT = $request->input('MPAPTIT'); //apellido paterno titular
        $mpfalta = $request->input('mpfalta'); //mpfalta
        $MPTmpRes = $request->input('MPTmpRes'); //tiempo residencia
        $MPTTmRes = $request->input('MPTTmRes'); //tipo tiempo residencia
        $MPPstNuc = $request->input('MPPstNuc'); //puesto que ocupa en la familia
        $MPIndJfF = $request->input('MPIndJfF'); //indicador de jefe de nucleo
        $MPIngMen = $request->input('MPIngMen'); //ingresos mensuales del usuario
        $MPDocInt = $request->input('MPDocInt'); //documento integrador
        $MDCodBE = $request->input('MDCodBE'); //codigo barrio Ecuador
        $MpCtvoActe = $request->input('MpCtvoActe'); //consecutivo de accidente
        $MpCtvGes = $request->input('MpCtvGes'); //consecutivo de gestacion
        $MpUltCtPr = $request->input('MpUltCtPr'); //ultimo consecutivo de preferencia
        $MPCtvMed = $request->input('MPCtvMed'); //consecutivo paciente
        $MPFchDef = $request->input('MPFchDef'); //fecha de defuncion

        $MpVivEcon = $request->input('MpVivEcon'); //vive con
        $MpOcuAnte = $request->input('MpOcuAnte'); //ocupacion anterior
        $MpProTot = $request->input('MpProTot'); //protesis total dental
        $MPFOTPAC = $request->input('MPFOTPAC'); //foto paciente
        $MPCODTRA = $request->input('MPCODTRA'); //codigo tramite del proceso
        $MPCODSEGTR = $request->input('MPCODSEGTR'); //segundo codigo del paciente
        $MPCODCAT = $request->input('MPCODCAT'); //codigo categoria del proceso del aspirante
        $MPCODSEGCA = $request->input('MPCODSEGCA'); //segudo codigo de categoria del proceso del aspirante
        $MPINDDER = $request->input('MPINDDER'); //indice derecho
        $MPINDIZQ = $request->input('MPINDIZQ'); //indice izquierdo
        $MPFECACT = $request->input('MPFECACT'); //fecha y hora actualizacion
        $MPConPob = $request->input('MPConPob'); //condicion poblacion

        //-----------VARIABLES PARA CONTRATO
        $MENNIT = $request->input('MENNIT'); //Nit del contrato
        $MTUCod = $request->input('MTUCod'); //Regimen del usuario
        $MTCodP = $request->input('MTCodP'); //Codigo tipo afiliado
        $MPNoCa = 0; //Numero de carnet
        $MPCUOM = '17530101'; //Fecha vigencia carnet
        $MPACMO = 0; //Semanas cotizadas
        $MPOrd = $request->input('MPOrd'); //Prioridad de liquidacion por topes
        $MPResExe = $request->input('MPResExe'); //Responsable exedente?
        $MpFicSIS = $request->input('MpFicSIS'); //Ficha sisben
        $MpPunSIS = $request->input('MpPunSIS'); //Puntaje sisben
        $MPPopla = NULL; //''
        $UltCtvPrx = 0;//Ultimo consecutivo de preexistencia


        try{

            if(!$MPCedu){
                throw new \Exception("Documento es requerido");
            }

            if(!$MPTDoc){
                throw new \Exception("Tipo de documento es requerido");
            }

            if(!$MPNHiC){
                throw new \Exception("Número de historia clínica es requerido");
            }

            if(!$MPFchN){
                throw new \Exception("Fecha de nacimiento es requerida");
            }

            if(!$MPNom1){
                throw new \Exception("Primer nombre es requerido");
            }

            if(!$MPApe1){
                throw new \Exception("Primer apellido es requerido");
            }

            if(!$MDCodD){
                throw new \Exception("El departamento de residencia es requerido");
            }

            if(!$MDCodM){
                throw new \Exception("El municipio de residencia es requerido");
            }

            if(!$MDCodB){
                throw new \Exception("El barrio de residencia es requerido");
            }

            if(!$MdCodMNac){
                throw new \Exception("El municipio de nacimiento es requerido");
            }

            if(!$MdCodDNac){
                throw new \Exception("El departamento de nacimiento es requerido");
            }

            if(!$MENNIT){
                throw new \Exception("Contrato es requerido");
            }

            if(!$MPSexo){
                throw new \Exception("Sexo del paciente requerido");
            }

            if(!$MPCodDisc){
                throw new \Exception("La discapacidad es obligatoria");
            }

            if(!$MPCodEtn){
                throw new \Exception("La etnia es obligatoria");
            }

            if(!$MPGrPo){
                throw new \Exception("El grupo poblacional es obligatorio");
            }

            if(!$MPEstC){
                throw new \Exception("El estado civil del paciente es obligatorio");
            }

            if(!$MENNIT){
                throw new \Exception("El contrato es obligatorio");
            }

            if(!$MTUCod){
                throw new \Exception("El régimen es obligatorio");
            }

            if(!$MTCodP){
                throw new \Exception("El tipo de afiliado es obligatorio");
            }

            if(!$MPOrd){
                throw new \Exception("Campo ORD es obligatorio");
            }

            $vigCnc = DB::table('MAECTOS')
                ->select('MENNIT', 'MeCfcha1', 'CtoFchIni', 'MeCnsCnt')
                ->where('MENNIT', $MENNIT)
                ->where('CtoFchIni','<=', $fechAct)
                ->where('MeCfcha1','>=', $fechAct)->first();


            $vigencia = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig','<=', $fechAct)
                ->orderByDesc('MEPPVig')->first();

            if ($vigencia == null){
                throw new \Exception("No hay vigencia para el contrato " .$MENNIT);
            }


            $paciente = DB::table('CAPBAS')
                ->select('MPTDoc', 'MPCedu', 'MPNOMC')
                ->where('MPCedu',''.$MPCedu.'')
                ->where('MPTDoc',''.$MPTDoc.'')->get();

            if(!$paciente->isEmpty()){
                throw new \Exception("El paciente ya se encuentra registrado");
            }

            $codPEtn = DB::table('ETNIAS1')
                ->select('MPCNEtn')
                ->where('MPCodEt', $MPCodEtn)->first();

            if($codPEtn != null){
                $MPCPEtn = $codPEtn->MPCNEtn; //codigo pais etnia
            }else{
                $MPCPEtn = " ";
            }


            $insertPaciente = DB::table('CAPBAS')->insert([
                'MPCedu' => $MPCedu,
                'MPTDoc' => $MPTDoc,
                'MPNom1' => $MPNom1,
                'MPNom2' => $MPNom2,
                'MPApe1' => $MPApe1,
                'MPApe2' => $MPApe2,
                'MPFchN' => $fechanac,
                'MPSexo' => $MPSexo,
                'MPDire' => $MPDire,
                'MPTele' => $MPTele,
                'MDCodD' => $MDCodD,
                'MDCodM' => $MDCodM,
                'MDCodB' => $MDCodB,
                'MPEstC' => $MPEstC,
                'MPGrEs' => $MPGrEs,
                'MOCodPri' => $MOCodPri,
                'MPNHiC' => $MPNHiC,
                'MPUCod' => $MPUCod,
                'MPFchA' => $MPFchA,
                'MPNOMC' => $MPNOMC,
                'MPNivEdu' => $MPNivEdu,
                'MpLgExp' => $MpLgExp,
                'MpTele1' => $MpTele1,
                'MpTele2' => $MpTele2,
                'MpMail' => $MpMail,
                'MPNumHCIn' => $MPNumHCIn,
                'MPNivEEs' => $MPNivEEs,
                'MPOtrAfl' => $MPOtrAfl,
                'MpUsrPrf' => 1,
                'MPEmpTra' => $MPEmpTra,
                'MPOtTiAf' => $MPOtTiAf,
                'MPPacNN' => $MPPacNN,
                'MPSemCSis' => $MPSemCSis,
                'MPSmCtCm' => $MPSmCtCm,
                'MPIpsAtn' => $MPIpsAtn,
                'MPTipAfi' => $MPTipAfi,
                'MPCalAfi' => $MPCalAfi,
                'MdCodMNac' => $MdCodMNac,
                'MdCodDNac' => $MdCodDNac,
                'MPEstPac' => $MPEstPac,
                'MPDere' => $MPDere,
                'MPBECar' => $MPBECar,
                'MPBEIps' => $MPBEIps,
                'MPCodPai' => $MPCodPai,
                'MPCodDisc' => $MPCodDisc,
                'MPCodEtn' => $MPCodEtn,
                'MPTDocMa' => $MPTDocMa,
                'MPCedMa' => $MPCedMa,
                'MPCscInM' => $MPCscInM,
                'MPConNac' => $MPConNac,
                'MPGrPo' => $MPGrPo,
                'MPViveS' => $MPViveS,
                'MCARNET' => $MCARNET,
                'MPSbGrPo' => $MPSbGrPo,
                'MPIndEtr' => $MPIndEtr,
                'MPPEREXPU' => $MPPEREXPU,
                'MPCEDTIT' => $MPCEDTIT,
                'MPINDIS' => 0,
                'MPINDEC' => $MPINDEC,
                'MPPARTIT' => $MPPARTIT,
                'MPNOMTIT' => $MPNOMTIT,
                'MPAMTIT' => $MPAMTIT,
                'MPAPTIT' => $MPAPTIT,
                'mpfalta' => $mpfalta,
                'MPTmpRes' => $MPTmpRes,
                'MPTTmRes' => $MPTTmRes,
                'MPPstNuc' => $MPPstNuc,
                'MPIndJfF' => $MPIndJfF,
                'MPIngMen' => $MPIngMen,
                'MPDocInt' => $MPDocInt,
                'MDCodBE' => $MDCodBE,
                'MpCtvoActe' => $MpCtvoActe,
                'MpCtvGes' => $MpCtvGes,
                'MpUltCtPr' => $MpUltCtPr,
                'MPCtvMed' => $MPCtvMed,
                'MPFchDef' => '17530101',
                'MPCPEtn' => $MPCPEtn,
                'MpVivEcon' => $MpVivEcon,
                'MpOcuAnte' => $MpOcuAnte,
                'MpProTot' => $MpProTot,
                'MPFOTPAC' => DB::raw("CONVERT(varbinary(1), '".$MPFOTPAC."')"),
                'MPCODTRA' => $MPCODTRA,
                'MPCODSEGTR' => $MPCODSEGTR,
                'MPCODCAT' => $MPCODCAT,
                'MPCODSEGCA' => $MPCODSEGCA,
                'MPINDDER' => DB::raw("CONVERT(varbinary(1), '".$MPINDDER."')"),
                'MPINDIZQ' => DB::raw("CONVERT(varbinary(1), '".$MPINDIZQ."')"),
                'MPFECACT' => '17530101',
                'MPConPob' => $MPConPob,
            ]);

            //---------------------CONTRATO

           DB::Connection('sqlsrv')

            ->insert("INSERT INTO [MAEPAC] ([MPCedu], [MPTDoc], [MENNIT], [MTUCod], [MTCodP],
                        [MPNoCa], [MPCUOM], [MPstatus], [MPACMO], [MPOrd], [MPResExe], [MpFicSIS],
                        [MpPunSIS], [MPPopla], [UltCtvPrx])
                    VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$MENNIT." ' ,  '".$MTUCod."' ,  '".$MTCodP."' ,
                        '".$MPNoCa."' ,  '".$MPCUOM."' ,
                        'A' ,  $MPACMO ,  $MPOrd ,  '".$MPResExe."' ,  $MpFicSIS ,  $MpPunSIS ,
                        '".$MPPopla."' , convert(int, $UltCtvPrx))");

            $retorno = [
                'status' => 200,
                'message' => 'Paciente creado correctamente',
                'cn' => $insertPaciente
            ];

        }catch(\exception $e){

            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];
        }

        return response()->json($retorno);

    }

    public function actualizaPaciente(Request $request){

        $MPCedu = $request->input('MPCedu'); //numero documento paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo documento paciente
        $MPNom1 = trim($request->input('MPNom1')); //primer nombre
        $MPNom2 = trim($request->input('MPNom2')); //segundo nombre
        $MPApe1 = trim($request->input('MPApe1')); //primer apellido
        $MPApe2 = trim($request->input('MPApe2')); //segunndo apellido
        $MPFchN = str_replace('-','',$request->input('MPFchN')); //fecha de nacimiento
        $MPOtTiAf = $request->input('MPOtTiAf'); //tipo de afiliacion
        $MPSexo = $request->input('MPSexo'); //sexo(F/M)
        $MPDire = $request->input('MPDire'); //direccion
        $MPTele = $request->input('MPTele'); //telefono
        $MDCodD = $request->input('MDCodD'); //codigo departamento
        $MDCodM = $request->input('MDCodM'); //codigo municipio
        $MDCodB = $request->input('MDCodB'); //codigo barrio
        $MPEstC = $request->input('MPEstC'); //estado del paciente
        $MPGrEs = $request->input('MPGrEs'); //codigo grupo atencion ATEEESP atencion especial
        $MOCodPri = $request->input('MOCodPri'); //codigo de la ocupacion
        $MPNHiC = $request->input('MPNHiC'); //numero historia clinica
        $MPUCod = $request->input('MPUCod'); //id usuario registra
        $MPNOMC = $request->input('MPNOMC'); //nombre completo
        $MPNivEdu = $request->input('MPNivEdu'); //nivel educativo
        $MpLgExp = $request->input('MpLgExp'); //lugar de expedicion
        $MpTele1 = $request->input('MpTele1'); //telefono 1
        $MpTele2 = $request->input('MpTele2'); //telefono 2
        $MpMail = $request->input('MpMail'); //correo electronico
        $MPNivEEs = $request->input('MPNivEEs'); //estado nivel educativo (lista quemada)
        $MPOtrAfl = $request->input('MPOtrAfl'); //otra afiliacion
        $MpUsrPrf = $request->input('MpUsrPrf'); //indicador de usuario preferencial (S/N)
        $MPEmpTra = $request->input('MPEmpTra'); //empresa donde trabaja
        $MPIpsAtn = $request->input('MPIpsAtn'); //ips atencion
        $MPCodDisc = $request->input('MPCodDisc'); //codigo de discapacidad
        $MPCodEtn = $request->input('MPCodEtn'); //codigo de la etnia
        $MPGrPo = $request->input('MPGrPo'); //grupo poblacional
        $MPViveS = $request->input('MPViveS'); //indicador vives solo
        $MCARNET = $request->input('MCARNET'); //numero de carnet
        $MPSbGrPo = $request->input('MPSbGrPo'); //sub grupo poblacional
        $MPIndEtr = $request->input('MPIndEtr'); //indicador de extranjeria
        $MPINDIS = $request->input('MPINDIS'); //indicador de discapacidad
        $MPINDEC = $request->input('MPINDEC'); //indicador enfermedad catastrofica
        $MdCodMNac = $request->input('MdCodMNac'); //municipio nacimiento
        $MdCodDNac = $request->input('MdCodDNac'); //departamento de nacimiento

        try{

            if(!$MPCedu){
                throw new \Exception("Documento es requerido");
            }

            if(!$MPTDoc){
                throw new \Exception("Tipo de documento es requerido");
            }

            if(!$MPNHiC){
                throw new \Exception("Número de historia clínica es requerido");
            }


           /*  if(!$MPNom1){
                throw new \Exception("Primer nombre es requerido");
            }

            if(!$MPApe1){
                throw new \Exception("Primer apellido es requerido");
            }

            if(!$MDCodD){
                throw new \Exception("El departamento de residencia es requerido");
            }

            if(!$MDCodM){
                throw new \Exception("El municipio de residencia es requerido");
            }

            if(!$MPSexo){
                throw new \Exception("Sexo del paciente requerido");
            }

            if(!$MPCodDisc){
                throw new \Exception("La discapacidad es obligatoria");
            }

            if(!$MPCodEtn){
                throw new \Exception("La etnia es obligatoria");
            }

            if(!$MPNivEdu){
                throw new \Exception("El nivel educativo es obligatorio");
            }

            if(!$MPNivEEs){
                throw new \Exception("Estado del nivel educativo es obligatorio");
            }

            if(!$MPGrPo){
                throw new \Exception("El grupo poblacional es obligatorio");
            }

            if(!$MPEstC){
                throw new \Exception("El estado civil del paciente es obligatorio");
            } */

           DB::table('CAPBAS')
           ->where('MPCedu', $MPCedu)
           ->where('MPTDoc', $MPTDoc)
            ->update([
                'MPNom1' =>  $MPNom1,
                'MPNom2' =>  $MPNom2,
                'MPApe1' =>  $MPApe1,
                'MPApe2' =>  $MPApe2,
                'MPSexo' =>  $MPSexo,
                'MPFchN' =>  $MPFchN,
                'MPOtTiAf' =>  $MPOtTiAf,
                'MPDire' =>  $MPDire,
                'MPTele' =>  $MPTele,
                'MDCodD' =>  $MDCodD,
                'MDCodM' =>  $MDCodM,
                'MPEstC' =>  $MPEstC,
                'MPGrEs' =>  $MPGrEs,
                'MOCodPri' =>  $MOCodPri,
                'MPNHiC' =>  $MPNHiC,
                'MPUCod' =>  $MPUCod,
                'MPNOMC' =>  $MPNom1.' '.$MPNom2.' '.$MPApe1.' '.$MPApe2,
                'MPNivEdu' =>  $MPNivEdu,
                'MpLgExp' =>  $MpLgExp,
                'MpTele1' =>  $MpTele1,
                'MpTele2' =>  $MpTele2,
                'MpMail' =>  $MpMail,
                'MPNivEEs' =>  $MPNivEEs,
                'MPOtrAfl' =>  $MPOtrAfl,
                'MpUsrPrf' =>  $MpUsrPrf,
                'MPEmpTra' =>  $MPEmpTra,
                'MPIpsAtn' =>  $MPIpsAtn,
                'MPCodDisc' =>  $MPCodDisc,
                'MPCodEtn' =>  $MPCodEtn,
                'MPGrPo' =>  $MPGrPo,
                'MPViveS' =>  $MPViveS,
                'MCARNET' =>  $MCARNET,
                'MPSbGrPo' =>  $MPSbGrPo,
                'MPIndEtr' =>  $MPIndEtr,
                'MPINDIS' =>  0,
                'MPINDEC' =>  $MPINDEC,
                'MDCodB' =>$MDCodB,
                'MdCodMNac' =>  $MdCodMNac,
                'MdCodDNac' =>  $MdCodDNac
            ]);

        $retorno=[
            'status' => 200,
            'message' => 'Paciente actualizado correctamente'
        ];

        }catch(\exception $e){
            $retorno = [
                'status' => 400,
                'message' => $e->getMessage()
            ];

        }
        return response()->json($retorno);
    }

    public function diffEdad(Request $request){
        $fechAct = new DateTime(Carbon::now('America/Bogota')->format('d-m-Y'));

        $fechaN = new DateTime($request->input('fechaN')); //Fecha nacimiento

        try{
            $dif =  $fechAct->diff($fechaN);
            $retorno = [
                'status' => 200,
                'data' => [
                    'años' => $dif->format('%Y'),
                    'meses' => $dif->format('%M'),
                    'dias' => $dif->format('%D')
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

    //Funcion inactivar/activar paciente
    public function estadoPaciente(Request $request){
        $MPCedu = $request->input('MPCedu'); //numero documento paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo documento paciente
        $MPEstPac = $request->input('MPEstPac'); //estado del paciente S: activo N: inactivo

        $DES = "";
        switch ($MPEstPac) {
            case 'N':
                $DES = 'INACTIVO';
                break;
            
            case 'S':
                $DES = 'ACTIVO';
                break;
        }

        try{
            $estadoPaciente = DB::table('CAPBAS')
                ->select('MPCedu', 'MPTDoc', 'MPEstPac')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)->first();
                
            if($estadoPaciente->MPEstPac == $MPEstPac){
                throw new \Exception("El paciente ya se encuentra ". $DES. " en el sistema. Por favor verifique.");
            }
            //VERIFIACION DE ADMISION ABIERTA
            $admAbierta = DB::table('TMPFAC')
                ->select('TmCtvIng', 'ClaPro', 'TFTDoc', 'TFCedu', 'TFHorI', 
                    'TFFchI', 'TFcCodPab', 'TFcCodCam')
                ->where('TFCedu', $MPCedu)
                ->where('TFTDoc', $MPTDoc)->first();
            
            $ingreso = DB::table('INGRESOS')
                ->select('MPCedu', 'MPTDoc', 'IngCsc', 'ClaPro', 'IngFecEgr', 'IngFchAnu')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('IngFecEgr', '1753-01-01')->first();

            if($admAbierta !== null ){
                throw new \Exception('Paciente con admision sin salida en el ingreso ' . $admAbierta->TmCtvIng.' no se puede seguir con el proceso');
            }

            if($ingreso !== null ){
                throw new \Exception('El paciente ' .$MPCedu . ' tiene una admisión sin salida en el ingreso ' .$ingreso->IngCsc. '. No se puede continuar con el proceso');  
            }  

            //Cambio de estado
            DB::table('CAPBAS')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->update([
                    'MPEstPac' => $MPEstPac
                ]);

            return response()->json([
                'status' => 200,
                'message' => 'Estado del paciente actualizado correctamente'
            ]);

        } catch(\Exception $e){
            return response()-> json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    //Funcion que calcula la fecha de nacimiento
    public function calculaFechaN(Request $request){

        $fechaActual = Carbon::now();
        $tiempo = $request->input('tiempo'); //Unidades de tiempo ej 1,2,3,...
        $unidad = $request->input('unidad'); //Unidad de tiempo  2: meses, 1: años, 3: días, 4: horas

        switch ($unidad) {
            case 2:
                $fechaNacimiento = $fechaActual->subMonths($tiempo);
                break;
            case 1:
                $fechaNacimiento = $fechaActual->subYears($tiempo);
                break;
            case 3:
                $fechaNacimiento = $fechaActual->subDays($tiempo);
                break;
            case 4:
                $fechaNacimiento = $fechaActual->subHours($tiempo);
                break;
            default:
                return "Unidad no válida";
        }
        
        return response()->json([
            'data' => [
                'fechaNacimiento' => $fechaNacimiento->format('Y-m-d')
            ]
        ], 200);
    }

    //Funcion para calcular la cedula del paciente 
    public function calculaDocumento(Request $request){

        $EMPCOD = $request->input('EMPCOD'); //codigo de la empresa
        $MCDpto = $request->input('MCDpto'); //codigo de la sede

        try{

            //Validacion del documento 
            $documento = DB::table('DOCUCON')
                ->select('DOCCOD', 'EMPCOD', 'DocDsc', 'DocCscEmp', 'DocCscL')
                ->where('EMPCOD', $EMPCOD)
                ->where('DOCCOD', 'NNS')->first();

            if($documento == null){
                throw new \Exception("No se encuentra el documento NNS");
            }

            $estado = DB::table('DOCUCON1')
                ->select('DocNumEs', 'DOCCOD', 'EMPCOD', 'DocNumBq', 'DocNumIn', 'DocNumFi',
                        'DocCon', 'DocNumAc', 'DocDscPrf', 'MCDpto')
                ->where('EMPCOD', $EMPCOD)
                ->where('DOCCOD', 'NNS')
                ->where('MCDpto', $MCDpto)->first();

            //Valida estado 
            if($estado->DocNumEs != 'A'){
                throw new \Exception("El documento NNS no se encuentra activo");
            }

            //Informacion para generacion del documento 
            $sede = DB::table('MAESED')
                ->select('MCDpto', 'EMPCOD', 'MCDptCod', 'MCCiuCod')
                ->where('EMPCOD', $EMPCOD)
                ->where('MCDpto', $MCDpto)->first();

            if($sede == null){
                throw new \Exception("No se encuentra la sede");
            }

            //Actualizacion del consecutivo
            DB::table('DOCUCON1')
                ->where('EMPCOD', $EMPCOD)
                ->where('DOCCOD', 'NNS')
                ->where('MCDpto', $MCDpto)
                ->update([
                    'DocNumAc' => $estado->DocNumAc + 1
                ]);

            $documento = $sede->MCDptCod.sprintf('%03d', $sede->MCCiuCod).'NN'.sprintf('%04d', $estado->DocNumAc);

            return response()->json([
                'data' => [
                    'documento' => $documento
                ]
            ], 200);


        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    //Fucion para ingresar paciente por NN
    public function insertPacienteNN(Request $request){
        
        $MPCedu = $request->input('MPCedu'); //numero documento paciente
        $MPTDoc = $request->input('MPTDoc'); //tipo documento paciente
        $MPFchN = str_replace('-','',$request->input('MPFchN')); //fecha de nacimiento
        $MPSexo = $request->input('MPSexo'); //sexo(F/M)
        $MPCodPaic = $request->input('MPCodPaic'); //codigo pais si es extranjero sino manda ''
        $MPConPob = $request->input('MPConPob'); //condicion poblacion

        //INFO PARA CONTRATO
        $fechAct = Carbon::now('America/Bogota')->format('Ymd');

        $MENNIT = $request->input('MENNIT'); //Nit del contrato
        $MTUCod = $request->input('MTUCod'); //Regimen del usuario
        $MTCodP = $request->input('MTCodP'); //Codigo tipo afiliado
        $MPNoCa = 0; //Numero de carnet
        $MPCUOM = '17530101'; //Fecha vigencia carnet
        $MPACMO = 0; //Semanas cotizadas
        $MPOrd = $request->input('MPOrd'); //Prioridad de liquidacion por topes
        $MPResExe = $request->input('MPResExe'); //Responsable exedente?
        $MpFicSIS = $request->input('MpFicSIS'); //Fecha sisben
        $MpPunSIS = $request->input('MpPunSIS'); //Puntaje sisben
        $MPPopla = NULL; //''
        $UltCtvPrx = 0;//Ultimo consecutivo de preexistencia

        $nombre = '';
        $apellido = 'SIN IDENTIFICACION';

        if($MPTDoc == 'AS'){
            $nombre = 'ADULTO';
        }elseif($MPTDoc == 'MS'){
            $nombre = 'MENOR';
        }

        try{

            if(!$MPCedu){
                throw new \Exception("Documento es requerido");
            }

            if(!$MPTDoc){
                throw new \Exception("Tipo de documento es requerido");
            }

            if(!$MPFchN){
                throw new \Exception("Fecha de nacimiento es requerida");
            }

            if(!$MPSexo){
                throw new \Exception("Sexo del paciente requerido");
            }

            if(!$MPCodPaic){
                throw new \Exception("El codigo del pais es requerido");
            }

            if(!$MPConPob){
                throw new \Exception("La condicion poblacional es requerida");
            }

            if(!$MENNIT){
                throw new \Exception("El contrato es obligatorio");   
            }
    
            if(!$MTUCod){
                throw new \Exception("El régimen es obligatorio");   
            }
    
            if(!$MTCodP){
                throw new \Exception("El tipo de afiliado es obligatorio");   
            }
    
            if(!$MPOrd){
                throw new \Exception("Campo ORD es obligatorio");   
            }
    
            if(!$MpFicSIS){
                $MpFicSIS = 0;
            }
    
            if(!$MpPunSIS){
                $MpPunSIS = 0;
            }

            //--------VALIDACIONES DEL CONTRATO

            $vigCnc = DB::table('MAECTOS')
                ->select('MENNIT', 'MeCfcha1', 'CtoFchIni', 'MeCnsCnt')
                ->where('MENNIT', $MENNIT)
                ->where('CtoFchIni','<=', $fechAct)
                ->where('MeCfcha1','>=', $fechAct)->first();
            

            $vigencia = DB::table('MAEEMP31')
                ->select('MEPPVig', 'MENNIT', 'PTCodi', 'MTUCo1')
                ->where('MENNIT', $MENNIT)
                ->where('MEPPVig','<=', $fechAct)
                ->orderByDesc('MEPPVig')->first();
        
            if ($vigencia == null){
                throw new \Exception("No hay vigencia para el contrato " .$MENNIT);
            }


            $validaCon = DB::table('MAEPAC')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('MENNIT', $MENNIT)->get();
            
            if(!$validaCon->isEmpty()){
                throw new \Exception('El contrato ya está asignado al paciente '. $MPCedu);  
            }

            $validaCon = DB::table('MAEPAC')
                ->where('MPCedu', $MPCedu)
                ->where('MPTDoc', $MPTDoc)
                ->where('MPOrd', $MPOrd)->first();

            if($validaCon != null){
                throw new \Exception('Ya existe un contrato o convenio con ese Orden para el paciente');
            }

            //CREACION DEL PACIENTE 

            DB::connection('sqlsrv')
                ->insert("
                INSERT INTO [CAPBAS] ([MPCedu], [MPTDoc], [MPFchN], [MPSexo], [MPEstC], [MPNHiC],
                [MPPacNN], [MPEstPac], [MPCodPai], [MPConPob], [MPIndEtr], [MPNom1], [MPNom2], [MPApe1],
                [MPApe2], [MPDire], [MPTele], [MDCodD], [MDCodM], [MDCodB], [MPGrEs], [MOCodPri], [MPUCod],
                [MPFchA], [MPNOMC], [mpfalta], [MPTmpRes], [MPTTmRes], [MPPstNuc], [MPIndJfF], [MPNivEdu],
                [MPIngMen], [MPDocInt], [MpLgExp], [MpTele1], [MpTele2], [MpMail], [MDCodBE], [MPNumHCIn],
                [MPNivEEs], [MPOtrAfl], [MpCtvoActe], [MpCtvoAtn], [MpCtvGes], [MpUltCtPr], [MpUsrPrf],
                [MPEmpTra], [MPOtTiAf], [MPCtvMed], [MPSemCSis], [MPSmCtCm], [MPIpsAtn], [MPTipAfi], 
                [MPCalAfi], [MdCodMNac], [MdCodDNac], [MPDere], [MPBECar], [MPBEIps], [MPCodDisc],
                [MPCodEtn], [MPFchDef], [MPCPEtn], [MPTDocMa], [MPCedMa],[MPCscInM], [MPConNac], [MPGrPo],
                [MPViveS], [MpVivEcon], [MpOcuAnte], [MpProTot], [MPFOTPAC], [MPCODTRA], [MPCODSEGTR], 
                [MPCODCAT], [MPCODSEGCA], [MPINDDER], [MPINDIZQ], [MCARNET], [MPFECACT], [MPSbGrPo], 
                [MPPEREXPU], [MPCEDTIT], [MPINDIS], [MPINDEC], [MPPARTIT], [MPNOMTIT], [MPAMTIT], 
                [MPAPTIT])
                VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$MPFchN."' ,  '".$MPSexo."' ,  'S' ,
                '".$MPCedu."' ,  'S' ,  'S' ,  '".$MPCodPaic."' ,  '".$MPConPob."' ,  'N' ,

                '".$nombre."', '', '".$apellido."', '', '', '', '', convert(int, 0), convert(int, 0), '',
                '', '', convert( DATETIME, '17530101', 112 ), '".$nombre.' '. $apellido."', convert(int, 0), 
                convert(int, 0), '', convert(int, 0), '', convert(int, 0), convert(int, 0), 
                convert(int, 0), '', '', '', '', convert(int, 0), '', '', '', convert(int, 0), 
                convert(int, 1), convert(int, 0), convert(int, 0), '', '', '', convert(int, 0), 
                convert(int, 0), convert(int, 0), '', convert(int, 0), convert(int, 0), convert(int, 0),
                '', '', '', convert(int, 0), '', '', convert( DATETIME, '17530101', 112 ), '', '', '',
                convert(int, 0), convert(int, 0), '', convert(int, 0), '', convert(int, 0), '', 
                CONVERT(varbinary(1), ''), convert(int, 0), convert(int, 0), '', '',
                CONVERT(varbinary(1), ''), CONVERT(varbinary(1), ''), '', 
                convert( DATETIME, '17530101', 112 ), '', '', '', '', '', '', '', '', '')
                ");

            //Inaert del contrato del paciente

            DB::Connection('sqlsrv')

            ->insert("INSERT INTO [MAEPAC] ([MPCedu], [MPTDoc], [MENNIT], [MTUCod], [MTCodP], 
                        [MPNoCa], [MPCUOM], [MPstatus], [MPACMO], [MPOrd], [MPResExe], [MpFicSIS], 
                        [MpPunSIS], [MPPopla], [UltCtvPrx])
                    VALUES ( '".$MPCedu."' ,  '".$MPTDoc."' ,  '".$MENNIT." ' ,  '".$MTUCod."' ,  '".$MTCodP."' ,  
                        '".$MPNoCa."' ,  '".$MPCUOM."' ,  
                        'A' ,  $MPACMO ,  $MPOrd ,  '".$MPResExe."' ,  $MpFicSIS ,  $MpPunSIS ,
                        '".$MPPopla."' , convert(int, $UltCtvPrx))");


            return response()->json([
                'status' => 200,
                'message' => 'Paciente creado correctamente'
            ]);




        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    
    }


}
