<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get("/planilla/{planilla_id}", function (Request $request, Response $response, $arguments){
    $response = $response->withHeader('Content-Type', 'application/pdf');

    $planilla = $this->db->where('id', $arguments["planilla_id"])->getOne('planillas');

    $sqlQuery = "SELECT empleados.lastname_emp, empleados.name_emp, empleados.salary_emp, planilla_empleado.* FROM empleados LEFT JOIN planilla_empleado ON empleados.id = planilla_empleado.empleado_id where empleados.status = 1 and planilla_empleado.planilla_id = ${arguments['planilla_id']}";

    $empleadosPlanilla = $this->db->rawQuery($sqlQuery);
    return $this->renderer->render($response, 'reportes/pdf/planilla.php', [
      "planilla"    => $planilla,
      "empleados"   => $empleadosPlanilla
    ]);
  });

    $app->get("/compras/{compra_id}/reportes", function (Request $request, Response $response, $arguments){
      $response = $response->withHeader('Content-Type', 'application/pdf');
      $array = array(
        "compra" => array(),
        "items"  => array()
      );
      $array["compra"] = $this->db
                            ->join("proveedores as prv", "prv.id = cpm.proveedor_id ", "INNER")
                            ->join("sucursales as suc", "suc.id = cpm.sucursal_id", "INNER")
                            ->where("cpm.id", $arguments['compra_id'])
                            ->getOne("compras as cpm", "suc.nombre_sucursal, prv.nombre_proveedor, cpm.*");
      $array["items"]["isEntregado"] = $this->db
                            ->join("materiales as mat", "mat.id = cpm.material_id", "INNER")
                            ->where("cpm.compra_id", $arguments['compra_id'])
                            ->where("cpm.estado", 0)
                            ->get("compras_items as cpm", null, "mat.nombre_material, cpm.* ");
      $array["items"]["noIsEntregado"] = $this->db
                            ->join("materiales as mat", "mat.id = cpm.material_id", "INNER")
                            ->where("cpm.compra_id", $arguments['compra_id'])
                            ->where("cpm.estado", 1)
                            ->get("compras_items as cpm", null, "mat.nombre_material, cpm.* ");
      return $this->renderer->render($response, 'reportes/pdf/compras.php', [
        "data" => $array,
        "isSave" => false
      ]);
  });