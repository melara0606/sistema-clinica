<?php 

  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->getHeaderSolicitud($data['sucursal'], $data["items"], $data['CountExamen'], $data['countGenero'], $data['type'], $data['fechas']);
  $pdf->getTableExamen($data['ItemsExamen']);
  
  $pdf->Output();