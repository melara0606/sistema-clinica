<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

		use Ramsey\Uuid\Uuid;
		use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

		function onArrayDataSucursal($object = array(), $db, $isUpdate = false){
			$array = array(
					"nombre_sucursal" => $object["nombre_sucursal"],
					"nit_suc" => $object["nit_suc"],
					"email_suc" => $object["email_suc"],
					"address_suc" => $object["address_suc"],
					"phone_suc" => $object["phone_suc"],
					"updated_at" => $db->now(),
					'lat'		=> "13.642307",
					'lng'		=> "-88.784270"
			);

			if(!$isUpdate)
				$array['created_at'] = $db->now();
			return $array;
		}

    $app->get("/sucursales", function(Request $request, Response $response, $arguments){
      bitacora($this, 'SUCURSALES', 'VIZUALIZAR DATOS DE TODAS LAS SUCURSALES');
      return $response->withJson($this->db->get("sucursales"), 201);
    });

		$app->get("/sucursales/{username}", function(Request $request, Response $response, $a){
      bitacora($this, 'SUCURSALES', 'VIZUALIZAR SUCURSAL');
			return onLoad(array(
				"condition" => true, "key" => "id", "value" => $a["username"],
				"table" => "sucursales", "response" => $response, "db" => $this->db
			));
		});

		$app->post("/sucursal", function(Request $request, Response $response, $arguments){
      $object = $request->getParsedBody();
      bitacora($this, 'SUCURSALES', 'ACTUALIZAR SUCURSAL');
			$array = onArrayDataSucursal($object['item'], $this->db, true);
			$this->db->where('id', $object['item']['id']);
			$isResponse = $this->db->update("sucursales", $array);

			return onLoad(array(
				"condition" => $isResponse, "key" => "id",
				"value" => $object['item']['id'],
				"table" => "sucursales",
				"response" => $response, "db" => $this->db
			));
		});

		$app->post('/sucursales/{username}/delete', function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
      bitacora($this, 'SUCURSALES', 'DAR DE BAJA SUCURSAL');
			$array = array( 'status' => intval(!$o['status']) );
			$this->db->where('id', $arguments["username"]);
			$isResponse = $this->db->update("sucursales", $array);

			return onLoad(array(
				"condition" => $isResponse, "key" => "id",
				"value" => $arguments["username"],
				"table" => "sucursales", "response" => $response,
				"db" => $this->db
			));
		});

		$app->post("/sucursales", function(Request $request, Response $response, $arguments){
				$condition = false;
        bitacora($this, 'SUCURSALES', 'AGREGAR SUCURSAL');
				$array = onArrayDataSucursal($request->getParsedBody(), $this->db);
				$array['id'] = Uuid::uuid4()->toString();
				if($this->db->insert("sucursales", $array))
					$condition = true;

				return onLoad(array(
					"condition" => $condition, "key" => "id", "value" => $array['id'],
					"table" => "sucursales", "response" => $response, "db" => $this->db
				));
		});

    $app->get('/sucursalChange', function(Request $request, Response $response, $arguments){
      $sucusal = $this->db->get('sucursales as suc', null, 'suc.id, suc.nombre_sucursal');
      $userObject = $this->session->get('userObject');

      return $response->withJson(array(
        "response"    => $sucusal,
        'session'     => $userObject['sucursal']['id']
      ), 201);
    });

    $app->post('/sucursalChange', function(Request $request, Response $response, $arguments){
      $object = $request->getParsedBody();
      $session = $this->session->get('userObject');
			$sucursal =  $this->db->where('id', $object['id'])->getOne('sucursales', 'id, nombre_sucursal');
      $session['sucursal'] = $sucursal;
			$session["sucursal_id"] = $sucursal["id"];
      bitacora($this, 'SUCURSALES', 'CAMBIAR ESTADO DE SUCURSAL');

      $this->session->set('userObject', $session);
      return $response->withJson(array(
        "response"    => array( 'ok' => true, 'message' => 'Hemos actualizado con exito la sucursal' )
      ), 201);
    });


		$app->post('/modificaciones/sucursal/geolocation', 
			function(Request $request, Response $response, $arguments) {
			$object = $request->getParsedBody();
      bitacora($this, 'SUCURSALES', 'ACTUALIZAR LOS PUNTOS DE LA AREA DE LA SUCURSAL');
			$isResponse = $this->db->where('id', $object['id'])->update('sucursales', array(
				"lat" => $object["lat"],
				"lng"	=> $object["lng"]
			));
	
			if($isResponse){
				return $response->withJson(array(
					"message" => "Hemos actualizado con exito tu direccion",
					"data"		=> $this->db->where('id', $object['id'])->getOne('sucursales')
				), 201);
			}else{
				return $response->withJson(array(
					"message" => "Tenemos un problema por el momento",
				), 401);
			}
		});


		$app->post('/sucursales/puntosGeograficos', function(Request $request, Response $response, $arguments){
			$objectArray = array();
			$object = $request->getParsedBody();
      bitacora($this, 'SUCURSALES', 'AGREGANDO LOS PUNTOS DE LA AREA DE LA SUCURSAL');
			
			foreach($object['paths'] as $item){
				array_push($objectArray, array(
					"sucursal_id" 		=> $object['id'],
					"lat"							=> $item[0],
					"lng"							=> $item[1]
				));
			}

			$idResponse = $this->db->insertMulti('sucursales_puntos_geograficos', $objectArray);
			if($idResponse){
				return $response->withJson(array(
					"message" => "Hemos agregado con exito tu peticion",
					"data"		=> $this->db->where('sucursal_id', $object['id'])->get('sucursales_puntos_geograficos')
				), 201);
			}else{
				return $response->withJson(array(
					"message" => "Por el momento tenemos un pequeño problema, intenta mas tarde"
				), 501);
			}
		});

		$app->post('/sucursales/puntosGeograficosDelete', function(Request $request, Response $response, $arguments){
			$object = $request->getParsedBody();

      bitacora($this, 'SUCURSALES', 'BORRANDO LOS PUNTOS DE LA AREA SUCURSAL');
			$idResponse = $this->db->where('sucursal_id', $object['id'])->delete('sucursales_puntos_geograficos');			
			if($idResponse){
				return $response->withJson(array(
					"message" => "Tu peticion fue ejecutada con exito"
				), 201);
			}else{
				return $response->withJson(array(
					"message" => "Por el momento tenemos un pequeño problema, intenta mas tarde"
				), 501);
			}
		});

		$app->post('/sucursales/puntos', function(Request $request, Response $response, $arguments){
			$object = $request->getParsedBody();
			return $response->withJson(array(
				"data" => $this->db
					->where('sucursal_id', $object['id'])
					->get('sucursales_puntos_geograficos', null, 'lat, lng')
			), 201);
		});

		$app->get('/sucursales/{sucursal_id}/puntosGeograficos', function(Request $request, Response $response, $arguments){
			$avgQuery = $this->db->rawQuery("SELECT AVG(lat) AS lat, AVG(lng) as lng from sucursales_puntos_geograficos where sucursal_id = '${arguments['sucursal_id']}'");

      bitacora($this, 'SUCURSALES', 'AGREGANDO LOS PUNTOS DE LA POSICION DE SUCURSAL');
			return $response->withJson(array(
				"promedio" => $avgQuery[0],
				"data" => $this->db->where('sucursal_id', $arguments["sucursal_id"])->get('sucursales_puntos_geograficos', null, 'lat, lng')
			), 201);
		});


		$app->get('/sucursals/PuntsGeograficos', function(Request $request, Response $response, $arguments){
			$sqlQuery = "select DISTINCT suc.* from sucursales_puntos_geograficos as sPuntos inner join sucursales as suc ON sPuntos.sucursal_id = suc.id where suc.status = 1";
      bitacora($this, 'SUCURSALES', 'PRESENTADO LOS PUNTOS DE LA POSICION DE SUCURSAL');

			return $response->withJson($this->db->rawQuery($sqlQuery), 201);
		});

		$app->get('/sucursals/reporte', function(Request $request, Response $response, $arguments){
			$response = $response->withHeader('Content-Type', 'application/pdf');

      bitacora($this, 'SUCURSALES', 'REPORTE DE LA SUCURSAL');	
			$arrayOfList = $this->db->where('status', 1)->orderBy('nombre_sucursal')->get('sucursales');
			return $this->renderer->render($response, 'reportes/pdf/sucursales.php', [
				"arrayOfList"	=> $arrayOfList
			]);
		});