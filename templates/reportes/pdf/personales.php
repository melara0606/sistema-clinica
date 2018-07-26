<?php 
  require_once("pdf.php");
  $pdf = new PDF('L');

  // Configuracion inicial
  $pdf->AliasNbPages();
  PDF::$TITLE_CATALOGO = "Listado de Empleados";
  PDF::$PRESETATION    = true;

  $pdf->AddPage();
  /**
   * Estructura de la tabla para la presentacion de los productos
   */
  $pdf->structTableEmpleados($arrayOfList, $sucursal);
  $pdf->Output();
?>