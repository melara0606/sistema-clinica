<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    $app->get("/promocion", function(Request $request, Response $response, $arguments) {
      $parameters = $request->getQueryParams();
      $session = $this->session->get('userObject');

      $all = $this->db->where('sucursal_id', $session['sucursal_id'])->get("promociones");

      if(@$parameters["estado"] == 1){
        $all = $this->db
          ->where('sucursal_id', $session['sucursal_id'])
          ->where('estado', 1)
          ->get("promociones");
      }

      return $response->withJson(array(
          "data" => $all
      ), 201);
    });

    $app->get("/promocion/search", function(Request $request, Response $response, $arguments) {
        $parameters = $request->getQueryParams();
        $stringQuery = "SELECT exam.*, cat_exam.nombre_categoria FROM examenes as exam "
                       ."INNER JOIN categorias_examenes as cat_exam "
                       ."ON exam.categoria_id = cat_exam.id WHERE exam.nombre_examen LIKE '%${parameters['q']}%' "
                       ."ORDER BY exam.nombre_examen LIMIT 0, 5;";

        bitacora($this, 'PROMOCIONES', 'BUSCAR PROMOCION');
        return $response->withJson(array(
            "data" => $this->db->rawQuery($stringQuery),
            "server" => true
        ), 201);
    });

    $app->get("/promocion/{idPromocion}", function(Request $request, Response $response, $arguments) {
        $session = $this->session->get('userObject'); 
        $one = $this->db
          ->where("id", $arguments["idPromocion"])
          ->where('sucursal_id', $session['sucursal_id'])
          ->getOne("promociones");

          if(count($one) > 0) {
            $one["examenes"] = $this->db->join("promocion_examenes as pro", "pro.examen_id = exam.id", "INNER")
                  ->join("categorias_examenes as cat", "cat.id = exam.categoria_id", "INNER")
                  ->where("pro.promocion_id", $arguments["idPromocion"])
                  ->get("examenes as exam", null, "exam.*, cat.nombre_categoria, pro.id");
            
            bitacora($this, 'PROMOCIONES', 'VIZUALIZAR DATO DE UNA PROMOCION');
            return $response->withJson(array(
                "data" => $one
            ), 201);
          }

          return $response->withJson(array(
              "data" => array()
          ), 201);

    });


    $app->post("/promocion", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $session = $this->session->get('userObject'); 

        $array = array(
          "estado" => 1,
          "precio" => $allPost["data"]['precio'],
          "sucursal_id" => $session['sucursal_id'],
          "nombre_promocion" => $allPost["data"]['nombre_promocion'],
        );

        bitacora($this, 'PROMOCIONES', 'AGREGAR DATO DE UNA PROMOCION');
        $promocion = $this->db->insert("promociones", $array);
        if($promocion){
            return $response->withJson(array(
                "data" => $this->db->where("id", $promocion)->getOne("promociones"),
                "message" => "Hemos agregado con exito la nueva promocion"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->post("/promocion/{idPromocion}/update", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $array = array(
            "nombre_promocion" => $allPost["data"]['nombre_promocion'],
            "precio" => $allPost["data"]['precio'],
        );

        bitacora($this, 'PROMOCIONES', 'ACTUALIZAR DATO DE UNA PROMOCION');
        $promocion = $this->db->where("id", $arguments["idPromocion"])->update("promociones", $array);
        if($promocion){
            return $response->withJson(array(
                "data" => $this->db->where("id", $arguments["idPromocion"])->getOne("promociones"),
                "message" => "Hemos actualizado con exito la promocion"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->post("/promocion/examen/delete", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $promocion = $this->db->where("id", $allPost["id"])->delete("promocion_examenes");

        bitacora($this, 'PROMOCIONES', 'BORRAR DATO DE UNA PROMOCION');
        if($promocion){
            return $response->withJson(array(
                "message" => "Hemos eliminado con exito el examen de la promocion"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

     $app->post("/promocion/estado", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();

        $promocion = $this->db->where("id", $allPost["id"])->update("promociones", array(
            "estado" => $this->db->not()
        ));

        bitacora($this, 'PROMOCIONES', 'DAR DE BAJA');
        if($promocion){
            return $response->withJson(array(
                "isReponse" => true,
                "message" => "Hemos actualizado con exito a la promocion"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->post("/promocion/add/examen", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();

        $examenPromocion = $this->db
                ->where("examen_id", $allPost['examen_id'])
                ->where("promocion_id", $allPost['promocion_id'])
                ->getOne("promocion_examenes");

        bitacora($this, 'PROMOCIONES', 'AGREGAR EXAMEN A LA PROMOCION');
        if(count($examenPromocion) > 0){
            return $response->withJson(array(
                "message" => "No puedes tener en una promocion el mismo examen dos veces"
            ), 502);
        }

        $array = array(
            "promocion_id"  => $allPost['promocion_id'],
            "examen_id"     => $allPost['examen_id']
        );

        $examen = $this->db->insert("promocion_examenes", $array);

        if($examen){
            $examen = $this->db->join("categorias_examenes as cat", "cat.id = exam.categoria_id", "INNER")
                        ->where("exam.id", $allPost['examen_id'])->getOne("examenes as exam");
            return $response->withJson(array(
                "data" => $examen,
                "message" => "Hemos agregado con exito el nuevo examen"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });
?>
