<?php
  require_once('PDF.php');

  $pdf = new PDF('L');
  $pdf->AddPage();
  $pdf->SetAutoPageBreak(true, 1);

  if($arrayOfResponse['view']){
    $isFisrt = true;
    foreach ($arrayOfResponse['results'] as $key => $value) {
      if($isFisrt){
        $pdf->getCategoriaOne($arrayOfResponse, $value);
      }else{
        if(intval($value['data']['is_only'])){
          $pdf->footerFirma($arrayOfResponse['CURRENT_DATE']);
          $pdf->AddPage();
          $pdf->getCategoriaOne($arrayOfResponse, $value);
        }
      }
      foreach ($value['results'] as $item) {
        $pdf->Cell(80, $pdf->height, utf8_decode($item['nombre_campo']), 0, 0, 'L');
        $pdf->Cell(40, $pdf->height, utf8_decode($item['resultado']), 0, 0, 'C'); 
        $pdf->Cell(45, $pdf->height, utf8_decode($item['rango_valor']), 0, 0, 'C'); 
        $pdf->Cell(45, $pdf->height, utf8_decode($item['unidades']), 0, 0, 'C'); 
        $pdf->Cell(0,  $pdf->height, utf8_decode($item['criterio_rango']), 0, 1, 'C');          
      }
      if(intval($value['data']['is_only']) && $isFisrt){
        $pdf->footerFirma($arrayOfResponse['CURRENT_DATE']);
        $pdf->AddPage();
        $pdf->getCategoriaOne($arrayOfResponse, $value);
      }
      $isFisrt = false;
    }
    $pdf->footerFirma($arrayOfResponse['CURRENT_DATE'], $isSave);
  }else{
    $pdf->SetFont('times','B', 14);
    $pdf->Cell(0, 15, 'LO SENTIMOS PERO NO ES LA FORMA CORRECTA DE MOSTRAR LOS RESULTADOS EN ESTA CATEGORIA', 1, 1, 'C');
  }  

  if($isSave) {
    $fileDir = $dir."/send/solicitud/{$fileName}.pdf";
    $pdf->Output($fileDir, 'F');
  }else{
    $pdf->Output();
  }
?>