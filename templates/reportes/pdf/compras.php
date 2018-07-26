<?php 
  require_once("pdf.php");

  $pdf = new PDF();
  $pdf->AddPage();
  $pdf->AliasNbPages();
  
  $pdf->headerCompra($data["compra"]);
  $pdf->Image('public/img/logoreporte.png' , 10, 10, -350);
  $pdf->bodyCompra($data["items"], $data['compra']['estado']);
  

  if($isSave) {
    $fileDir = $dir."/send/{$fileName}.pdf";
    $pdf->Output($fileDir, 'F');
  }else{
    $pdf->Output();
  }  
?>