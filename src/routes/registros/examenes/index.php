<?php
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  function onArrayDataExamensCategoria($object = array(), $db, $isUpdate = false){
		return array(
      "nombre_categoria" => $object['compra']['nombre_categoria']
    );
  }

  $app->get("/categoria_examenes", function(Request $request, Response $response){
    bitacora($this, 'CATEGORIA-EXAMENES', 'VIZUALIZAR CATEGORIA EXAMENES');
    return $response->withJson(array(
      "data" => $this->db->orderBy('nombre_categoria', 'asc')->get("categorias_examenes")
    ), 201);
  });

  $app->post("/categoria_examenes", function(Request $request, Response $response, $arguments){
    $array = onArrayDataExamensCategoria($request->getParsedBody(), $this->db);
    $id = $this->db->insert("categorias_examenes", $array);

    bitacora($this, 'CATEGORIA-EXAMENES', 'AGREGAR CATEGORIA EXAMENES');
    return onLoad(array(
      "condition" => $id, "key" => "id", "value" => $id,
      "table" => "categorias_examenes", "response" => $response, "db" => $this->db
    ));
  });

  $app->post("/categoria_examenes/{id}", function(Request $request, Response $response, $arguments){
		$array = onArrayDataExamensCategoria($request->getParsedBody(), $this->db, true);
		$this->db->where('id', $arguments['id']);
		$isResponse = $this->db->update("categorias_examenes", $array);

    bitacora($this, 'CATEGORIA-EXAMENES', 'ACTUALIZAR CATEGORIA EXAMENES');
		return onLoad(array(
			"condition" => $isResponse, "key" => "id", "value" => $arguments['id'],
			"table" => "categorias_examenes", "response" => $response, "db" => $this->db
		));
	});

  $app->get("/categoria_examenes/{id}/examenes", function(Request $request, Response $response, $arguments){
    $objects = $this->db->where('id', $arguments['id'])->getOne('categorias_examenes');
    $objects['examenes'] = $this->db->where('categoria_id', $arguments['id'])->get('examenes');

    return $response->withJson(array(
      "data" => $objects
    ), 201);
	});


  $app->post("/add_examenes", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    $array = array(
      "precio"        => $object['precio'],
      "tipo_reporte"  => $object['tipo_reporte'],
      "categoria_id"  => $object['categoria_id'],
      "nombre_examen" => $object['nombre_examen'],
      "is_only"       => isset($object['is_only']) ? $object['is_only'] : 0
    );

    $id = $this->db->insert("examenes", $array);

    bitacora($this, 'CATEGORIA-EXAMENES', 'AGERGAR EXAMEN A LA CATEGORIA EXAMENES');
    return onLoad(array(
      "condition" => $id, "key" => "id", "value" => $id,
      "table" => "examenes", "response" => $response, "db" => $this->db
    ));
  });

  $app->post("/edit_examens/{id_examen}", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    $array = array(
      "precio"        => $object['precio'],
      "tipo_reporte"  => $object['tipo_reporte'],
      "nombre_examen" => $object['nombre_examen'],
      "is_only"       => isset($object['is_only']) ? $object['is_only'] : 0
    );

    $id = $this->db->where("id", $arguments["id_examen"])->update("examenes", $array);

    bitacora($this, 'CATEGORIA-EXAMENES', 'ACTUALIZAR CATEGORIA EXAMENES');
    return onLoad(array(
      "condition" => $id, "key" => "id", "value" => $arguments["id_examen"],
      "table" => "examenes", "response" => $response, "db" => $this->db
    ));
  });
