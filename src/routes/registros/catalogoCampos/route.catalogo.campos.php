<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    $app->get('/examenes/all', function(Request $request, Response $response, $arguments){
      bitacora($this, 'EXAMENES', 'VIZUALIZAR DATOS');
        return $response->withJson(array(
            "object" => $this->db->get('examenes')
        ), 201);
    });

    $app->post('/campos/options/delete', function(Request $request, Response $response, $arguments)
    {
        bitacora($this, 'CAMPOS EXAMENES', 'BORRAR UN CAMPO');
        $object = $request->getParsedBody();
        $isResponse = $this->db->
            where('id', $object["code"])->
            delete('seleccion_campos');

        if($isResponse){
            return $response->withJson(array(
                "response" => $isResponse
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => $this->db->getLastError()
            ), 301);
        }
    });

    $app->post('/catalogoCampos/add/campus', function(Request $request, Response $response)
    {
        $object = $request->getParsedBody();
        bitacora($this, 'CAMPOS EXAMENES', 'AGREGAR UN CAMPO');

        $one = $this->db->where('grupo_seleccion', $object['grupoSeleccion'])->getOne('seleccion_campos', 'nombre_grupo, is_multiple');

        $isResponse = $this->db->insert('seleccion_campos', array(
            "nombre_seleccion" => $object['name'],
            "grupo_seleccion"  => $object['grupoSeleccion'],
            "nombre_grupo"     => $one['nombre_grupo'],
            "is_multiple"      => $one['is_multiple']
        ));

        if($isResponse){
            return $response->withJson(array(
                "message" => "Hemos agregado con exito el nuevo elemento",
                "data"    => $this->db->where('id', $isResponse)->getOne('seleccion_campos')
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => $this->db->getLastError()
            ), 301);
        }
    });

    $app->get('/catalogoCampos', function(Request $request, Response $response, $arguments){
        $all = $this->db->orderBy('nombre_tipo_campo')->get("tipo_catalogo_campo");
        bitacora($this, 'CAMPOS EXAMENES', 'VIZUALIZAR DATOS');
        return $response->withJson(array(
            "data" => $all
        ), 201);
    });

    $app->get('/objectCategoria', function(Request $request, Response $response, $arguments) {
        return $response->withJson(array(
            "data" => $this->db->get('categoria_grupo_seleccion')
        ), 201);
    });

    $app->post('/objectCategoria', function(Request $request, Response $response, $arguments) {
        $object = $request->getParsedBody();
        $id = $this->db->insert('categoria_grupo_seleccion', array(
            "nombre_categoria_grupo"    => $object['nombre']
        ));

        return $response->withJson(array(
            "message" => "Hemos agregado con exito la nueva categoria",
            "data"    => $this->db->where('id', $id)->getOne('categoria_grupo_seleccion')
        ), 201);
    });

    $app->post('/objectCategoria/deleteCategoriaGrupo', function(Request $request, Response $response, $arguments) {
        $object = $request->getParsedBody();        
        $res = $this->db->where('id', $object['id'])->delete('categoria_grupo_seleccion');
        if($this->db->getLastError()) {
            return $response->withJson(array(
                "message" => "Lo sentimos pero no podemos eliminar esta categoria, posiblemente tenga grupos aun.",
            ), 401);
        }else{
            return $response->withJson(array(
                "message" => "La peticion fue realizada con exito"
            ), 201);
        }
    });

    $app->post('/objectCategoria/update', function(Request $request, Response $response, $arguments) {
        $object = $request->getParsedBody();        
        $isResponse = $this->db->where('id', $object['data']['id'])->update('categoria_grupo_seleccion', array(
            "nombre_categoria_grupo" => $object['data']['nuevoCategoriaNombre']
        ));

        if($isResponse) {
            return $response->withJson(array(
                "message" => "Hemos actualizado con exito la categoria",
                "data"    => $this->db->where('id', $object['data']['id'])->getOne('categoria_grupo_seleccion')
            ), 200);
        }else{
            return $response->withJson(array(
                "message" => "Tenemos un problema con el servidor intenta mas tarde"
            ), 401);
        }
    });

    $app->get('/objectCategoria/grupos/{id}', function(Request $request, Response $response, $arguments) {
        $gruposActuales = "SELECT DISTINCT grupo_seleccion, nombre_grupo FROM seleccion_campos WHERE is_multiple = 1 AND grupo_seleccion NOT IN ( SELECT grupo_id FROM grupo_categoria_seleccion WHERE categoria_grupo_seleccion_id = '".$arguments['id']."' )";

        $grupoSelecionados = "SELECT DISTINCT grupo_id, nombre_grupo FROM grupo_categoria_seleccion as gc inner join seleccion_campos as sc on gc.grupo_id = sc.grupo_seleccion where categoria_grupo_seleccion_id = '".$arguments['id']."'";

        return $response->withJson(array(
            "data"      => $this->db->rawQuery($gruposActuales),
            "grupos"    => $this->db->rawQuery($grupoSelecionados)
        ), 201);
    });

    $app->post('/objectCategoria/grupos', function(Request $request, Response $response, $arguments) {
        $object = $request->getParsedBody();
        $this->db->where('categoria_grupo_seleccion_id', $object['id'])->delete('grupo_categoria_seleccion');

        foreach ($object['data'] as $key => $value) {
            $this->db->insert('grupo_categoria_seleccion', array(
                "categoria_grupo_seleccion_id"  => $object['id'],
                "grupo_id"                      => $value['grupo_seleccion']
            ));
            if($key == 1) break;
        }

        return $response->withJson(array(
            "message" => "Hemos agregado con exito los grupos a la categoria"
        ), 201);
    });

    $app->get("/campos/options/{item}", function(Request $request, Response $response, $arguments){
        $items = $this->db->where('grupo_seleccion', $arguments["item"])->get("seleccion_campos");
        bitacora($this, 'CAMPOS EXAMENES', 'VIZUALIZAR UN DATO');
        return $response->withJson(array(
            "data" => $items
        ), 201);
    });

    $app->get('/seleccion_campos/data/{conteo}', function(Request $request, Response $response, $arguments) {
        $SQLQuery = "SELECT DISTINCT (grupo_seleccion), nombre_grupo, is_multiple FROM seleccion_campos WHERE grupo_seleccion ='".$arguments['conteo']."'";
        return $response->withJson(array(
            "data" => $this->db->rawQuery($SQLQuery)
        ), 201);
    });

    $app->get("/catalogoCampos/{item}", function(Request $request, Response $response, $arguments) {
        $one = $this->db->where("id", $arguments["item"])->getOne("tipo_catalogo_campo");
        $one["campos"] = $this->db->where("id_tipo_catalogo", $arguments["item"] )->get("catalogo_campos");
        
        if($arguments["item"] == '1'){
            $one["grupos"] = $this->db->rawQuery("SELECT DISTINCT (grupo_seleccion), nombre_grupo FROM seleccion_campos");
            $one["gruposObject"] = $this->db->get('seleccion_campos');
        }else if($arguments["item"] == '4'){
            $one["campos"] = $this->db
                ->join('categoria_grupo_seleccion', 
                    'catalogo_campos.categoria_seleccion = categoria_grupo_seleccion.id', 'INNER')
                ->get('catalogo_campos', null,'catalogo_campos.*, categoria_grupo_seleccion.nombre_categoria_grupo');
        }

        return $response->withJson(array(
            "data" => $one
        ), 201);
    });

    $app->post("/catalogoCampos/add", function(Request $request, Response $response, $arguments){
        $object = $request->getParsedBody();
        $type = $object["object"];

        $array = array(
            "id_tipo_catalogo"  => $type,
            "nombre_campo" => $object["item"]["nombre_campo"],
            "unidades"     => $object["item"]["unidades"]
        );

        if( $type == '1')
            $array["grupo_seleccion"] = $object["item"]["grupo_seleccion"];
        else if( $type == '2')
            $array["rango_valor"] = $object["item"]["rango_valor"];
        else if($type == '4')
            $array["categoria_seleccion"] = $object["item"]["categoria_seleccion"];

        $isResponse = $this->db->insert("catalogo_campos", $array);
        bitacora($this, 'CAMPOS EXAMENES', 'AGREGAR UN CAMPO');

        if($isResponse){
            $one = $this->db
                ->join('categoria_grupo_seleccion', 
                    'catalogo_campos.categoria_seleccion = categoria_grupo_seleccion.id', 'LEFT')
                ->where('catalogo_campos.id', $isResponse)
                ->getOne("catalogo_campos", 'catalogo_campos.*,categoria_grupo_seleccion.nombre_categoria_grupo');
            return $response->withJson(array(
                "data" => $one,
                "response" => true,
                "message" => "Hemos agregado con exito el nuevo campo"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador",
                "response" => false
            ), 502);
        }
    });

    $app->post("/catalogoCampos/{id}/edit", function(Request $request, Response $response, $arguments){
        $object = $request->getParsedBody();
        $type = $object["object"];

        $array = array(
            "id_tipo_catalogo"  => $type,
            "nombre_campo" => $object["item"]["nombre_campo"],
            "unidades"     => $object["item"]["unidades"]
        );

        bitacora($this, 'CAMPOS EXAMENES', 'EDITAR UN CAMPO');
        if( $type == '1'){
          $array["grupo_seleccion"] = $object["item"]["grupo_seleccion"];
          $array["valor_opcional"]  = $object["item"]["valor_opcional"];
        }
        else if( $type == '2')
            $array["rango_valor"] = $object["item"]["rango_valor"];
        else if($type == '4')
            $array["categoria_seleccion"] = $object["item"]["categoria_seleccion"];

        $isResponse = $this->db->where("id", $arguments["id"])->update("catalogo_campos", $array);
        if($isResponse){
            $one = $this->db
                ->join('categoria_grupo_seleccion', 
                    'catalogo_campos.categoria_seleccion = categoria_grupo_seleccion.id', 'LEFT')
                ->where('catalogo_campos.id', $arguments["id"])
                ->getOne("catalogo_campos", 'catalogo_campos.*,categoria_grupo_seleccion.nombre_categoria_grupo');

            //$one = $this->db->where('id', )->getOne("catalogo_campos");
            return $response->withJson(array(
                "data" => $one,
                "response" => true,
                "message" => "Hemos agregado con exito el nuevo campo"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador",
                "response" => false
            ), 502);
        }
    });

    $app->post('/campos/creacion/update', function(Request $request, Response $response) {
        $object = $request->getParsedBody();        
        
        $isResponse = $this->db->where('id', $object['data']['id'])->update('seleccion_campos', array(
            'nombre_seleccion' => $object['data']['nuevoNombre']
        ));

        bitacora($this, 'CAMPOS EXAMENES', 'EDITAR UN CAMPO');
        if($isResponse){
            return $response->withJson(array(
                "data" => $this->db->where('id', $object['data']['id'])->getOne('seleccion_campos'),
                "message" => "Hemos actualizado con exito"
            ), 201);
        }else{
            return $response->withJson(array(
                "message" => "Hemos tenido un problema por el momento, ponte en contacto con tu administrador",
                "response" => false
            ), 502);
        }
    });

    $app->post('/campos/creacion/add', function(Request $request, Response $response) {
        $object = $request->getParsedBody();
        $results = $this->db->orderBy('id', 'DESC')->getOne('seleccion_campos', 'grupo_seleccion');

        $grupoSeleccion = intval($results['grupo_seleccion']);
        bitacora($this, 'CAMPOS EXAMENES', 'AGREGAR UN CAMPO');
        if($grupoSeleccion > 0){
            $resultDB = $this->db->insert('seleccion_campos', array(
                "nombre_seleccion" => "NUEVO CAMPO",
                "grupo_seleccion"  => ++$grupoSeleccion,
                "nombre_grupo"     => $object['nombre'],
                "is_multiple"      => $object['isMultiple']
            ));

            if($resultDB){
                return $response->withJson(array(
                    "data" => $this->db->where('id', $resultDB)->getOne('seleccion_campos')
                ), 201);
            }
        }
    });

    $app->post('/campos/creacion/edit', function(Request $request, Response $response) {
        $object = $request->getParsedBody();
        bitacora($this, 'CAMPOS EXAMENES', 'ACTUALIZAR UN CAMPO');

        $resultDB = $this->db->where('grupo_seleccion', $object['grupSelecion'])->update('seleccion_campos', array(
            "nombre_grupo"  => $object['nombre'],
            "is_multiple"   => $object['isMultiple']
        ));

        $SQLQuery = "SELECT DISTINCT (grupo_seleccion), nombre_grupo, is_multiple FROM seleccion_campos WHERE grupo_seleccion ='".$object['grupSelecion']."'";
        return $response->withJson(array(
            "data" => $this->db->rawQuery($SQLQuery)
        ), 201);
    });
    

    /* para los campos multiples */
    function generar_id($a = array(), $db){
        $arrayCategoria = array();
        $str = $a[0]['categoria_seleccion'].", ".$a[1]['categoria_seleccion'];
        $SQLQuery = "SELECT seleccion_campos.nombre_seleccion, seleccion_campos.grupo_seleccion, seleccion_campos.nombre_grupo,grupo_categoria_seleccion.categoria_grupo_seleccion_id FROM seleccion_campos INNER JOIN grupo_categoria_seleccion ON seleccion_campos.grupo_seleccion = grupo_categoria_seleccion.grupo_id WHERE grupo_categoria_seleccion.categoria_grupo_seleccion_id IN ( ". $str ." ) ORDER BY grupo_categoria_seleccion.categoria_grupo_seleccion_id";
        $g = $db->rawQuery($SQLQuery);
        
        foreach ($g as $value) {
            if(!array_key_exists($value['categoria_grupo_seleccion_id'], $arrayCategoria)){
                $arrayCategoria[$value['categoria_grupo_seleccion_id']] = array();
            }

            if(!array_key_exists($value['grupo_seleccion'], $arrayCategoria[$value['categoria_grupo_seleccion_id']])){
                $arrayCategoria[$value['categoria_grupo_seleccion_id']][$value['grupo_seleccion']] = array();
            }

            array_push($arrayCategoria[$value['categoria_grupo_seleccion_id']][$value['grupo_seleccion']], $value);
        }

        return $arrayCategoria;
    }

    $app->get('/examen_multiples', function(Request $request, Response $response, $args) {
        $arrayResponse = array();

        $arrayResponse['campos'] = $this->db
            ->where('catalogo_campos.id_tipo_catalogo', 4)
            ->orderBy('catalogo_campos.categoria_seleccion')
            ->get('catalogo_campos', null, 
                'catalogo_campos.id, catalogo_campos.nombre_campo, catalogo_campos.categoria_seleccion');
        
        $arrayResponse['categoria_seleccion'] = $this->db
            ->join('categoria_grupo_seleccion', 'catalogo_campos.categoria_seleccion = categoria_grupo_seleccion.id', 'INNER')
            ->where('catalogo_campos.id_tipo_catalogo', 4)
            ->orderBy('catalogo_campos.categoria_seleccion')
            ->get('catalogo_campos', null, 'DISTINCT (catalogo_campos.categoria_seleccion), categoria_grupo_seleccion.nombre_categoria_grupo');

        $arrayResponse['grupo_seleccion'] = generar_id($arrayResponse['categoria_seleccion'], $this->db);
        
        return $response->withJson(array(
            "arrayOfList" => $arrayResponse
        ), 201);
    });

    $app->post('/examen_multiples/response', function(Request $request, Response $response, $args) {
        $object = $request->getParsedBody();
        $rResultado =$this->db
            ->join('catalogo_campos as cc', 'sr.catalogo_campo_id = cc.id', 'INNER')
            ->where('cc.id_tipo_catalogo', 4)
            ->where('sr.solicitud_item_examen_id', $object['id'])
            ->get('solicitud_respuesta as sr', null, 'sr.*');

        return $response->withJson(array(
            "arrayOfList" => $rResultado
        ), 201);
    });

    $app->post('/examen_multiples/add', function(Request $request, Response $response, $args) {
        $arrayResponse = array();
        $object = $request->getParsedBody();
        
        foreach ($object['campos'] as $key => $value) {
            foreach ($value as $i) {
                if(isset($i['resultado'])){
                    $array = array(
                        "catalogo_campo_id"             => $i['id'],
                        "solicitud_item_examen_id"      => $object['solicitudId'],
                        "resultado"                     => json_encode($i['resultado']),
                        "seleccion_valor"               => "",
                        "criterio_rango"                => ""
                    );

                    if(isset($i['id_resultado'])){
                        $responseResponse = $this->db
                            ->where('id', $i['id_resultado'])
                            ->update('solicitud_respuesta', $array);
                    }else{
                        $responseResponse = $this->db
                            ->insert('solicitud_respuesta', $array);
                    }
                    array_push($arrayResponse, $responseResponse);
                }
            }
        }
        return $response->withJson(array(
            "arrayOfList" => $arrayResponse,
            "response"    => true,
            "message"     => "Hemos realizado con exito la peticion."
        ), 201);
    });