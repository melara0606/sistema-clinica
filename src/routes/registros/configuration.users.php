<?php
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  function onReloadRecursos($session, $db) {
    $userObject = $session->get("userObject");
    $userObject['recursos'] = $db->join("perfil_recursos as pre", "pre.recurso_id = rec.id", "INNER")
              ->where("pre.perfil_id", $userObject["perfil"]["perfil_id"])
              ->get("recursos as rec");
    $session->set('userObject', $userObject);
  }

  $app->group('/users', function() {
    $this->get('/list', function(Request $request, Response $response, $args) {
      $userObject = $this->db->join("usuarios as us", "us.id_user = eu.usuario_id", "INNER")
        ->join("empleados as emp", "eu.empleado_id = emp.id", "INNER")
        ->join("sucursales as suc", "emp.sucursal_id = suc.id", "INNER")
        ->join("usuarios_perfiles as up", "up.usuario_id = us.id_user", "INNER")
        ->join("perfiles as per", "per.id = up.perfil_id", "INNER")
        ->get("empleado_usuario as eu", null, "us.id_user, us.email, emp.name_emp, emp.lastname_emp, emp.id as empleadoCode, suc.nombre_sucursal, suc.id as idSucursal, per.nombre, per.id as idPerfil, us.estado");

      return $response->withJson( $userObject, 201);
    });

    $this->post('/QueryEmployeer', function(Request $request, Response $response, $args){
      $parsedBody = $request->getParsedBody();
      $sqlQuery = "select e.id,  e.name_emp, e.lastname_emp, e.sucursal_id, u.id_user, u.email from empleados as e left join empleado_usuario as eu on e.id = eu.empleado_id left join usuarios as u on eu.usuario_id = u.id_user where e.sucursal_id = '${parsedBody['sucursal']}' and u.email is null";

      return $response->withJson(array(
        "data" => $this->db->rawQuery($sqlQuery)
      ), 201);
    });

    $this->post("/changeStatus", function(Request $request, Response $response, $args){
      $parsedBody = $request->getParsedBody();
      $isRsponse = $this->db->where("id_user", $parsedBody["user"]["id_user"])->update("usuarios", array(
        "estado"  => $this->db->not()
      ));

      if($isRsponse){
        return $response->withJson(array(
          "response" => "Hemos actualizado con exito el estado del usuario"
        ), 201);
      }
    });

    $this->post('/createUser', function(Request $request, Response $response, $args) {
      $parsedBody = $request->getParsedBody();
      $employeer = $parsedBody['employeer'];

      $userExists = $this->db->where('email', $employeer['user'])->getOne('usuarios');

      if(@$userExists['email'] ){
        return $response->withJson(array(
          "message" => "Lo sentimos pero el usuario ya existe, prueba con otro"
        ), 501);
      }

      $passwordToken = base64_encode(hash("whirlpool", $employeer['password'] ));
      $hash = crypt($passwordToken, '$6$admin$');

      $userArray = array(
        "email"           => $employeer['user'],
        "sucursal_id"     => $employeer['sucursal'],
        "password"        => $hash,
        "is_admin"        => 0,
        "remember_token"  => 'Ekjuy01',
        "estado"          => 1,
        "created_at"      => $this->db->now(),
        "updated_at"      => $this->db->now()
      );

      $idUserNew = $this->db->insert('usuarios', $userArray);
      if($idUserNew){
        $idUserEmployeer = $this->db->insert('empleado_usuario', array(
          "usuario_id" => $idUserNew, "empleado_id" => $employeer['empleado']
        ));

        $idUserPerfil = $this->db->insert('usuarios_perfiles', array(
          "usuario_id" => $idUserNew, "perfil_id" => $employeer['perfil']
        ));

        $userObject = $this->db->join("usuarios as us", "us.id_user = eu.usuario_id", "INNER")
          ->join("empleados as emp", "eu.empleado_id = emp.id", "INNER")
          ->join("sucursales as suc", "emp.sucursal_id = suc.id", "INNER")
          ->join("usuarios_perfiles as up", "up.usuario_id = us.id_user", "INNER")
          ->join("perfiles as per", "per.id = up.perfil_id", "INNER")
          ->where('emp.id', $employeer['empleado'])
          ->getOne("empleado_usuario as eu", null, "us.id_user, us.email, emp.name_emp, emp.lastname_emp, emp.id as empleadoCode, suc.nombre_sucursal, suc.id as idSucursal, per.nombre, per.id as idPerfil, us.estado");

          return $response->withJson(array(
            "data" => $userObject
          ), 201);
      }else{
        return $response->withJson(array(
          "message" => "Lo sentimos pero tenemos un problema por el momento"
        ), 501);
      }
    });

    $this->post("/changePassword", function(Request $request, Response $response, $args) {
      $parsedBody = $request->getParsedBody();
      $passwordToken = base64_encode(hash("whirlpool", $parsedBody["passwordTxt"]));
      $hash = crypt($passwordToken, '$6$admin$');

      $isResponse = $this->db->where("id_user", $parsedBody["idUser"])->update("usuarios", array(
        "password" => $hash
      ));

      if($isResponse){
        return $response->withJson(array(
          "response" => "Hemos actualizado con exito la contraseña del usuario"
        ), 201);
      }else{
        return $response->withJson(array(
          "response" => "Tenemos un problemas por el momento"
        ), 503);
      }
    });

    $this->group('/recursos', function() {
       $this->map(["GET", "POST"], "", function(Request $request, Response $response, $args){
        $methods = $request->getMethod();
        if( strcmp($methods, 'GET') === 0 ){
          return $response->withJson( 
            $this->db->orderBy('nombre', 'asc')->get("recursos"), 201);
        }else{
          $parsedBody = $request->getParsedBody();
          $array = array(
            "nombre"    => $parsedBody["nombre"],
            "icons"     => $parsedBody["icons"]
          );

          $isResponse = $this->db->where("id", $parsedBody["id"])->update("recursos", $array);

          if($isResponse){
            return $response->withJson(array(
              "response" => "Hemos actualizado con exito el recurso",
              "data"     => $this->db->where("id", $parsedBody["id"])->getOne("recursos")
            ), 201);
          }else{
            return $response->withJson(array(
              "response" => "Tenemos un problemas por el momento"
            ), 503);
          }
        }
      });
    });

    $this->group('/perfiles', function() {
      $this->map(["GET", "POST"], "", function(Request $request, Response $response, $args){
        $methods = $request->getMethod();
        if( strcmp($methods, 'GET') === 0 ){
          return $response->withJson( $this->db->where('is_view', 1)->get("perfiles"), 201);
        }else if(strcmp($methods, 'POST') === 0){
          $parsedBody = $request->getParsedBody();
          $id = $this->db->insert("perfiles", array(
            "nombre"          => $parsedBody["nombre"],
            "fecha_registro"  => $this->db->now()
          ));
          return $response->withJson( array(
            "data" => $this->db->where("id", $id)->getOne("perfiles")
          ), 201);
        }
      });

      $this->post("/{id}/delete", function(Request $request, Response $response, $args){
        $id = $args["id"];
        if( $this->db->where("id", $id)->delete("perfil_recursos") ) {
          onReloadRecursos($this->session, $this->db);
          return $response->withJson( array(
            "message" => "Hemos eliminado el recurso con exito"
          ), 201);
        }else{
          return $response->withJson( array(
            "message" => "Tenemos un problemas por el momento, ponte en contacto con tu administrador"
          ), 401);
        }
      });

      $this->post("/{id}/recursos", function(Request $request, Response $response, $args){
        $parsedBody = $request->getParsedBody();
        $this->db->where("id", $args["id"])->update("perfil_recursos", array(
          "consultar"   => $parsedBody["item"]["consultar"],
          "agregar"     => $parsedBody["item"]["agregar"],
          "editar"      => $parsedBody["item"]["editar"],
          "eliminar"    => $parsedBody["item"]["eliminar"]
        ));

        $recurso = $this->db
          ->join("perfil_recursos as pr", "pr.recurso_id = r.id", "INNER")
          ->where("pr.id", $args["id"])
          ->getOne("recursos as r", "r.*, pr.*");

        onReloadRecursos($this->session, $this->db);
        return $response->withJson(array(
          "message" => "Hemos actualizado con exito el recurso al perfil",
          "data"       => $recurso
        ), 201);
      });

      $this->post("/recursos", function(Request $request, Response $response, $args){
        $parsedBody = $request->getParsedBody();

        $perfilId = $this->db->insert("perfil_recursos", array(
          "consultar"   => $parsedBody["item"]["consultar"],
          "agregar"     => $parsedBody["item"]["agregar"],
          "editar"      => $parsedBody["item"]["editar"],
          "eliminar"    => $parsedBody["item"]["eliminar"],
          "recurso_id"  => $parsedBody["item"]["recurso"],
          "perfil_id"   => $parsedBody["item"]["perfil"]
        ));

        $recurso = $this->db
          ->join("perfil_recursos as pr", "pr.recurso_id = r.id", "INNER")
          ->where("pr.id", $perfilId)
          ->orderBy('r.nombre', 'asc')
          ->get("recursos as r", null, "r.*, pr.*");

        onReloadRecursos($this->session, $this->db);
        return $response->withJson(array(
          "message" => "Hemos agregado con exito el recurso al perfil",
          "data"    => $recurso,
          "query"   => $this->db->getLastQuery()
        ), 201);
      });

      $this->post('/update', function(Request $request, Response $response, $args){
        $parsedBody = $request->getParsedBody();
        $idUpdate = $this->db->where("id", $parsedBody['perfil']['id'])->update('perfiles', array(
          "nombre" => $parsedBody['perfil']['nombre']
        ));

        if($idUpdate){
          return $response->withJson( array(
            "data" => $this->db->where('id', $parsedBody['perfil']['id'])->getOne('perfiles')
          ), 201);
        }else{
          return $response->withJson( array(
            "message" => 'Tenemos un problemas por el momento'
          ), 501);
        }
      });

      $this->get("/recursosperfiles", function(Request $request, Response $response, $args){
        $parametrs = $request->getQueryParams();
        $subQuery = $this->db->subQuery();
        $subQuery->where("perfil_id", $parametrs["perfil"]);
        $subQuery->get("perfil_recursos", null, "recurso_id");

        $recursos = $this->db
                    ->where("id", $subQuery, "not in")
                    ->where('isActive', 1)
                    ->get("recursos");

        return $response->withJson( $recursos , 201);
      });

      $this->get("/{id}", function(Request $request, Response $response, $args){
        $perfil = $this->db->where("id", $args["id"])->getOne("perfiles");
        $perfil["usuarios"] = $this->db
          ->join("usuarios_perfiles as up", 'up.usuario_id = u.id_user', 'INNER')
          ->join("empleado_usuario as eu", 'eu.usuario_id = u.id_user', 'INNER')
          ->join("empleados as e", 'e.id = eu.empleado_id', 'INNER')
          ->where("up.perfil_id", $args["id"])
          ->get("usuarios as u", null,
            'e.name_emp, e.lastname_emp, u.email, u.id_user, u.estado'
          );

        $perfil["recursos"] = $this->db
          ->join("perfil_recursos as pr", "pr.recurso_id = r.id", "INNER")
          ->where("pr.perfil_id", $args["id"])
          ->orderBy('r.nombre', 'asc')
          ->get("recursos as r", null, "r.*, pr.*");

        return $response->withJson( $perfil , 201);
      });

      $this->post("/changePerfil", function(Request $request, Response $response, $args){
        $parsedBody = $request->getParsedBody();
        $isResponse = $this->db->where("usuario_id", $parsedBody["perfil"]["id_user"])->update("usuarios_perfiles", array(
          "perfil_id" => $parsedBody["change"]
        ));

        if($isResponse){
          $objectUser = $this->db->where("id", $parsedBody["change"])->getOne("perfiles");

          return $response->withJson(array(
            "response" => "Hemos actualizado con exito el perfil del usuario",
            "data" => $objectUser
          ), 201);
        }else{
          return $response->withJson(array(
            "response" => "Tenemos un problemas por el momento"
          ), 503);
        }
      });
    });

    $this->post('/changeStatusRecurso', function(Request $request, Response $response, $args) {
      $parsedBody = $request->getParsedBody();
      $this->db->where('id', $parsedBody['id'])->update('recursos', array(
        "isActive" => $this->db->not()
      ));

      return $response->withJson( array(
        "message"    => "Hemos realizado con exito la actualizacion",
        "object"     => $this->db->where('id', $parsedBody['id'])->getOne('recursos')
      ), 201);
    });
  });
?>
