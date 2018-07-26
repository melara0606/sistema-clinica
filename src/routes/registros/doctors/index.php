<?php
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Psr\Http\Message\ServerRequestInterface as Request;

	use Ramsey\Uuid\Uuid;
	use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

	function onLoadDoctor($condition, $db, $id, $response, $message = null){
		if($condition){
			return onLoadGetData($db, array(
				"key" 	=> 'id',
				"value"	=> $id,
				"table"		=> 'doctores'
			), $response);
		}else{
			return onLoadGetData(null, null, $response, true, $message);
		}
	}

	function onArrayData($object = array(), $db, $isUpdate = false){
		$array = array(
			"jvpm_doc" => $object["jvpm_doc"],
			"name_doc" => $object["name_doc"],
			"lastname_doc" => $object["lastname_doc"],
			"email" => $object["email"],
			"nit"	=> $object["nit"],
			"nrc"	=> $object["nrc"],
			"phone_doc" => $object["phone_doc"],
			"updated_at" => $db->now(),
			'lat'		=> "13.642307",
			'lng'		=> "-88.784270"
		);

		if(!$isUpdate){
			$array["created_at"] =  $db->now();
		}
		return $array;
	}

  $app->get('/doctors/searchQuery', function(Request $request, Response $response, $arguments) {
    $QueryParams = $request->getQueryParams();
    $QuerySearch = "SELECT * FROM doctores WHERE name_doc LIKE '%".$QueryParams['q']."%' OR lastname_doc LIKE '%".$QueryParams['q']."%' OR email LIKE '%".$QueryParams['q']."%' OR nit LIKE '%".$QueryParams['q']."%' OR phone_doc LIKE '%".$QueryParams['q']."%' ORDER BY name_doc, lastname_doc limit 0, 5";

    return $response->withJson(array(
    "data"  => $this->db->rawQuery($QuerySearch)
    ), 201);
  });

  $app->get("/doctors", function(Request $request, Response $response, $arguments){
		$doctores = $this->db
											->orderBy('lastname_doc', 'asc')
											->orderBy('name_doc', 'asc')
											->get("doctores", null, "*, CONCAT(lastname_doc, ', ', name_doc) as nombreCompleto" );

    bitacora($this, 'DOCTORES', 'VIZUALIZAR DATOS');
		return $response->withJson( $doctores , 201);
  });

	$app->post("/doctors", function(Request $request, Response $response, $arguments){
		$condition = false;
		$o = $request->getParsedBody();
		$array = onArrayData($o, $this->db);
		$array['id'] = Uuid::uuid4()->toString();

    bitacora($this, 'DOCTORES', 'AGREGAR DATOS');
		if($this->db->insert("doctores", $array))
		$condition = true;

		return onLoadDoctor($condition, $this->db, $array['id'], $response, 'Tenemos un problema con la inserccion');
 	});

 $app->post("/doctor", function(Request $request, Response $response, $arguments){
		$o = $request->getParsedBody();
    bitacora($this, 'DOCTORES', 'ACTUALIZAR DATOS');
		$array = onArrayData($o['item'], $this->db, true);
		$this->db->where('id', $o['item']['id']);
		$isResponse = $this->db->update("doctores", $array);
		return onLoadDoctor($isResponse, $this->db, $o['item']['id'], $response, 'Tenemos un detalle con la actualizacion');
	});

	$app->post('/doctors/{username}/delete', function(Request $request, Response $response, $arguments){
		$o = $request->getParsedBody();
    bitacora($this, 'DOCTORES', 'BORRAR DATOS');
		$array = array( 'status' => intval(!$o['status']) );
		$this->db->where('id', $arguments["username"]);

		$isResponse = $this->db->update("doctores", $array);
		return onLoadDoctor($isResponse, $this->db, $arguments['username'], $response, 'Tenemos un detalle con la actualizacion');
	});

	$app->get("/doctors/{username}", function(Request $request, Response $response, $arguments){
		return onLoadGetData($this->db, array("key" => 'id', "value" => $arguments['username'], "table" => 'doctores' ), $response);
	});


	$app->post('/modificaciones/doctores/geolocation', function(Request $request, Response $response, $arguments) {
		$object = $request->getParsedBody();
		$isResponse = $this->db->where('id', $object['id'])->update('doctores', array(
			"lat" => $object["lat"],
			"lng"	=> $object["lng"]
		));

    bitacora($this, 'DOCTORES', 'PUNTOS GEOGRAFICOS MODIFICACION');
		if($isResponse){
			return $response->withJson(array(
				"message" => "Hemos actualizado con exito tu direccion",
				"data"		=> $this->db->where('id', $object['id'])->getOne('doctores')
			), 201);
		}else{
			return $response->withJson(array(
				"message" => "Tenemos un problema por el momento",
			), 401);
		}
	});

	$app->get('/doctores/reporte', function(Request $request, Response $response, $arguments){
		$response = $response->withHeader('Content-Type', 'application/pdf');

		$arrayOfList = $this->db->where('status', 1)->orderBy('lastname_doc')->get('doctores');
		return $this->renderer->render($response, 'reportes/pdf/doctores.php', [
			"arrayOfList"	=> $arrayOfList
		]);
	});