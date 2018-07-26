<?php 
  require_once("pdf.php");
  $pdf = new PDF('L');

  // Configuracion inicial
  $pdf->AliasNbPages();
  PDF::$TITLE_CATALOGO = "Listado de sucursales";
  PDF::$PRESETATION    = true;

  $pdf->AddPage();
  /**
   * Estructura de la tabla para la presentacion de los productos
   */
  $pdf->structTableSucursales($arrayOfList);
  $pdf->Output();
?>