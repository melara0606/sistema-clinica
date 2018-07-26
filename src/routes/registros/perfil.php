<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->post('/changepassword/perfil', function(Request $request, Response $response) {
    $session = $this->session->get('userObject');
    bitacora($this, 'PERFIL', 'CAMBIAR LA CONTRASEÑA DEL USUARIO'); 
    if($session["is_paciente"] === true ){
      return $response->withJson(array(
        "data" => $this->db->where('id', $session['id'])->getOne('pacientes'),
        "isPaciente"  => true
      ));
    } else {
      return $response->withJson(array(
        "data" => $this->db->where('id', $session['data']['id'])->getOne('empleados'),
        "isPaciente"  => false
      ));
    }
  });

  $app->post('/changeData/personal', function(Request $request, Response $response) {
    $object = $request->getParsedBody();

    bitacora($this, 'PERFIL', 'CAMBIAR LA INFORMACION DEL USUARIO'); 
    $responseDB =$this->db->where('id', $object['data']['id'])->update('pacientes', array(
      "telefono"                  => $object["data"]["telefono"],
      "responsable_paciente"      => $object["data"]["responsable_paciente"],
      "dui"                       => $object["data"]["dui"],
      "address_pac"               => $object["data"]["address_pac"]
    ));

    if($responseDB){
      return $response->withJson(array(
        "message" => "Hemos actualizado con exito tu datos",
        "data"    => $this->db->where('id', $object['data']['id'])->getOne('pacientes')
      ));
    }else{
      return $response->withJson(array(
        "message" => "Lo sentimos pero tenemos un problema con el servidor"
      ), 502);
    }
  });

  $app->post('/changeData/empleado', function(Request $request, Response $response) {
    $object = $request->getParsedBody();

    bitacora($this, 'PERFIL', 'CAMBIAR LA INFORMACION DEL EMPLEADO'); 
    $responseDB =$this->db->where('id', $object['data']['id'])->update('empleados', array(
      "phone_emp"                   => $object["data"]["phone_emp"],
      "address_emp"                 => $object["data"]["address_emp"]
    ));

    if($responseDB){
      return $response->withJson(array(
        "message" => "Hemos actualizado con exito tu datos",
        "data"    => $this->db->where('id', $object['data']['id'])->getOne('empleados')
      ));
    }else{
      return $response->withJson(array(
        "message" => "Lo sentimos pero tenemos un problema con el servidor"
      ), 502);
    }
  });

  $app->post('/changepassword/empleado', function(Request $request, Response $response) {
    $object = $request->getParsedBody();
    $session = $this->session->get('userObject');
    $parameters['password'] = base64_encode(hash("whirlpool", $object["data"]["current_password"]));

    $passwordNew = base64_encode(hash("whirlpool", $object["data"]["new_password"]));
    $hash = crypt($passwordNew, '$6$admin$');

    bitacora($this, 'PERFIL', 'CAMBIAR LA CONTRASEÑA DEL EMPLEADO');
    if(password_verify($parameters['password'], $session['password'])){
      $this->db->where('id_user', $session['id_user'])->update('usuarios', array(
        "password"  => $hash
      ));

      $session["password"] = $hash;
      $this->session->set('userObject', $session);

      return $response->withJson(array(
        "message" => "Hemos actualizado con exito la contraseña"
      ));
    }else{
      return $response->withJson(array(
        "message" => "Lo sentimos pero la contaseña actual no es la correcta"
      ), 502);
    }
  });

  $app->post('/changepassword/personal', function(Request $request, Response $response) {
    $object = $request->getParsedBody();
    $session = $this->session->get('userObject');
    $parameters['password'] = base64_encode(hash("whirlpool", $object["data"]["current_password"]));

    $passwordNew = base64_encode(hash("whirlpool", $object["data"]["new_password"]));
    $hash = crypt($passwordNew, '$6$admin$');

    bitacora($this, 'PERFIL', 'CAMBIAR LA CONTRASEÑA DEL EMPLEADO');
    if(password_verify($parameters['password'], $session['password'])){
      $this->db->where('id', $session['id'])->update('pacientes', array(
        "password"  => $hash
      ));

      $session["password"] = $hash;
      $this->session->set('userObject', $session);

      return $response->withJson(array(
        "message" => "Hemos actualizado con exito la contraseña"
      ));
    }else{
      return $response->withJson(array(
        "message" => "Lo sentimos pero la contaseña actual no es la correcta"
      ), 502);
    }
  });

  
?>
