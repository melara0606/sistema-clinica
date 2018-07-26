<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get("/inventario", function (Request $request, Response $response){
    $userObject = $this->session->get('userObject');

    $sucursal = $this->db->where('id', $userObject["sucursal_id"])->getOne('sucursales', 'nombre_sucursal');
    bitacora($this, 'INVENTARIO', 'VIZUALIZAR EL INVENTARIO'); 
    $comprasObject = $this->db
        ->join("materiales as mat", "mat.id = inv.material_id", "INNER")
        ->join('catalogo_materiales as cm', 'cm.id = mat.catalogo_id', 'INNER')
        ->where("inv.sucursal", $userObject["sucursal_id"])
        ->orderBy('mat.nombre_material', 'asc')
        ->get("inventario as inv", null, "mat.*, inv.existencia, cm.nombre_catalogo, inv.id");

    return $response->withJson(array(
      "items" => $comprasObject,
      "sucursal" => $sucursal
    ), 201);
  });

  $app->get('/inventario/reporte', function(Request $request, Response $response, $arguments){
    $response = $response->withHeader('Content-Type', 'application/pdf');
    $session = $this->session->get('userObject');
    $sucursal_id = $session["sucursal"]['id'];

    bitacora($this, 'INVENTARIO', 'VIZUALIZAR EL INVENTARIO EN REPORTE'); 
    
    $sqlQuery = "SELECT mat.*, inv.existencia, cat_mat.nombre_catalogo FROM inventario AS inv INNER JOIN sucursales AS suc ON inv.sucursal = suc.id INNER JOIN materiales AS mat ON inv.material_id = mat.id INNER JOIN catalogo_materiales AS cat_mat ON mat.catalogo_id = cat_mat.id WHERE suc.id = '${sucursal_id}' ORDER BY mat.nombre_material";

    $arrayOfListProducts = $this->db->rawQuery($sqlQuery);
    return $this->renderer->render($response, 'reportes/pdf/inventario.php', [
      "arrayOfList" => $arrayOfListProducts,
      "objectSucursal"  => $this->db->where('id', $sucursal_id)->getOne('sucursales')
    ]);
  });

  $app->get('/inventario/{id}/item', function(Request $request, Response $response, $arguments) {
    $id = $arguments['id'];
    bitacora($this, 'INVENTARIO', 'VIZUALIZAR EL INVENTARIO POR SU ITEM'); 
    $inventario = $this->db
      ->join('materiales as mat', 'mat.id = inv.material_id', 'INNER')
      ->join('sucursales as suc', 'suc.id = inv.sucursal', 'INNER')
      ->where('inv.id', $id)
      ->getOne('inventario as inv', 'inv.*, mat.nombre_material, suc.nombre_sucursal');

    $inventario['items'] = $this->db
        ->join('empleado_usuario as eu', 'eu.usuario_id = ir.usuario_id', 'INNER')
        ->join('empleados as e', 'e.id = eu.empleado_id', 'INNER')
        ->orderBy('ir.fecha', 'desc')
        ->where('invetario_id', $id)
      ->get('inventario_reajuste as ir', null, 'ir.*, e.name_emp, e.lastname_emp');

      return $response->withJson(array(
        "data" => $inventario
      ), 201);
  });

  $app->post('/inventario/post/item', function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'INVENTARIO', 'ACTUALIZAR EL REAJUSTE DEL INVENTARIO'); 
    $session = $this->session->get('userObject');

    $idReajuste = $this->db->insert('inventario_reajuste', array(
      "fecha"           => $this->db->now(),
      "invetario_id"    => $object['inventario'],
      "cantidad"        => $object['object']['cantidad'],
      "motivo"          => $object['object']['motivo'],
      "usuario_id"      => $session['id_user']
    ));

    if($idReajuste){
      $isUpdate = $this->db->where('id', $object['inventario'])->update('inventario', array(
        "existencia"  => $this->db->dec(floatval($object['object']['cantidad']))
      ));

      $inventario = $this->db
        ->join('empleado_usuario as eu', 'eu.usuario_id = ir.usuario_id', 'INNER')
        ->join('empleados as e', 'e.id = eu.empleado_id', 'INNER')
        ->where('ir.id', $idReajuste)
      ->getOne('inventario_reajuste as ir', 'ir.*, e.name_emp, e.lastname_emp');

      return $response->withJson(array(
        "message" => "Hemos reajustado el material",
        "data"    => $inventario,
        "existencia"  => $this->db->where('id', $object['inventario'])->getOne('inventario', 'existencia')
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Por el momento tenemos un inconveniente con la base de datos"
      ), 401);
    }
  });
?>
