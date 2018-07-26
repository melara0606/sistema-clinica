<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  class Planilla {
    function __construct($object = array()){
      $this->isss = $object["isss"];
      $this->afp = $object["afp"];
      $this->type = $object["type"];
      $this->salary = $object["salary"];
      $this->db = $object["db"];
      $this->sucursal = $object["sucursal"];
      $this->isUltima = $object["isUltima"];
      $this->empleado = $object["empleado"];
    }

    public function getOtrosDescuento() {
      $beginDescuento = "2017-01-01";
      $empleado = $this->empleado;

      if($this->isUltima){
        $beginDescuento = $this->isUltima["fecha_creacion"];
      }

      $SQLPermiso = "SELECT sum(prn.days_permisos) as diasPermiso FROM permisos_incapacidades as prn WHERE prn.fecha_inicio BETWEEN '${beginDescuento}' AND CURDATE() AND is_descuento = 1 and empleado_id = '${empleado}'";

      $dataObject = $this->db->rawQuery($SQLPermiso);
      if( $dataObject ){
        return (intval($dataObject[0]["diasPermiso"]) ? intval($dataObject[0]["diasPermiso"]) : 0 );
      }
      return 0;
    }

    private function getISSS(){
      if($this->type == 1)
        return 0.0;
      return ($this->salary > 1000 ) ? 30 : $this->salary * $this->isss;
    }
    private function getAFP(){
      if($this->type == 1)
        return 0.0;
      return $this->salary * $this->afp;
    }

    private function getRenta() {
      if($this->type == 2)
        return self::getRentaFijo();
      return $this->salary * 0.10;
    }

    private function getRentaFijo(){
      $salary = $this->salary - (self::getAFP() + self::getISSS());
      $sqlQuery = "SELECT rv.* FROM renta_valores as rv where rv.desde_renta <= ${salary} and rv.hasta_renta >= ${salary}";
      $arrayRenta = $this->db->rawQuery($sqlQuery)[0];
      $renta = ($salary - floatval($arrayRenta["sobre_exceso"])) * floatval($arrayRenta["porcentaje_renta"]) + $arrayRenta["cuota_fija_renta"];
      return $renta;
    }

    public function getRetenciones(){
      return array(
        "isss"    => self::getISSS(),
        "afp"     => self::getAFP(),
        "renta"   => self::getRenta(),
        "diasDescuento"   => $this->getOtrosDescuento(),
        "totalDeduciones" => (self::getISSS() + self::getAFP() + self::getRenta())
      );
    }
  }

  $app->get('/planillas/yearPlanillas', function (Request $request, Response $response, $arguments){
    $QuerySQL = "SELECT distinct SUBSTRING(fecha_creacion, 1, 4) as yearPlanilla FROM planillas order by yearPlanilla";
    return $response->withJson(array(
      "year" => $this->db->rawQuery($QuerySQL)
    ), 201);
  });

  $app->get("/planillas/{idYear}", function (Request $request, Response $response, $arguments){
    $session = $this->session->get('userObject');
    $QuerySQL = "SELECT * FROM planillas as pl WHERE SUBSTRING(fecha_creacion, 1, 4) = '${arguments['idYear']}' and sucursal_id = '${session['sucursal_id']}'  order by mes_ayor asc";
    return $response->withJson($this->db->rawQuery($QuerySQL), 201);
  });

  $app->post("/planilla", function (Request $request, Response $response, $arguments){
    $arrayResponse = array();
    $dateCreacion = date("m/y");

    $session = $this->session->get('userObject');

    $isPlanilla = $this->db
              ->where("sucursal_id", $session["sucursal_id"])
              ->where("mes_ayor", $dateCreacion)
              ->getOne("planillas");

    if(!$isPlanilla){
      if(intval(date("d")) < 21 )
        return $response->withJson(array(
          "status" => 203
        ), 200);

      $employees = $this->db
                      ->where("sucursal_id", $session["sucursal_id"])
                      ->where('status', 1)
                      ->get("empleados");

      if(count($employees) === 0){
        return $response->withJson(array(
          "status" => 205
        ), 200);
      }


      $operation = $this->db->get("configuration");
      $ultimaPlanilla = $this->db
                          ->where('sucursal_id', $session["sucursal_id"] )
                          ->orderBy("fecha_creacion", "DESC")
                          ->getOne("planillas");

      $planillaQuery = $this->db->insert("planillas", array(
        "mes_ayor" => $dateCreacion,
        "fecha_creacion" => $this->db->now(),
        "sucursal_id" => $session["sucursal_id"]
      ));

      foreach($employees as $value){
        $planilla = new Planilla(array(
          "isUltima"  => $ultimaPlanilla,
          "sucursal" => $session["sucursal_id"],
          "isss" => $operation[3]["value_campus"],
          "afp"  => $operation[4]["value_campus"],
          "type" => $value["tipo_contratacion"],
          "salary" => $value["salary_emp"],
          "empleado"   => $value["id"],
          "db"   => $this->db
        ));


        $arrayRenta = $planilla->getRetenciones();
        $otrosDecuentos = (($value["salary_emp"] - $arrayRenta["totalDeduciones"]) / 30 );

        $this->db->insert("planilla_empleado", array(
          "planilla_id"   => $planillaQuery,
          "empleado_id"   => $value["id"],
          "isss"          => $arrayRenta["isss"],
          "afp"           => $arrayRenta["afp"],
          "renta"         => $arrayRenta["renta"],
          "salario"       => $value["salary_emp"],
          "otros"         => $otrosDecuentos * $arrayRenta["diasDescuento"]
        ));
        array_push($arrayResponse, $arrayRenta);
      }

      return $response->withJson(array(
        "data" => $this->db->where('id', $planillaQuery)->getOne("planillas"),
        "objects" => $arrayResponse, "status" => 201
      ) , 201);

    }else{
      return $response->withJson(array(
        "status" => 204
      ), 200);
    }
  });
?>
