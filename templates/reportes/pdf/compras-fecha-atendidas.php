<?php 

  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->getHeaderComprasPorFecha($data['sucursal'], $data['compras'], $data['fechas']);
  $pdf->Output();