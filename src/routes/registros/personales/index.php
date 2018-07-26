<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

		use Ramsey\Uuid\Uuid;
		use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

		function generatorCodeEmpleado($string){
			$string = explode(" ", trim($string));
			$CodeEmpleado = "";
			if(count($string) > 1)
				$CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[1][0]);
			else
				$CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[0][0]);
			return $CodeEmpleado.date('y');
		}

		function onArrayDataPersonal($object = array(), $db, $isUpdate = false){
			$code = $db->where('configuration_id', 1)->getOne('configuration');

			if(!$isUpdate){
				$recode = generatorCodeEmpleado($object["lastname_emp"]);
				$code = intval($code['value_campus']) > 9 ? "0".$code['value_campus'] : intval($code['value_campus']) <= 99 ? "00".$code['value_campus'] : $code['value_campus'];
				$code = $recode.$code;
			}

			$array = array(
				"name_emp" => $object["name_emp"],
				"lastname_emp" => $object["lastname_emp"],
				"address_emp" => $object["address_emp"],
				"fecha_contratacion_emp" => $object["fecha_contratacion_emp"],
				"phone_emp" => $object["phone_emp"],
				"cargo_emp" => $object["cargo_emp"],
				"salary_emp" => $object["salary_emp"],
				"tipo_contratacion" => $object["tipo_contratacion"],
				"salary_day"		=> (floatval($object["salary_emp"])/30),
				"sucursal_id" => $object["sucursal_id"],
				"updated_at" => $db->now()
			);

			if(!$isUpdate){
				$array['code_emp'] = $code;
				$array['created_at'] = $db->now();
			}

			return $array;
		}

    $app->get('/empleados/searchQuery', function(Request $request, Response $response, $arguments) {
     $QueryParams = $request->getQueryParams();
     $session = $this->session->get('userObject');
     $QuerySearch = "SELECT empleados.*, s.nombre_sucursal FROM empleados INNER join sucursales as s on empleados.sucursal_id = s.id WHERE (name_emp LIKE '%".$QueryParams['q']."%' OR lastname_emp LIKE '%".$QueryParams['q']."%' OR phone_emp LIKE '%".$QueryParams['q']."%' OR code_emp LIKE '%".$QueryParams['q']."%') AND empleados.sucursal_id='${session['sucursal_id']}' ORDER BY name_emp, lastname_emp limit 0, 5";

     return $response->withJson(array(
      "data"  => $this->db->rawQuery($QuerySearch)
     ), 201);
    });

    $app->get("/empleados", function(Request $request, Response $response, $arguments){
      $session = $this->session->get('userObject');
			$query = "select e.*, s.nombre_sucursal from sucursales as s INNER join empleados as e on e.sucursal_id = s.id where e.sucursal_id='${session['sucursal_id']}' order by name_emp asc, lastname_emp asc";

      bitacora($this, 'EMPLEADOS', 'VIZUALIZAR DATOS DEL EMPLEADOS');
			return $response->withJson($this->db->rawQuery($query), 201);
    });

		$app->get("/personal/{username}", function(Request $request, Response $response, $a){
      bitacora($this, 'EMPLEADOS', 'VIZUALIZAR DATOS DE UN EMPLEADO');
			return onLoad(array(
				"condition" => true, "key" => "id_personal", "value" => $a["username"],
				"table" => "personal", "response" => $response, "db" => $this->db
			));
		});

		$app->post("/empleados", function(Request $request, Response $response, $arguments){
  		$o = $request->getParsedBody();
      $array = onArrayDataPersonal($o['item'], $this->db, true);
   		$this->db->where('id', $o['item']['id']);
      bitacora($this, 'EMPLEADOS', 'AGREGAR DATOS DEL EMPLEADO');

			if($this->db->update("empleados", $array)){
				$query = "select e.*, s.nombre_sucursal from sucursales as s INNER join empleados as e on e.sucursal_id = s.id where e.id='{$o['item']['id']}'";
				$result = $this->db->rawQuery($query);
				return $response->withJson($result[0], 201);
			}else{
				return onLoad(array(
					"condition" => false, "key" => "id", "value" => $o['item']['id'], "table" => "personal", "response" => $response, "db" => $this->db
				));
			}
		});

		$app->post('/empleados/{username}/delete', function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
			$array = array( 'status' => intval(!$o['status']) );
			$this->db->where('id', $arguments["username"]);
			$isResponse = $this->db->update("empleados", $array);

      bitacora($this, 'EMPLEADOS', 'DAR DE BAJA AL EMPLEADO');
			return onLoad(array(
				"condition" => $isResponse, "key" => "id", "value" => $arguments["username"],
				"table" => "empleados", "response" => $response, "db" => $this->db
			));
		});

		$app->post("/personal", function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
			$array = onArrayDataPersonal($o, $this->db);
			$array['id'] = Uuid::uuid4()->toString();
			$id = $this->db->insert("empleados", $array);

      bitacora($this, 'EMPLEADOS', 'AGREGAR EMPLEADO');
			if($id){
				$this->db->where('configuration_id', 1)->update('configuration', array(
					"value_campus" => $this->db->inc(1)
				));

				$result = $this->db->rawQuery("select e.*, s.nombre_sucursal from sucursales as s INNER join empleados as e on 	e.sucursal_id = s.id where e.id='".$array['id']."'");
				return $response->withJson($result[0], 201);
			}

			return onLoad(array(
				"condition" => false, "key" => "id", "value" => $id, "table" => "personal", "response" => $response, "db" => $this->db
			));
		});

		$app->get('/personals/reporte', function(Request $request, Response $response, $arguments){
      $response = $response->withHeader('Content-Type', 'application/pdf');
      $idSucursal = $this->session->get('userObject')['sucursal_id'];

      bitacora($this, 'EMPLEADOS', 'VIZUALIZAR REPORTE DE LOS EMPLEADOS');
      $sucursal = $this->db
        ->where('id', $idSucursal)
        ->getOne('sucursales');

      $allObject = $this->db
        ->join('empleado_usuario as eu', 'eu.empleado_id = e.id', 'LEFT')
        ->join('usuarios as u', 'u.id_user = eu.usuario_id', 'LEFT')
        ->join('usuarios_perfiles as up', 'up.usuario_id = u.id_user', 'LEFT')
        ->join('perfiles as p', 'p.id = up.perfil_id', 'LEFT')
        ->where('e.status', 1)
        ->where('e.sucursal_id', $idSucursal)
      ->get('empleados as e', null, 'e.*, u.email, p.nombre as nPerfil');

      return $this->renderer->render($response, 'reportes/pdf/personales.php', [
        "arrayOfList"   => $allObject,
        "sucursal"      => $sucursal
      ]);
		});