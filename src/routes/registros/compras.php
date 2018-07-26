<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get("/compras", function (Request $request, Response $response){
    $userObject = $this->session->get('userObject');

    bitacora($this, 'COMPRAS', 'PRESENTAR LOS DATOS DE LA COMPRAS'); 
    $comprasObject = $this->db
        ->join("proveedores as prv", "prv.id = cpr.proveedor_id", "INNER")
        ->where("sucursal_id", $userObject["sucursal_id"])
        ->orderBy("fecha_compra", 'desc')
        ->get("compras as cpr", null, "cpr.*, prv.nombre_proveedor");

    return $response->withJson($comprasObject, 201);
  });

  $app->post('/compras/{id}/codigo', function (Request $request, Response $response, $arguments){
    $body = $request->getParsedBody();
    $isCode = $this->db->where('id', $body['compra']['id'])->update('compras', array(
      'codigo_factura'    => $body['compra']['codigo_factura']
    ));

    bitacora($this, 'COMPRAS', 'PRESENTAR EL CODIGO DE LA COMPRA'); 
    if( $this->db->count == 1 ){
      return $response->withJson(array(
        "message" => 'Hemos actualizado con exito el codigo',
        "item"    => $this->db->where('id', $body['compra']['id'])->getOne('compras')
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => 'Hemos tenido un problema por el momento'
      ), 501);
    }
  });

  $app->get("/compras/{id}", function (Request $request, Response $response, $arguments){
    $compraId = $arguments["id"];
    bitacora($this, 'COMPRAS', 'PRESENTAR LOS DATOS DE UNA COMPRA'); 
    $comprasObjects = $this->db->join("proveedores as pr", "pr.id = cp.proveedor_id", "INNER")
          ->join("sucursales as sc", "sc.id = cp.sucursal_id", "INNER")
          ->where("cp.id", $compraId)
          ->getOne("compras as cp", 'pr.nombre_proveedor, sc.nombre_sucursal, cp.*');
      return $response->withJson($comprasObjects, 201);
  });

  $app->get("/compras/{id}/items", function (Request $request, Response $response, $arguments){
    $compraId = $arguments["id"];

    bitacora($this, 'COMPRAS', 'PRESENTAR LOS ITEMS DE LA COMPRA'); 
    $comprasItemsObjects = $this->db
          ->join("materiales as mat", "cpr.material_id = mat.id", "INNER")
          ->join("catalogo_materiales as cm", "cm.id = mat.catalogo_id", "INNER")
          ->where( "compra_id", $compraId )
          ->get("compras_items as cpr", null,  'cm.*, cpr.*, mat.nombre_material, cm.id as catalogo_id, cm.is_fecha_vencimiento');

      return $response->withJson($comprasItemsObjects, 201);
  });

/*
* actualizar los materiales segun la entrega (inventario)
* el total de la compra una actualizacion
* > verificar si el material existe en la tabla de inventario, si es el caso actualizar
* > sino agregar el material para la sucursal, agregar la cantidad actual.
*/
  $app->post("/compras/status", function (Request $request, Response $response){
    $userObject = $this->session->get('userObject');
    $body = $request->getParsedBody();
    $objects = $body['objects'];

    bitacora($this, 'COMPRAS', 'ACTUALIZAR EL ESTADO DE LA COMPRA'); 

    $isValid = $this->db->where('id', $objects["compra_id"])->getOne("compras", "estado");

    if($isValid["estado"] == '1'){
      $this->db->where("id", $objects["id"])->where("estado", 1)->update("compras_items", array(
        "estado" => 0,
        "fecha_vencimiento" => $body["isDate"]
      ));

      if($this->db->count == 1){
        $isInventario = $this->db
                          ->where('sucursal', $userObject['sucursal_id'])
                          ->where('material_id', $objects["material_id"])
                          ->getOne('inventario');

        $material = $this->db->where('id', $objects["material_id"])
                              ->getOne('materiales');

        if( count($isInventario) == 0 ){
          $isNew = $this->db->insert('inventario', array(
            "material_id"   => $objects["material_id"],
            "sucursal"      => $userObject['sucursal_id'],
            "existencia"    => intval($objects["cantidad"]) * intval($material["unidades"])
          ));
        }else{
          $isUpdate = $this->db->where('id', $isInventario['id'])->update('inventario', array(
            "existencia" => $this->db->inc(
              intval($objects["cantidad"]) * intval($material["unidades"])
            )
          ));
        }

        if($this->db->count == 1){
          $isTotal = $objects["cantidad"] * $objects["precio"];
          $this->db->where('id', $objects["compra_id"])->update("compras", array(
            "total_compra" => $this->db->inc(floatval($isTotal))
          ));

          if($this->db->count == 1){
            $isCompra = $this->db->where('id', $objects["compra_id"])->getOne("compras");
            return $response->withJson(array(
              "compra"    => $isCompra
            ), 201);
          }
        }
      }
    }
  });

  $app->post("/compras/{id_compra}/close", function (Request $request, Response $response, $arguments){
    $isCompra = $arguments["id_compra"];

    bitacora($this, 'COMPRAS', 'CERRAR ESTADO DE LA COMPRA'); 
    $isCompraArray = array(
      "estado" => 0
    );
    $this->db->where("id", $isCompra)->update("compras", $isCompraArray);
    if($this->db->count == 1 ){
      return $response->withJson(array(
        "ok"    => true
      ), 201);
    }

    return $response->withJson(array(
        "ok"    => false
      ), 406);
  });

  $app->post("/compras", function (Request $request, Response $response){
      $objects = $request->getParsedBody();
      bitacora($this, 'COMPRAS', 'AGREGAR UNA NUEVA COMPRA');
      $sessionUser = $this->session->get("userObject");
      $arrayCompra = array(
        "proveedor_id"    => $objects["proveedor"],
        "sucursal_id"     => $sessionUser["sucursal_id"],
        "total_compra"    => 0,
        "estado"          => 1,
        "fecha_compra"    => $this->db->now()
      );
      $responseId = $this->db->insert("compras", $arrayCompra);

      if($responseId){
        $count = count($objects["list"]);
        for ($i=0; $i < $count ; $i++) {
            $item = $objects["list"][$i];
            $arrayInsert = array(
                "compra_id"     => $responseId,
                "material_id"   => $item["compra"]["material"],
                "precio"   => $item["compra"]["precio"],
                "cantidad"   => $item["compra"]["nameCantidad"],
                "estado"        => 1
            );
            $responseValue = $this->db->insert("compras_items", $arrayInsert);
        }
      }
  });
