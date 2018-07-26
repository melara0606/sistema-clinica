<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get('/citas/{id}/examenes', function(Request $request, Response $response, $arguments = []){
    bitacora($this, 'HORARIOS', 'VIZUALIZAR LOS EXAMENES POR CITAS');
    $arrayOfObject = array();

    $arrayOfObject['data'] = $this->db
        ->join('pacientes', 'citas.paciente_id = pacientes.id', 'INNER')
        ->where('citas.id', $arguments['id'])
        ->getOne('citas', 'citas.id, citas.paciente_id, citas.fecha, citas.horario, citas.estado, citas.pagar, pacientes.name_pac, pacientes.lastname_pac');

    $arrayOfObject['item'] = $this->db
      ->join('examenes', 'citas_examenes.examen_id = examenes.id', 'INNER')
      ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
      ->where('citas_examenes.cita_id', $arguments['id'])
      ->get('citas_examenes', null, 'citas_examenes.cita_id, citas_examenes.precio, examenes.nombre_examen,  categorias_examenes.nombre_categoria');

    return $response->withJson(array(
      "arrayOfObject" => $arrayOfObject
    ), 201);
  });

  $app->get('/horarios/all', function(Request $request, Response $response){
    bitacora($this, 'HORARIOS', 'VIZUALIZAR LOS HORARIOS');
    return $response->withJson(array(
      "data" => $this->db->get('citas_horarios')
    ), 201);
  });

  $app->get('/horarios/all/active', function(Request $request, Response $response){
    bitacora($this, 'HORARIOS', 'PRESENTAR LOS HORARIOS ACTIVOS');
    return $response->withJson(array(
      "data" => $this->db->where('status', 1)->get('citas_horarios')
    ), 201);
  });

  $app->post('/horarios/status', function(Request $request, Response $response) {
    $object = $request->getParsedBody();

    bitacora($this, 'HORARIOS', 'ACTUALIZAR EL ESTADO DE HORARIOS');    
    $this->db->where('id', $object['id'])->update('citas_horarios', array(
      "status"  => $this->db->not()
    ));
    return $response->withJson(array(
      "message"   => "Hemos actualizado con exito, el estado del horario"
    ), 201);
  });

  $app->post('/horarios/add', function(Request $request, Response $response) {
    date_default_timezone_set("America/El_Salvador");
    $object = $request->getParsedBody();

    bitacora($this, 'HORARIOS', 'AGREGAR UN NUEVO HORARIO');    
    $strInitial = date('h:i:sa', strtotime($object['hours']['initial']));
    $strEnd     = date('h:i:sa', strtotime($object['hours']['end']));

    $isResponse = $this->db->insert('citas_horarios', array(
      "hora_entrada"  => $strInitial,
      "hora_salidad"  => $strEnd
    ));

    if($isResponse){
      return $response->withJson(array(
        "data"      => $this->db->where('id', $isResponse)->getOne('citas_horarios'),
        "message"   => "Hemos agregado con exito tu nuevo horario"
      ), 201);
    }else{
      return $response->withJson(array(
        "message"   => "Hemos tenido un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });

  $app->post('/horarios/update', function(Request $request, Response $response) {
    date_default_timezone_set("America/El_Salvador");
    $object = $request->getParsedBody();

    bitacora($this, 'HORARIOS', 'ACTUALIZAR EL HORARIO'); 
    $strInitial = date('h:i:sa', strtotime($object['hours']['initial']));
    $strEnd     = date('h:i:sa', strtotime($object['hours']['end']));

    $isResponse = $this->db->where('id', $object['id'])->update('citas_horarios', array(
      "hora_entrada"  => $strInitial,
      "hora_salidad"  => $strEnd
    ));

    if($isResponse){
      return $response->withJson(array(
        "data"      => $this->db->where('id', $object['id'])->getOne('citas_horarios'),
        "message"   => "Hemos actualizado con exito tu nuevo horario"
      ), 201);
    }else{
      return $response->withJson(array(
        "message"   => "Hemos tenido un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });

  $app->post('/citas/all/administration/items', function(Request $request, Response $response) {
    $object = $request->getParsedBody();

    bitacora($this, 'HORARIOS', 'PRESENTAR LOS ITEMS DE LA CITAS'); 
    return $response->withJson(array(
      "data"  => $this->db->where('cita_id', $object['cita_id'])->get('citas_examenes')
    ), 201);
  });

  $app->post('/citas/all/administration', function(Request $request, Response $response) {
    date_default_timezone_set("America/El_Salvador");
    $object = $request->getParsedBody();

    $fechaPedido = date('Y-m-d', strtotime($object['fecha']));
    if($object['fecha'] == 'now')
      $fechaPedido = date("Y-m-d");
    
    $all = $this->db
      ->join('pacientes as pac', 'pac.id = c.paciente_id', 'INNER')
      ->join('cita_entidad', 'cita_entidad.cita_id = c.id', 'LEFT')
      ->where('fecha', $fechaPedido)
    ->get( 'citas as c' , null, 'c.*, pac.name_pac, pac.lastname_pac, pac.telefono, cita_entidad.entidad_id ');

    return $response->withJson(array(
      "data"  => $all,
      "sqlQuery" => $this->db->getLastQuery()
    ), 201);
  });

  $app->get('/citas/{id}/motrar/examenes', function(Request $request, Response $response, $arguments) {
    print_r($arguments);
  });

  $app->post('/citas/all/changeStatus', function(Request $request, Response $response) {
    $object = $request->getParsedBody();

    bitacora($this, 'HORARIOS', 'ACTUALIZAR EL ESTADO DE LA CITA'); 
    $this->db->where('id', $object['id'])->update('citas', array(
      'estado'  => $this->db->not()
    ));

    return $response->withJson(array(
      "message"  => "Se realizo la operacion con exito"
    ), 201);
  });

  $app->post('/citas', function(Request $request, Response $response, $arguments) {
    $session = $this->session->get('userObject');
    bitacora($this, 'PERFIL', 'AGREGAR UNA NUEVA CITA');
    $allResults = $this->db->orderby('fecha')->where('paciente_id', $session['id'])->get('citas');
    return $response->withJson(array(
      "data" => $allResults
    ), 201);
  });

  $app->post('/citas/cancelar', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    $result = $this->db->where('id', $object['item'])->update('citas', array(
      "estado"  => 2
    ));

    if($result){
      return $response->withJson(array(
        "message"  => "Se cancelo la cita con exito"
      ), 201);
    }else{
      return $response->withJson(array(
        "message"   => "Hemos tenido un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });

  /* para la cita dependiendo si pertenece a una entidad */
  $app->get('/categoria_examenes/{entidad_id}/entidad', function(Request $request, Response $response, $arguments) {
    $id = $arguments['entidad_id'];

    // Verificando si hay suficiente dinero para la cita
    $result = $this->db->where('id_entidad', $id)->getOne('entidad_monto', 'monto');
    if(floatval($result['monto']) < 50){
      return $response->withJson(array(
        "data"  => array(),
        "response" => false
      ), 201);
    }else{
      $allResponse = $this->db
      ->join('entidades_examenes', 'entidades_examenes.id_examen = examenes.id', 'INNER')
        ->where('id_entidad', $arguments['entidad_id'])
        ->get('examenes', null, 'entidades_examenes.id_entidad, entidades_examenes.id_examen as id, entidades_examenes.precio, examenes.nombre_examen');
      return $response->withJson(array(
        "data"  => $allResponse,
        "response" => true
      ), 201);
    }

  });

  $app->post('/citas/add', function(Request $request, Response $response) {
    $object = $request->getParsedBody();
    $session = $this->session->get('userObject');

    bitacora($this, 'PERFIL', 'AGREGAR UNA NUEVA CITA');
    $date = date_create($object['fecha']);
    $date = date_format($date,"Y-m-d");

    $cita = $this->db
        ->where('fecha', $date)
        ->where('horario', $object['horario'])
    ->getOne('citas');

    if(@count($cita) > 0){
      return $response->withJson(array(
        "message" => "Lo sentimos pero ese horario ya esta reservado.",
      ), 401);
    }

    $id = $this->db->insert('citas', array(
      'estado' => 1,
      'fecha' => $object['fecha'],
      'horario' => $object['horario'],
      'paciente_id' => $session['id'],
      'tipo_cita' => $object['tipoCita'],
      'sucursal_id' => $object['sucursal'],
      'pagar'   => $object['itemsListExamen']['pagar']
    ));

    if($id){
      $array = array();
      foreach ($object['itemsListExamen']['item'] as $value) {
        array_push($array, array(
          'cita_id'     => $id,
          'examen_id'   => $value['id'],
          'precio'      => $value['precio']
        ));
      }
      $ids = $this->db->insertMulti('citas_examenes', $array);

      return $response->withJson(array(
        "message" => "Tu reserva fue agregada con exito",
        "data"    => $this->db->where('id', $id)->getOne('citas'),
        "object"  => $date
      ), 200);
    }else{
      return $response->withJson(array(
        "message" => "Tenemos un problema por el momento intenta mas tarde",
      ), 401);
    }
  });

  $app->post('/citas/add/paciente', function(Request $request, Response $response) {
    bitacora($this, 'PACIENTE', 'AGREGAR UNA NUEVA CITA');
    $object = $request->getParsedBody();

    //$timeBegind = date_create();
    $timeEnd    = strtotime ( '-8 hours', strtotime ( $object['horarioEnd']) );
    $timeBegind = strtotime ( '-8 hours', strtotime ( $object['horarioBegin']) );

    $timeEnd    = date("H:i:s", $timeEnd);
    $timeBegind = date("H:i:s", $timeBegind);

    $id = $this->db->insert('citas', array(
      'estado' => 1,
      'fecha' => $object['fecha'],
      'tipo_cita' => $object['tipoCita'],
      'paciente_id' => $object['paciente'],
      'horario' => $timeBegind."-".$timeEnd,
      'pagar'   => $object['itemsListExamen']['pagar'],
    ));

    if($id){
      $array = array();
      foreach ($object['itemsListExamen']['item'] as $value) {
        array_push($array, array(
          'cita_id'     => $id,
          'examen_id'   => $value['id'],
          'precio'      => $value['precio']
        ));
      }
      $ids = $this->db->insertMulti('citas_examenes', $array);
      if($object['tipoCita'] == 2){
        $this->db->insert('cita_entidad', array(
          "cita_id"     => $id,
          "entidad_id"  => $object['entidad']
        ));
      }
      return $response->withJson(array(
        "message" => "Tu reserva fue agregada con exito"
      ), 200);
    }else{
      return $response->withJson(array(
        "message" => "Tenemos un problema por el momento intenta mas tarde",
      ), 401);
    }
  });