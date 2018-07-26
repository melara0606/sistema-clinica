<?php 
  require_once("pdf.php");

  PDF::$PRESETATION = true;
  PDF::$PAGNUMSHOW  = false;
  PDF::$TITLE_CATALOGO = 'Reporte de permisos de empleados';

  $pdf = new PDF('L');
  $pdf->AddPage();

  $pdf->onComprobantePermisos($data['sucursal'], $data['permisosOnIncapacidades'], $data['rangoFechas']);
  $pdf->Output();
?>