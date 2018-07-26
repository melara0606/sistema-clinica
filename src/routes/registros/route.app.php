<?php 
 use \Psr\Http\Message\ResponseInterface as Response;
 use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->post('/movil/login', function(Request $request, Response $response) {
    $value = json_decode($request->getBody());
    $user = $this->db->where('codigo_paciente', $value->username)->getOne('pacientes', 'status, password, id');

    if($user['status'] == 0){
      return $response->withJson(array(
        "message" => "Los sentimos pero no eres un usuario activo",
        "response" => false
      ), 401);
    }

    // Verificando los datos
    $parameters = base64_encode(hash("whirlpool", $value->password));
    if(password_verify($parameters, $user['password'] )) {
      $all = $this->db
        ->join('entidades', 'pacientes.entidad_id = entidades.id', 'INNER')
        ->where('pacientes.id', $user['id'])
        ->getOne('pacientes', 'pacientes.id, pacientes.codigo_paciente, pacientes.name_pac, pacientes.lastname_pac, pacientes.telefono, pacientes.carnet, pacientes.dui, pacientes.address_pac, pacientes.date_pac, pacientes.genero_paciente, pacientes.responsable_paciente, pacientes.`status`, entidades.name_ext');

        return $response->withJson(array(
        "data" => $all,
        "response" => true
        ), 200);
    }else{
      return $response->withJson(array(
      "message" => "Lo sentimos pero los datos ingresados no son validos",
      "response" => false
      ), 402);
    }
  });

  $app->post('/movil/solicitudes', function(Request $request, Response $response){
    $value = json_decode($request->getBody());
      if(isset($value)){
        $allSolicitudes = $this->db
          ->join('sucursales', 'sucursales.id = solicitud.sucursal_id', 'INNER')
          ->where('solicitud.paciente_id', $value->id)
        ->get('solicitud', null, 'solicitud.*, sucursales.nombre_sucursal');
        
        return $response->withJson(array(
          "data" => $allSolicitudes,
          "response" => true
        ), 200);
      }else{
        return $response->withJson(array(
          "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
          "response" => false
        ), 401);
      }
  });

  $app->post('/movil/solicitudes/items', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $solicitud = $this->db
        ->join('sucursales', 'sucursales.id = solicitud.sucursal_id', 'INNER')
        ->where('solicitud.id', $value->id)
      ->getOne('solicitud', 'solicitud.*, sucursales.nombre_sucursal');

      $solicitud['items'] = $this->db
        ->join('examenes', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
        ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
        ->where('solicitud_item_examen.solicitud_id', $value->id)
      ->get('solicitud_item_examen', null, 'solicitud_item_examen.is_cortesias, solicitud_item_examen.precio, solicitud_item_examen.examen_id, examenes.nombre_examen, categorias_examenes.nombre_categoria, solicitud_item_examen.solicitud_id');

      return $response->withJson(array(
        "data" => $solicitud,
        "response" => true
      ), 200);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  // para poder ver la citas
  $app->post('/movil/citas', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      return $response->withJson(array(
        "data"    => $this->db->orderby('fecha')->where('paciente_id', $value->id)->get('citas')
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/citas/items', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $arrayOfObject = array();
      $arrayOfObject['data'] = $this->db
          ->join('pacientes', 'citas.paciente_id = pacientes.id', 'INNER')
          ->where('citas.id', $value->id)
          ->getOne('citas', 'citas.id, citas.paciente_id, citas.fecha, citas.horario, citas.estado, citas.pagar, pacientes.name_pac, pacientes.lastname_pac');

      $arrayOfObject['item'] = $this->db
        ->join('examenes', 'citas_examenes.examen_id = examenes.id', 'INNER')
        ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
        ->where('citas_examenes.cita_id', $value->id)
        ->get('citas_examenes', null, 'citas_examenes.cita_id, citas_examenes.precio, examenes.nombre_examen,  categorias_examenes.nombre_categoria');

      return $response->withJson(array(
        "arrayOfObject" => $arrayOfObject
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  // para poder ver la solicitudes
  $app->post('/movil/solicitudes/perfil', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $solicitud = $this->db
        ->join('sucursales', 'solicitud_adomicilio.sucursal_id = sucursales.id', 'INNER')
        ->where('solicitud_adomicilio.paciente_id', $value->id)
        ->get('solicitud_adomicilio', null, 'solicitud_adomicilio.id, solicitud_adomicilio.codigo,  solicitud_adomicilio.estado, solicitud_adomicilio.pagar, sucursales.nombre_sucursal');

      return $response->withJson(array(
        'data' => $solicitud
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/solicitudesadomocilio/items', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $arrayOfList = $this->db
        ->join('pacientes as pac', 'pac.id = solDom.paciente_id', 'INNER')
        ->where('estado', 1)
        ->where('solDom.id', $value->id)
        ->getOne('solicitud_adomicilio as solDom', 
          'solDom.*, pac.name_pac, pac.lastname_pac, pac.telefono, pac.address_pac');

      $arrayOfList['examenesList'] = $this->db
        ->join('examenes as ex', 'ex.id = solDom.examen_id', 'INNER')
        ->where('solDom.solicitud_adomicilio_id', $value->id)
        ->get('solicitud_adomicilio_examenes as solDom', null, 'solDom.examen_id, ex.nombre_examen, solDom.precio');
      return $response->withJson(array(
        'data' => $arrayOfList
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/update/perfil', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $Isresponse = $this->db->where('id', $value->id)->update("pacientes", (array) $value->object);

      if($Isresponse){
        return $response->withJson(array(
          "response"  => true,
          "data"      => $this->db->where('id', $value->id)->getOne('pacientes')
        ), 200);
      }else{
        return $response->withJson(array(
          "response" => false,
          "message" => "Lo sentimos pero tenemos un problema con el servidor por le momento"
        ), 401);
      }
    }else{
      return $response->withJson(array(
        "response" => false,
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion."
      ), 401);
    }
  });

  $app->post("/movil/citas/datos", function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $arrayOfList['horarios'] = $this->db->get('citas_horarios') ;
      $arrayOfList['sucursales'] = $this->db->where('status', 1)->get('sucursales', null, 'nombre_sucursal, id');
      $arrayOfList['categorias'] = $this->db->get('categorias_examenes');

      $arrayOfList['examenes_entidad'] = $this->db
        ->join('entidades_examenes', 'entidades_examenes.id_entidad = entidades.id', 'INNER')
        ->join('examenes', 'entidades_examenes.id_examen = examenes.id', 'INNER')
        ->join('pacientes', 'pacientes.entidad_id = entidades.id', 'INNER')
        ->where('pacientes.id', $value->id)
        ->get('entidades', null, 'entidades_examenes.id_entidad, entidades_examenes.id_examen, examenes.nombre_examen,
        entidades_examenes.precio, pacientes.id');
  
      return $response->withJson(array(
        "response" => $arrayOfList
      ), 201);
    }else{
      return $response->withJson(array(
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/solicitud-adomicilio', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      return $response->withJson(array(
        "data" => $this->db
          ->join('sucursales', 'solicitud_adomicilio.sucursal_id = sucursales.id', 'INNER')
          ->where('solicitud_adomicilio.paciente_id', $value->id)
          ->orderby('solicitud_adomicilio.codigo')
          ->get('solicitud_adomicilio', 10, 'solicitud_adomicilio.id, sucursales.nombre_sucursal, solicitud_adomicilio.codigo, solicitud_adomicilio.estado,  solicitud_adomicilio.lng, solicitud_adomicilio.lat, solicitud_adomicilio.pagar, solicitud_adomicilio.sucursal_id,solicitud_adomicilio.paciente_id')
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });


  $app->post('/movil/citas/informacion', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $objectResponse = $this->db
        ->join('sucursales', 'citas.sucursal_id = sucursales.id', 'INNER')
        ->where('citas.id', $value->id)
        ->getOne('citas', 'citas.id, citas.sucursal_id, citas.fecha, citas.horario, citas.estado, citas.pagar, citas.tipo_cita,citas.id, sucursales.nombre_sucursal');

      $objectResponse['examenes'] = $this->db
          ->join('examenes', 'citas_examenes.examen_id = examenes.id', 'INNER')
          ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
          ->where('citas_examenes.cita_id', $value->id)
        ->get('citas_examenes', null, 'citas_examenes.examen_id, citas_examenes.precio, examenes.nombre_examen,categorias_examenes.nombre_categoria');

      return $response->withJson(array(
        "data" => $objectResponse
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/citas/estado', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());

    if($value){
      $this->db->where('id', $value->id)->update('citas', array(
        'estado' => 0
      ));

      return $response->withJson(array(
        "message" => "Has cancelado con exito la cita",
        "response" => true
      ), 200);

    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/solicitud-adomicilio/informacion', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());
    if($value){
      $objectResponse = $this->db
        ->join('sucursales', 'solicitud_adomicilio.sucursal_id = sucursales.id ', 'INNER')
        ->where('solicitud_adomicilio.id', $value->id)
        ->getOne('solicitud_adomicilio', 'solicitud_adomicilio.id, solicitud_adomicilio.codigo, solicitud_adomicilio.lat, solicitud_adomicilio.lng, solicitud_adomicilio.estado, solicitud_adomicilio.pagar, sucursales.nombre_sucursal');

      $objectResponse['examenes'] = $this->db
          ->join('solicitud_adomicilio_examenes', 'solicitud_adomicilio_examenes.examen_id = examenes.id', 'INNER')
          ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
          ->where('solicitud_adomicilio_examenes.solicitud_adomicilio_id', $value->id)
        ->get('examenes', null, 'solicitud_adomicilio_examenes.precio, examenes.nombre_examen,solicitud_adomicilio_examenes.solicitud_adomicilio_id, categorias_examenes.nombre_categoria');

      return $response->withJson(array(
        "data" => $objectResponse
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });

  $app->post('/movil/solicitud-adomicilio/estado', function(Request $request, Response $response, $arguments) {
    $value = json_decode($request->getBody());

    if($value){
      $this->db->where('id', $value->id)->update('solicitud_adomicilio', array(
        'estado' => 0
      ));

      return $response->withJson(array(
        "message" => "Has cancelado con exito a la solicitud adomicilio",
        "response" => true
      ), 200);

    }else{
      return $response->withJson(array(
        "message" => "Los sentimos pero no tienes permisos para realizar esta peticion.",
        "response" => false
      ), 401);
    }
  });