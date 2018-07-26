<?php
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  $app->get('/fechavecimiento', function(Request $request, Response $response, $args) {
    $fechaCurrent = strftime( "%Y-%m-%d", time());
    $fechaDentro5 = strtotime ( '+25 day' , strtotime($fechaCurrent));
    $SQLQuery = "SELECT compras_items.precio, compras_items.cantidad, compras_items.fecha_vencimiento,    materiales.nombre_material, catalogo_materiales.is_fecha_vencimiento FROM compras_items INNER JOIN materiales ON compras_items.material_id = materiales.id INNER JOIN catalogo_materiales ON materiales.catalogo_id = catalogo_materiales.id WHERE is_fecha_vencimiento = 1 AND compras_items.estado = 0 AND compras_items.fecha_vencimiento BETWEEN '".$fechaCurrent."' and '".date('Y-m-j', $fechaDentro5)."'";

    return $response->withJson(array(
      "productsVecimiento"  => $this->db->rawQuery($SQLQuery)
    ), 201);
  });

  $app->get('/admin', function (Request $request, Response $response, $args) {
    if(!$this->session->exists('userObject'))
      return $response->withStatus(302)->withHeader('Location', 'admin/login');

    return $this->view->render($response, 'index.phtml');
  })->setName('home');

  $app->group('/admin/login', function() {
    $this->map(['GET', 'POST'], '', function($request, $response, $args) {
      $methods = $request->getMethod();
      if( strcmp($methods, 'GET') === 0 ){
        $flashMessage = $this->flash->getMessages();
        $this->view->render($response, 'login.phtml', [
          'flash' => @$flashMessage['errorLogin'][0],
          'base' => $this->baseUrl
        ]);
      } else if(strcmp($methods, 'POST') === 0) {
        $router = $this->router;
        bitacora($this, 'USUARIO', 'VER EL PERFIL');
        $parameters = $request->getParsedBody();
        $userObject = $this->db->where('email', $parameters['email'])->getOne('usuarios');
        $userPaciente = $this->db->where('codigo_paciente', $parameters['email'])->getOne('pacientes');

        if( !$parameters['email'] || !$parameters['password'] ){
          $this->flash->addMessage('errorLogin', array(
            "message" => 'Debes ingresar los datos necesarios',
            "usuario" => $parameters['email']
          ));
          return $response->withStatus(302)->withHeader('Location', 'admin/login');
        }

        if($userObject['estado'] === 0 || $userPaciente['status'] === 0){
          $this->flash->addMessage('errorLogin', array(
            "message" => 'El usuario esta desactivado, ponte en contacto con tu administrador'
          ));
          return $response->withStatus(302)->withHeader('Location', 'admin/login');
        }

        $parameters['password'] = base64_encode(hash("whirlpool", $parameters['password']));

        // verificacion de un usuario normal -- sistema
        if($userObject){
          if(password_verify($parameters['password'], $userObject['password'] )) {
            $changeSucursal = false;
            $perfilUser = $this->db
                            ->join('usuarios_perfiles as up', 'up.perfil_id = pr.id', 'INNER')
                            ->where('up.usuario_id', $userObject['id_user'])
                            ->getOne('perfiles as pr');

            $userObject['data'] = $this->db
                  ->join('empleado_usuario as emUser', 'emUser.empleado_id = emp.id', 'INNER')
                  ->where('emUser.usuario_id', $userObject['id_user'])
                ->getOne('empleados as emp', 'emp.name_emp, emp.lastname_emp, emp.id');

            $userObject['perfil'] = $perfilUser;
            $userObject['recursos'] = $this->db->join("perfil_recursos as pre", "pre.recurso_id = rec.id", "INNER")
                      ->where("pre.perfil_id", $userObject["perfil"]["perfil_id"])
                      ->where('rec.isActive', 1)
                      ->orderBy('rec.nombre', 'asc')
                      ->get("recursos as rec");

            $userObject['sucursal'] = $this->db->where('id', $userObject['sucursal_id'])
                                        ->getOne('sucursales', 'id, nombre_sucursal');
            $userObject["is_paciente"] = false;
            $this->session->set('userObject', $userObject);
            foreach ($userObject['recursos'] as $value) {
              if( strcmp( $value["url"], "changesucursal" ) === 0 ){
                $changeSucursal = true;
              }
            }

            bitacora($this, 'LOGIN', "EL USUARIO ".$parameters['email'] ." ENTRO AL SISTEMA");
            if($changeSucursal)
                return $response->withRedirect($router->pathFor('select'));
            return $response->withRedirect($router->pathFor('home'));
          }else {
            $this->flash->addMessage('errorLogin', array(
              "message" => 'Contraseña incorrecta, revisa bien tu credeciales.',
              "usuario" => $parameters['email']
            ));
            return $response->withStatus(302)->withHeader('Location', 'login');
          }
        }

        if(password_verify($parameters['password'], $userPaciente['password'] )) {
          $perfilUser = $this->db->where('pr.id', 6)->getOne('perfiles as pr');
          $userPaciente['perfil'] = $perfilUser;
          $userPaciente['recursos'] = $this->db->join("perfil_recursos as pre", "pre.recurso_id = rec.id", "INNER")
                    ->where("pre.perfil_id", $userPaciente["perfil"]["id"])
                    ->orderBy('rec.nombre', 'asc')
                    ->get("recursos as rec");
          $userPaciente["is_paciente"] = true;
          $this->session->set('userObject', $userPaciente);
          return $response->withRedirect($router->pathFor('home'));
        }else {
          $this->flash->addMessage('errorLogin', array(
            "message" => 'Contraseña incorrecta, revisa bien tu credeciales.',
            "usuario" => $parameters['email']
          ));
          return $response->withStatus(302)->withHeader('Location', 'login');
        }
      }
    });
  });

    $app->get("/generatorPassword", function ($request, $response, $args) {
      $psswd = strtoupper(substr( md5(microtime()), 1, 10));
      $passwordToken = base64_encode(hash("whirlpool", $psswd));
      $hash = crypt($passwordToken, '$6$admin$');
      echo $hash."<br/>";
      echo $psswd."<br/>";
    });

    $app->get("/logout", function(Request $request, Response $response, $args){
        $this->session->destroy();
        return $response->withStatus(302)->withHeader('Location', 'admin/login');
    });

    $app->get("/select", function(Request $request, Response $response, $args){
        if(!$this->session->exists('userObject'))
             return $response->withStatus(302)->withHeader('Location', 'login');

        return $this->view->render($response, 'select_sucursal.phtml', array(
            "sucursales" => $this->db->get('sucursales')
        ));
    })->setName('select');

    $app->post("/select", function(Request $request, Response $response, $args){
       $router = $this->router;
       $params = $request->getParsedBody();
       $session = $this->session->get('userObject');
       $session["sucursal_id"]  = $params["sucursal"];
       $session['sucursal']     = $this->db->where('id', $params["sucursal"])
                                   ->getOne('sucursales', 'id, nombre_sucursal');
                                   
       $this->session->set('userObject', $session);
       return $response->withRedirect($router->pathFor('home'));
    });

    $app->get('/user', function ($request, $response, $args) {
      return $response->withJson($this->session->get('userObject'), 201);
    });


  /* Para recuperar la contraseña */
  $app->get('/admin/forgotpwd', function(Request $request, Response $response, $args) {
    return $this->view->render($response, 'forgotpwd.phtml', [      
      'base' => $this->baseUrl
    ]);
  });

  $app->post('/admin/forgotpwd', function(Request $request, Response $response, $args) {
    /* Logica para recuperar la contraseña */
    $parameters = $request->getParsedBody();
    $userObject = $this->db
        ->join('empleados', 'empleado_usuario.empleado_id = empleados.id', 'INNER')
        ->join('usuarios', 'empleado_usuario.usuario_id = usuarios.id_user', 'INNER')
        ->where('email', $parameters['email'])
      ->getOne('empleado_usuario', 'empleado_usuario.usuario_id, usuarios.email, empleados.lastname_emp, empleados.name_emp');

    if(count($userObject) == 0){
      $this->flash->addMessage('errorLogin', array(
        "message" => 'Lo sentimos pero ese usuario no esta registrado en nuestro sistema',
        "usuario" => $parameters['email']
      ));
      return $response->withStatus(302)->withHeader('Location', 'login');
    }

    // Generando la contraseña
    $psswd = strtoupper(substr( md5(microtime()), 1, 10));
    $passwordToken = base64_encode(hash("whirlpool", $psswd));
    $hash = crypt($passwordToken, '$6$admin$');
    $mail = new PHPMailer(false);
    /*print_r($userObject);*/

    $this->db->where('id_user', $userObject['usuario_id'])->update('usuarios', [
      "password" => $hash
    ]);

    try {
      $mail->SMTPDebug = 2;
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'mmfisherst@gmail.com';
      $mail->Password = 'mmfisherst12345';
      $mail->SMTPSecure = 'ssl';
      $mail->Port = 465;
  
      //Recipients
      $mail->setFrom('mmfisherst@gmail.com', utf8_decode( "Laboratorio Clínico MM FISHER'ST"));
      $mail->addAddress( $userObject['email'], $userObject['name_emp']." ". $userObject['lastname_emp']);

      $mail->isHTML(true);
      $mail->Subject = utf8_decode( "Laboratorio Clínico MM FISHER'ST");
      $mail->Body    = "</h1>Has solicitado cambio de contraseña.</h1> <h2>Tu nuevas credeciales<br/> <strong>Usuario: </strong> ".$userObject['email']."<br/> <strong>Contraseña: </strong> ".$psswd."</h2>";

      $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
      );
      echo "<div style='display:none'>";
        $mail->send();
      echo "</div>";
      $this->flash->addMessage('errorLogin', array(
        "message" => 'Se ha enviado un correo electronico con las nuevas credeciales.',
        "usuario" => $parameters['email']
      ));
      return $response->withStatus(302)->withHeader('Location', 'login');
    } catch (Exception $e) {
      $this->flash->addMessage('errorLogin', array(
        "message" => 'Por el momento tenemos un problema intenta mas tarde.',
        "usuario" => $parameters['email']
      ));
      return $response->withStatus(302)->withHeader('Location', 'login');
    }

  });

  $app->post('/admin/forgotpwd/paciente', function(Request $request, Response $response, $args){
    $params = $request->getParsedBody();
    $passwordToken = base64_encode(hash("whirlpool", '0123456789'));
    $hash = crypt($passwordToken, '$6$admin$');

    return $this->db->where('id', $params['id'])->update('pacientes', [
      'password' => $hash
    ]);
  });
