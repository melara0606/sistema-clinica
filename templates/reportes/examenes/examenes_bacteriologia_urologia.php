<?php
  require_once('PDF.php');

  $pdf = new PDF('L');
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 1);
  $pdf->getCategoriaOne($arrayOfResponse, array('data' => array('nombre_categoria' => 'EXAMEN DE BACTERIOLOGIA/UROCULTIVO')), 2);

  foreach ($arrayOfResponse['results']['valor'] as $value) {
    $campus = trim($value['resultado']) === '' ? 'seleccion_valor' : 'resultado';
    $pdf->Cell(45, $pdf->height, utf8_encode($value['nombre_campo']).": ", 0, 0, 'L', 0);
    $pdf->SetFont('times','B', 10);
    $pdf->Cell(80, $pdf->height, utf8_encode($value[$campus]), 0, 0, 'L', 0);
    $pdf->SetFont('times','', 10);
  }
  $pdf->Ln($pdf->height);
  $pdf->Cell(0, $pdf->height, 'RESULTADO', 0, 1, 'L');
  $pdf->Ln($pdf->height);

  /* Logica para la presentacion de los resultados */
  $pdf->getBodyBacteriologiaUrologia($arrayOfResponse['results']['grupos_seleccion']);

  $pdf->footerFirma($arrayOfResponse['CURRENT_DATE'], isset($isSave));
  
  if(isset($isSave)) {
    $fileDir = $dir."/send/solicitud/{$fileName}.pdf";
    $pdf->Output($fileDir, 'F');
  }else{
    $pdf->Output();
  }  