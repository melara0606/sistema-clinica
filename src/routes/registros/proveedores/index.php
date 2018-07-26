<?php
	use Ramsey\Uuid\Uuid;
	use \Psr\Http\Message\ResponseInterface as Response;
	use \Psr\Http\Message\ServerRequestInterface as Request;
	use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

		function onArrayDataProveedor($object = array(), $db, $isUpdate = false){
			return $array = array(
				"nombre_proveedor" => $object["nombre_proveedor"],
				"direccion_proveedor" => $object["direccion_proveedor"],
				"telefono_proveedor" => $object["telefono_proveedor"],
				"nit_proveedor" => $object["nit_proveedor"],
				"nrc_proveedor" => $object["nrc_proveedor"],
				"representante_proveedor" => $object["representante_proveedor"],
				"telefono_respresentante" => $object["telefono_respresentante"],
				"email_representante" => $object["email_representante"],
				"updated_at" => $db->now()
			);

			if(!$isUpdate){
				$array['created_at'] = $db->now();
			}
		}

    $app->get('/proveedores/searchQuery', function(Request $request, Response $response, $arguments) {
     $QueryParams = $request->getQueryParams();
     $QuerySearch = "SELECT * FROM proveedores WHERE nombre_proveedor LIKE '%".$QueryParams['q']."%' OR nit_proveedor LIKE '%".$QueryParams['q']."%' OR nrc_proveedor LIKE '%".$QueryParams['q']."%' OR email_representante LIKE '%".$QueryParams['q']."%' OR telefono_respresentante LIKE '%".$QueryParams['q']."%' OR telefono_proveedor LIKE '%".$QueryParams['q']."%' ORDER BY nombre_proveedor limit 0, 5";

     return $response->withJson(array(
      "data"  => $this->db->rawQuery($QuerySearch)
     ), 201);
    });

    $app->get("/proveedores", function(Request $request, Response $response, $arguments){
      bitacora($this, 'PROVEEDORES', 'VIZUALIZAR PROVEEDORES');
			$proveedores = $this->db
        ->orderBy('nombre_proveedor', 'asc')
        ->get("proveedores", 10);
      return $response->withJson($proveedores, 201);
    });

		$app->get("/proveedores/{username}", function(Request $request, Response $response, $a){
      bitacora($this, 'PROVEEDORES', 'VIZUALIZAR DATO DE UN PROVEEDOR');
			return onLoad(array(
				"condition" => true, "key" => "id", "value" => $a["username"],
				"table" => "proveedores", "response" => $response, "db" => $this->db
			));
		});

		$app->post("/proveedores", function(Request $request, Response $response, $arguments){
			$condicion = false;
			$o = $request->getParsedBody();
			$array = onArrayDataProveedor($o, $this->db);
			$array['id'] = Uuid::uuid4()->toString();

      bitacora($this, 'PROVEEDORES', 'AGREGAR DATO DE UN PROVEEDOR');
			if($this->db->insert("proveedores", $array))
				$condicion = true;

			return onLoad(array(
				"condition" => $condicion, "key" => "id", "value" => $array['id'], "table" => "proveedores", "response" => $response, "db" => $this->db
			));
 		});

		$app->put("/proveedores/{username}", function(Request $request, Response $response, $arguments){
  		$o = $request->getParsedBody();
			$array = onArrayDataProveedor($o, $this->db);

      bitacora($this, 'PROVEEDORES', 'ACTUALIZAR DATO DE UN PROVEEDOR');
   		$this->db->where('id', $arguments['username']);
			$isResponse = $this->db->update("proveedores", $array);

			return onLoad(array(
				"condition" => $isResponse, "key" => "id",
				"value" => $arguments['username'],
				"table" => "proveedores", "response" => $response,
				"db" => $this->db
			));
		});

		$app->post('/proveedores/{username}/delete', function(Request $request, Response $response, $arguments){
			$o = $request->getParsedBody();
			$array = array( 'status' => intval(!$o['status']) );
      bitacora($this, 'PROVEEDORES', 'BORRAR DATO DE UN PROVEEDOR');
			$this->db->where('id', $arguments["username"]);
			$isResponse = $this->db->update("proveedores", $array);

			return onLoad(array(
				"condition" => $isResponse, "key" => "id", "value" => $arguments["username"],
				"table" => "proveedores", "response" => $response, "db" => $this->db
			));
		});

		$app->get("/proveedores/{id_proveedor}/materiales", function(Request $request, Response $response, $arguments){
			$QuerySelect = $this->db
				->join("proveedor_materiales as pm", "pm.material_id = mat.id" ,"INNER")
				->join("catalogo_materiales as cmat", "cmat.id = mat.catalogo_id" ,"INNER")
				->where("pm.proveedor_id", $arguments["id_proveedor"])
				->orderBy('mat.nombre_material', 'asc')
				->get("materiales as mat", null, "pm.*, cmat.nombre_catalogo, mat.nombre_material, cmat.id as catalogo_id");

      bitacora($this, 'PROVEEDORES', 'AGREGAR MATERIAL A UN PROVEEDOR');
			return $response->withJson(array(
				"data"      => $QuerySelect,
				"server"    => true
			), 201);
		});

		$app->get('/proveedors/reporte', function(Request $request, Response $response, $arguments){
			$response = $response->withHeader('Content-Type', 'application/pdf');
	
			$arrayOfList = $this->db->where('status', 1)->orderBy('nombre_proveedor')->get('proveedores');
      bitacora($this, 'PROVEEDORES', 'VIZUALIZAR REPORTE DE LOS PROVEEDORES');
			return $this->renderer->render($response, 'reportes/pdf/proveedores.php', [
				"arrayOfList"	=> $arrayOfList
			]);
		});