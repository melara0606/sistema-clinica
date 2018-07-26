<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    use Ramsey\Uuid\Uuid;
    use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

    function generatorCodePaciente($string){
      $string = explode(" ", trim($string));
      $CodeEmpleado = "";
      if(count($string) > 1)
        $CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[1][0]);
      else
        $CodeEmpleado = strtoupper($string[0][0]).strtoupper($string[0][0]);
      return $CodeEmpleado;
    }

    function onArrayDataPacientes($object = array(), $db, $isUpdate = false){
      $code = $db->where('configuration_id', 3)->getOne('configuration');

      if(!$isUpdate){
        $recode = generatorCodePaciente($object["lastname_pac"]);
        $code = intval($code['value_campus']) > 9 ? "0".$code['value_campus'] : intval($code['value_campus']) <= 99 ? "00".$code['value_campus'] : $code['value_campus'];
        $code = $recode.$code;
      }

      $array = array(
        "name_pac"		            => @$object['name_pac'],
        "lastname_pac"		        => @$object['lastname_pac'],
        "address_pac"		          => @$object['address_pac'],
        "date_pac"		            => @$object['date_pac'],
        "entidad_id"		          => @$object['entidad_id'],
        "genero_paciente"		      => @$object['genero_paciente'],
        "responsable_paciente"		=> @$object['responsable_paciente'],
        "telefono"		            => @$object['telefono'],
        "carnet"		              => @$object['carnet'],
        "dui"		                  => @$object['dui'],
        "address_pac"		          => @$object['address_pac'],
        "updated_at"		          => @$db->now()
      );

      if(!$isUpdate){
        $array['created_at'] = $db->now();
        $array['codigo_paciente'] = $code;
        $array['password'] = crypt(base64_encode(hash("whirlpool", '0123456789')), '$6$admin$');
      }
      return $array;
    }

    $app->get('/pacientes/searchQuery', function(Request $request, Response $response, $arguments) {
     $QueryParams = $request->getQueryParams();
     $QuerySearch = "SELECT * FROM pacientes WHERE pacientes.name_pac LIKE '%".$QueryParams['q']."%' OR pacientes.lastname_pac LIKE '%".$QueryParams['q']."%' OR pacientes.dui LIKE '%".$QueryParams['q']."%' OR pacientes.carnet LIKE '%".$QueryParams['q']."%' OR pacientes.codigo_paciente LIKE '%".$QueryParams['q']."%' ORDER BY pacientes.name_pac, pacientes.lastname_pac limit 0, 5";

     return $response->withJson(array(
      "data"  => $this->db->rawQuery($QuerySearch)
     ), 201);
    });

    $app->get("/pacientes", function(Request $request, Response $response, $arguments){
      $pacientes = $this->db
        ->orderBy('name_pac', 'asc')
        ->orderBy('lastname_pac', 'asc')
        ->get("pacientes", 10);
      return $response->withJson($pacientes, 201);
    });

    $app->get("/pacientes/{username}", function(Request $request, Response $response, $a){

      bitacora($this, 'PACIENTES', 'VIZUALIZAR DATOS DEL PACIENTE');
      return onLoad(array(
        "condition" => true, "key" => "id", "value" => $a["username"],
        "table" => "pacientes", "response" => $response, "db" => $this->db
      ));
    });

    $app->post("/pacientes/{username}", function(Request $request, Response $response, $arguments){
      bitacora($this, 'PACIENTES', 'ACTUALIZAR DATOS DEL PACIENTE');
      $array = onArrayDataPacientes($request->getParsedBody(), $this->db, true);
      $this->db->where('id', $arguments['username']);
      $isResponse = $this->db->update("pacientes", $array);

      return onLoad(array(
        "condition" => $isResponse, "key" => "id", "value" => $arguments['username'],
        "table" => "pacientes", "response" => $response, "db" => $this->db
      ));
    });

    $app->post('/pacientes/{username}/delete', function(Request $request, Response $response, $arguments){
      bitacora($this, 'PACIENTES', 'ELIMINAR DATOS DEL PACIENTE');
      $o = $request->getParsedBody();
      $array = array( 'status' => intval(!$o['status']) );
      $this->db->where('id', $arguments["username"]);
      $isResponse = $this->db->update("pacientes", $array);

      return onLoad(array(
        "condition" => $isResponse, "key" => "id", "value" => $arguments["username"],
        "table" => "pacientes", "response" => $response, "db" => $this->db
      ));
    });

    $app->post("/pacientes", function(Request $request, Response $response, $arguments){
      bitacora($this, 'PACIENTES', 'AGREGAR DATOS DEL PACIENTE');
      $array = onArrayDataPacientes($request->getParsedBody(), $this->db);
      $array['id'] = Uuid::uuid4()->toString();

      if($this->db->insert("pacientes", $array)){
        $this->db->where('configuration_id', 3)->update('configuration', array(
          "value_campus" => $this->db->inc(1)
        ));

        return onLoad(array(
          "condition" => true, "key" => "id", "value" => $array['id'],
          "table" => "pacientes", "response" => $response, "db" => $this->db
        ));
      }

      return onLoad(array(
        "condition" => false, "key" => "id", "value" => null,
        "table" => "pacientes", "response" => $response, "db" => $this->db
      ));
    });
