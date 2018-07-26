<?php 
  class PDF extends FPDF {
    public $height = 6;

    public function headerExamen($sucursal = array(), $paciente = array())
    {
      $this->Image('public/img/logoreporte.png' , 10, 10, -200);
      
      $this->SetFont('times','B', 18);
      $this->SetTextColor(19, 25, 111);

      $this->Cell(80);
      $this->Cell(100, $this->height + 2, utf8_decode('LABORATORIO CLÍNICO'), 0, 1, 'C');      
      
      $this->Cell(80);
      $this->Cell(100, $this->height + 1,  utf8_decode("M.M FISHER'ST") , 0, 0, 'C');

      $this->SetFont('times','', 10);
      $this->SetXY(180, 10);
      $this->MultiCell(110, $this->height - 2, utf8_decode($sucursal['address_suc']), 0, 'R', 0);

      $this->SetLineWidth(0.5);
      $this->Line(10, 30, 290, 30);

      /* Informacion de los pacientes */
      $this->SetFont('Arial','', 12);
      $this->SetTextColor(0, 0, 0);
      $this->Ln(14);

      $date = date_create($paciente['fecha_creacion']);
      $this->Cell(190, 
        $this->height, 
        'PACIENTE: ' .(utf8_decode(strtoupper($paciente['name_pac']).' '.strtoupper($paciente['lastname_pac']))), 0, 0, 'L');
      
      $this->Cell(45, $this->height, 'EDAD: '.$paciente['edad'].utf8_decode(' AÑOS'), 0, 0, 'R');
      $this->Cell(0, $this->height, 
        'SEXO: '.( $paciente['genero_paciente'] === '1' ? 'MASCULINO' : 'FEMENINO' ), 0, 1, 'R');

      $this->Cell(100, $this->height, 'NUMERO DE CARNET: '.strtoupper($paciente['carnet']), 0, 0, 'L');
      $this->Cell(100, $this->height, 'CODIGO DE MUESTRA: '.strtoupper($paciente['id']), 0, 0, 'C');
      $this->Cell(0, $this->height, 'FECHA DE SOLICITUD: '.(date_format($date, 'd-m-Y')), 0, 1, 'C');
      $this->Line(10, 45, 290, 45);
      $this->Ln(4);
    }

    /* funcion para generar los reportes de categoria 1  */
    private function tableCategoriaOne() {
      $this->SetFont('Arial','B', 10);
      $this->Cell(80, $this->height + 1, 'NOMBRE DE LA PRUEBA', 1, 0, 'L');
      $this->Cell(40, $this->height + 1, 'RESULTADO', 1, 0, 'C'); 
      $this->Cell(45, $this->height + 1, 'VALOR NORMAL', 1, 0, 'C'); 
      $this->Cell(45, $this->height + 1, 'UNIDADES', 1, 0, 'C'); 
      $this->Cell(0,  $this->height + 1, 'CRITERIO', 1, 1, 'C');
    }

    public function footerFirma($fecha = array()){
      
      $this->SetY(-18);
      $this->SetFont('Arial','', 10);
      $date = date_create($fecha[0]['fecha']);
      $this->Cell(160, $this->height, 'FECHA DE IMPRESION: '.(date_format($date, 'd-m-Y')), 0, 0, 'L');
      $this->Cell(0, $this->height, 'F:__________________________________________________________', 0, 1, 'L');
      $this->Cell(160);
      $this->Cell(0, $this->height-1, 'Responsable', 0, 1, 'C');
    }

    public function getCategoriaOne($data = array(), $value = array(), $categoria = 1)
    {
      $this->headerExamen($data['sucursal'], $data['paciente'][0]);
      $this->SetFont('Arial','B', 16);
      $this->Cell(0, $this->height + 2, utf8_decode($value['data']['nombre_categoria']), 0, 1, 'C', 0);
      if($categoria == 1)
        self::tableCategoriaOne();
      
      $this->SetFont('times','', 10);
    }

    public function getBodyBacteriologiaUrologia($array = array())
    {
      $strArrayValue = "";
      $arrayOrderData = self::order($array);
      $arrayOrden = ['SENSIBLES', 'RESISTENTES', 'INTERMEDIOS'];

      foreach($arrayOrden as $key){
        $strArrayValue = "";        
        foreach ($arrayOrderData[$key] as $value) {
          $strArrayValue .= utf8_decode($value['nombre_campo']).": ".$value['resultado']." ".utf8_decode($value['unidades'])." | ";
        }
        if(strcmp($strArrayValue, '') != 0){
          $this->SetFont('Arial','B', 12);
          $this->Cell(0, $this->height, utf8_decode($key), 0, 1, 'L');
          $this->SetFont('times','', 11);
          $this->MultiCell(0, $this->height + 1, ($strArrayValue), 1, 'L');
        }
        $this->Ln($this->height);
      }
    }

    public function getCamposNormales($results = array())
    {
      $width = 40;
      $campus = '';
      $this->Ln(5);
      $i = 0;
      foreach ($results as $value) {        
        if(strcmp(trim($value['nombre_campo']), 'OBSERVACIONES')  == 0){
          continue;
        }

        if($i == 5){
          $this->Ln( $this->height * 1.2 );
          $this->Cell(100, $this->height, 'RESTOS ALIMENTICIOS', 0, 1, 'L' );
        }

        (strcmp( $value['resultado'], '' ) == 0 ) ? $campus = 'seleccion_valor' : $campus = 'resultado';

        $this->Cell($width - 2, $this->height, utf8_decode($value['nombre_campo']), 0, 0, 'L' );
        $this->Cell(2, $this->height, ':', 0, 0, 'C' );
        $this->Cell($width - 5, $this->height, utf8_decode($value[ $campus ]), 0, 0, 'C' );
        $this->Cell($width - 15, $this->height, utf8_decode($value['unidades']), 0, 1, 'C' );
        $i++;
      }
    }

    public function getMultiplesCampus($m = array(), $c = array(), $observacion)
    {
      $array = array();
      $this->SetXY(125, 67);

      $this->SetFont('Arial','B', 9);
      foreach ($c as $value) {
        $this->Cell(51, $this->height, utf8_encode( $value['nombre_categoria_grupo'] ), 1, 0, 'C');
        $array[ $value['nombre_categoria_grupo'] ] = array();
        foreach ($value['seleccion'] as $v) {
          $this->Cell(15, $this->height, utf8_encode( $v['nombre_grupo'] ), 1, 0, 'C');
          array_push($array[ $value['nombre_categoria_grupo'] ], $v['nombre_grupo']);
        }
      }

      $i = 1;
      $Y = 0;
      $X = 125;
      $this->Ln($this->height);
      $this->SetFont('Arial','', 9);
      foreach ($array as $key => $v) {
       foreach ($m[ $key ] as $value) {
        foreach ($value as $item) {
          $this->SetX($X);
          $object = (array) json_decode($item['resultado']);
          $this->Cell(51, $this->height, utf8_decode($item['nombre_campo'] ), 0, 0, 'L' );
          $this->Cell(15, $this->height, $object[@$v[0]], 0, 0, 'C');
          $this->Cell(15, $this->height, $object[@$v[1]], 0, 1, 'C');
        }
       }
       if($i == 1){
          $X = 206;
          $this->SetY(67 + $this->height);
        }else{
          $Y = $this->GetY();
        }
       $i++;
      }
      self::crearTable($Y + 5, $X);
      $this->SetXY(125, $Y + 5);

      $this->Cell(0, $this->height, 'OBSERVACIONES: ', 'LR', 1, 'L' );
      $this->SetXY(125, $Y + 10);
      $this->MultiCell(0, $this->height, utf8_decode($observacion['resultado']), 'LRB', 'J');
    }
    // PRIVATE

    private function crearTable($posY = 0, $posX = 0){
      $this->Line(125, 67, 125, $posY);
      $this->Line(125 + 51, 67, 125 + 51, $posY);
      $this->Line(125 + 66, 67, 125 + 66, $posY);
      $this->Line(125 + 81, 67, 125 + 81, $posY);

      $this->Line(125 + 132, 67, 125 + 132, $posY);
      $this->Line(125 + 147, 67, 125 + 147, $posY);
      $this->Line(125 + 162, 67, 125 + 162, $posY);

      $this->Line(125, $posY, 125 + 162, $posY);

    }

    private function order($a = array()){
      $array = array(
        "SENSIBLES"   => array(),
        "RESISTENTES" => array(),
        "INTERMEDIOS" => array()
      );

      foreach ($a as $value) {
        array_push($array[ $value['seleccion_valor'] ], $value );
      }

      return $array;
    }
  }
?>