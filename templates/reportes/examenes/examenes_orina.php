<?php
  require_once('PDF.php');

  $pdf = new PDF('L');
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 1);
  $data = $arrayOfResponse['results'][0];
  $pdf->getCategoriaOne($arrayOfResponse, array('data' => array('nombre_categoria' => 'EXAMEN GENERAL DE ORINA')), 2);
  $pdf->Ln(5);

  foreach ($data['response'] as $value) {
    $campus = ($value['id_tipo_catalogo'] == 1) ? 'seleccion_valor' : 'resultado';
    $pdf->Cell(50, $pdf->height, utf8_decode(strtoupper($value['nombre_campo'])), 0,0, 'L');
    $pdf->Cell(2, $pdf->height, ':', 0, 0, 'C');
    $pdf->Cell(45, $pdf->height, utf8_decode(strtoupper($value[$campus])), 0, 0, 'C');
    $pdf->Cell(25, $pdf->height, utf8_decode($value['unidades']), 0, 1, 'C');
  }

  $pdf->footerFirma($arrayOfResponse['CURRENT_DATE']);
  $pdf->output();
  