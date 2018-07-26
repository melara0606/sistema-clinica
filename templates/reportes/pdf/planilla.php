
<?php 
    require_once("pdf.php");

    $pdf = new PDF('Landscape', 'mm', 'Letter');
    $pdf->AddPage();
    
    $pdf->headerPlanilla(array(
        "planilla" => $planilla
    ));

    $pdf->Image('public/img/logoreporte.png' , 10, 10, -350);

    $i = 1;
    foreach($empleados as $key => $empleado){
        $pdf->tableEmployeer(array(
            "current" => $i,
            "employee" => $empleado
        ));
        $i++;
    }

    $pdf->footerPlanilla();
    echo $pdf->Output();
?>