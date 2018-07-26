<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get('/permisos/all', function(Request $request, Response $response) {
    $session = $this->session->get('userObject');
    bitacora($this, 'PERMISOS', 'VIZUALIZAR LOS DATOS DE LOS PERMISOS');
    $all = $this->db
            ->join('empleados as emp', 'emp.id = pinc.empleado_id', 'INNER')
            ->where('emp.sucursal_id', $session['sucursal_id'])
            ->orderBy('pinc.created_at', 'DESC')
          ->get('permisos_incapacidades as pinc', 5, 'pinc.*, emp.name_emp, emp.lastname_emp, emp.code_emp');

    return $response->withJson(array(
      "data" => $all
    ), 201);
  });

  $app->post("/permisos/query", function (Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    $session = $this->session->get('userObject');
    bitacora($this, 'PERMISOS', 'VIZUALIZAR LA BUSQUEDAD DE LOS PERMISOS');

    $SQLQuery  = "SELECT * FROM empleados WHERE ( name_emp like '%{$object['query']}%'  or lastname_emp like '%{$object['query']}%') AND status = '1'  AND sucursal_id = '{$session['sucursal_id']}' LIMIT 5";
    $employeersSucursal = $this->db->rawQuery($SQLQuery);

    return $response->withJson(array(
      "data" => $employeersSucursal,
      "server" => true,
      'query' => $SQLQuery
    ), 201);
  });

  function onDescuentoReady($db, $empleado_id){
    $sqlQuery = "SELECT count(*) as conteo FROM permisos_incapacidades as pr where pr.empleado_id = '${empleado_id}' and pr.tipo = '1'";
    $dbData = $db->rawQuery($sqlQuery);

    if($dbData){
      if(intval($dbData[0]["conteo"]) > 4){
        return 1;
      }
    }
    return 0;
  }


  $app->post("/permisos", function (Request $request, Response $response, $arguments){
    $isDescuento = 0;
    bitacora($this, 'PERMISOS', 'AGREGAR UN NUEVO PERMISOS');
    $object = $request->getParsedBody();
    $object["end"]    = date("Y-m-d", strtotime("{$object['end']} -1 day"));
    $object["begin"]  = date("Y-m-d", strtotime("{$object['begin']} -1 day"));

    $SQLBase = "SELECT pincaFecha.fecha, pinca.tipo FROM permisos_incapacidades AS pinca INNER JOIN permisos_incapacidades_fechas AS pincaFecha ON pincaFecha.permiso_id = pinca.id WHERE pinca.empleado_id = '{$object['id']}' AND ";

    if(strcmp($object["type"], '1') === 0){
      $SQLBase .="pincaFecha.fecha = '{$object["begin"]}' ";
      $isDescuento = onDescuentoReady($this->db, $object["id"]);
    }else{
      $SQLBase .="pincaFecha.fecha BETWEEN '{$object["begin"]}' AND ' {$object["end"]}' ";
    }

    $SQLBase .= "ORDER BY pincaFecha.fecha";
    $arrayFechas = $this->db->rawQuery($SQLBase);
    if(count($arrayFechas) === 0){
      //$object["begin"] = date('Y-m-d', strtotime('-1 day'));

      $id = $this->db->insert('permisos_incapacidades', array(
        "is_descuento"      => (strcmp($object["type"], '3') === 0) ? true : $isDescuento,
        "empleado_id"       => $object["id"],
        "fecha_fin"         => $object["end"],
        "tipo"              => $object["type"],
        "days_permisos"     => $object["days"],
        "fecha_inicio"      => $object["begin"],
        "created_at"        => $this->db->now()
      ));

      if($id){
        if(strcmp($object["type"], '1') === 0){
          $this->db->insert('permisos_incapacidades_fechas', array(
            "permiso_id"  => $id,
            "fecha"       => $object["begin"]
          ));
        }else{
          $count = intval($object["days"]);
          for ($i=0; $i < $count; $i++) {
            $this->db->insert('permisos_incapacidades_fechas', array(
              "permiso_id"  => $id,
              "fecha"       => date('Y-m-d', strtotime($object["begin"]."+ {$i} days"))
            ));
          }
        }
        return $response->withJson(array(
          'message' => 'Hemos agregado con exito tu peticion',
          'id'  => $id
        ), 200);
      }else{
        return $response->withJson(array(
          'message' => 'Lo sentimos pero tenemos un problema con el sistema por el momento',
          'response' => true
        ), 301);
      }
    }else{
      return $response->withJson(array(
        'message' => 'Lo sentimos pero no se puede ejecutar tu peticion, ve lo detalles',
        'response' => false,
        'data'  => $arrayFechas
      ), 302);
    }
  });

  $app->post("/permisos/search", function (Request $request, Response $response){
    $object = $request->getParsedBody();
    bitacora($this, 'PERMISOS', 'BUSCAR UN PERMISO');
    $QueryPermisos = $this->db->where("permiso_id", $object["permiso_id"])->getOne("permisos_comentarios");

    return $response->withJson(array(
      "object" => $QueryPermisos, "count" => count($QueryPermisos), "permiso" => $object["permiso_id"]
    ), 201);
  });

  $app->post("/permisos/addComments", function (Request $request, Response $response){
    $object = $request->getParsedBody();
    bitacora($this, 'PERMISOS', 'AGREGAR UN COMENTARIO A UN PERMISO');
    $arrayResponse = $this->db->insert("permisos_comentarios", array(
      "permiso_id"    => $object["permiso_id"],
      "comentario"    => $object["comments"]
    ));

    return $response->withJson(array(
      "response"  => $arrayResponse
    ), 201);
  });
?>
