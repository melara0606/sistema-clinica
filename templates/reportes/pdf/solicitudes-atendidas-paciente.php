<?php 

  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();

  $pdf->getHeaderSolicitudPaciente($data['paciente'], $data['solicitudesAtendidas'], @$data['itemExamenes'], $data['fechas']);
  $pdf->Output();