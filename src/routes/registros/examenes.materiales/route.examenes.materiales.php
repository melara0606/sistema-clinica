<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    $app->get("/examenes/{id}/materiales", function(Request $request, Response $response, $arguments) {
        $one = $this->db->where("id", $arguments["id"])->getOne("examenes");
        $stringQuery = "SELECT examenes_materiales.*, materiales.nombre_material, materiales.presentancion, "
                       ."catalogo_materiales.nombre_catalogo FROM catalogo_materiales INNER JOIN materiales "
                       ."ON catalogo_materiales.id = materiales.catalogo_id INNER JOIN examenes_materiales "
                       ."ON examenes_materiales.id_material = materiales.id WHERE "
                       ."examenes_materiales.id_examen = '${arguments['id']}' "
                       ."ORDER BY materiales.nombre_material";

        $one["materiales"] = $this->db->rawQuery($stringQuery);
        return $response->withJson(array(
            "data" => $one
        ), 201);
    });

    $app->get("/examenes/{id}/campos", function(Request $request, Response $response, $arguments) {
        $stringQuery = "SELECT excampo.*, cat.id_tipo_catalogo, cat.nombre_campo, cat.unidades, tipo.nombre_tipo_campo "
                       ."FROM examen_campo AS excampo "
                       ."INNER JOIN catalogo_campos AS cat ON excampo.catalogo_campo_id = cat.id "
                       ."INNER JOIN tipo_catalogo_campo AS tipo ON cat.id_tipo_catalogo = tipo.id "
                       ."WHERE	excampo.examen_id = '${arguments['id']}' "
                       ."ORDER BY excampo.orden_value";
        $one = $this->db->rawQuery($stringQuery);
        return $response->withJson(array(
            "data" => $one
        ), 201);
    });

    $app->post("/examenes/add/materiales", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $examenEntidad = $this->db
                ->where("id_material", $allPost['id_material'])
                ->where("id_examen", $allPost['id_examen'])
                ->getOne("examenes_materiales");


        if(count($examenEntidad) > 0){
            return $response->withJson(array(
                "message" => "No puedes tener en un examen el mismo material dos veces"
            ), 502);
        }

        $array = array(
            "id_material"   => $allPost['id_material'],
            "id_examen"     => $allPost['id_examen'],
            "uso"           => $allPost['uso']
        );

        $examen = $this->db->insert("examenes_materiales", $array);

        bitacora($this, 'EXAMENES-MATERIALES', 'AGREGAR MATERIALES A LOS EXAMENES');
        if($examen){
            $examen = $this->db
                        ->join("catalogo_materiales as cat", "cat.id = mat.catalogo_id", "INNER")
                        ->where("mat.id", $allPost['id_material'])->getOne("materiales as mat");

            return $response->withJson(array(
                "data" => $examen,
                "message" => "Hemos agregado con exito el nuevo material"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->post("/examenes/material/delete", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $entidad = $this->db->where("id", $allPost["id"])->delete("examenes_materiales");

        bitacora($this, 'EXAMENES-MATERIALES', 'ELIMINAR MATERIALES A LOS EXAMENES');
        if($entidad){
            return $response->withJson(array(
                "message" => "Hemos eliminado con exito el material de el examen"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->post("/examenes/campo/delete", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $entidad = $this->db->where("id", $allPost["id"])->delete("examen_campo");

        bitacora($this, 'EXAMENES-CAMPO', 'ELIMINAR CAMPO DEL EXAMEN');
        if($entidad){
            return $response->withJson(array(
                "message" => "Hemos eliminado con exito el campo de el examen"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->get("/search/campo", function (Request $request, Response $response){
      $params = $request->getQueryParams();

      $QuerySelect = $this->db
                          ->join("tipo_catalogo_campo as tipo", "tipo.id = cat.id_tipo_catalogo" ,"INNER")
                          ->where("nombre_campo", "%".$params['query']."%", 'like')
                          ->get("catalogo_campos as cat", 4, "cat.*, tipo.nombre_tipo_campo");

      return $response->withJson(array(
        "data"      => $QuerySelect,
        "server"    => true
      ), 201);
    });

    $app->post("/examenes/add/campo", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();

        $examenCampo = $this->db
                ->where("catalogo_campo_id", $allPost['catalogo_campo_id'])
                ->where("examen_id", $allPost['examen_id'])
                ->getOne("examen_campo");


        if(count($examenCampo) > 0){
            return $response->withJson(array(
                "message" => "No puedes tener en un examen el mismo campo dos veces"
            ), 502);
        }

        $array = array(
            "catalogo_campo_id"   => $allPost['catalogo_campo_id'],
            "examen_id"     => $allPost['examen_id']
        );
        $examen = $this->db->insert("examen_campo", $array);

        bitacora($this, 'EXAMENES-CAMPO', 'AGREGAR EXAMEN AL CAMPO DEL EXAMEN');
        if($examen){
            $querSQL = "SELECT excampo.*, cat.id_tipo_catalogo, cat.nombre_campo, cat.unidades, tipo.nombre_tipo_campo "
                      ."FROM examen_campo AS excampo "
                      ."INNER JOIN catalogo_campos AS cat ON excampo.catalogo_campo_id = cat.id "
                      ."INNER JOIN tipo_catalogo_campo AS tipo ON cat.id_tipo_catalogo = tipo.id "
                      ."WHERE cat.id = '${allPost['catalogo_campo_id']}'";

            return $response->withJson(array(
                "data" => $this->db->rawQuery($querSQL),
                "message" => "Hemos agregado con exito el nuevo campo"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    function getCodeSucursalDomicilio() {
        return "DOM".date("dmyhis");
    }

    $app->post('/addSolicitud/adomicilio', function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $session = $this->session->get('userObject');
        $codigoSucursal = getCodeSucursalDomicilio();
        
        $id = $this->db->insert('solicitud_adomicilio', array(
          "codigo"        => $codigoSucursal,
          "sucursal_id"   => $allPost['sucursal'],
          "paciente_id"	  => $session["id"],
          "pagar"	        => $allPost['pagar'],
          "lat"	          => $allPost['ubicacion']['lat'],
          "lng"	          => $allPost['ubicacion']['lng']
        ));

        bitacora($this, 'SOLICITUD-ADOMICILIO', 'AGREGAR SOLICITUD ADOMICILIO');
        if($id){
          $array = array();
          foreach ($allPost['listExamen'] as $value) {
            array_push($array, array(
              'solicitud_adomicilio_id'   => $id,
              'examen_id'                 => $value['id'],
              'precio'                    => $value['precio']
            ));
          }
          $ids = $this->db->insertMulti('solicitud_adomicilio_examenes', $array);

          return $response->withJson(array(
            "message" => "Hemos agregado tu solicitud, dentro de poco se le hara la visita necesaria",
            'ids'     => $ids
          ), 201);
        }else{
          return $response->withJson(array(
            "message" => "Tenemos un problema por el momento, intenta mas tarde"
          ), 401);
        }
    });
?>
