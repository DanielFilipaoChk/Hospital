<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Casos;
use Log;
use BD;
use Carbon\Carbon;
use App\Models\CASOS1;
class CasosController extends Controller
{

   public function __construct() {
      $this->CasosModel = new Casos();
   }

   public function getAllCasos(Request $request) {
    
      $CSTpDocO = $request["CSTpDocO"]; //tipo de documento origen
      $CSDocOri = $request["CSDocOri"]; // documento origen
      $CSTpDocD = $request["CSTpDocD"]; //tipo documento destino
      $CSDocDes = $request["CSDocDes"]; // documento destino
      $CSFchIni = $request["CSFchIni"]; //fecha inicial
      $CSFchFin = $request["CSFchFin"]; //fecha final

      $CSTpDocO_v = 'CSTpDocO';
      $CSDocOri_v = 'CSDocOri';
      $CSTpDocD_v = 'CSTpDocD';
      $CSDocDes_v = 'CSDocDes';
      $CSFchIni_v = 'CSFchIni';
      
     try {

      if (!$CSFchIni){
         $CSFchIni = ' ';
         $CSFchIni_v = "' '";
      }

      if (!$CSTpDocO){
         $CSTpDocO = ' ';
         $CSTpDocO_v = "' '";
      }
      if (!$CSDocOri){
         $CSDocOri = ' ';
         $CSDocOri_v = "' '";
      }
      if (!$CSTpDocD){
         $CSTpDocD = ' ';
         $CSTpDocD_v = "' '";
      }
      if (!$CSDocDes){
         $CSDocDes = ' ';
         $CSDocDes_v = "' '";
      }

        $casos = DB::Connection('sqlsrv')
        ->select("SELECT * FROM CASOS 
        WHERE ($CSFchIni_v >= convert(DATETIME,'".$CSFchIni."',102))
        and ($CSTpDocO_v = '".$CSTpDocO."')
        and ($CSDocOri_v = '".$CSDocOri."')
        and ($CSTpDocD_v = '".$CSTpDocD."')
        and ($CSDocDes_v = '".$CSDocDes."')");

        $status = 200;
      }
      catch (\exception $e) {
        $casos = [
          'message' => 'Algo salio mal'
        ];
        $status = 404;
      }

      return response()->json($casos, $status);
   }

   public function ConsultaFolios(Request $request) {

      $HISCKEY = $request->input("HISCKEY"); // NUMERO DE DOCUMENTO DEL PACIENTE
      $HISTipDoc = $request->input("HISTipDoc"); // TIPO DE DOCUMENTO DEL PACIENTE

      try {
         $ingresos = DB::Connection('sqlsrv')
         ->select("SELECT hc.HisFHorAt,hc.HISTipDoc,hc.HISCKEY, hc.HISCSEC,hc.HCEsp,hc.HISCMMED,hc.HCtvIn1,hc.HISCLPR,es.MENomE 
         FROM HCCOM1 hc WITH (NOLOCK)
         INNER JOIN MAEESP es ON hc.HCEsp = es.MECodE
         WHERE hc.HISCKEY = '".$HISCKEY."' AND hc.HISTipDoc = '".$HISTipDoc."'
         ORDER BY hc.HISCKEY, hc.HISTipDoc, hc.HisFHorAt");
         $status = 200;
      }
      catch(\exception $e) {
        $ingresos = [
          'message' => 'Algo salio mal'
        ];
        $status = 404;
      }
        return response()->json($ingresos, $status);

   }

   public function ConsultaAdmisiones(Request $request){
      $MPCedu = $request->input("MPCedu"); // NUMERO DE DOCUMENTO DEL PACIENTE
      $MPTDoc = $request->input("MPTDoc"); // TIPO DE DOCUMENTO DEL PACIENTE
      $IngCsc = $request->input("IngCsc"); //CONSECUTIVO DE INGRESO 

      try {
         $admision = DB::Connection('sqlsrv')
         ->select("SELECT IngCsc, MPTDoc, MPCedu, IngAtnAct, ClaPro, IngFecAdm
         FROM INGRESOS WITH (NOLOCK)
         WHERE MPCedu = '".$MPCedu."' AND MPTDoc = '".$MPTDoc."' AND IngCsc = '".$IngCsc."'
         ORDER BY MPCedu, MPTDoc, IngCsc");

         $status = 200;
      }
      catch(\exception $e) {
        $admision = [
          'message' => 'Algo salio mal'
        ];
        $status = 404;
      }
        return response()->json($admision, $status);
   }


   public function createCasos(Request $request) {

     $datos = $request->input("datos"); // ARRAY DE LOS DATOS EN FORMATO ARRAY 
     $CSFchReg = Carbon::now('America/Bogota')->format('Ymd');
     $EMPCOD = $request->input("EMPCOD");  // CODIGO DE LA EMPRESA 
     $MCDpto = $request->input("MCDpto");  // CODIGO DE LA SEDE   
     $CSTipMod = $request->input("CSTipMod"); //TIPO DE MODIFICACION DEL CASO   
     $CSTpDocO = $request->input("CSTpDocO"); // TIPO DOCUMENTO ORIGEN
     $CSDocOri = $request->input("CSDocOri");  //NUMERO DE DOCUMENTO ORIGEN  
     $CSTpDocD = $request->input("CSTpDocD"); //TIPO DE DOCUMENTO DESTINO   
     $CSDocDes = $request->input("CSDocDes"); //NUMERO DE DOCUMENTO DESTINO
     $CSFchIni = $request->input("CSFchIni"); //FECHA INICIAL
     $CSFchFin = $request->input("CSFchFin"); //FECHA FINAL
   
     if($CSFchIni == "") {
      $CSFchIni  = '1753-01-01';
     }
     else {
      $CSFchIni = $CSFchIni;
     }

     try {
      if(!$EMPCOD){
         throw new \Exception("La Empresa es obligatoria");   
      }

     if(!$MCDpto){
         throw new \Exception("El Departamento es obligatorio");   
      }

     if(!$CSTipMod){
         throw new \Exception("El Tipo de Modificacion es obligatorio");   
      }

     if(!$CSTpDocO){
         throw new \Exception("El Tipo de Documento Origen  es obligatorio");   
     }

     if(!$CSDocOri){
      throw new \Exception("El  Documento Origen  es obligatorio");   
     }

     if(!$CSTpDocD){
      throw new \Exception("El  Tipo de Documento Destino  es obligatorio");   
     }

     if(!$CSDocDes){
      throw new \Exception("El Documento Destino  es obligatorio");   
     }

     if($CSTpDocO == $CSTpDocD && $CSDocOri == $CSDocDes){
      throw new \Exception("Los Documentos de Origen y Destino ".$CSTpDocD."-".$CSDocDes." no pueden ser el mismo, verifique");   
     }

     if(!$CSFchFin){
      throw new \Exception("La Fecha final es obligatoria");   
     }

     $consecutivo = DB::Connection('sqlsrv')
     ->select("SELECT TOP 1 CSCons as CONSECUTIVO FROM CASOS ORDER BY CSCons DESC");

     $consecutivoAct = $consecutivo[0]->CONSECUTIVO + 1;
     
     DB::Connection('sqlsrv')
     ->insert("INSERT INTO [CASOS] ([CSCons], [EMPCOD], [MCDpto], [CSDocOri], [CSTpDocO],
      [CSDocDes], [CSTpDocD], [CSDocPpl], [CSTpDocP], [CSFchIni], [CSFchFin], [CSFchReg],
       [CSUsuReg], [CSTipMod], [CSEstAct]) 
       VALUES ('".$consecutivoAct."', '".$EMPCOD."', '".$MCDpto."', '".$CSDocOri."', '".$CSTpDocO."','".$CSDocDes."',
        '".$CSTpDocD."', '".$CSDocOri."', '".$CSTpDocO."','".date('Ymd H:i:s', strtotime($CSFchIni))."', '".date('Ymd H:i:s', strtotime($CSFchFin))."', '".date('Ymd H:i:s', strtotime($CSFchReg))."', 'CUE9F3Q8', '".$CSTipMod."', 'S' )");

         $chunkedData = array_chunk($datos, 100);
         foreach ($chunkedData as $chunk) {
            $batchData = [];

            foreach ($chunk as $dato) {
               $batchData[] = [
                     'CSCons' => $consecutivoAct,
                     'EMPCOD' => $dato['EMPCOD'],
                     'MCDpto' => $dato['MCDpto'],
                     'CSFol' => $dato['CSFol'],
                     'CSMed' => $dato['CSMed'],
                     'CSEspMed' => $dato['CSEspMed'],
                     'CsFolRel' => $dato['CsFolRel'],
                     'CsFecRel' => date('Ymd H:i:s', strtotime($dato['CsFecRel'])),
                     'CsAtnFol' => $dato['CsAtnFol'],
                     'CSCtvoIng' => $dato['CSCtvoIng'],
                     'CSClapro' => $dato['CSClapro'],
                     'CSFecAdm' => date('Ymd H:i:s', strtotime($dato['CSFecAdm'])),
                     'CSUsuDes' => $dato['CSUsuDes'],
                     'CSFecDes' => date('Ymd H:i:s', strtotime($dato['CSFecDes'])),
               ];
            }
            CASOS1::insert($batchData);
         }
         
        DB::Connection('sqlsrv')
        ->update("UPDATE [CAPBAS] SET
           [MPEstPac] = 'N'
            WHERE [MPTDoc] = '".$CSTpDocO."'
            AND [MPCedu] = '".$CSDocOri."'
         ");

      return response()->json([
         'message' => 'Nuevo registro creado correctamente!',
         'status' => 200
      ]);
   }
   catch(\exception $e) {
      return response()->json([
         'error' => $e->getMessage(),
         'status' => 400
     ]);
   }
   }

   public function ConsultaFoliosUpdate(Request $request) {

      $CSCons = $request->input("CSCons"); //CONSECUTIVO DEL CASO 
      $EMPCOD = $request->input("EMPCOD"); // EMPRESA

      try {
         $ingresos = DB::Connection('sqlsrv')
         ->select("SELECT hc.CSCons,hc.EMPCOD,hc.CSEspMed, hc.CSMed,hc.CsFecRel,hc.CsFolRel,hc.CSCtvoIng,hc.CSClapro,hc.CSFecAdm,hc.CSFol,hc.MCDpto,es.MENomE 
         FROM CASOS1 hc WITH (NOLOCK)
         INNER JOIN MAEESP es ON hc.CSEspMed = es.MECodE
         WHERE hc.CSCons = '".$CSCons."' AND hc.EMPCOD = '".$EMPCOD."'
         ORDER BY hc.CSCons, hc.EMPCOD, hc.MCDpto, hc.CSFol");
         $status = 200;
      }
      catch(\exception $e) {
        $ingresos = [
          'message' => 'Algo salio mal'
        ];
        $status = 404;
      }
        return response()->json($ingresos, $status);

   }

   public function asociarFoliosActualizar(Request $request) {

      $datos = $request->input("datos"); // DATOS ENVIADOS EN FORMATO ARRAY
      $CSFchIni = $request->input("CSFchIni"); // FECHA INICIAL
      $CSFchFin = $request->input("CSFchFin"); // FECHA FINAL
      $CSFchReg = Carbon::now('America/Bogota')->format('Ymd'); // FECHA REGISTRO
      $CSUsuReg = $request->input("CSUsuReg"); //USUARIO QUE REGISTRA
      $CSTipMod = $request->input("CSTipMod"); // TIPO DE MODIFICACION
      $CSEstAct = $request->input("CSEstAct"); // ESTADO DEL PACIENTE
      $CSCons = $request->input("CSCons"); // CONSECUTIVO DEL CASO
      $EMPCOD = $request->input("EMPCOD"); // EMPRESA
      $MCDpto = $request->input("MCDpto"); //SEDE

      try {
         foreach ($datos as $dato) {
            DB::connection('sqlsrv')
                ->update("UPDATE [CASOS1] SET
                    [CsFolRel] = ?,
                    [CSUsuDes] = ?,
                    [CSFecDes] = ?
                    WHERE [CSCons] = ?
                    AND [EMPCOD] = ?
                    AND [MCDpto] = ?
                    AND [CSFol] = ?",
                    [
                        $dato["CsFolRel"],
                        $dato["CSUsuDes"],
                        $dato["CSFecDes"],
                        $dato["CSCons"],
                        $EMPCOD,
                        $MCDpto,
                        $dato["CSFol"]
                    ]);
        }
        

         DB::Connection('sqlsrv')
         ->update("UPDATE [CASOS] SET
         [CSFchIni] = '".date('Ymd H:i:s', strtotime($CSFchIni))."',
         [CSFchFin] = '".date('Ymd H:i:s', strtotime($CSFchFin))."',
         [CSFchReg] = '".date('Ymd H:i:s', strtotime($CSFchReg))."',
         [CSUsuReg] = '".$CSUsuReg."',
         [CSTipMod] = '".$CSTipMod."',
         [CSEstAct] = '".$CSEstAct."'
         WHERE [CSCons] = '".$CSCons."'
         AND [EMPCOD] = '".$EMPCOD."'
         AND [MCDpto] = '".$MCDpto."'");

        return response()->json([
          'message' => 'El caso se actualizado correctamente!',
          'status' => 200
        ]);

      }
      catch(\exception $e) {
        $updatecaso = [
          'mesasge' => $e->getMessage()
        ];
        $status = 404;
      }
        return response()->json($updatecaso, $status);
   }
   
}
