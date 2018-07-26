<?php 

  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->getHeaderSolicitudEntidad($data['entidad'], $data['saldosActuales'][0], $data['solicitudesAtendidas'], $data['generoSolicitud'], @$data['itemExamenes'], $data['fechas']);
  $pdf->Output();