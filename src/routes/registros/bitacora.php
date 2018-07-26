<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get('/bitacora/all', function(Request $request, Response $response){
    $bitacoraUser = $this->db
      ->join('usuarios as u', 'u.id_user = b.user_id', 'INNER')
      ->join('empleado_usuario as eu', 'eu.usuario_id = u.id_user', 'INNER')
      ->join('empleados as e', 'eu.empleado_id = e.id', 'INNER')
      ->orderby('fecha', 'desc')
    ->get('bitacora as b', null, 'b.*, e.lastname_emp, e.name_emp, u.email');


    return $response->withJson(array(
      "data" => $bitacoraUser
    ), 201);
  });