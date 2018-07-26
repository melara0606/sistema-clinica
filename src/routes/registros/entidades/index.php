<?php
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use \Psr\Http\Message\ResponseInterface as Response;

	use Ramsey\Uuid\Uuid;
	use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

	function onLoadEntidad($condition, $db, $id, $response, $message = null){
		if($condition){
			return onLoadGetData($db, array(
				"key" 	=> 'id', "value"	=> $id, "table"	=> 'entidades'
			), $response);
		}else{
			return onLoadGetData(null, null, $response, true, $message);
		}
	}

	function onArrayDataEntidad($object = array(), $db, $isUpdate = false){
		return $array = array(
			"name_ext" => $object["name_ext"],
			"address_ext" => $object["address_ext"],
			"phone_ext" => $object["phone_ext"],
			"nit_entidad" => $object["nit_entidad"],
			"nrc_entidad" => $object["nrc_entidad"],
			"represent_ext" => $object["represent_ext"],
			"phone_represent_ext" => $object["phone_represent_ext"],
			"mail_represent_ext" => $object["mail_represent_ext"],
			"updated_at" => $db->now()
		);

		if(!$isUpdate){
			$array['created_at'] = $db->now();
		}
	}

	$app->get("/entidades", function(Request $request, Response $response, $arguments){
		$entidades = $this->db->orderby('view', 'asc')->orderby('name_ext', 'asc')->get("entidades");
		return $response->withJson($entidades, 201);
	});

	$app->get("/entidades/{username}", function(Request $request, Response $response, $arguments){
		return onLoadEntidad(true, $this->db, $arguments['username'], $response);
	});

	$app->post("/entidad", function(Request $request, Response $response, $arguments){
		$o = $request->getParsedBody();
		$array = onArrayDataEntidad($o['item'], $this->db, true);

		$this->db->where('id', $o['item']['id']);
		$isResponse = $this->db->update("entidades", $array);
    bitacora($this, 'ENTIDAD', 'ACTUALIZAR');
		return onLoadEntidad($isResponse, $this->db, $o['item']['id'], $response, 'Tenemos un problema con la inserccion');
	});

	$app->post('/entidades/{username}/delete', function(Request $request, Response $response, $arguments){
		$o = $request->getParsedBody();
		$array = array( 'status' => intval(!$o['status']) );
		$this->db->where('id', $arguments["username"]);

		$isResponse = $this->db->update("entidades", $array);
		bitacora($this, 'ENTIDAD', 'DAR DE BAJA');
		return onLoadEntidad($isResponse, $this->db, $arguments['username'], $response, 'Tenemos un problema con la actualizacion');
	});

	$app->post("/entidades", function(Request $request, Response $response, $arguments){
		$condition = false;
		$o = $request->getParsedBody();
		$array = onArrayDataEntidad($o, $this->db, true);
		$array['id'] = Uuid::uuid4()->toString();

		if($this->db->insert("entidades", $array))
			$condition = true;

		bitacora($this, 'ENTIDAD', 'INGRESAR');
		return onLoadEntidad(	$condition, $this->db, $array['id'], $response, 'Tenemos un problema con la actualizacion');
 	});

  $app->get('/entidads/reporte', function(Request $request, Response $response, $arguments){
			$response = $response->withHeader('Content-Type', 'application/pdf');
			bitacora($this, 'ENTIDAD', 'reporte/entidades');
			$allEntidades = $this->db
							->join('entidad_monto as em', 'e.id = em.id_entidad', 'LEFT')
							->where('e.status', 1)
							->where('e.view', 1)
					->get('entidades as e', null, 'e.*, em.monto');
			
			return $this->renderer->render($response, 'reportes/pdf/entidades.php', [
					"arrayOfList"   => $allEntidades
			]);
	});
