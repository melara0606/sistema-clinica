<?php
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  function onArrayDataExamens($object = array(), $db, $isUpdate = false){
		return array(
      "nombre_grupo" => $object['nombre_grupo']
    );
  }

  $app->get("/examenes", function(Request $request, Response $response, $arguments){
      return $response->withJson(array(
        "data" => $this->db->get("tipo_examen")
      ), 201);
  });

  $app->post("/examenes", function(Request $request, Response $response, $arguments){
    $array = onArrayDataExamens($request->getParsedBody(), $this->db);
    $id = $this->db->insert("tipo_examen", $array);

    bitacora($this, 'EXAMENES', 'AGREGAR CAMPO DEL EXAMEN');
    return onLoad(array(
      "condition" => $id, "key" => "codigo_tipo", "value" => $id,
      "table" => "tipo_examen", "response" => $response, "db" => $this->db
    ));
  });

	$app->post("/examenes/{username}", function(Request $request, Response $response, $arguments){
		$array = onArrayDataExamens($request->getParsedBody(), $this->db, true);
		$this->db->where('codigo_tipo', $arguments['username']);
		$isResponse = $this->db->update("tipo_examen", $array);

		return onLoad(array(
			"condition" => $isResponse, "key" => "codigo_tipo", "value" => $arguments['username'],
			"table" => "tipo_examen", "response" => $response, "db" => $this->db
		));
	});
