<?php 
  require_once("pdf.php");

  PDF::$PRESETATION = true;
  PDF::$IMAGESHOW   = false;
  PDF::$PAGNUMSHOW  = false;
  PDF::$TITLE_CATALOGO = 'COMPROBANTE';

  $pdf = new PDF('L', 'mm', array(150, 101));
  $pdf->AddPage();

  $pdf->onComprobanteExamen($data['comprobanteOnExamen']);
  $pdf->Output();
?>