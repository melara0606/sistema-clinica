<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

		use Ramsey\Uuid\Uuid;
		use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

		 function generatorCodeUsuario($string){
			$string = explode(" ", trim($string));
			$CodeEmpleado = "";
			if(count($string) > 1)
				$CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[1][0]);
			else
				$CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[0][0]);
			return $CodeEmpleado.date('y');
		}

		function onArrayDataUsuario($object = array(), $db, $isUpdate = false){
			$code = $db->where('configuration_id', 1)->getOne('configuration');

			if(!$isUpdate){
				$recode = generatorCodeUsuario($object["lastname_emp"]);
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

    $app->get("/usuarios", function(Request $request, Response $response, $arguments){
			$query = "select e.*, s.nombre_sucursal from sucursales as s INNER join empleados as e on e.sucursal_id = s.id";
			return $response->withJson($this->db->rawQuery($query), 201);
    });

		$app->get("/usuarios/{username}", function(Request $request, Response $response, $a){
			return onLoad(array(
				"condition" => true, "key" => "id_personal", "value" => $a["username"],
				"table" => "personal", "response" => $response, "db" => $this->db
			));
		});

		$app->put("/usuarios/{username}", function(Request $request, Response $response, $arguments){
  		$o = $request->getParsedBody();
			$array = onArrayDataPersonal($o, $this->db, true);
   		$this->db->where('id', $arguments['username']);

			if($this->db->update("usuarios", $array)){
				$query = "select e.*, s.nombre_sucursal from sucursales as s INNER join empleados as e on e.sucursal_id = s.id where e.id='".$arguments['username']."'";
				$result = $this->db->rawQuery($query);
				return $response->withJson($result[0], 201);
			}else{
				return onLoad(array(
					"condition" => false, "key" => "id", "value" => $arguments['username'], "table" => "personal", "response" => $response, "db" => $this->db
				));
			}
		});

		$app->post('/usuarios/{username}/delete', function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
			$array = array( 'status' => intval(!$o['status']) );
			$this->db->where('id', $arguments["username"]);
			$isResponse = $this->db->update("empleados", $array);

			return onLoad(array(
				"condition" => $isResponse, "key" => "id", "value" => $arguments["username"],
				"table" => "empleados", "response" => $response, "db" => $this->db
			));
		});

		$app->post("/usuarios", function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
			$array = onArrayDataPersonal($o, $this->db);
			$array['id'] = Uuid::uuid4()->toString();
			$id = $this->db->insert("empleados", $array);

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
