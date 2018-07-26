<?php 
  class PDF extends FPDF {

    public static $IMAGESHOW        = TRUE;
    public static $PAGNUMSHOW       = TRUE;
    public static $PRESETATION      = false;
    public static $TITLE_CATALOGO   = "BLANCO";

    public $height = 6;
    public $months  = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

    function tableEmployeer($object = array()){

      $sum = floatval($object["employee"]["isss"] + $object["employee"]["afp"] + $object["employee"]["renta"]);

      $this->SetFont('Arial','', 9);
      $this->SetFillColor(200, 200, 200);
      $this->Cell(10, $this->height + 1, $object["current"], 1, 0, 'C');
      $this->Cell(70, $this->height + 1, utf8_decode($object["employee"]["lastname_emp"]." ".$object["employee"]["name_emp"]), 1, 0, 'L');
      $this->Cell(20, $this->height + 1, "$ ".number_format($object["employee"]["salary_emp"], 2), 1, 0, 'C', 0);
      $this->Cell(15, $this->height + 1, "$ ".number_format($object["employee"]["isss"], 2), 1, 0, 'C', 0);
      $this->Cell(15, $this->height + 1, "$ ".number_format($object["employee"]["afp"], 2), 1, 0, 'C', 0);
      $this->Cell(15, $this->height + 1, "$ ".number_format($object["employee"]["renta"], 2), 1, 0, 'C', 0);      
      $this->Cell(25, $this->height + 1, "$ ".number_format($sum, 2), 1, 0, 'C', 0);
      $this->Cell(25, $this->height + 1, "$ ".number_format($object["employee"]["otros"], 2), 1, 0, 'C', 0);
      $this->Cell(20, $this->height + 1, "$ ".number_format(( floatval($object["employee"]["salary_emp"]) - ($sum + floatval($object["employee"]["otros"]))), 2), 1, 0, 'C', 0);
      $this->Cell(0, $this->height  + 1, "", 1, 1, 'C', 0);
    }

    function footerPlanilla(){

      $this->Ln(15);
      $this->SetFont('Courier','', 12);
      
      $this->Cell(36, $this->height, "Generado por: ", 0, 0, 'L');
      $this->Cell(80, $this->height, "", 'B', 0, 'L');

      $this->Cell(24, $this->height, "Firma:", 0, 0, 'C');
      $this->Cell(70, $this->height, "", 'B', 0, 'L');

      $this->Cell(0, $this->height, "Sello", 0, 0, 'C');

    }

    function header(){
      if(self::$IMAGESHOW){
        $this->Image('public/img/logoreporte.png' , 10, 10, -350);
      }
      
      $this->SetFont('times','B', 16);
      $this->SetTextColor(19,25,111);
      $this->Cell(0, $this->height + 2, utf8_decode('LABORATORIO CLÍNICO "M.M FISHER\'ST"'), 0, 1, 'C');      
      
      if(self::$PRESETATION){
        $this->SetFont('Arial','B', 15);
        $this->Cell(0, $this->height + 1,  utf8_decode(self::$TITLE_CATALOGO) , 0, 1, 'C');        
      }
      $this->Ln(8);
    }

    public function Footer(){
      if(self::$PAGNUMSHOW){
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
      }
    }

    function headerPlanilla($object = array()){
        $month = intval(substr($object["planilla"]["mes_ayor"], 0, 2));
        $year  = intval(substr($object["planilla"]["fecha_creacion"], 0, 4));
        
        $this->SetFont('times','', 10);
        $this->Cell(0, $this->height, utf8_decode('Planilla de pagos al mes de '.$this->months[$month - 1].' del año '.$year), 0, 1, 'C');

        $this->SetTextColor(0,0,0);
        $this->Cell(0, $this->height + 2, '__________________________________________________________________________________________________________', 0, 1, 'C');
        
        $this->Ln(6);
        $this->SetFont('Arial','B', 10);
        $this->SetFillColor(190,209,223);
        $this->Cell(10, $this->height * 2 + 2, "#", 1, 0, 'C', 1);
        $this->Cell(70, $this->height * 2 + 2, "Nombres del empleado", 1, 0, 'C', 1);
        $this->Cell(20, $this->height * 2 + 2, "Sueldo", 1, 0, 'C', 1);
        $getX = $this->GetX();        
        $this->Cell(45, $this->height + 1, "Deducciones", 1, 0, 'C', 1);        
        $this->Cell(25, $this->height * 2 + 2, "Deducciones", 1, 0, 'C', 1);
        $this->Cell(25, $this->height * 2 + 2, "Otros Desc.", 1, 0, 'C', 1);
        $this->Cell(20, $this->height * 2 + 2, "A Pagar", 1, 0, 'C', 1);
        $this->Cell(0, $this->height * 2 + 2, "Firma", 1, 0, 'C', 1);

        $this->SetXY($getX, $this->GetY() + $this->height + 1);
        $this->Cell(15, $this->height + 1, "ISSS", 1, 0, 'C', 1);
        $this->Cell(15, $this->height + 1, "AFP", 1, 0, 'C', 1);
        $this->Cell(15, $this->height + 1, "Renta", 1, 1, 'C', 1);
    }
    /**
     * header for inventario
     */

    public function structTableInventario($object, $arrayOfList = array()) {
      $this->Ln(4);
      $this->SetFont('Arial','', 12);        
      $this->Cell(35, $this->height + 1, "Sucursal ", 0, 0, 'R');
      $this->Cell(0, $this->height + 1, utf8_decode($object["nombre_sucursal"]) , "B", 1, 'L');

      $this->Ln(10);
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 6, "No", 1, 0, 'C', 1);
      $this->Cell(90, $this->height + 6, "Nombre del material", 1, 0, 'L', 1);
      $this->Cell(40,  $this->height + 6, "Existencias", 1, 0, 'C', 1);
      $this->Cell(42,  $this->height + 6, "Tipo de producto", 1, 1, 'C', 1);
      
      $i = 0;
      $this->SetTextColor(0);
      foreach ($arrayOfList as $value) {
        $this->Cell(15,  $this->height + 3, ($i + 1) , 1, 0, 'C');
        $this->Cell(90, $this->height + 3, utf8_decode($value['nombre_material']) , 1, 0, 'L');
        $this->Cell(40,  $this->height + 3, utf8_decode($value['existencia']), 1, 0, 'C');
        $this->Cell(42,  $this->height + 3, utf8_decode($value["nombre_catalogo"]) , 1, 1, 'C');
        $i++;
      }

      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no tienes materiales en tu inventario", 1, 0,  'C');
      }
    }

    public function structTableDoctores($arrayOfList = array()) {
      $this->Ln(4);
      /* Estructura de la tabla */
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 6, "No", 1, 0, 'C', 1);
      $this->Cell(80, $this->height + 6, "Nombre completo", 1, 0, 'L', 1);
      $this->Cell(28,  $this->height + 6, "Codigo/JVPM", 1, 0, 'C', 1);
      $this->Cell(75,  $this->height + 6, "E-mail", 1, 0, 'C', 1);
      $this->Cell(32,  $this->height + 6, "NIT", 1, 0, 'C', 1);
      $this->Cell(22,  $this->height + 6, "NRC", 1, 0, 'C', 1);
      $this->Cell(22,  $this->height + 6, "Celular", 1, 1, 'C', 1);
      
      /**/
      $i = 0;
      $this->SetTextColor(0);
      $this->SetFont('Arial','', 10);
      foreach ($arrayOfList as $value) {
        $this->Cell(15,  $this->height + 2, ($i + 1) , 1, 0, 'C');
        $this->Cell(80, $this->height + 2, 
          utf8_decode(($value['name_doc'].", " .$value['lastname_doc'])), 1, 0, 'L');
        $this->Cell(28,  $this->height + 2, utf8_decode($value['jvpm_doc']), 1, 0, 'C');
        $this->Cell(75,  $this->height + 2, utf8_decode($value['email']), 1, 0, 'C');
        $this->Cell(32,  $this->height + 2, utf8_decode($value['nit']), 1, 0, 'C');
        $this->Cell(22,  $this->height + 2, utf8_decode($value['nrc']), 1, 0, 'C');
        $this->Cell(22,  $this->height + 2, utf8_decode($value['phone_doc']), 1, 1, 'C');
        $i++;
      }

      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no tienes materiales en tu inventario", 1, 0,  'C');
      }
    }

    public function structTableSucursales($arrayOfList = array()) {
      $this->Ln(4);
      /* Estructura de la tabla */
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 6, "No", 1, 0, 'C', 1);
      $this->Cell(140, $this->height + 6, "Nombre", 1, 0, 'L', 1);
      $this->Cell(75,  $this->height + 6, "E-mail", 1, 0, 'C', 1);
      $this->Cell(32,  $this->height + 6, "NIT", 1, 0, 'C', 1);
      $this->Cell(22,  $this->height + 6, "Celular", 1, 1, 'C', 1);
      
      
      $i = 0;
      $this->SetTextColor(0);
      $this->SetFont('Arial','', 10);
      foreach ($arrayOfList as $value) {
        $this->Cell(15,  $this->height + 2, ($i + 1) , 1, 0, 'C');
        $this->Cell(125, $this->height + 2,  utf8_decode($value['nombre_sucursal']), 1, 0, 'L');
        $this->Cell(80,  $this->height + 2, utf8_decode($value['email_suc']), 1, 0, 'C');
        $this->Cell(42,  $this->height + 2, utf8_decode($value['nit_suc']), 1, 0, 'C');
        $this->Cell(22,  $this->height + 2, utf8_decode($value['phone_suc']), 1, 1, 'C');
        $i++;
      }

      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no tienes materiales en tu inventario", 1, 0,  'C');
      }
    }

    public function structTableProveedores($arrayOfList = array()) {
      $this->Ln(4);
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 6, "No", 1, 0, 'C', 1);
      $this->Cell(90,  $this->height + 6, "Nombre", 1, 0, 'L', 1);
      $this->Cell(40,  $this->height + 6, "NIT", 1, 0, 'C', 1);
      $this->Cell(30,  $this->height + 6, "NRC", 1, 0, 'C', 1);
      $this->Cell(70,  $this->height + 6, "Representante", 0, 0, 'C', 1);
      $this->Cell(25,  $this->height + 6, "Celular", 1, 1, 'C', 1);
      
      $i = 0;
      $this->SetTextColor(0);
      $this->SetFont('Arial','', 10);
      foreach ($arrayOfList as $value) {
        $this->Cell(15,  $this->height + 2, ($i + 1) , 1, 0, 'C');
        $this->Cell(90, $this->height + 2, utf8_decode($value['nombre_proveedor']), 1, 0, 'L');
        $this->Cell(40,  $this->height + 2, utf8_decode($value['nit_proveedor']), 1, 0, 'C');
        $this->Cell(30,  $this->height + 2, utf8_decode($value['nrc_proveedor']), 1, 0, 'C');
        $this->Cell(70,  $this->height + 2, utf8_decode($value['representante_proveedor']), 1, 0, 'L');
        $this->Cell(25,  $this->height + 2, utf8_decode($value['telefono_respresentante']), 1, 1, 'C');
        $i++;
      }

      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no tienes materiales en tu inventario", 1, 0,  'C');
      }/**/
    }

    public function structTableEmpleados($arrayOfList = array(), $sucursal = null) {
      $this->Cell(0, $this->height + 2, utf8_decode($sucursal['nombre_sucursal']), 'B', 1, 'C');

      $this->Ln(8);
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 4, "No", 1, 0, 'C', 1);
      $this->Cell(70,  $this->height + 4, "Nombre", 1, 0, 'L', 1);
      $this->Cell(30,  $this->height + 4, "Telefono", 1, 0, 'C', 1);
      $this->Cell(30,  $this->height + 4, "Cargo", 1, 0, 'C', 1);
      $this->Cell(25,  $this->height + 4, "Salario", 1, 0, 'C', 1);
      $this->Cell(40,  $this->height + 4, "Tipo de Contrato", 1, 0, 'C', 1);
      $this->Cell(0,   $this->height + 4, "Usuario", 1, 1, 'C', 1);
      
      
      $i = 0;
      $this->SetTextColor(0);
      $this->SetFont('Arial','', 11);
      $cargo = '';
      foreach ($arrayOfList as $value) {
        $cargo = self::strlenCargo($value['cargo_emp']);
        $this->Cell(15,  $this->height + 2, ($i + 1) , 1, 0, 'C');
        $this->Cell(70,  $this->height + 2, utf8_decode($value['name_emp'].' '.$value['lastname_emp']), 1, 0, 'L');
        $this->Cell(30,  $this->height + 2, utf8_decode($value['phone_emp']), 1, 0, 'C');
        $this->SetFont('Arial','', 8);
        $this->Cell(30,  $this->height + 2, utf8_decode($cargo), 1, 0, 'C');
        $this->SetFont('Arial','', 11);
        $this->Cell(25,  $this->height + 2, utf8_decode($value['salary_emp']), 1, 0, 'C');
        $this->Cell(40,  $this->height + 2, $value['tipo_contratacion'] == 1 ? 'Fijo': 'Contrato', 1, 0, 'C');
        $this->Cell(0,   $this->height + 2, utf8_decode($value['email']), 1, 1, 'C');
        $i++;
      }
      
      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no hay empleados en planilla", 1, 0,  'C');
      }
    }

    private function strlenCargo($str = '') {
      if(strlen($str) > 20)
        return substr($str, 0, 20);
      return $str;
    }

    public function structTableEntidades($arrayOfList = array(), $sucursal = null) {
      $this->Cell(0, $this->height + 2, utf8_decode($sucursal['nombre_sucursal']), 'B', 1, 'C');

      $this->Ln(8);
      $this->SetFillColor(0,0,100); 
      $this->SetTextColor(255,255,255);
      
      $this->SetFont('Arial','', 12); 
      $this->Cell(15,  $this->height + 4, "No", 1, 0, 'C', 1);
      $this->Cell(70,  $this->height + 4, "Nombre", 1, 0, 'L', 1);
      $this->Cell(35,  $this->height + 4, "NIT", 1, 0, 'C', 1);
      $this->Cell(35,  $this->height + 4, "NRC", 1, 0, 'C', 1);
      $this->Cell(25,  $this->height + 4, "Telefono", 1, 0, 'C', 1);
      $this->Cell(70,  $this->height + 4, "Representante", 1, 0, 'C', 1);
      $this->Cell(0,  $this->height + 4, "Monto", 1, 1, 'C', 1);
      
      
      $i = 0;
      $this->SetTextColor(0);
      $this->SetFont('Arial','', 11);
      foreach ($arrayOfList as $value) {
        $this->Cell(15,  $this->height + 2, ($i + 1) , 1, 0, 'C');
        $this->Cell(70,  $this->height + 2, utf8_decode($value['name_ext']), 1, 0, 'L');
        $this->Cell(35,  $this->height + 2, utf8_decode($value['nit_entidad']), 1, 0, 'C');
        $this->Cell(35,  $this->height + 2, utf8_decode($value['nrc_entidad']), 1, 0, 'C');
        $this->Cell(25,  $this->height + 2, utf8_decode($value['phone_ext']), 1, 0, 'C');
        $this->Cell(70,  $this->height + 2, utf8_decode($value['represent_ext']), 1, 0, 'L');
        $this->Cell(0,   $this->height + 2, "$ ".number_format($value['monto'], 2), 1, 1, 'C');
        $i++;
      }

      if(count($arrayOfList) == 0){
        $this->Cell(0, $this->height + 6, "Por el momento no hay Entidades", 1, 0,  'C');
      }
    }
    
    /************************************** FUNCIONES PARA REPORTES DE COMPRAS*****************************************/
    function headerCompra($array = array()) {      
      $date = date_create($array["fecha_compra"]);

      $this->Ln(5);
      $this->SetFont('Arial','B', 14);      
      $this->Cell(0, $this->height + 1, "Orden de compra", 0, 1, 'C');

      // Detalle
      $this->Ln(8);
      $this->SetFont('Arial','', 10);  
      
      $this->Cell(35, $this->height + 1, "Sucursal ", 0, 0, 'L');
      $this->Cell(0, $this->height + 1, utf8_decode($array["nombre_sucursal"]) , "B", 1, 'L');

      $this->Cell(35, $this->height + 1, "Proveedor ", 0, 0, 'L');
      $this->Cell(0, $this->height + 1, utf8_decode($array["nombre_proveedor"]) , "B", 1, 'L');

      $this->Cell(35, $this->height + 1, "Fecha ", 0, 0, 'L');
      $this->Cell(35, $this->height + 1, date_format($date, "d/m/Y"), "B", 0, 'C');

      $this->Cell(35, $this->height + 1, "Total ", 0, 0, 'C');
      $this->Cell(35, $this->height + 1, number_format($array["total_compra"], 2) ." $" , "B", 0, 'C');

      $this->Cell(0, $this->height + 1, ($array["estado"] == 0 ? "Entregado" : "No Entregado" ) , 0, 1, 'C');
    }

    function onRepetir($array = array(), $isStatus = 0) {
      $this->Cell(10, $this->height + 1, "#", 1, 0, 'C');
      $this->Cell(100, $this->height + 1, "Material", 1, 0, 'C');
      $this->Cell(25, $this->height + 1, "Cantidad", 1, 0, 'C');
      $this->Cell(25, $this->height + 1, "Precio", 1, 0, 'C');
      $this->Cell(30, $this->height + 1, "Total", 1, 1, 'C');

      $this->SetFont('Arial','', 10);
      $total = 0.00;
      $isEstado = null;
      
      foreach($array as $key => $value ){
        $total = floatval($value["precio"]) * intval($value['cantidad']);

        $this->Cell(10, $this->height - 1, ( $key + 1 ), 1, 0, 'C');
        $this->Cell(100, $this->height - 1, utf8_decode( $value["nombre_material"] ), 1, 0, 'C');
        $this->Cell(25, $this->height - 1, $value['cantidad'], 1, 0, 'C');
        $this->Cell(25, $this->height - 1, number_format($value["precio"], 2) ." $", 1, 0, 'C');
        $this->Cell(30, $this->height - 1, number_format($total, 2) ." $", 1, 1, 'C');
      }

      $this->Ln(15);
    }

    function bodyCompra($array = array(), $isStatus = 0){      
      $this->Ln(12);

      foreach( $array as $key => $value ){
        if(count($value) > 0){
          $this->SetFont('Arial','B', 14);
          if(strcmp($key, 'isEntregado') === 0){
            $this->Cell(0, $this->height + 1, "Productos Entregados", 0, 1, 'C');
          }
          else{
            if(!$isStatus)
              $this->Cell(0, $this->height + 1, "Productos No entregados" , 0, 1, 'C');
            else
              $this->Cell(0, $this->height + 1,"Listado de productos", 0, 1, 'C');
          }

          $this->Ln(1);        
          $this->SetFont('Arial','', 11);
          $this->onRepetir($value, $isStatus);
        }
      }      
    }


    /*
      Function for reporte de solicitud
    */
   
   public function getHeaderSolicitud($sucursal = array(), $items = array(), $countExamen, $countGenero = array(), $type = '', $fechas = array())
   {

      $this->Cell(0, $this->height + 1, 'SOLICITUDES ATENDIDAS', 0, 1, 'C');
      $this->SetFont('Arial','', 12);
      $this->Cell(0, $this->height - 1, $fechas['be'].'/'.$fechas['end'], 0, 1, 'C');

      $this->Ln(6);
      $this->SetFont('Arial','', 11);
      $this->Cell(20, $this->height, 'Sucursal: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($sucursal['nombre_sucursal']), 0, 0, 'L');

      $this->Cell(20, $this->height, 'Telefono: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($sucursal['phone_suc']), 0, 1, 'L');

      $this->Cell(20, $this->height, 'Direccion: ', 0, 0, 'L');
      $this->MultiCell(0, $this->height - 2, utf8_decode($sucursal['address_suc']), 0, 'L');
      
      if($items){
        $typeResponse = "PROMOCION";
        if($type == 2){
          $typeResponse = "PARTICULAR";
        }else if($type == 3){
          $typeResponse = "ENTIDAD";
        }

        $this->Ln(6);
        $this->SetFont('Arial','', 11);
        $this->Cell(20, $this->height, 'Tipo: ', 0, 0, 'L');
        $this->Cell(120, $this->height, utf8_decode($typeResponse), 0, 1, 'L');

        $this->Cell(40, $this->height, 'Total de Solicitudes: ', 0, 0, 'L');
        $this->Cell(100, $this->height, count($items), 0, 0, 'L');      
      }

      if($countGenero || $countExamen){
        $this->Cell(20, $this->height, 'Masculino:', 0, 0, 'L');
        $this->Cell(0, $this->height, @$countGenero["1"] ? $countGenero["1"] : "0", 0, 1, 'L');

        $this->Cell(40, $this->height, 'Total de Examenes: ', 0, 0, 'L');
        $this->Cell(100, $this->height, @$countExamen['total'], 0, 0, 'L');

        $this->Cell(20, $this->height, 'Femenino:', 0, 0, 'L');
        $this->Cell(0, $this->height, @$countGenero["2"] ? $countGenero["2"] : "0", 0, 1, 'L');
        
      }
   }

   public function getHeaderSolicitudEntidad($entidad = array(), $saldosActuales = array(), $solicitudesAtendidas = array(), $generoSolicitud = array(), $itemExamenes = array(), $fechas = array())
   {
      $this->Ln(4);
      $this->Cell(0, $this->height + 1, 'Reporte de Movimientos Financieros por Instituciones Afiliadas', 0, 1, 'C');
      $this->SetFont('Arial','', 12);
      $this->Cell(0, $this->height, 
        "Fechas: ".date("d/m/Y", strtotime($fechas['be'])). " a ".date("d/m/Y", strtotime($fechas['end'])), 
      0, 1, 'C');

      $this->Ln(6);
      $this->SetFont('Arial','', 11);
      $this->Cell(45, $this->height, 'Afiliado: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($entidad['name_ext']), 0, 1, 'L');
      
      $this->Cell(45, $this->height, 'Telefono: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($entidad['phone_ext']), 0, 1, 'L');

      $this->Cell(45, $this->height, 'Contacto inmediato: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($entidad['represent_ext']), 0, 1, 'L');

      $this->Cell(45, $this->height, 'Direccion: ', 0, 1, 'L');
      $this->MultiCell(0, $this->height, utf8_decode($entidad['address_ext']), 0, 'L');

      // Reporte de fechas de montos
      $this->Ln(10);
      $this->Cell(45, $this->height, 'Ultimo Deposito:', 0, 0, 'L');
      $this->Cell(40, $this->height, "$ ".number_format($saldosActuales['ultimo_monto'], 2), 0, 0, 'L');

      $this->Cell(35, $this->height, 'Fecha de Ingreso: ', 0, 0, 'L');
      $this->Cell(0, $this->height, date("d/m/Y", strtotime($saldosActuales['fecha_ingreso'])), 0, 1, 'L');

      $this->Cell(64, $this->height, 'Saldo disponible a la fecha actual :', 0, 0, 'L');
      $this->Cell(40, $this->height, "$ ".number_format($saldosActuales['monto'], 2), 0, 0, 'L');


      $this->Ln(15);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Solicitudes Atendidas', 0, 1, 'L');
      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(40, $this->height + 3, 'Codigo solicitud', 1, 0, 'C');
      $this->Cell(85, $this->height + 3, 'Nombre Paciente', 1, 0, 'C');
      $this->Cell(25, $this->height + 3, 'Fecha', 1, 0, 'C');
      $this->Cell(20, $this->height + 3, 'Total', 1, 1, 'C');

      $i = 0;
      $total = 0.0;
      $this->SetFont('Arial','', 11);
      if(count($solicitudesAtendidas) > 0){
        foreach ($solicitudesAtendidas as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(40, $this->height, $value['id'], 1, 0, 'C');
          $this->Cell(85, $this->height, $value['name_pac']." ".$value['lastname_pac'], 1, 0, 'L');
          $this->Cell(25, $this->height, date("d/m/Y", strtotime($value['fecha_creacion'])), 1, 0, 'C');
          $this->Cell(20, $this->height, '$ '.number_format($value['monto'], 2), 1, 1, 'C');
          $total += floatval($value['monto']);
        }

        $this->SetFont('Arial','B', 13);
        $this->Cell(145, $this->height + 2, 'TOTAL  ', 1, 0, 'R');
        $this->Cell(0, $this->height + 2, '$ '.number_format($total, 2), 1, 1, 'C');
        
        $this->SetFont('Arial','', 11);
        $this->Ln(4);
        $this->Cell(30, $this->height, 'Total Hombres: ', 0, 0, 'L');
        $this->Cell(90, $this->height, @$generoSolicitud['1'] ? @$generoSolicitud['1'] : '0' , 0, 0, 'L');

        $this->Cell(30, $this->height, 'Total Mujeres: ', 0, 0, 'L');
        $this->Cell(0, $this->height, @$generoSolicitud['2'] ? @$generoSolicitud['2'] : '0', 0, 1, 'L');
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }

      $this->Ln(12);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Total por examenes', 0, 1, 'L');
      
      $this->Ln(2);
      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(140, $this->height + 3, 'Nombre del Examen', 1, 0, 'L');
      $this->Cell(0, $this->height + 3, 'Conteo', 1, 1, 'C');
      $this->SetFont('Arial','', 11);

      if(@count($itemExamenes) > 0){
        $i = 0;
        foreach ($itemExamenes as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(140, $this->height, $value['nombre_examen'], 1, 0, 'L');
          $this->Cell(0, $this->height, $value['conteo'], 1, 1, 'C');
        }
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }
   }

   public function getHeaderSolicitudPromocionParticular($sucursal = array(), $solicitudesAtendidas = array(), $generoSolicitud = array(), $conteoSolicitudes = array(), $itemExamenes = array(), $fechas = array())
   {
      $this->Cell(0, $this->height + 1, 'Reporte de Ingresos del Laboratorio por Examenes Realizados', 0, 1, 'C');
      $this->SetFont('Arial','', 12);
      $this->Cell(0, $this->height, 
        "Fechas: ".date("d/m/Y", strtotime($fechas['be'])). " a ".date("d/m/Y", strtotime($fechas['end'])), 
      0, 1, 'C');

      $this->Ln(6);
      $this->SetFont('Arial','', 11);
      $this->Cell(45, $this->height, 'Sucursal: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($sucursal['nombre_sucursal']), 0, 1, 'L');
      
      $this->Cell(45, $this->height, 'Telefono: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($sucursal['phone_suc']), 0, 1, 'L');

      $this->Cell(45, $this->height, 'Direccion: ', 0, 1, 'L');
      $this->MultiCell(0, $this->height - 2, utf8_decode($sucursal['address_suc']), 0,'L');

      $this->Ln(10);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Solicitudes Atendidas', 0, 1, 'L');
      $this->SetFont('Arial','B', 12);
      $this->Cell(10, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(80, $this->height + 3, 'Nombre del paciente', 1, 0, 'L');
      $this->Cell(40, $this->height + 3, 'Codigo solicitud', 1, 0, 'C');
      $this->Cell(35, $this->height + 3, 'Fecha', 1, 0, 'C');
      $this->Cell(0, $this->height + 3, 'Total', 1, 1, 'C');

      $i = 0;
      $this->SetFont('Arial','', 11);
      $subTotal = 0.00;
      if(count($solicitudesAtendidas) > 0){
        foreach ($solicitudesAtendidas as $value) {
          $this->Cell(10, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(80, $this->height, utf8_decode($value['name_pac'].' '.$value['lastname_pac']), 1, 0, 'L');
          $this->Cell(40, $this->height, $value['id'], 1, 0, 'C');
          $this->Cell(35, $this->height, date("d/m/Y", strtotime($value['fecha_creacion'])), 1, 0, 'C');
          $this->Cell(0, $this->height, '$ '.number_format($value['monto'], 2), 1, 1, 'C');
          $subTotal += $value['monto'];
        }
        $this->Ln(4);
        $this->Cell(30, $this->height, 'Total Hombres: ', 0, 0, 'L');
        $this->Cell(90, $this->height, @$generoSolicitud['1'] ? @$generoSolicitud['1'] : '0' , 0, 0, 'L');

        $this->Cell(30, $this->height, 'Total Mujeres: ', 0, 0, 'L');
        $this->Cell(0, $this->height, @$generoSolicitud['2'] ? @$generoSolicitud['2'] : '0', 0, 1, 'L');

        $this->Cell(30, $this->height, 'Promociones: ', 0, 0, 'L');
        $this->Cell(90, $this->height, @$conteoSolicitudes['1'] ? @$conteoSolicitudes['1'] : '0' , 0, 0, 'L');

        $this->Cell(30, $this->height, 'Particular: ', 0, 0, 'L');
        $this->Cell(0, $this->height, @$conteoSolicitudes['2'] ? @$conteoSolicitudes['2'] : '0', 0, 1, 'L');

        $this->Ln(8);
        $this->SetFont('Arial','B', 13);
        $this->Cell(60, $this->height, 'Ingresos Totales: $', 0, 0, 'L');
        $this->Cell(0, $this->height, number_format($subTotal, 2), 0, 1, 'L');
        $this->SetFont('Arial','', 11);
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }

      $this->Ln(12);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Total por examenes', 0, 1, 'L');
      
      $this->Ln(2);
      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(140, $this->height + 3, 'Nombre del Examen', 1, 0, 'L');
      $this->Cell(0, $this->height + 3, 'Conteo', 1, 1, 'C');
      $this->SetFont('Arial','', 11);

      if(count($itemExamenes) > 0){
        $i = 0;
        foreach ($itemExamenes as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(140, $this->height, utf8_decode($value['nombre_examen']), 1, 0, 'L');
          $this->Cell(0, $this->height, $value['conteo'], 1, 1, 'C');
        }
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }
   }

   public function getHeaderComprasPorFecha($sucursal = array(), $compras = array(), $fechas = array())
   {
      $this->Cell(0, $this->height + 1, 'Compras realizadas', 0, 1, 'C');
      $this->SetFont('Arial','', 12);
      $this->Cell(0, $this->height, 
        "Fechas: ".date("d/m/Y", strtotime($fechas['be'])). " a ".date("d/m/Y", strtotime($fechas['end'])), 
      0, 1, 'C');

      $this->Ln(6);
      $this->SetFont('Arial','', 11);
      $this->Cell(45, $this->height, 'Sucursal: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($sucursal['nombre_sucursal']), 0, 1, 'L');
      
      $this->Cell(45, $this->height, 'Telefono: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($sucursal['phone_suc']), 0, 1, 'L');

      $this->Cell(45, $this->height, 'Direccion: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($sucursal['address_suc']), 0, 1, 'L');

      $this->Ln(10);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Compras Realizadas', 0, 1, 'L');
      $this->SetFont('Arial','B', 12);

      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(40, $this->height + 3, 'Fecha', 1, 0, 'C');
      $this->Cell(100, $this->height + 3, 'Proveedor', 1, 0, 'C');
      $this->Cell(30, $this->height + 3, 'Total', 1, 1, 'C');

      $i = 0;
      $subTotal = 0.00;
      $this->SetFont('Arial','', 11);
      if(count($compras) > 0){
        foreach ($compras as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(40, $this->height, date("d/m/Y", strtotime($value['fecha_compra'])), 1, 0, 'C');
          $this->Cell(100, $this->height, $value['nombre_proveedor'], 1, 0, 'C');
          $this->Cell(30, $this->height, "$ ".number_format($value['total_compra'], 2), 1, 1, 'C');
          $subTotal += $value['total_compra'];
        }

        $this->Ln(8);
        $this->SetFont('Arial','B', 13);
        $this->Cell(60, $this->height, 'Ingresos Totales: $', 0, 0, 'L');
        $this->Cell(0, $this->height, number_format($subTotal, 2), 0, 1, 'L');
        $this->SetFont('Arial','', 11);
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay compras realizadas', 1, 0, 'C');
      }
   }

   public function getHeaderSolicitudPaciente($paciente = array(), $solicitudesAtendidas = array(), $itemExamenes = array(), $fechas = array())
   {
      $this->Ln(1);
      $this->Cell(0, $this->height + 1, 'Historial de Examenes por paciente', 0, 1, 'C');
      $this->SetFont('Arial','', 12);
      $this->Cell(0, $this->height, 
        "Fechas: ".date("d/m/Y", strtotime($fechas['be'])). " a ".date("d/m/Y", strtotime($fechas['end'])), 
      0, 1, 'C');

      $this->Ln(6);
      $this->SetFont('Arial','', 11);
      $this->Cell(45, $this->height, 'Paciente: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($paciente['name_pac'])." ".utf8_decode($paciente['lastname_pac']), 0, 1, 'L');
      
      $this->Cell(45, $this->height, 'Telefono: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($paciente['telefono']), 0, 1, 'L');

      $this->Cell(45, $this->height, 'Direccion: ', 0, 1, 'L');
      $this->MultiCell(0, $this->height, utf8_decode($paciente['address_pac']), 0, 'L');

      $this->Ln(10);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Solicitudes Atendidas', 0, 1, 'L');
      
      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(90, $this->height + 3, 'Codigo solicitud', 1, 0, 'C');
      $this->Cell(40, $this->height + 3, 'Fecha', 1, 0, 'C');
      $this->Cell(40, $this->height + 3, 'Total', 1, 1, 'C');

      $i = 0;
      $this->SetFont('Arial','', 11);
      $subTotal = 0.00;
      if(count($solicitudesAtendidas) > 0){
        foreach ($solicitudesAtendidas as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(90, $this->height, $value['id'], 1, 0, 'C');
          $this->Cell(40, $this->height, date("d/m/Y", strtotime($value['fecha_creacion'])), 1, 0, 'C');
          $this->Cell(40, $this->height, '$ '.number_format($value['monto'], 2), 1, 1, 'C');
          $subTotal += $value['monto'];
        }
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }

      $this->Ln(12);
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, 'Total por examenes', 0, 1, 'L');
      
      $this->Ln(2);
      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(140, $this->height + 3, 'Nombre del Examen', 1, 0, 'L');
      $this->Cell(0, $this->height + 3, 'Conteo', 1, 1, 'C');
      $this->SetFont('Arial','', 11);

      if(count($itemExamenes) > 0){
        $i = 0;
        foreach ($itemExamenes as $value) {
          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(140, $this->height, utf8_decode($value['nombre_examen']), 1, 0, 'L');
          $this->Cell(0, $this->height, $value['conteo'], 1, 1, 'C');
        }
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay solicitudes atendidas', 1, 0, 'C');
      }
   }

   public function getTableExamen($itemsExamen = array()) {
      $this->SetFont('Arial','B', 14);
      $this->Ln(10);
      $this->Cell(0, $this->height + 1, 'CANTIDADES DE SOLICITUDES', 0, 1, 'C');
      $this->Ln(5);

      if(count($itemsExamen) == 0){
        $this->Cell(0, $this->height + 4, 'Por el momento no hay solicitudes, para la fechas seleccionadas', 1, 1, 'C');
      }else{
        foreach ($itemsExamen as $key => $value) {
          $this->SetFont('Arial','B', 12);
          $this->Cell(165, $this->height + 4, utf8_decode($key), 1, 0, 'L');
          $this->Cell(0, $this->height + 4, 'Cantidad', 1, 1, 'C');

          $this->SetFont('Arial','', 11);
          foreach ($value as $keyGe => $array) {
            $this->Cell(165, $this->height + 2, utf8_decode($array['nombre_examen']), 1, 0, 'L');
            $this->Cell(0, $this->height + 2, $array['cantidad'], 1, 1, 'C');
          }
          $this->Ln($this->height);
        }
      }
   }

   public function onComprobanteExamen($comprobanteOnExamen = array()) 
   {
      $this->SetFont('Arial','', 11);

      $this->Cell(20, $this->height, 'Fecha: ', 0, 0, 'L');
      $this->Cell(0, $this->height, date("d/m/Y", strtotime($comprobanteOnExamen['fecha_creacion'])), 0, 1, 'L');
      
      $this->Cell(20, $this->height, 'Codigo: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($comprobanteOnExamen['id']), 0, 1, 'L');
      

      $this->Cell(20, $this->height, 'Nombre: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($comprobanteOnExamen['name_pac']. ' '.$comprobanteOnExamen['lastname_pac']), 0, 1, 'L');


      $this->Ln(8);
      $debe = $comprobanteOnExamen['monto'] - $comprobanteOnExamen['abono'];

      $this->Cell(20, $this->height, 'Debe: ', 0, 0, 'L');
      $this->Cell(30, $this->height, "$ ".number_format($debe, 2), 0, 0, 'L');

      $this->Cell(20, $this->height, 'Abono: ', 0, 0, 'L');
      $this->Cell(30, $this->height, "$ ".number_format($comprobanteOnExamen['abono'], 2), 0, 1, 'L');

      $this->Cell(20, $this->height, 'Total: ', 0, 0, 'L');
      $this->Cell(30, $this->height, "$ ".number_format($comprobanteOnExamen['monto'], 2), 0, 1, 'L');

      $this->Ln(6);
      $this->SetFont('Arial','B', 10);
      $this->MultiCell(0, $this->height, '* Presentar este comprobante al momento de retirar sus examenes', 'T', 'C');
   }

   public function onComprobanteCitas($comprobanteOnExamen = array()) 
   {
      $this->SetFont('Arial','', 11);

      $this->Cell(20, $this->height, 'Fecha: ', 0, 0, 'L');
      $this->Cell(0, $this->height, date("d/m/Y", strtotime($comprobanteOnExamen['fecha'])), 0, 1, 'L');
      
      $this->Cell(20, $this->height, 'Horario: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($comprobanteOnExamen['horario']), 0, 1, 'L');
      

      $this->Cell(20, $this->height, 'Nombre: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($comprobanteOnExamen['name_pac']. ' '.$comprobanteOnExamen['lastname_pac']), 0, 1, 'L');


      $this->Ln(8);
      $this->SetFont('Arial','B', 12);
      $this->Cell(30, $this->height, 'Total a pagar: ', 0, 0, 'L');
      $this->Cell(30, $this->height, "$ ".number_format($comprobanteOnExamen['pagar'], 2), 0, 1, 'L');

      $this->Ln(6);
      $this->SetFont('Arial','B', 10);
      $this->MultiCell(0, $this->height, '* Presentar este comprobante al momento de presentarse.', 'T', 'C');
   }

   public function onComprobanteSolicitud($comprobanteOnExamen = array()) 
   {
      $this->SetFont('Arial','', 11);

      $this->Cell(20, $this->height, 'Codigo: ', 0, 0, 'L');
      $this->Cell(0, $this->height, $comprobanteOnExamen['codigo'], 0, 1, 'L');
      
      $this->Cell(20, $this->height, 'Sucursal: ', 0, 0, 'L');
      $this->Cell(120, $this->height, utf8_decode($comprobanteOnExamen['nombre_sucursal']), 0, 1, 'L');
      

      $this->Cell(20, $this->height, 'Nombre: ', 0, 0, 'L');
      $this->Cell(0, $this->height, utf8_decode($comprobanteOnExamen['name_pac']. ' '.$comprobanteOnExamen['lastname_pac']), 0, 1, 'L');


      $this->Ln(8);
      $this->SetFont('Arial','B', 12);
      $this->Cell(30, $this->height, 'Total a pagar: ', 0, 0, 'L');
      $this->Cell(30, $this->height, "$ ".number_format($comprobanteOnExamen['pagar'], 2), 0, 1, 'L');

      $this->Ln(6);
      $this->SetFont('Arial','B', 10);
      $this->MultiCell(0, $this->height, '* Presentar este comprobante al momento de presentarse.', 'T', 'C');
   }

   public function onComprobantePermisos($sucursal = array(), $permisosOnIncapacidades = array(), $rangeDate = array()) 
   {
      $this->SetFont('Arial','B', 13);
      $this->Cell(0, $this->height, utf8_decode($sucursal['nombre_sucursal']) , 0, 1, 'C');
      $this->Cell(0, $this->height, 
        "Fechas: ".date("d/m/Y", strtotime($rangeDate['b'])).
          " a ".date("d/m/Y", strtotime($rangeDate['f'])), 
        0, 1, 'C');
      $this->Ln(8);

      $this->SetFont('Arial','B', 12);
      $this->Cell(20, $this->height + 3, 'No', 1, 0, 'C');
      $this->Cell(120, $this->height + 3, 'Nombre Empleado', 1, 0, 'C');
      $this->Cell(30, $this->height + 3, 'Tipo', 1, 0, 'C');
      $this->Cell(30, $this->height + 3, 'Fecha Inicio', 1, 0, 'C');
      $this->Cell(30, $this->height + 3, 'Fecha Fin', 1, 0, 'C');
      $this->Cell(0, $this->height + 3, 'Observacion', 1, 1, 'C');

      $i = 0;
      $this->SetFont('Arial','', 11);
      if(count($permisosOnIncapacidades) > 0){
        foreach ($permisosOnIncapacidades as $value) {
          $strName = $value['lastname_emp']." ".$value['name_emp'];
          $tipo = ($value['tipo'] == 1) ? "Permiso" : ($value['tipo'] == 2) ? 'Incapacidad' : 'Casos Especiales';

          $this->Cell(20, $this->height, ++$i, 1, 0, 'C');
          $this->Cell(120, $this->height, utf8_decode($strName), 1, 0, 'C');
          $this->Cell(30, $this->height, $tipo, 1, 0, 'C');
          $this->Cell(30, $this->height, date("d/m/Y", strtotime($value['fecha_inicio'])), 1, 0, 'C');
          $this->Cell(30, $this->height, date("d/m/Y", strtotime($value['fecha_fin'])), 1, 0, 'C');
          $this->Cell(0, $this->height, utf8_decode($value['comentario']) , 1, 1, 'C');
        }
      }else{
        $this->Cell(0, $this->height + 3, 'Por el momento no hay permisos para esta sucursal', 1, 0, 'C');
      }
   }
  }
?>