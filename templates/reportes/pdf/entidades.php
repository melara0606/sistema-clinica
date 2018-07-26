<?php 
  require_once("pdf.php");
  $pdf = new PDF('L');

  // Configuracion inicial
  $pdf->AliasNbPages();
  PDF::$TITLE_CATALOGO = "Listado de Entidades";
  PDF::$PRESETATION    = true;

  $pdf->AddPage();
  $pdf->structTableEntidades($arrayOfList);
  $pdf->Output();
?>