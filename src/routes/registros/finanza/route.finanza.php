<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/reporte/solicitudes/entidades', function(Request $request, Response $response, $arguments) {
  $reponseArray = array();
  $QueryParams = $request->getQueryParams();
  $response = $response->withHeader('Content-Type', 'application/pdf');

  $reponseArray['entidad'] = $this->db->where('id', $QueryParams['entidad'])->getOne('entidades');
  $reponseArray['fechas'] = array(
    'be' =>  date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['b'] ))),
    'end' => date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['f'] )))
  );

  $reponseArray['saldosActuales'] = $this->db->join('monto_historial mh', 'mh.id_monto = enm.id', 'INNER')
      ->where('id_entidad', $QueryParams['entidad'])
      ->orderBy('fecha_ingreso', 'desc')
      ->get('entidad_monto as enm', 1, 'enm.id_entidad, enm.monto, mh.fecha_ingreso, mh.monto AS ultimo_monto');

  $reponseArray['solicitudesAtendidas'] = $this->db->join('pacientes AS pac', 'sol.paciente_id = pac.id ', 'INNER')
      ->where('pac.entidad_id', $QueryParams['entidad'])
      ->where('sol.tipo_solicitud', 3)
      ->where('fecha_creacion', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
      ->get('solicitud AS sol', null, 'pac.name_pac, pac.lastname_pac, sol.paciente_id,  sol.tipo_solicitud, sol.fecha_creacion, sol.monto, sol.id');

  $reponseArray['generoSolicitud'] = $this->db->rawQuery("SELECT genero_paciente, count( genero_paciente ) AS countSolicitud FROM pacientes WHERE id IN ( SELECT DISTINCT pac.id FROM solicitud AS sol INNER JOIN pacientes AS pac ON sol.paciente_id = pac.id WHERE pac.entidad_id = '".$QueryParams['entidad']."' AND sol.tipo_solicitud = '3' AND fecha_creacion BETWEEN '".$reponseArray['fechas']['be']."' AND '".$reponseArray['fechas']['end']."' ) GROUP BY genero_paciente");
  
  $reponseArray['generoSolicitud'] = arrayOrderGenero($reponseArray['generoSolicitud']);
  $ids = getStringSolicitud($reponseArray['solicitudesAtendidas']);
  
  if($ids){
    $reponseArray['itemExamenes'] = $this->db
      ->join('examenes', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
      ->where('solicitud_item_examen.solicitud_id', $ids, 'IN')
      ->groupBy('examenes.id')
      ->get('solicitud_item_examen', null, 'examenes.nombre_examen, count( * ) AS conteo ');
  }
  return $this->renderer->render($response, 'reportes/pdf/solicitudes-atendidas-entidad.php', [
    "data" => $reponseArray
  ]);

  // print_r($reponseArray);
});


// Reporte por solicitud por la sucursal
$app->get('/reporte/solicitudes/sucursales', function(Request $request, Response $response, $arguments) {
  $reponseArray = array();
  $QueryParams = $request->getQueryParams();
  $session = $this->session->get('userObject');
  $response = $response->withHeader('Content-Type', 'application/pdf');

  $reponseArray['fechas'] = array(
    'be' =>  date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['b'] ))),
    'end' => date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['f'] )))
  );

  $reponseArray['sucursal'] = $this->db->where('id', $session['sucursal_id'])
                                ->getOne('sucursales', 'nombre_sucursal, address_suc, phone_suc');

  $reponseArray['solicitudesAtendidas'] = $this->db
    ->join('pacientes', 'pacientes.id = solicitud.paciente_id', 'INNER')
    ->where('solicitud.tipo_solicitud', 3, '<>')
    ->orderBy('solicitud.fecha_creacion', 'DESC')
    ->where('solicitud.sucursal_id', $session['sucursal_id'])
    ->where('fecha_creacion', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
    ->get('solicitud', null, 'solicitud.monto, solicitud.fecha_creacion, solicitud.tipo_solicitud,  solicitud.id, pacientes.name_pac, pacientes.lastname_pac');

  $reponseArray['generoSolicitud'] = $this->db
      ->join('pacientes', 'solicitud.paciente_id = pacientes.id', 'INNER')
      ->where('solicitud.tipo_solicitud', 3, '<>')
      ->where('solicitud.sucursal_id', $session['sucursal_id'])
      ->where('fecha_creacion', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
      ->groupBy('pacientes.genero_paciente')
      ->get('solicitud', null, 'pacientes.genero_paciente, count( * ) AS countSolicitud');

  $reponseArray['conteoSolicitudes'] = $this->db
      ->where('solicitud.tipo_solicitud', 3, '<>')
      ->where('solicitud.sucursal_id', $session['sucursal_id'])
      ->where('fecha_creacion', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
      ->groupBy('solicitud.tipo_solicitud')
      ->get('solicitud', null, 'count( solicitud.tipo_solicitud ) AS countSolicitud, solicitud.tipo_solicitud as genero_paciente');
  
  $ids = getStringSolicitud($reponseArray['solicitudesAtendidas']);
  $reponseArray['generoSolicitud'] = arrayOrderGenero($reponseArray['generoSolicitud']);
  $reponseArray['conteoSolicitudes'] = arrayOrderGenero($reponseArray['conteoSolicitudes']);

  if($ids){
    $reponseArray['itemExamenes'] = $this->db->join('examenes', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
        ->where('solicitud_item_examen.solicitud_id', $ids, 'IN')
        ->groupBy('examenes.id')
        ->get('solicitud_item_examen', null, 'examenes.nombre_examen, count( * ) AS conteo ');
  }
  return $this->renderer->render($response, 'reportes/pdf/solicitudes-atendidas-promocio-particular.php', [
    "data" => $reponseArray
  ]);
});

// Reporte de solicitud por paciente
$app->get('/reporte/solicitudes/paciente', function(Request $request, Response $response, $arguments) {
  $reponseArray = array();
  $QueryParams = $request->getQueryParams();
  $response = $response->withHeader('Content-Type', 'application/pdf');


  $reponseArray['fechas'] = array(
    'be' =>  date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['b'] ))),
    'end' => date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['f'] )))
  );

  $reponseArray['paciente'] = $this->db->where('id', $QueryParams['paciente'])
      ->getOne('pacientes', 'name_pac, lastname_pac, address_pac, telefono');

  $reponseArray['solicitudesAtendidas'] = $this->db->where('solicitud.tipo_solicitud', 3, '<>')
      ->orderBy('solicitud.fecha_creacion', 'DESC')
      ->where('solicitud.paciente_id', $QueryParams['paciente'])
      ->where('fecha_creacion', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
      ->get('solicitud', null, 'solicitud.monto, solicitud.fecha_creacion, solicitud.tipo_solicitud,  solicitud.id'); 

  $ids = getStringSolicitud($reponseArray['solicitudesAtendidas']);

  if($ids){
    $reponseArray['itemExamenes'] = $this->db->join('examenes', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
        ->where('solicitud_item_examen.solicitud_id', $ids, 'IN')
        ->groupBy('examenes.id')
        ->get('solicitud_item_examen', null, 'examenes.nombre_examen, count( * ) AS conteo ');
  }

  return $this->renderer->render($response, 'reportes/pdf/solicitudes-atendidas-paciente.php', [
    "data" => $reponseArray
  ]);
});

$app->get('/reporte/solicitud/compras', function(Request $request, Response $response, $arguments) {
  $reponseArray = array();
  $QueryParams = $request->getQueryParams();
  $session = $this->session->get('userObject');

  $reponseArray['fechas'] = array(
    'be' =>  date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['b'] ))),
    'end' => date('Y-m-j', strtotime('-1 day', strtotime ( $QueryParams['f'] )))
  );

  $response = $response->withHeader('Content-Type', 'application/pdf');
  $reponseArray['sucursal'] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales', 'nombre_sucursal, address_suc, phone_suc');

  $reponseArray['compras'] = $this->db->join('proveedores', 'compras.proveedor_id = proveedores.id', 'INNER')
    ->where('compras.sucursal_id', $session['sucursal_id'])
    ->where('fecha_compra', Array($reponseArray['fechas']['be'], $reponseArray['fechas']['end']), 'BETWEEN')
    ->get('compras', null, ' compras.id, compras.proveedor_id, compras.sucursal_id, compras.fecha_compra, compras.total_compra, compras.estado, compras.codigo_factura, proveedores.nombre_proveedor');
  
  return $this->renderer->render($response, 'reportes/pdf/compras-fecha-atendidas.php', [
    "data" => $reponseArray
  ]);
});