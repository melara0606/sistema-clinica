<?php 

  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->getHeaderSolicitudPromocionParticular($data['sucursal'], $data['solicitudesAtendidas'], $data['generoSolicitud'], $data['conteoSolicitudes'], @$data['itemExamenes'], $data['fechas']);
  $pdf->Output();