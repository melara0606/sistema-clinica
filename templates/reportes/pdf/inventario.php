<?php 
  require_once("pdf.php");
  // $pdf = new PDF('L');
  $pdf = new PDF();

  // Configuracion inicial
  $pdf->AliasNbPages();
  PDF::$TITLE_CATALOGO = "Inventario";
  PDF::$PRESETATION    = true;

  $pdf->AddPage();    
  /**
   * Estructura de la tabla para la presentacion de los productos
   */

  $pdf->structTableInventario($objectSucursal, $arrayOfList);

  $pdf->Output();
?>