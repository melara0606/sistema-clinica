<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use Auths\MysqlDatatable;

  $app->get("/materiales", function (Request $request, Response $response, $arguments){
    $objects = $this->db->orderBy('nombre_catalogo', 'asc')->get('catalogo_materiales');
    bitacora($this, 'MATERIALES', 'VIZUALIZAR LOS MATERIALES'); 
    return $response->withJson($objects, 201);
  });

  $app->post("/QueryDelete/materiales", function (Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'MATERIALES', 'BORRAR UN MATERIALES'); 
    $QueryResponse = $this->db->where("id", $object["id"])->delete("proveedor_materiales");

    return $response->withJson(array(
      "response"    => $QueryResponse
    ), 201);

  });

  $app->get("/materiales/{material_id}", function (Request $request, Response $response, $arguments){
    $objectData = $this->db->where("id", $arguments["material_id"])->getOne("catalogo_materiales");
    $materiales = $this->db
                        ->where("catalogo_id", $arguments["material_id"])
                        ->orderBy('update_create', 'desc')
                        ->get("materiales");

    bitacora($this, 'MATERIALES', 'VIZUALIZAR LOS DATOS DE UN MATERIAL'); 
    return $response->withJson(array(
      "id"       => $objectData["id"],
      "material" => $objectData,
      "items"    => $materiales
    ), 201);
  });

  $app->post("/materiales/{materia_id}", function (Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    $array = array(
      "catalogo_id"     => $arguments["materia_id"],
      "nombre_material" => $object["nombre_material"],
      "presentancion"     => $object["presentancion"],
      "unidades"          => $object["unidades"]
    );

    $isCreatead = $this->db->insert("materiales", $array);

    if($isCreatead){
      $array["id"] = $isCreatead;
      return $response->withJson($array, 201);
    }else{
      return $response->withJson($array, 401);
    }
  });

  $app->get("/proveedor/{proveedor_id}/material", function (Request $request, Response $response, $arguments){
    $QuerySelect = $this->db
                        ->join("proveedor_materiales as pm", "pm.material_id = mat.id" ,"INNER")
                        ->join("catalogo_materiales as cmat", "cmat.id = mat.catalogo_id" ,"INNER")
                        ->where("proveedor_id", $arguments["proveedor_id"])
                        ->get("materiales as mat", null ,"pm.*, cmat.nombre_catalogo, mat.nombre_material");
    
    bitacora($this, 'PROVEEDORES', 'VIZUALIZAR LOS MATERIALES PARA UN PROVEEDOR'); 
    return $response->withJson(array(
      "data"      => $QuerySelect,
      "server"    => true
    ), 201);
  });

  $app->post("/material/add", function (Request $request, Response $response){
    $object = $request->getParsedBody();

    bitacora($this, 'MATERIALES', 'AGREGAR A UN NUEVO MATERIAL'); 

    $exists = $this->db
          ->where('proveedor_id', $object["proveedor_id"])
          ->where('material_id', $object["material"])
          ->getOne('proveedor_materiales');

    if(count($exists) == 0){
      $responseQuery = $this->db->insert("proveedor_materiales", array(
        "proveedor_id"   => $object["proveedor_id"],
        "material_id"    => $object["material"]
      ));
      $QuerySelect = $this->db
                      ->join("proveedor_materiales as pm", "pm.material_id = mat.id" ,"INNER")
                      ->join("catalogo_materiales as cmat", "cmat.id = mat.catalogo_id" ,"INNER")
                      ->where("pm.id", $responseQuery)
                      ->getOne("materiales as mat", "pm.*, cmat.nombre_catalogo, mat.nombre_material");

      return $response->withJson(array(
        "data"      => $QuerySelect,
        "server"    => true
      ), 201);
    }else{
      return $response->withJson(array(
        "server"    => false
      ), 301);
    }
  });

  $app->post("/update/material",  function (Request $request, Response $response){
    $object = $request->getParsedBody();

    bitacora($this, 'MATERIALES', 'ACTUALIZAR UN MATERIAL'); 

    $updateObjec = $this->db->where("id", $object["id"])->update('materiales', array(
      "nombre_material"       => $object["nombre_material"],
      "presentancion"         => $object["presentancion"],
      "unidades"              => $object["unidades"],
      "update_create"         => $this->db->now()
    ));

    if( $updateObjec ){
      return $response->withJson(array(
        "data"      => $this->db->where("id", $object["id"])->getOne("materiales"),
        "server"    => true
      ), 201);
    }
  });

  $app->get("/search/materiales", function (Request $request, Response $response){
    $params = $request->getQueryParams();
    bitacora($this, 'MATERIALES', 'BUSCAR LOS MATERIALES'); 
    $QuerySelect = $this->db
                        ->join("catalogo_materiales as ct", "ct.id=mat.catalogo_id" ,"INNER")
                        ->where("nombre_material", "%".$params['query']."%", 'like')
                        ->get("materiales as mat", 4, "mat.*, ct.nombre_catalogo");

    return $response->withJson(array(
      "data"      => $QuerySelect,
      "server"    => true
    ), 201);
  });
?>
