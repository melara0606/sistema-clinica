<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    $app->get("/entidad/{id}/examen", function(Request $request, Response $response, $arguments) {
        $one = $this->db->where("id", $arguments["id"])->getOne("entidades");

        bitacora($this, 'ENTIDADES-EXAMENES', 'VIZUALIZAR LOS EXAMENES');
        $one["examenes"] = $this->db->join("entidades_examenes as ent", "ent.id_examen = exam.id", "INNER")
                                    ->join("categorias_examenes as cat", "cat.id = exam.categoria_id", "INNER")
                                    ->where("ent.id_entidad", $arguments["id"])
                                    ->get("examenes as exam", null, "exam.*, cat.nombre_categoria, ent.id");
        return $response->withJson(array(
            "data" => $one
        ), 201);
    });


    $app->post('/examen/ordenCampos', function(Request $request, Response $response, $arguments) {
        $allPost = $request->getParsedBody();

        foreach ($allPost['ids'] as $key => $value) {
            $this->db->where('id', $value)->update('examen_campo', array(
                "orden_value"   => ($key + 1)
            ));
        }

        return $response->withJson(array(
            "mensaje" => "Hemos actualizado con exito el orden de los campos"
        ), 200);
    });

    $app->post("/entidad/add/examen", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        
        bitacora($this, 'ENTIDADES-EXAMENES', 'AGREGAR EXAMENES A LA ENTIDAD');
        $examenEntidad = $this->db
                ->where("id_examen", $allPost['id_examen'])
                ->where("id_entidad", $allPost['id_entidad'])
                ->getOne("entidades_examenes");


        if(count($examenEntidad) > 0){
            return $response->withJson(array(
                "message" => "No puedes tener en una entidad el mismo examen dos veces"
            ), 502);
        }

        $array = array(
            "id_entidad"  => $allPost['id_entidad'],
            "id_examen"   => $allPost['id_examen'],
            "precio"      => $allPost['precio'],
        );

        $examen = $this->db->insert("entidades_examenes", $array);

        if($examen){
            $examen = $this->db->join("categorias_examenes as cat", "cat.id = exam.categoria_id", "INNER")
                        ->where("exam.id", $allPost['id_examen'])->getOne("examenes as exam");
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

    $app->post("/entidad/examen/delete", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();
        $entidad = $this->db->where("id", $allPost["id"])->delete("entidades_examenes");

        bitacora($this, 'ENTIDADES-EXAMENES', 'ELIMINAR EXAMENES A LA ENTIDAD');
        if($entidad){
            return $response->withJson(array(
                "message" => "Hemos eliminado con exito el examen de la promocion"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    // routes para el monto y la entidad
    $app->post("/entidad/add/monto", function(Request $request, Response $response, $arguments){
        $allPost = $request->getParsedBody();

        bitacora($this, 'ENTIDADES-EXAMENES', 'AGREGAR MONTO A LA ENTIDAD');
        $montoEntidad = $this->db
                ->where("id_entidad", $allPost['id_entidad'])
                ->getOne("entidad_monto");

        if(count($montoEntidad) == 0){
            $montoEntidad["id"] = $this->db->insert("entidad_monto", array(
                "id_entidad"  => $allPost['id_entidad'],
                "monto"       => "0.0"
            ));
        }

        $array = array(
            "id_monto"          => $montoEntidad["id"],
            "monto"             => floatval($allPost['monto']),
            "fecha_ingreso"     => $this->db->now()
        );
        $montoHistorial = $this->db->insert("monto_historial", $array);
        if($montoHistorial){
            $monto = $this->db->where("id", $montoEntidad["id"])->update("entidad_monto", array(
                "monto" => $this->db->inc( floatval($allPost['monto']) )
            ));

            if($monto){
                return $response->withJson(array(
                    "message"   => "Hemos actualizado con exito el monto de la entidad",
                    "data"      => $this->db->where("id", $montoEntidad["id"])->getOne("entidad_monto")
                ), 201);
            }else{
                return $response->withJson(array(
                    "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
                ), 502);
            }
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador"
            ), 502);
        }
    });

    $app->get("/entidad/{id_entidad}/monto", function(Request $request, Response $response, $arguments) {
        $all = $this->db->where('id_entidad', $arguments['id_entidad'])->getOne("entidad_monto");

        bitacora($this, 'ENTIDADES-EXAMENES', 'VIZUALIZAR MONTO DE LA ENTIDAD');
        return $response->withJson(array(
            "data" => $all
        ), 201);
    });

    $app->get("/entidad/{id_entidad}/examenes/monto", function(Request $request, Response $response, $arguments){

        $all = $this->db->where("id", $arguments["id_entidad"])->getOne("entidades");
        $all["monto"] = $this->db->where("id_entidad", $arguments["id_entidad"])->getOne("entidad_monto");

        $sqlQuery = "SELECT entidades_examenes.precio, entidades_examenes.id_entidad, entidades_examenes.id, entidades_examenes.id_examen, examenes.nombre_examen, examenes.categoria_id, categorias_examenes.nombre_categoria FROM categorias_examenes INNER JOIN examenes ON categorias_examenes.id = examenes.categoria_id INNER JOIN entidades_examenes ON entidades_examenes.id_examen = examenes.id WHERE  entidades_examenes.id_entidad = '${arguments["id_entidad"]}' ORDER BY  examenes.nombre_examen";

        $all["examenes"] = $this->db->rawQuery($sqlQuery);
        return $response->withJson(array(
            "data" => $all
        ), 201);     
    });
?>
