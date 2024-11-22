<?php

namespace App\Http\Controllers\ApiAdmisiones;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class RenderFormatosController extends Controller
{
    //funcion para render de formato de ingreso 
    public function formatoIngreso($organizacion, $sede, $paciente, $infoGral, $eps){
        $pdfContent = "";
        $pdfContent .= <<<EOT
                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                <div style="width: 25%; float: left;">

                                </div>
                                <div style="width: 40%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$organizacion->EmpRazSoc</p>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$organizacion->EmpNit - $organizacion->EmpDVer</p>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">DOCUMENTO DE ADMISION</p><br>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px; text-decoration: underline;">DATOS DEL PACIENTE</p>
    
                                </div>
                                <div style="width: 15%; float: left;">
                                    <p style="font-weight:bold; font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 14px">IDENTIFICACION</p>
                                    <p style=" font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 14px">$paciente->TFTDoc No.  $paciente->TFCedu </p>
                                </div>
                            </div><br><br><br><br><br>
                               
                            EOT;
            
        $pdfContent .= '<table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                            <tr>
                                <td style="width: 20%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>FECHA INGRESO</strong> <br>
                                    '.Carbon::parse($paciente->TFFchI)->format('Y-m-d').'</td>
                                <td style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>HORA</strong><br>
                                    '.$paciente->TFHorI.'</td>
                                <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>HABITACIÓN</strong><br>
                                    '.$paciente->TFCoCamI.'</td>
                                <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>SERVICIO INGRESO</strong><br>
                                    '.$paciente->ClaproIN.'</td>
                                <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>SEDE INGRESO</strong><br>
                                    '.$sede->MCDnom.'</td>
                                <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>CONSECUTIVO DE INGRESO</strong><br>
                                    '.$paciente->TmCtvIng.'</td>
                            </tr>
                            </table>
                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 35%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>NOMBRES</strong> <br>
                                        '.trim($paciente->MPNom1) .' '. trim($paciente->MPNom2).'</td>
                                    <td style="width: 35%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>APELLIDOS</strong><br>
                                        '.trim($paciente->MPApe1) .' '. trim($paciente->MPApe2).'</td>
                                    <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>FECHA NACIMIENTO</strong><br>
                                        '.Carbon::parse($paciente->TFMPFchN)->format('Y-d-m').'</td>
                                    <td style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>E. CIVIL</strong><br>
                                        '.$paciente->MPEstCD.'</td>
                                    <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>EDAD</strong><br>
                                        '.$paciente->edad.'</td>
                                    <td style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>SEXO</strong><br>
                                        '.$paciente->TFMPSexo.'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>DIRECCION DE RESIDENCIA</strong> <br>
                                        '.trim($paciente->TFDire) .'</td>
                                    <td style="width: 25%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TELEFONO</strong><br>
                                        '.trim($paciente->TFTele) .'</td>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>EMPRESA DONDE TRABAJA</strong><br>
                                        '.$infoGral->MPEmpTra.'</td>
                                    <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TEL. TRABAJO</strong><br>
                                        '.$infoGral->MpTele1.'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>EMAIL</strong> <br>
                                        '.trim($infoGral->MpMail) .'</td>
                                    <td style="width: 33.3%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>DISCAPACIDAD</strong><br>
                                        '.trim($infoGral->discapacidad) .'</td>
                                    <td style="width: 36.6%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>ATENCION ESPECIAL</strong><br>
                                        '.trim($infoGral->atnEspD).'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>GRUPO CULTURAL</strong> <br>
                                        '.trim($infoGral->grupoCultural) .'</td>
                                    <td style="width: 70%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>GRUPO POBLACIONAL</strong><br>
                                        '.trim($infoGral->grupoPoblacional) .'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 60%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align: center;"><strong>OCUPACION</strong> <br>
                                        '.trim($infoGral->MODesc) .'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>NIVEL EDUCATIVO</strong><br>
                                        '.trim($infoGral->nivEducativo) .'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>ESTADO FINALIZACION</strong><br>
                                        '.trim($infoGral->MPNivEEsD) .'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 80%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: left;"><strong>DX. INGRESO</strong><br>
                                        '.trim($paciente->TFDi1I).' '.trim($paciente->diagnostico) .'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>SEDE PACIENTE</strong><br>
                                        </td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 80%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: left;"><strong>OBSERVACIONES ADMISIÓN</strong><br>
                                        '.trim($paciente->observaciones) .'</td>
                                </tr>

                            </table><br>

                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                <div style="width: 50%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">ACOMPAÑANTE</p>
                                </div>
                                <div style="width: 50%; float: left;">
                                    <p style="font-weight:bold; font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 14px">RESPONSABLE EXCEDENTES</p>
                                </div>
                            </div><br>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 18%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>NOMBRE</strong><br>
                                        '.trim($paciente->TFNoAc) .'</td>
                                    <td style="width: 14%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TELEFONO</strong><br>
                                        '.trim($paciente->TFTeAc) .'</td>
                                    <td style="width: 18%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>PARENTESCO</strong><br>
                                        '.trim($paciente->parentescoAc) .'</td>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000; border-left: 3px solid #000;  border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>NOMBRE</strong><br>
                                        '.trim($paciente->TFNoRe) .' '. trim($paciente->TFNoRe2).' </td>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000; border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>APELLIDOS</strong><br>
                                        '. trim($paciente->TFApeRes) .' '.trim($paciente->TFApeRes2) .'</td>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000; border-right: 3px solid #000;  border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>C. CIUDADANIA</strong><br>
                                        '.trim($paciente->TFDocRep) .'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000; border-left: 3px solid #000;  border-bottom: 3px solid #000; border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>DIRECCION</strong><br>
                                        '.trim($paciente->TFDirRep) .'</td>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000;  border-bottom: 3px solid #000; border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>DEPARTAMENTO</strong><br>
                                        '.trim($infoGral->dptoResponsable) .'</td>
                                    <td style="width: 16.6%; padding: 5px; border: 1px solid #000;  border-bottom: 3px solid #000; border-top: 3px solid #000; font-size: 10px; text-align: center;"><strong>MUNICIPIO</strong><br>
                                        '.trim($infoGral->municipioResponsable) .'</td>
                                    <td style="width: 14%; padding: 5px; border: 1px solid #000;  border-bottom: 3px solid #000; font-size: 10px; text-align: center;"><strong>TEL. RESIDENCIA</strong><br>
                                        '.trim($paciente->TFTeRe) .'  </td>
                                    <td style="width: 18%; padding: 5px; border: 1px solid #000;  border-bottom: 3px solid #000; font-size: 10px; text-align: center;"><strong>EMPRESA</strong><br>
                                        '. trim($paciente->TFEmTrRe) .'</td>
                                    <td style="width: 18%; padding: 5px; border: 1px solid #000; border-right: 3px solid #000;   border-bottom: 3px solid #000; font-size: 10px; text-align: center;"><strong>TEL. TRABAJO</strong><br>
                                        '.trim($paciente->TFTeTrRe) .'</td>
                                </tr>

                            </table><br>

                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                <div>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">OTROS PLANES DE SALUD</p>
                                </div>
                            </div>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 70%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: left;"><strong>EMPRESA (S)</strong><br>
                                        '.trim($infoGral->MPOtrAfl).'</td>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TIPO DE AFILIACION</strong><br>
                                    '.trim($infoGral->MPOtTiAf).'</td>
                                </tr>

                            </table><br>
                                                                                    
                            
                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                <div>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">DATOS DEL RESPONSABLE DE LA CUENTA</p>
                                </div>
                            </div>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: left;"><strong>EMPRESA/PARTICULAR</strong><br>
                                        </td>
                                    <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>NIT</strong><br>
                                        '.trim($eps->MEcntr).'</td>
                                    <td style="width: 30%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>NOMBRE</strong><br>
                                        '.trim($eps->MENOMB).'</td>
                                    <td style="width: 15%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>CONTRATO</strong><br>
                                        '.trim($eps->MENNIT).'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TIPO VINCULACION</strong><br>
                                        '.trim($paciente->tipoAfiliacion).'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>TIPO AFILIACION</strong><br>
                                        '.trim($paciente->tipoAfiliacion).'</td>
                                </tr>

                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 35%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: left;"><strong>ENRO POLIZA/CARNÉ/CONTRATO</strong><br>
                                        '.trim($paciente->TFMPNoCa).'</td>
                                    <td style="width: 35%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>MEDICO TRATANTE</strong><br>
                                        '.trim($paciente->medico).'</td>
                                    <td style="width: 20%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>C. CIUDADNIA</strong><br>
                                        '.trim($paciente->ccMed).'</td>
                                    <td style="width: 10%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center;"><strong>ESPECIALIDAD</strong><br>
                                        '.trim($paciente->espMedico).'</td>
                                </tr>

                            </table>
                                                                                    
                            
                            
                            ';
        
        return $pdfContent;
    }

    //Funcion para reder de carta de instrucciones 
    public function cartaInstrucciones($organizacion, $sede, $paciente, $responsable){
        $pdfContent = "";

        // Set the locale configuration to Spanish
        setlocale(LC_TIME, 'es_ES.UTF-8');

        // Get the current date
        $fechaActual = Carbon::now();

        // Format the date according to your requirements
        $fechaFormateada = $fechaActual->formatLocalized('%A %d %B de %Y');   

        $pdfContent .= <<<EOT
                            <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 3px">
                                <div >
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$sede->municipioSede, $fechaFormateada</p><br><br><br>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">Señores</p>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$organizacion->EmpRazSoc</p>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">La ciudad</p><br><br><br>

                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">ASUNTO: CARTA DE INSTRUCCIONES Y AUTORIZACIONES PARA LLENAR PAGARE CON ESPACIOS EN BLANCO</p>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">PACIENTE: $paciente->MPNom1 $paciente->MPNom2 $paciente->MPApe1 $paciente->MPApe2 </p>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">NUMERO: $paciente->MPTDoc $paciente->MPCedu </p>
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">Nosotros:</p></div> 
                                </div><br><br>

                                <div style="width: 100%; border-bottom: 1px solid black;">
                                    <p style="margin: 1px; padding: 0; font-family: Arial, sans-serif; font-size: 12px; display: inline-block ;padding-left: 20px;">$responsable->TFNoRe $responsable->TFNoRe2 $responsable->TFApeRes $responsable->TFApeRes2</p><br>
                                    <p style="margin: 1px 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px; display: inline-block; border-bottom: 1px solid black; width: 100%;"></p><br>
                                    <p style="margin: 1px 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px; display: inline-block; border-bottom: 1px solid black; width: 100%;"></p><br>
                                </div>
                                <div >
                                    <p style="font-family: Arial, sans-serif; font-size: 14px; padding-left: 20px;">Identificados como aparece al pie de nuestras firmas, obrando en nuestro propio nombre, por medio de la presente y en los términos del Artículo 622 del código de comercio, autorizamos a la $organizacion->EmpRazSoc XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX, irrevocable permanente para llenar los espacios en blanco del PAGARE A LA ORDEN que otorgamos a su favor, sin previo aviso y de acuerdo con las siguientes instrucciones, a) La cuantía será igual al número de letras. Cheques, facturas u otros valores en general por cualquier obligación presente o futura que directa o indirectamente, conjunta o separadamente y por concepto de PRESTACION DE SERVICIOS MEDICO-HOSPITALARIOS que hayamos recibido de la XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX $organizacion->EmpRazSoc. XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX a favor del paciente indicado en la parte superior de esta carta. b) En cuanto a la fecha de emisión en el cual se llenan los espacios dejados en blanco. c) EN cuanto a la fecha de vencimiento del PAGARE la $organizacion->EmpRazSoc. XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX. Deberá colocarle el día con el mes y año pactado a partir de la fecha de emisión, d) EL PAGARE así lleno, será exigible inmediatamente y prestara merito ejecutivo sin más requisitos y renunciamos a formular excepciones del mismo, e) En lo no previsto, la $organizacion->EmpRazSoc XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX Queda plenamente autorizada para actuar a su leal saber y entender en defensa de sus intereses que en ningún momento podamos alegar que carece de facultades o autorizaciones suficientes para completar el título.</p>
                                </div>
                                <div >
                                    <p style=" margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">Atentamente, </p>
                                </div><br><br><br><br>
                            

                                <div style="width: 100%; margin-bottom: 3px">
                                    <div style="width: 30%; display: inline-block;margin-left: 50px">
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">$responsable->TFNoRe $responsable->TFNoRe2 $responsable->TFApeRes $responsable->TFApeRes2</p>
                                        <hr style="border: none; border-top: 1px solid #000; margin-top: 1px;">
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: center; font-size: 12px;">DEUDOR SOLIDARIO</p><br><br><br>
                                        <hr style="border: none; border-top: 1px solid #000; margin-top: 1px;">
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">FIRMA</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">$responsable->TFTDoc $responsable->TFCedu</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">DIRECCIÓN: $responsable->TFDirRep</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">TELEFONO: $responsable->TFTeRe</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">BARRIO: </p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">CIUDAD: $responsable->municipio</p>
                                    </div>
                                    <div style="width: 30%; display: inline-block; margin-left: 120px">

                                        <hr style="border: none; border-top: 1px solid #000; margin-top: 1px;">
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: center; font-size: 12px;">DEUDOR SOLIDARIO</p><br><br><br>
                                        <hr style="border: none; border-top: 1px solid #000; margin-top: 1px;">
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">FIRMA</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">C.C:</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">DIRECCIÓN:</p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">TELEFONO: </p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">BARRIO: </p>
                                        <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">CIUDAD: </p>
                                    </div>
                                </div>

                            </div>
                               
                            EOT;
        
        return $pdfContent;

    }

    //Funcion para generar el pagare de la orden
    public function pagareOrden($organizacion, $sede, $paciente, $responsable, $admision){
        $pdfContent = "";
        $fechaActual = Carbon::now('America/Bogota');
        $dia = $fechaActual->format('d');
        $mes = $fechaActual->format('m');
        $anio = $fechaActual->format('y');


        $pdfContent .= <<<EOT
                            <div style="width: 100%;  margin-bottom: 3px; padding: 3px">
                                <div style="width: 25%; float: left;">

                                </div>
                                <div style="width: 40%; float: left; text-align: center;">
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$organizacion->EmpRazSoc</p>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">$organizacion->EmpNit - $organizacion->EmpDVer</p>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">PAGARE A LA ORDEN</p><br>

                                </div>
                                <div style="width: 20%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px; text-align: right;">Propio:</p>
                                    <p style="font-weight:bold; font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 14px;text-align: center;">IDENTIFICACION</p>
                                    <p style=" font-family: Arial, sans-serif; margin: 0; padding: 0; font-size: 14px;text-align: center;">$paciente->MPTDoc No.  $paciente->MPCedu </p>
                                </div>
                            </div><br><br><br><br><br>

                            <p style="font-family: Arial, sans-serif; font-size: 11px; padding-left: 0; width:100%">CERTIFICO LIBREMENTE QUE CONOZCO Y ACEPTO LA CARTA DE INSTRUCCIONES Y AUTORIZACIONES PARA LLENAR CON ESPACIOS EN 
                                BLANCO POR VALOR DE: ___________________________________________________________________________ PAGARE No _____________
                                VENCIMIENTO: _____________________________________________ DE _______________ DE ___________________ CUOTAS ______________
                                CIUDAD DE PAGO: $sede->municipioSede<br>
                                INTERESES DE PLAZO: ___________ POR CIENTO (%) MENSUAL  INTERESES DE MORA ______________ POR CIENTO (%) MENSUAL<br>
                                $responsable->TFNoRe $responsable->TFNoRe2 $responsable->TFApeRes $responsable->TFApeRes2 _________________________________________________________ Y <br>
                                __________________________________________________________________________________________<br>
                                MAYOR (ES) DE EDAD Y DOMICILIADO (S) EN $responsable->municipio Y _________________________________, <br>
                                IDENTIFICADO (S) CON CEDULA (S) DE CIUDADNIA NUMERO (S) $responsable->TFDocRep Y ________________________, DECLARO (AMOS) QUE<br>
                                POR VIRTUD DEL PRESENTE TITULO VALOR, PAGARE (MOS) INCONDICIONALMENTE, A LA <strong>ORDEN DE LA SOCIEDAD</strong> $organizacion->EmpRazSoc,
                                O A QUIEN REPRESENTE SUS DERECHOS EN LA CIUDAD Y FECHA DE VENCIMIENTO ANTES INDICADOS <br>
                                LA SUMA DE $ _____________________________________________________________________________________________<br>
                                __________________________________________________________________________________________________________<br>
                                más los intereses antes señalados. En el evento de que no cancele(mos) oportunamente el capital ni los intereses estipulados, se hará exigible
                                inmediatamente la obligación principal y sus intereses, ya que operará la denominada cláusula de exigibilidad anticipada de la obligación, caso en el cual
                                el tenedor podrá hacer exigible su inmediato pago total o el pago del saldo insoluto, tanto del capital como de sus interes, sin necesidad de practicar
                                los requerimientos privados o judiciales para la constitución en mora, a los cuales expresamente renuncio (amos). En caso de mora en el pago de la
                                obligación aquí contraída , me (nos) obligo (amos) a cancelar intereses moratorios a la tasa del ________ % mensual, hasta el día de la solución o
                                pago efectivo de la deuda.<br>
                                Expresamente declaro (amos) acusada la presentación para el pago, el aviso de rechazo y protesto. En caso de cobro judicial o extrajudicial serán
                                de mi (nuestro) cargo los costos y gastos de cobranza , igualmente asumo (imos) los derechos fiscales que cause este pagaré.<br>
                                Expresamente autorizo (amos) a la $organizacion->EmpRazSoc XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
                                para que la información contenida en el presente documento , que tiene caracter estrictamente personal y comercial, sea consultada, verificada
                                y suministrada a terceras personas, incluyendo los bancos de datos. Igualmente, la autorizo (amos) para que esta información sea usada
                                y puesta en circulación con fines estrictamente comerciales. De igual modo, autorizo (amos) expresamente para que en el evento de presentarse un
                                incumplimiento de mi (nuestra) parte, sea(mos) reportado(s) a los bancos de datos de FENALCO (Procrédito) y COVINOC o a cualquier otro, con fines lícitos
                                Para constancia y en señal de aceptación, firmo(amos) en la ciudad de $sede->municipioSede <br>
                                , a los $dia dias del mes $mes de $anio
                            </P>

                            <div style="width: 100%; margin-bottom: 3px; padding: 3px;">
                                <div style="width: 48%; float: left; text-align: left;">
                                    <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Firma deudor solidario_________________________________</p><br>
                                    <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">CC. No {$responsable->TFDocRep} {$responsable->TFNoRe} {$responsable->TFNoRe2} {$responsable->TFApeRes} {$responsable->TFApeRes2}</p>
                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both"><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">{$responsable->TFDirRep}</p>
                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Direccion residencia o trabajo</p><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">{$responsable->TFTeRe}</p>
                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Telefono</p><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">{$paciente->MPNom1} {$paciente->MPNom2} {$paciente->MPApe1} {$paciente->MPApe2}</p>
                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both; ">
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Nombre del paciente</p><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 14px;"><strong>$admision->TmCtvIng</strong></p>
                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both; ">
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">No. Ingreso</p>
                                </div>
                                <div style="width: 50%; float: right; align: right;">
                                    <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">Firma deudor solidario_________________________________</p><br>
                                    <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 11px;">CC. No _________________________________</p><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">_________________________________________</p>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Direccion residencia o trabajo</p><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">_______________________________</p>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Telefono</p><br><br>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 14px;"><strong> $paciente->MPCedu</strong></p>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">_______________________</p>
                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Cedula</p>
                                </div>
                            </div>

           
                        EOT;

        return $pdfContent;
    }

    //Funcion para generar evento catastrofico
    public function eventoCatastrofico($organizacion, $ingreso, $naturaleza){
        $pdfContent = "";
        $TFFchI = carbon::parse($ingreso->TFFchI)->format('d/m/Y');
        $TFFchS = carbon::parse($ingreso->TFFchS)->format('d/m/Y');

        if($TFFchI < '01/01/1900'){
            $TFFchI = '';
        }

        if($TFFchS < '01/01/1900'){
            $TFFchS = '';
        }

        // Supongamos que $fechaInicio y $fechaFin son tus fechas
        
        if($TFFchI != '' && $TFFchS != ''){
            $fechaInicio = Carbon::parse($TFFchI);
            $fechaFin = Carbon::parse($TFFchS);
            // Restar las fechas y obtener la diferencia en días, meses o años
            $diferencia = $fechaFin->diff($fechaInicio);
            // Acceder a la diferencia en días
            $dias = $diferencia->days;
        }else{
            $dias = '';
        }
       
        
        $pdfContent .= '
                            
                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">REPUBLICA DE COLOMBIA</p>
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px">MINISTERIO DE SALUD</p>
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">FONDO DE SOLIDARIDAD Y GARANTIA</p>
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 12px;">SUBCUENTA RIESGOS CATASTROFICOS Y ACCIDENTES DE TRANSITO</p>
                                
                            </div>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px;">
                                <tr>
                                    <td style="width: 50%; font-weight:bold; padding: 5px; border: 2px solid #000;font-size: 10px; text-align: left;">
                                        FORMULARIO UNICO PARA LA RECLAMACION DE LAS<br>
                                        INSTITUCIONES PRESTADORAS DE SERVICIOS DE SALUD<br>
                                        POR CONCEPTO DE SERVICIOS MEDICOS PRESTADOS A LAS<br>
                                        VICTIMAS DE <span style="font-size: 14px;">EVENTOS CATASTRÓFICOS </span>(FOSGA01)</td>
                                    <td style="width: 50%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left; vertical-align: top;">
                                        ESPACIO PARA EL NUMERO DE RADICACION
                                    </td> 
                                </tr>
                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">1. DATOS DEL CENTRO ASISTENCIAL</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">1.1 Nombre:</p>
                                                </td>
                                                <td style="width: 60%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->EmpRazSoc).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">NIT:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->EmpNit).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 18%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">1.2 Direccion:</p>
                                                </td>
                                                <td style="width: 71%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->EmpDir).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;"></td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:30px">
                                            <tr>
                                                <td style="width: 13%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Ciudad:</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->municipioEmpresa).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Dpto:</p>
                                                </td>
                                                <td style="width: 25%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->departamentoEmpresa).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Telefono:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($organizacion->EmpTlf).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">2. DATOS DE LA VICTIMA DEL EVENTO CATASTROFICO:</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">2.1 Nombres y apellidos:</p>
                                                </td>
                                                <td style="width: 50%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFNomC).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">edad:</p>
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->edad).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">sexo:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->MPSexoD).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">2.2 Documento de Identidad:</p>
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFTDoc).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">No:</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFCedu).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Pas:</p>
                                                </td>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">De:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">2.3 Dirección:</p>
                                                </td>
                                                <td style="width: 50%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFDire).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Ciudad:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->ciudad).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Telefono:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFTele).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 60%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">2.4 Empresa en que trabaja:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->MPEmpTra).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Municipio:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Depto:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">fhjklñ-*

                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">3. IDENTIFICACION DEL SITIO DE LA CATASTROFE:</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 70%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">3.1 Dirección donde ocurrio el evento catastrofico:</p>
                                                </td>
                                                <td style="width: 60%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SOSitAcc).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Fecha/Hora:</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SOFchAcc).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">3.2 Departamento:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;&nbsp;'.trim($ingreso->SONomD).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Municipio:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SONomM).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Zona:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SORulUrb).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">4. NATURALEZA DEL EVENTO CATASTROFICO:</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 35%; padding: 0 5px;">
                                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">4.1 Naturales</p>
                                                </td>
                                                <td style="width: 35%; padding: 0 5px;">
                                                    <p style="font-weight:bold; padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">4.2 Tecnolgicos</p>
                                                </td>
                                                <td style="width: 35%; padding: 0 5px;">
                                                    <p style="font-weight:bold; padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">4.3 Terroristas</p> 
                                                </td> 
                                            </tr>
                                        </table><br>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">'.$this->cicloSq($naturaleza,1, $ingreso->SOTpoEC).'                  
                                                </td> 
                                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">'.$this->cicloNatu($naturaleza,1).'                  
                                                </td> 
                                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">'.$this->cicloSq($naturaleza,2, $ingreso->SOTpoEC).' 
                                                </td> 
                                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">'.$this->cicloNatu($naturaleza,2).'                  
                                                </td> 
                                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">'.$this->cicloSq($naturaleza,3, $ingreso->SOTpoEC).' 
                                                </td> 
                                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">'.$this->cicloNatu($naturaleza,3).'                  
                                                </td> 
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">5. DATOS SOBRE LA ATENCION MEDICA A LA VICTIMA</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Fecha Ingreso:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($TFFchI).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Fecha Egreso:</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($TFFchS).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;"&nbsp;>Dias de Estancia:</p>
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($dias).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">5.1. Tipo de tratamiento:</p>
                                                </td>
                                                <td style="width: 80%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->ClaProD).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;"></p>
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">5.2. Diagnostico de Ingreso:</p>
                                                </td>
                                                <td style="width: 80%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim(mb_convert_case($ingreso->TFDN1I, MB_CASE_UPPER, "UTF-8")).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;"></p>
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">5.3. Diagnostico de Egreso:</p>
                                                </td>
                                                <td style="width: 80%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim(mb_convert_case($ingreso->TFcDscDS1, MB_CASE_UPPER, "UTF-8")).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;"></p>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                            
                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top:2px">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left:10px">6. REFERENCIA</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">6.1. Tipo de Referencia:</p>
                                                </td>
                                                <td style="width: 10%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Remisión</p>
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Orden de servicio</p>
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Interconsulta</p>
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 55%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">Transferencia Tecnologica</p>
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                                                                
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Persona Referida por:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Ciudad</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Fecha</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                
                                            </tr>
                                        </table>

                                        <table style="width: 100%; border-collapse: collapse; padding-left:10px">
                                            <tr>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Persona Referida a:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Ciudad</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 15%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Fecha</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                            
                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top: 2px;">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left: 10px;">7. DATOS SOBRE LA MUERTE DE LA VICTIMA</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px;">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Causa inmediata de la Muerte:</p>
                                                </td>
                                                <td style="width: 80%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim(mb_convert_case($ingreso->TFcDscCMu, MB_CASE_UPPER, "UTF-8")).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px;">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Fecha/Hora de la Muerte:</p>
                                                </td>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->TFFchM).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 50%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;"></p>
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px;">
                                            <tr>
                                                <td style="width: 60%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Nombres y Apellidos del médico que firmó el Certificado de Defunción:</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SOMNomFCD).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px;">
                                            <tr>
                                                <td style="width: 30%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Registro Médico No.</p>
                                                </td>
                                                <td style="width: 60%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;'.trim($ingreso->SOMNroReg).'</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                                <td style="width: 5%; padding: 0 5px;">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px;">de</p>
                                                </td>
                                                <td style="width: 40%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">&nbsp;</p>
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 12px; margin-top: 2px;">
                                <tr>
                                    <td style="width: 100%; padding: 5px; border: 2px solid #000; font-size: 10px; text-align: left;">
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 13px; padding-left: 10px;">5. DECLARACION DE LA INSTITUCION PRESTADORA DE SERVICIOS DE SALUD</p>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px;">
                                            <tr>
                                                <td style="width: 80%; padding: 0 5px;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; text-align: left; font-size: 12px;">En representación de la Institución prestadora de servicios de salud, declaro bajo la gravedad del juramento que la
                                                            información contenida en este formulario es cierta y podrá ser verificada por la Dirección General de gestión Financiera del
                                                            Ministerio de Salud.</p>
                                                </td>
                                            </tr>
                                        </table><br><br><br>
                                        <table style="width: 100%; border-collapse: collapse; padding-left: 10px">
                                            <tr>
                                                <td style="width: 20%; padding: 0 5px; text-align: center;">
                                                    
                                                </td>
                                                <td style="width: 50%; padding: 0 5px; text-align: center;">
                                                    <hr style="border: none; border-top: 1px solid #000; margin-top: 1px; clear: both;">
                                                    <p style="padding: 0; margin: 0; font-family: Arial, sans-serif; font-size: 12px;">Firma y Sello</p>
                                                </td>
                                                <td style="width: 20%; padding: 0 5px; text-align: center;">
                                                    
                                                </td>
                                                
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>


                            ';

                            

        return $pdfContent;

    }

    public function cicloSq($naturaleza, $tipo, $codEvento){

        $pdfContent = "";
        $ind = "";

        foreach($naturaleza as $natuCatastrofico){

            if($natuCatastrofico->CESUBTIP == $tipo){

                if($natuCatastrofico->CESUBCOD == $codEvento){
                    $ind = "X"; 
                }

                $pdfContent .= '    
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;">'.$ind.'</div>

                                ';
                
                $ind = "";
        
            }
        }
        return $pdfContent;
    }

    public function cicloNatu($naturaleza, $tipo){

        $pdfContent = "";

        foreach($naturaleza as $natuCatastrofico){

            if($natuCatastrofico->CESUBTIP == $tipo){
                $pdfContent .=  '   <div style="height: 17px; padding: 1px;"> 
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 12px; padding-left: 5px;">'.$natuCatastrofico->CESUBDET.'</p>
                                    </div>';
                                
        
            }
        }
        return $pdfContent;
    }

    //Funcion para generar anexo 1
    public function anexo1($organizacion, $ingreso, $contratoIngreso, $paciente, $usuReporta){

        $fechaActual = date("Y-m-d");
        $horaActual = date("H:i:s");

        $indRC = "";
        $indTI = "";
        $indCC = "";
        $indCE = "";
        $indPA = "";
        $indAS = "";
        $indMS = "";


        $MPApe1 = !empty(trim($paciente->MPApe1)) ? trim($paciente->MPApe1) : 'NO TIENE';
        $MPApe2 = !empty(trim($paciente->MPApe2)) ? trim($paciente->MPApe2) : 'NO TIENE';
        $MPNom1 = !empty(trim($paciente->MPNom1)) ? trim($paciente->MPNom1) : 'NO TIENE';
        $MPNom2 = !empty(trim($paciente->MPNom2)) ? trim($paciente->MPNom2) : 'NO TIENE';

        $MPFchN = Carbon::parse($paciente->MPFchN)->format('Y-m-d');

        switch (trim($paciente->MPTDoc)) {
            case 'RC':
                $indRC = 'X';
                break;
            case 'TI':
                $indTI = 'X';
                break;
            case 'CC':
                $indCC = 'X';
                break;
            case 'CE':
                $indCE = 'X';
                break;
            case 'PA':
                $indPA = 'X';
                break;
            case 'AS':
                $indAS = 'X';
                break;
            case 'MS':
                $indMS = 'X';
                break;
        }

        $pdfContent = "";
        $pdfContent .= '
                            <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                                <div style="width: 15%; float: left;">
                                    <img src="images/Logo-G-Ocho.png" alt="Logo" style="width: 70px; height: 70px; position: absolute; top: 10px; left: 10px;"><br>
                                </div>
                                <div style="width: 80%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">ANEXO TÉCNICO No. 1</p>
                                    <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">MINISTERIO DE LA PROTECCIÓN SOCIAL</p>
                                    <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px">INFORME DE POSIBLES INCONSISTENCIAS EN LA BASES DE DATOS DE LA ENTIDAD RESPONSABLE DEL PAGO</p>
                                </div>
                                <div style="width: 20%; float: left;">
                                </div>
                            </div><br><br>

                            <div style="width: 100%; text-align: right; margin-bottom: 3px; padding: 5px; margin-top: 5px;">
                                <div style="width: 5%; float: right;">
                                </div>
                                <div style="width: 15%; float: right;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>HORA</strong> '.$horaActual.'</p>
                                </div>
                                <div style="width: 15%; float: right;">
                                    <p style="margin: 0px; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>FECHA</strong> '.$fechaActual .'</p>
                                </div>
                                
                                <div style="width: 10%; float: right; margin-left: 2px; padding: 0px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="border: 1px solid #000; width: 15%; height: 15px;"></td>
                                            <td style="border: 1px solid #000; width: 15%; height: 15px;"></td>
                                            <td style="border: 1px solid #000; width: 15%; height: 15px;"></td>
                                            <td style="border: 1px solid #000; width: 15%; height: 15px;"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="width: 20%; float: right;">
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">NUMERO INFORME</p>
                                </div>
                                <div style="width: 20%; float: right;">
                                </div>
                                
                            </div>

                            <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 10px; margin-top: 5px;">
                                <div style="width: 65%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>INFORMACION DEL PRESTADOR</strong></p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Nombre</strong></p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($organizacion->MCDRazSoc).'</p>
                                </div>

                                <div style="width: 10%; float: left; padding:10px">
                                    <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 5px; margin-top: 5px;">
                                        <div style="width: 30%; float: left;">
                                            <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;font-family: Arial, sans-serif; font-size: 10px">X</div>
                                            <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;font-family: Arial, sans-serif; font-size: 10px"></div>
                                        </div>
                                        <div style="width: 60%; float: left;margin-left:20px">
                                            <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>NIT</strong></p>
                                            <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>CC</strong></p>
                                        </div>
                                    </div> 
                                </div>

                                <div style="width: 30%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;'.trim($organizacion->MCDNIT).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Número DV</strong></p>
                                </div>
                               
                            </div>

                            <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 10px; margin-top: 29px;">
                                <div style="width: 25%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Codigo</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDCodIPS).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Telefono </strong>&nbsp;&nbsp;&nbsp;'.$organizacion->MCDIndTel.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDTele).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Indicativo&nbsp;&nbsp;&nbsp;Número</p>
                                </div>

                                <div style="width: 40%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Dir. prestador</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDDir).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Departamento </strong>&nbsp;&nbsp;&nbsp;'.trim($organizacion->departamentoSede).'&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDptCod).'</p>
                                </div>

                                <div style="width: 40%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;&nbsp;</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Municipio </strong>&nbsp;'.trim($organizacion->municipioSede).'&nbsp;&nbsp;&nbsp;'.$organizacion->MCCiuCod.'</p>
                                </div>
                            </div><br><br>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px;">
                                <tr>
                                    <td style="width: 70%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align: left;"><strong>
                                        ENTIDAD A LA QUE SE LE INFORMA (PAGADOR)</strong>&nbsp;&nbsp;&nbsp;'.trim($contratoIngreso->MENOMB).'</td>
                                    <td style="width: 10%; padding: 5px; border-right: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: right;">
                                        <strong>Codigo</strong></td>
                                    <td style="width: 20%; padding: 5px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: left;">
                                        '.trim($contratoIngreso->MeCodRip).'</td>
                                </tr>
                            </table>

                            <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 5px;">
                                <div style="width: 25%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Tipo De Incosistencia</p>
                                </div>

                                <div style="width: 5%; float: left;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>   
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;"></div>   
                                </div>

                                <div style="width: 70%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">El usuario no existe en la base de datos.</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Los datos del usuario no corresponden con los del documento de identificación presentado</p>
                                </div>

                            </div>

                            <hr style="border: none; border-top: 1px solid #000; clear: both;">

                            <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:center">DATOS DEL USUARIO (COMO APARECE EN LA BASE DE DATOS)</p>	

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:5px">
                                <tr>
                                    <td style="width: 25%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:center">
                                       '.strtoupper($MPApe1).'</td>
                                    <td style="width: 25%; padding: 5px; border: 1px solid #000; font-size: 10px; text-align: center; ">
                                        '.strtoupper($MPApe2).'</td>
                                    <td style="width: 25%; padding: 5px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center;">
                                        '.strtoupper($MPNom1).'</td>
                                    <td style="width: 25%; padding: 5px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center;">
                                        '.strtoupper($MPNom2).'</td>
                                </tr>
                            </table>
                            <table style="font-weight:bold; width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px;">
                                <tr>
                                    <td style="width: 25%; padding: 5px; font-size: 10px; text-align:center">
                                        1er Apellido</td>
                                    <td style="width: 25%; padding: 5px;  font-size: 10px; text-align: center; ">
                                        2do Apellido</td>
                                    <td style="width: 25%; padding: 5px; font-size: 10px; text-align: center;">
                                        1er Nombre</td>
                                    <td style="width: 25%; padding: 5px; font-size: 10px; text-align: center;">
                                        2do Nombre</td>
                                </tr>
                            </table>

                            <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Tipo Documento De Identificación</p>	

                            <table style="width: 100%; border-collapse: collapse; padding-left:10px; margin-top:8px">
                                <tr>
                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRC.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indTI.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indCC.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indCE.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Registro Civil</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Tarjeta De Identidad</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Cédula De Ciudadanía</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Cédula De Extranjería</p>
                                    </td> 

                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indMS.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pasaporte</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Adulto Sin Identificación</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Menor Sin Identificación</p>
                                    </td> 

                                    <td style="width: 70%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px; text-align:center">'.trim($paciente->MPCedu).'</p>
                                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px; text-align:center">Número Documento De Identificación</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:9px; text-align:left"><strong>Fecha De Nacimiento</strong> &nbsp;&nbsp;&nbsp;'.trim($MPFchN).'</p>
                                    </td> 
                                    
                                </tr>
                            </table>

                            <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 5px;">
                                <div style="width: 22%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px; text-align:right">Dirección Residencia Habitual</p>
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px; text-align:right">Departamento</p>
                                </div>

                                <div style="width: 40%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->MPDire).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->departamentoPaciente).'&nbsp;&nbsp;&nbsp;&nbsp;'.trim($paciente->MDCodD).'</p>     
                                </div>

                                <div style="width: 10%; float: left;">
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Telefono</p>
                                    <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Municipio</p>
                                </div>

                                <div style="width:40%; float: left;">
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->MPTele).'</p>
                                    <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->municipioPaciente).'&nbsp;&nbsp;&nbsp;&nbsp;'.trim($paciente->MDCodM).'</p>
                                </div>

                            </div>

                            <hr style="border: none; border-top: 1px solid #000; clear: both;">

                            <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Cobertura En Salud</p>

                            <table style="width: 100%; border-collapse: collapse; padding-left:10px; margin-top:8px">
                                <tr>
                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRC.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indTI.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Contributivo</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Subsidiado - Total</p>
                                    </td> 

                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Subsidiado - Parcial</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pobl. Pobre No Asegurada Con SISBEN</p>
                                    </td> 

                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pobl. Pobre No Asegurada Sin SISBEN</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Desplazadon</p>
                                    </td> 

                                    <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                    </td> 
                                    <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Plan Adicional De Salud</p>
                                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Otro</p>
                                    </td>  
                                </tr>
                            </table>

                            <hr style="border: none; border-top: 1px solid #000; clear: both;">
                            <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:center">INFORMACIÓN DE LA POSIBLE INCOSISTENCIA</p>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:5px">
                                <tr>
                                
                                    <td style="width: 40%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:left">
                                        <p style="font-weight:bold;  margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">DATOS SEGÚN DOCUMENTO DE IDENTIFICACION (Fisico)</p>

                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:5px">
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Primer Apellido</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Segundo Apellido</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Primer Nombre</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Segundo Nombre</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Tipo Documento De Identificación</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Número Documento De Identificación</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%; padding: 0px; font-size: 10px; text-align:left">
                                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 0px;text-align:center; font-weight:bold;"></div>
                                                </td>
                                                <td style="width: 90%; padding: 0px; font-size: 10px; text-align:left">
                                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 1px; padding-top:6px">Fecha De Nacimiento</p>
                                                </td>
                                            </tr>
                                            
                                        </table>
                                    </td>

                                    <td style="width: 80%; padding: 0px; border-bottom: 1px solid #000; border-top: 1px solid #000; border-left: 1px solid #000; font-size: 10px; text-align: center; ">
                                        
                                        <p style="font-weight:bold;  margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">DATOS SEGÚN DOCUMENTO DE IDENTIFICACION (Fisico)</p>
                                    
                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:5px">
                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Primer Apellido</td>
                                                '.$this->cicloTableSq(16,0).'
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Segundo Apellido</td>
                                                '.$this->cicloTableSq(16,0).'
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Primer Nombre</td>
                                                '.$this->cicloTableSq(16,0).'
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Segundo Nombre</td>
                                                '.$this->cicloTableSq(16,0).'
                                            </tr>
                                        </table>
                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">

                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Tipo Documento De Identificación</td>
                                                '.$this->cicloTableSq(9,0).'
                                                <td style="width: 14%; padding: 5px; border-top: 1px solid #000;  border-left: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align:left"></td>

                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 5px; border-bottom: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Número Documento De Identificación</td>
                                                '.$this->cicloTableSq(9,0).'
                                                <td style="width: 14%; padding: 5px;border-left: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align:left"></td>

                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 5px;border-top: 1px solid #000; border-rigth: 1px solid #000; font-size: 10px; text-align:left">
                                                Fecha De Nacimiento</td>
                                                '.$this->cicloTableSq(9,1).'
                                                <td style="width: 14%; padding: 5px; border-left: 1px solid #000; font-size: 10px; text-align:left"></td>

                                            </tr>
                                        </table>
                                    </td>
                                    
                                </tr>
                            </table>

                            <p style="font-weight:bold; margin: 0; padding: 5px; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Observaciones: </p>
                            <hr style="width:95%; border: none; border-top: 1px solid #000; clear: both; padding:5px;">
                            <hr style="width:95%; border: none; border-top: 1px solid #000; clear: both; padding:5px">
                            <hr style="width:95%; border: none; border-top: 1px solid #000; clear: both; padding:5px">

                            <hr style="border: none; border-top: 1px solid #000; clear: both;">
                            <p style="font-weight:bold; margin: 0; padding: 0px; font-family: Arial, sans-serif; font-size: 10px; text-align:center">INFORMACIÓN DE LA PERSONA QUE REPORTA</p>

                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:5px">
                                <tr>
                                    <td style="width: 50%; padding: 0px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:center">
                                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                            <tr>
                                                <td style="width: 25%; padding: 8px; border-bottom: 1px solid #000;font-size: 10px; text-align:left">
                                                    Nombre De Quien Reporta</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 8px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:left">
                                                    '.$usuReporta->nombreReporta.'</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; padding: 8px; border-top: 1px solid #000; font-size: 10px; text-align:left">
                                                <strong>Cargo O Actividad</strong>&nbsp;&nbsp;&nbsp;'.$usuReporta->AGrpId.'</td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td style="width: 50%; padding: 0px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center; ">
                                        <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 0px; margin-top: 3px;">
                                            <div style="width: 30%; display: inline-block; vertical-align: top;">
                                                <p style="margin-top: 5px; padding: 5px; font-family: Arial, sans-serif; font-size: 10px"><strong>Teléfono</strong></p>
                                                <p style="margin-top: 20px; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Tel. Celular</strong></p>
                                            </div>
                                            <div style="width: 60%; display: inline-block; vertical-align: top; ">
                                                <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                                    <tr>
                                                        <td style="width: 5%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                            '.trim($organizacion->MCDIndTel).'</td>
                                                        <td style="width: 40%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                            '.trim($organizacion->MCDTele).'</td>
                                                        <td style="width: 10%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                            '.trim($organizacion->MCDExtTel).'</td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td style="width: 10%; padding: 5px; font-size: 10px; text-align:center">
                                                            Indicativo</td>
                                                        <td style="width: 25%; padding: 5px; font-size: 10px; text-align:center">
                                                            Número</td>
                                                        <td style="width: 10%; padding: 5px; bfont-size: 10px; text-align:center">
                                                            Extensión</td>
                                                    </tr>

                                                </table>

                                                <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                                    <tr>
                                                        <td style="width: 100%; padding:8px; border: 1px solid #000 ; font-size: 10px; text-align:left">
                                                            '.trim($organizacion->MCDCel).'</td>
                                                        
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>  
                                    </td>
                                </tr>
                            </table>

                           
                            ';
        

        return $pdfContent;
    }

    public function cicloTableSq($cant,$ind){
        $html = '';
        $arrVal = ['a','a','a','a','-','m','m','-','d','d'];
        if($ind == 0){
            for ($i = 0; $i < $cant; $i++) {
                $html .= '<td style="width: 2%; padding: 5px; border-top: 1px solid #000;  border-left: 1px solid #000; font-size: 10px; text-align:left"></td>';
            }
        }else{
            for ($i = 0; $i < $cant; $i++) {
                $html .= '<td style="width: 2%; padding: 5px; border-top: 1px solid #000;  border-left: 1px solid #000; font-size: 7px; text-align:left">'.$arrVal[$i].'</td>';
            }
        }
        return $html;
    }
    
    //Funcion para generar anexo 2
    public function anexo2($organizacion, $contratoIngreso, $paciente, $admision, $ingreso, $referencia, $dx, $usuReporta){

        $fechaActual = date('Y-m-d');
        $horaActual = date('H:i:s');
        $fechaUrg = Carbon::parse($ingreso->IngFeHAtU)->format('Y-m-d');
        $horaUrg = Carbon::parse($ingreso->IngFeHAtU)->format('H:i');

        $indRC = "";
        $indTI = "";
        $indCC = "";
        $indCE = "";
        $indPA = "";
        $indAS = "";
        $indMS = "";

        $indEG = "";
        $indEP = "";
        $indAT = "";
        $indATr = "";
        $indEC = "";

        $ind1 = "";
        $ind2 = "";
        $ind3 = "";

        $indNA1 = '0';
        $indNA2 = '0';
        $indNA3 = '0';
        $indNA4 = '0'; 

        $indRefT = '';
        $indRefF = '';
        

        $MPApe1 = !empty(trim($paciente->MPApe1)) ? trim($paciente->MPApe1) : 'NO TIENE';
        $MPApe2 = !empty(trim($paciente->MPApe2)) ? trim($paciente->MPApe2) : 'NO TIENE';
        $MPNom1 = !empty(trim($paciente->MPNom1)) ? trim($paciente->MPNom1) : 'NO TIENE';
        $MPNom2 = !empty(trim($paciente->MPNom2)) ? trim($paciente->MPNom2) : 'NO TIENE';

        $MPFchN = Carbon::parse($paciente->MPFchN)->format('Y-m-d');

        switch (trim($paciente->MPTDoc)) {
            case 'RC':
                $indRC = 'X';
                break;
            case 'TI':
                $indTI = 'X';
                break;
            case 'CC':
                $indCC = 'X';
                break;
            case 'CE':
                $indCE = 'X';
                break;
            case 'PA':
                $indPA = 'X';
                break;
            case 'AS':
                $indAS = 'X';
                break;
            case 'MS':
                $indMS = 'X';
                break;
        }

        //switch causa externa 
        switch (trim($admision->TFCauE)){
            case 13:
                $indEG = 'X';
                break;
            case 14:
                $indEP = 'X';
                break;
            case 1:
                $indAT = 'X';
                break;
            case 2:
                $indATr = 'X';
                break;
            case 6:
                $indEC = 'X';
                break;
        }

        //switch triage
        switch(trim($admision->TFEstP)){
            case 1:
                $ind1 = 'X';
                break;
            case 2:
                $ind2 = 'X';
                break;
            case 3:
                $ind3 = 'X';
                break;
        }

        // Convertir el número en una cadena

        if(!empty($ingreso->IngNroAn2)){
            $cadenaNumero = strval($ingreso->IngNroAn2);

            // Calcular la longitud de la cadena
            $longitud = strlen($cadenaNumero);
    
            if ($longitud == 1) {
                $indNA4 = substr($cadenaNumero, 0, 1);
            } elseif ($longitud == 2) {
                $indNA3 = substr($cadenaNumero, 0, 1);
                $indNA4 = substr($cadenaNumero, 1, 1);
            } elseif ($longitud == 3) {
                $indNA2 = substr($cadenaNumero, 0, 1);
                $indNA3 = substr($cadenaNumero, 1, 1);
                $indNA4 = substr($cadenaNumero, 2, 1);
            } elseif ($longitud == 4) {
                $indNA1 = substr($cadenaNumero, 0, 1);
                $indNA2 = substr($cadenaNumero, 1, 1);
                $indNA3 = substr($cadenaNumero, 2, 1);
                $indNA4 = substr($cadenaNumero, 3, 1);
            }
            
        }


        if($referencia){
            $municipioIpsRef = trim($referencia->municipioIpsRef);
            $codMunicipioIpsRef = trim($referencia->codMunicipioIpsRef);
            $departamentoIpsRef = trim($referencia->departamentoIpsRef);
            $codDepartamentoIpsRef = trim($referencia->codDepartamentoIpsRef);
            $ipsRef = trim($referencia->ipsRef);
            $RefIPSRef = trim($referencia->RefIPSRef);
            $indRefT = 'X';
        }else{
            $municipioIpsRef = '';
            $codMunicipioIpsRef = '';
            $departamentoIpsRef = '';
            $codDepartamentoIpsRef = '';
            $ipsRef = '';
            $RefIPSRef = '';
            $indRefF = 'X';
        }



        $pdfContent = "";
        $pdfContent .= '

                        <div style="width: 100%; text-align: center; margin-bottom: 3px; padding: 3px">
                            <div style="width: 15%; float: left;">
                                <img src="images/Logo-G-Ocho.png" alt="Logo" style="width: 70px; height: 70px; position: absolute; top: 10px; left: 10px;"><br>
                            </div>
                            <div style="width: 80%; float: left;">
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">ANEXO TÉCNICO No. 2</p>
                                <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px">MINISTERIO DE LA PROTECCIÓN SOCIAL</p>
                                <p style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px">INFORME DE LA ATENCION INICIAL DE URGENCIAS</p>
                            </div>
                            <div style="width: 20%; float: left;">
                            </div>
                        </div><br><br>

                        <div style="width: 100%; text-align: right; margin-bottom: 3px; padding: 5px; margin-top: 5px;">
                            <div style="width: 5%; float: right;">
                            </div>
                            <div style="width: 15%; float: right;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>HORA</strong> '.$horaActual.'</p>
                            </div>
                            <div style="width: 15%; float: right;">
                                <p style="margin: 0px; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>FECHA</strong> '.$fechaActual .'</p>
                            </div>
                            
                            <div style="width: 10%; float: right; margin-left: 2px; padding: 0px;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="border: 1px solid #000; width: 15%; height: 15px; font-family: Arial, sans-serif; font-size: 10px; font-weight:bold; text-align:center">'.$indNA1.'</td>
                                        <td style="border: 1px solid #000; width: 15%; height: 15px; font-family: Arial, sans-serif; font-size: 10px; font-weight:bold; text-align:center">'.$indNA2.'</td>
                                        <td style="border: 1px solid #000; width: 15%; height: 15px; font-family: Arial, sans-serif; font-size: 10px; font-weight:bold; text-align:center">'.$indNA3.'</td>
                                        <td style="border: 1px solid #000; width: 15%; height: 15px; font-family: Arial, sans-serif; font-size: 10px; font-weight:bold; text-align:center">'.$indNA4.'</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="width: 20%; float: right;">
                                <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">NUMERO DE ATENCIÓN</p>
                            </div>
                            <div style="width: 20%; float: right;">
                            </div>
                            
                        </div>

                        <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 10px; margin-top: 5px;">
                            <div style="width: 65%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>INFORMACION DEL PRESTADOR</strong></p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Nombre</strong></p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($organizacion->MCDRazSoc).'</p>
                            </div>

                            <div style="width: 10%; float: left; padding:10px">
                                <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 5px; margin-top: 5px;">
                                    <div style="width: 30%; float: left;">
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;font-family: Arial, sans-serif; font-size: 10px">X</div>
                                        <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold;font-family: Arial, sans-serif; font-size: 10px"></div>
                                    </div>
                                    <div style="width: 60%; float: left;margin-left:20px">
                                        <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>NIT</strong></p>
                                        <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>CC</strong></p>
                                    </div>
                                </div> 
                            </div>

                            <div style="width: 30%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;'.trim($organizacion->MCDNIT).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Número DV</strong></p>
                            </div>
                            
                        </div>

                        <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 10px; margin-top: 29px;">
                            <div style="width: 25%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Codigo</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDCodIPS).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Telefono </strong>&nbsp;&nbsp;&nbsp;'.$organizacion->MCDIndTel.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDTele).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Indicativo&nbsp;&nbsp;&nbsp;Número</p>
                            </div>

                            <div style="width: 40%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Dir. prestador</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDDir).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Departamento </strong>&nbsp;&nbsp;&nbsp;'.trim($organizacion->departamentoSede).'&nbsp;&nbsp;&nbsp;'.trim($organizacion->MCDptCod).'</p>
                            </div>

                            <div style="width: 40%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">&nbsp;&nbsp;</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Municipio </strong>&nbsp;'.trim($organizacion->municipioSede).'&nbsp;&nbsp;&nbsp;'.$organizacion->MCCiuCod.'</p>
                            </div>
                        </div><br><br>

                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px;">
                            <tr>
                                <td style="width: 70%; padding: 5px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align: left;"><strong>
                                    ENTIDAD A LA QUE SE LE INFORMA (PAGADOR)</strong>&nbsp;&nbsp;&nbsp;'.trim($contratoIngreso->MENOMB).'</td>
                                <td style="width: 10%; padding: 5px; border-right: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: right;">
                                    <strong>Codigo</strong></td>
                                <td style="width: 20%; padding: 5px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: left;">
                                    '.trim($contratoIngreso->MeCodRip).'</td>
                            </tr>
                        </table>

                        <p style="font-weight:bold; margin-top: 3px; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:center">DATOS DEL USUARIO (COMO APARECE EN LA BASE DE DATOS)</p>	

                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:1px">
                            <tr>
                                <td style="width: 25%; padding: 0px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:center">
                                    '.strtoupper($MPApe1).'</td>
                                <td style="width: 25%; padding: 0px; border: 1px solid #000; font-size: 10px; text-align: center; ">
                                    '.strtoupper($MPApe2).'</td>
                                <td style="width: 25%; padding: 0px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center;">
                                    '.strtoupper($MPNom1).'</td>
                                <td style="width: 25%; padding: 0px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center;">
                                    '.strtoupper($MPNom2).'</td>
                            </tr>
                        </table>
                        <table style="font-weight:bold; width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px;">
                            <tr>
                                <td style="width: 25%; padding: 5px; font-size: 10px; text-align:center">
                                    1er Apellido</td>
                                <td style="width: 25%; padding: 5px;  font-size: 10px; text-align: center; ">
                                    2do Apellido</td>
                                <td style="width: 25%; padding: 5px; font-size: 10px; text-align: center;">
                                    1er Nombre</td>
                                <td style="width: 25%; padding: 5px; font-size: 10px; text-align: center;">
                                    2do Nombre</td>
                            </tr>
                        </table>

                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Tipo Documento De Identificación</p>	

                        <table style="width: 100%; border-collapse: collapse; padding-left:10px; margin-top:8px">
                            <tr>
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRC.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indTI.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indCC.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indCE.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Registro Civil</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Tarjeta De Identidad</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Cédula De Ciudadanía</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Cédula De Extranjería</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indMS.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pasaporte</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Adulto Sin Identificación</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Menor Sin Identificación</p>
                                </td> 

                                <td style="width: 70%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px; text-align:center">'.trim($paciente->MPCedu).'</p>
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px; text-align:center">Número Documento De Identificación</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:9px; text-align:left"><strong>Fecha De Nacimiento</strong> &nbsp;&nbsp;&nbsp;'.trim($MPFchN).'</p>
                                </td> 
                                
                            </tr>
                        </table>

                        <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 5px;">
                            <div style="width: 22%; float: left;">
                                <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px; text-align:right">Dirección Residencia Habitual</p>
                                <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px; text-align:right">Departamento</p>
                            </div>

                            <div style="width: 40%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->MPDire).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->departamentoPaciente).'&nbsp;&nbsp;&nbsp;&nbsp;'.trim($paciente->MDCodD).'</p>     
                            </div>

                            <div style="width: 10%; float: left;">
                                <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Telefono</p>
                                <p style="font-weight:bold; margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">Municipio</p>
                            </div>

                            <div style="width:40%; float: left;">
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->MPTele).'</p>
                                <p style="margin: 0; padding: 2px; font-family: Arial, sans-serif; font-size: 10px">'.trim($paciente->municipioPaciente).'&nbsp;&nbsp;&nbsp;&nbsp;'.trim($paciente->MDCodM).'</p>
                            </div>

                        </div>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">

                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Cobertura En Salud</p>

                        <table style="width: 100%; border-collapse: collapse; padding-left:10px; margin-top:8px">
                            <tr>
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRC.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indTI.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Contributivo</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Subsidiado - Total</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Reg. Subsidiado - Parcial</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pobl. Pobre No Asegurada Con SISBEN</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Pobl. Pobre No Asegurada Sin SISBEN</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Desplazadon</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indPA.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAS.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Plan Adicional De Salud</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Otro</p>
                                </td>  
                            </tr>
                        </table>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">
                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:center">INFORMACIÓN DE LA ATENCIÓN</p>
                        <hr style="border: none; border-top: 1px solid #000; clear: both; ">
                        
                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Origen De La Atención</p>

                        <table style="width: 100%; border-collapse: collapse; padding-left:10px; margin-top:8px">
                            <tr>
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indEG.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indEP.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Enfermedad General</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Enfermedad Profesional</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indAT.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indATr.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Accidente De Trabajo</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Accidente De Transito</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indEC.'</div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Evento Catastrofico</p>
                                </td> 

                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:3px">Clas. Triage</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$ind1.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$ind2.'</div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$ind3.'</div>
                                </td> 
                                <td style="width: 35%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">1. Rojo</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">2. Amarillo</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">3. Verde</p>
                                </td>  
                            </tr>
                        </table>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">
                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Ingreso A Urgencias</p>
                       
                        <table style="width: 70%; border-collapse: collapse; padding-right:0px; margin-top:1px;">
                            <tr>
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">FECHA</p>
                                </td> 
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.$fechaUrg.'</p>
                                </td> 
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">HORA</p>
                                </td> 
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.$horaUrg.'</p>
                                </td> 
                                <td style="width: 40%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Paciente Viene Remitido</p>
                                </td> 
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRefT.'</div>
                                </td> 
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Si</p>
                                </td> 
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;">'.$indRefF.'</div>
                                </td> 
                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">No</p>
                                </td> 
                            </tr>
                        </table>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">

                        <table style="width: 100%; border-collapse: collapse; padding-right:0px; margin-top:1px;">
                            <tr>
                                <td style="width: 40%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Nombre Del Prestador De Servicios Que Remite</p>
                                </td> 
                                <td style="width: 50%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.trim($ipsRef).'</p>
                                </td> 
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Codigo</p>
                                </td> 
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.trim($RefIPSRef).'</p>
                                </td> 
                            </tr>
                        </table>
                        <table style="width: 100%; border-collapse: collapse; padding-right:0px; margin-top:1px;">
                            <tr>
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Departamento</p>
                                </td> 
                                <td style="width: 40%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.trim($departamentoIpsRef).'&nbsp;&nbsp;&nbsp; '.trim($codDepartamentoIpsRef).'</p>
                                </td> 
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Municipio</p>
                                </td> 
                                <td style="width: 40%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.trim($municipioIpsRef).'&nbsp;&nbsp;&nbsp; '.trim($codMunicipioIpsRef).'</p>
                                </td> 
                            </tr>
                        </table>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">

                        <p style="font-weight:bold; margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 10px; text-align:left">Motivo De Consulta:</p>
                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">'.trim($ingreso->motConsulta).'</p>
                        <hr style="border: none; border-top: 1px solid #000; clear: both;">

                        <table style="width: 100%; border-collapse: collapse; padding-right:0px; margin-top:1px;">
                            <tr>
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Impresion Diagnostica</p>
                                </td> 
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Codigo CIE10</p>
                                </td> 
                                <td style="width: 70%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:6px">Descripción</p>
                                </td> 
                            </tr>
                        </table>

                        <table style="width: 100%; border-collapse: collapse; padding-right:0px; margin-top:1px;">
                            <tr>
                                <td style="width: 20%; padding: 0 5px; vertical-align: top;">
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">Diagnostico Principal</p>
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">Diagnostico Relacionado</p>
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">Diagnostico Relacionado</p>
                                    <p style=" margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">Diagnostico Relacionado</p>
                                </td> 
                                <td style="width: 10%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">'.strtoupper(trim($ingreso->dxPpalCod)).'</p>
                                    '.$this->cicloDxC($dx).'
                                </td> 
                                <td style="width: 70%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">'.strtoupper(trim($ingreso->dxPpal)).'</p>
                                    '.$this->cicloDxD($dx).'
                                </td> 
                            </tr>
                        </table>

                        <p style="font-weight:bold; margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 0px; padding-top:4px">Destino Paciente</p>   
                        <table style="width: 70%; border-collapse: collapse; padding-left:10px; margin-top:1px">
                            <tr>

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Domicilio</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Observación</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Internación</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Remisión</p>
                                </td> 

                                <td style="width: 5%; padding: 0 5px; vertical-align: top;">
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                    <div style="border: 1px solid #000; width: 15px; height: 15px; padding: 1px;text-align:center; font-weight:bold; font-family: Arial, sans-serif; font-size: 10px;"></div>
                                </td> 
                                <td style="width: 30%; padding: 0 5px; vertical-align: top;">
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Contraremisión</p>
                                    <p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:6px">Otro</p>
                                </td> 
                            </tr>
                        </table>

                        <hr style="border: none; border-top: 1px solid #000; clear: both;">
                        <p style="font-weight:bold; margin: 0; padding: 0px; font-family: Arial, sans-serif; font-size: 10px; text-align:center">INFORMACIÓN DE LA PERSONA QUE REPORTA</p>

                        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                            <tr>
                                <td style="width: 50%; padding: 0px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:center">
                                    <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                        <tr>
                                            <td style="width: 25%; padding: 8px; border-bottom: 1px solid #000;font-size: 10px; text-align:left">
                                                Nombre De Quien Reporta</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 25%; padding: 8px; border-top: 1px solid #000; border-bottom: 1px solid #000;font-size: 10px; text-align:left">
                                                '.$usuReporta->nombreReporta.'</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 25%; padding: 8px; border-top: 1px solid #000; font-size: 10px; text-align:left">
                                            <strong>Cargo O Actividad</strong>&nbsp;&nbsp;&nbsp;'.$usuReporta->AGrpId.'</td>
                                        </tr>
                                    </table>
                                </td>

                                <td style="width: 50%; padding: 0px; border-left: 1px solid #000; border-top: 1px solid #000; border-bottom: 1px solid #000; font-size: 10px; text-align: center; ">
                                    <div style="width: 100%; text-align: left; margin-bottom: 3px; padding: 0px; margin-top: 3px;">
                                        <div style="width: 30%; display: inline-block; vertical-align: top;">
                                            <p style="margin-top: 5px; padding: 5px; font-family: Arial, sans-serif; font-size: 10px"><strong>Teléfono</strong></p>
                                            <p style="margin-top: 20px; padding: 2px; font-family: Arial, sans-serif; font-size: 10px"><strong>Tel. Celular</strong></p>
                                        </div>
                                        <div style="width: 60%; display: inline-block; vertical-align: top; ">
                                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                                <tr>
                                                    <td style="width: 5%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                        '.trim($organizacion->MCDIndTel).'</td>
                                                    <td style="width: 40%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                        '.trim($organizacion->MCDTele).'</td>
                                                    <td style="width: 10%; padding: 5px; border: 1px solid #000;font-size: 10px; text-align:left">
                                                        '.trim($organizacion->MCDExtTel).'</td>
                                                </tr>
                                                
                                                <tr>
                                                    <td style="width: 10%; padding: 5px; font-size: 10px; text-align:center">
                                                        Indicativo</td>
                                                    <td style="width: 25%; padding: 5px; font-size: 10px; text-align:center">
                                                        Número</td>
                                                    <td style="width: 10%; padding: 5px; bfont-size: 10px; text-align:center">
                                                        Extensión</td>
                                                </tr>

                                            </table>

                                            <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 10px; margin-top:0px">
                                                <tr>
                                                    <td style="width: 100%; padding:8px; border: 1px solid #000 ; font-size: 10px; text-align:left">
                                                        '.trim($organizacion->MCDCel).'</td>
                                                    
                                                </tr>
                                            </table>
                                        </div>
                                    </div>  
                                </td>
                            </tr>
                        </table>



        ';
        
        return $pdfContent;
    }

    public function cicloDxD($dx){
        $p = '';
        foreach ($dx as $value) {
            $p .= '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">'.strtoupper(trim($value->HCDXNOM)).'</p>';            
        }
        return $p;
    }

    public function cicloDxC($dx){
        $p = '';
        foreach ($dx as $value) {
            $p .= '<p style="margin: 0; font-family: Arial, sans-serif; font-size: 10px; padding-left: 5px; padding-top:2px">'.trim($value->HCDXCOD).'</p>';            
        }
        return $p;
    }
}
