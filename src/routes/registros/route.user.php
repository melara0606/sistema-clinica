<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->post('/perfil', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    bitacora($this, 'PERFIL', 'VER EL PERFIL');
    $session = $this->session->get('userObject');
    $perfil = $this->db->join('solicitud_monto as sol_monto', 'sol.id = sol_monto.solicitud_monto', 'LEFT')
                    ->where('sol.paciente_id', $session["id"])
                    ->orderBy('fecha_creacion', 'DESC')
                    ->get('solicitud as sol', 10, 'sol.*, sol_monto.monto as "Abono"');

    return $response->withJson(array(
      "data" => $perfil,
      "server" => true
    ), 201);
  });

  $app->post('/solicitud/perfil', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    $querySQL = "SELECT  solEx.*, ex.nombre_examen, catEx.nombre_categoria FROM solicitud_item_examen AS solEx INNER JOIN examenes AS ex ON solEx.examen_id = ex.id INNER JOIN categorias_examenes AS catEx ON catEx.id = ex.categoria_id WHERE solEx.solicitud_id = '{$object['id']}'";

    $results["examenes"] = $this->db->rawQuery($querySQL);
    $results["monto"] = $this->db->where('solicitud_monto', $object['id'])->getOne('solicitud_monto');
    bitacora($this, 'PERFIL', 'VER LAS SOLICITUDES DE UN PERFIL');

    return $response->withJson(array(
      "data" => $results
    ), 201);
  });
?>
