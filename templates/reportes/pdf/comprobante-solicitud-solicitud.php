<?php 
  require_once("pdf.php");

  PDF::$PRESETATION = true;
  PDF::$IMAGESHOW   = false;
  PDF::$PAGNUMSHOW  = false;
  PDF::$TITLE_CATALOGO = 'COMPROBANTE DE SOLICITUD ADOMICILIO';

  $pdf = new PDF('L', 'mm', array(150, 95));
  $pdf->AddPage();

  $pdf->onComprobanteSolicitud($data['comprobanteOnSolicitud']);
  $pdf->Output();
?>