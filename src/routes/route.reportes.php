<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  function getStringSolicitud($array) {
    return array_map(function($i){ return $i['id']; }, $array);
  }

  function arrayOrderGenero($array) {
    $arrayResponse = array();
    for ($i=0; $i < count($array); $i++) {
      $arrayResponse[$array[$i]["genero_paciente"]] = $array[$i]["countSolicitud"];
    }
    return $arrayResponse;
  }

  function arrayOfListExamen($items = array()) {
    $arrayResponse = array();
    foreach ($items as $key => $value) {
      if(!@array_key_exists($value['nombre_categoria'], $arrayResponse))
        $arrayResponse[$value['nombre_categoria']] = array();
      array_push($arrayResponse[$value['nombre_categoria']], $value);
    }
    return $arrayResponse;
  }

  $app->get('/reporte/solicitudes/atentidas', function(Request $request, Response $response, $arguments) {
    $arrayResponse = array();
    $QueryParams = $request->getQueryParams();
    $session = $this->session->get('userObject');
    $response = $response->withHeader('Content-Type', 'application/pdf');

    $arrayResponse['fechas'] = array(
      'be' =>  date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['begind'] ))),
      'end' => date('Y-m-j', strtotime('+1 day', strtotime ( $QueryParams['end'] )))
    );

    $arrayResponse["sucursal"] = $this->db
                ->where('id', $session['sucursal_id'])
                ->getOne('sucursales', 'nombre_sucursal, phone_suc, address_suc');

    $arrayResponse["items"] = $this->db->join('pacientes AS pac', 'sol.paciente_id = pac.id', 'INNER')
        ->where('sol.sucursal_id', $session['sucursal_id'])
        ->where('sol.estado', 4)
        ->where('sol.tipo_solicitud', $QueryParams['typeSolicitud'])
        ->where('fecha_creacion', Array($arrayResponse['fechas']['be'], $arrayResponse['fechas']['end']), 'BETWEEN')
        ->get('solicitud AS sol', null, 'sol.id');

    $arrayResponse['type'] = $QueryParams['typeSolicitud'];

    if(count($arrayResponse["items"]) === 0){
      $arrayResponse["ItemExamen"]    = array();
        $arrayResponse["ItemsExamen"]  = array();
        $arrayResponse["CountExamen"]  = array();
        $arrayResponse["countGenero"]  = array();
    }else{
      $arraySolicitud = getStringSolicitud($arrayResponse["items"]);
      
      $arrayResponse["ItemExamen"] = $this->db
          ->join('examenes as exam', 'solItemExamen.examen_id = exam.id', 'INNER')
          ->join('categorias_examenes as catExamen', 'exam.categoria_id = catExamen.id', 'INNER')
          ->where('solItemExamen.solicitud_id', $arraySolicitud , 'IN')->groupBy('exam.id')
          ->get('solicitud_item_examen as solItemExamen', null, 'exam.nombre_examen, catExamen.nombre_categoria, catExamen.id, count(exam.nombre_examen) as "cantidad"');
      $arrayResponse["ItemsExamen"] = arrayOfListExamen($arrayResponse["ItemExamen"]);
      $arrayResponse["CountExamen"] = $this->db
          ->join('examenes as exam', 'solItemExamen.examen_id = exam.id', 'INNER')
          ->join('categorias_examenes as catExamen', 'exam.categoria_id = catExamen.id', 'INNER')
          ->where('solItemExamen.solicitud_id', $arraySolicitud , 'IN')
          ->getOne('solicitud_item_examen as solItemExamen', 'count(exam.nombre_examen) as "total"');

      $array = $this->db
          ->join('pacientes as pac', 'pac.id = sol.paciente_id', 'INNER')
          ->where('sol.id', $arraySolicitud, 'IN')->groupBy('pac.genero_paciente')
          ->orderBy('pac.genero_paciente', 'asc')
          ->get('solicitud as sol', null, 'pac.genero_paciente, COUNT(pac.genero_paciente) as countSolicitud');

      $arrayResponse["countGenero"] = arrayOrderGenero($array);
    }
    return $this->renderer->render($response, 'reportes/pdf/solicitudes-atendidas.php', [
      "data" => $arrayResponse
    ]);
  });


/* Permisos, comprobante de solicitud, tipeo sanguineo */
$app->get('/reportes/comprobante/permisos', function(Request $request, Response $response, $arguments) {
  $responseArray = array();
  $QueryParams = $request->getQueryParams();
  $session = $this->session->get('userObject');
  $response = $response->withHeader('Content-Type', 'application/pdf');

  $responseArray['sucursal'] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales', 'nombre_sucursal');

  $responseArray['rangoFechas'] = $request->getQueryParams();
  $responseArray['permisosOnIncapacidades'] = $this->db
    ->join('permisos_incapacidades', 'permisos_incapacidades.empleado_id = empleados.id', 'INNER')
    ->join('permisos_comentarios', 'permisos_comentarios.permiso_id = permisos_incapacidades.id', 'LEFT')
    ->where('empleados.sucursal_id', $session['sucursal_id'])
    ->where('permisos_incapacidades.created_at', Array($QueryParams['b'], $QueryParams['f']), 'BETWEEN')
    ->get('empleados', null, 'permisos_incapacidades.id,permisos_incapacidades.fecha_inicio,permisos_incapacidades.fecha_fin,permisos_incapacidades.tipo,empleados.name_emp,empleados.lastname_emp,permisos_comentarios.comentario,empleados.sucursal_id');

    return $this->renderer->render($response, 'reportes/pdf/comprobante-permisos.php', [
      "data" => $responseArray
    ]);
});

$app->get('/reportes/comprobante/examen', function(Request $request, Response $response, $arguments) {
  $responseArray = array();
  $QueryParams = $request->getQueryParams();
  $response = $response->withHeader('Content-Type', 'application/pdf');

  $responseArray['comprobanteOnExamen'] = $this->db
      ->join('solicitud', 'solicitud.paciente_id = pacientes.id', 'INNER')
      ->join('solicitud_monto', 'solicitud_monto.solicitud_monto = solicitud.id', 'LEFT')
      ->where('solicitud.id', $QueryParams['id'])
      ->getOne('pacientes', 'pacientes.lastname_pac,pacientes.name_pac,solicitud.id,solicitud.fecha_creacion,solicitud.monto,solicitud_monto.monto AS abono');

  return $this->renderer->render($response, 'reportes/pdf/comprobante-solicitud-examen.php', [
    "data" => $responseArray
  ]);
});

$app->get('/reportes/comprobante/citas', function(Request $request, Response $response, $arguments) {
  $responseArray = array();
  $QueryParams = $request->getQueryParams();

  $responseArray['comprobanteOnCitas'] = $this->db
      ->join('pacientes', 'citas.paciente_id = pacientes.id', 'INNER')
      ->where('citas.id', $QueryParams['id'])
      ->getOne('citas', 'citas.id,citas.paciente_id,citas.fecha,citas.horario,citas.estado,citas.pagar,pacientes.name_pac,pacientes.lastname_pac');

  $response = $response->withHeader('Content-Type', 'application/pdf');
  return $this->renderer->render($response, 'reportes/pdf/comprobante-solicitud-citas.php', [
    "data" => $responseArray
  ]);
});

$app->get('/reportes/comprobante/solicitud', function(Request $request, Response $response, $arguments) {
  $responseArray = array();
  $QueryParams = $request->getQueryParams();

  $responseArray['comprobanteOnSolicitud'] = $this->db
  ->join('sucursales', 'solicitud_adomicilio.sucursal_id = sucursales.id', 'INNER')
  ->join('pacientes', 'solicitud_adomicilio.paciente_id = pacientes.id', 'INNER')
  ->where('solicitud_adomicilio.id', $QueryParams['id'])
      ->getOne('solicitud_adomicilio', 'solicitud_adomicilio.id, solicitud_adomicilio.codigo, solicitud_adomicilio.pagar,      sucursales.nombre_sucursal, pacientes.name_pac, pacientes.lastname_pac');

  $response = $response->withHeader('Content-Type', 'application/pdf');
  return $this->renderer->render($response, 'reportes/pdf/comprobante-solicitud-solicitud.php', [
    "data" => $responseArray
  ]);
  /*print_r($responseArray);*/
});