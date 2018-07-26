<?php
  require_once('PDF.php');

  $pdf = new PDF('L');
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 1);
  $pdf->getCategoriaOne($arrayOfResponse, array('data' => array('nombre_categoria' => 'EXAMEN GENERAL DE HECES')), 2);

  // campos normales
  $pdf->Ln(5);
  $count = count($arrayOfResponse['results']);
  $pdf->getCamposNormales($arrayOfResponse['results']);
  $pdf->getMultiplesCampus(
    $arrayOfResponse['multiples'], 
    $arrayOfResponse['categorias'], 
    $arrayOfResponse['results'][$count - 1]
  );
  
  $pdf->footerFirma($arrayOfResponse['CURRENT_DATE']);
  $pdf->output();
  