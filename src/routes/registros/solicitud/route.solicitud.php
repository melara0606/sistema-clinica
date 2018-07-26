<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->post('/solicitud/adomicilio/{id}/cancelar', function(Request $request, Response $response, $arguments) {
    $id = $arguments['id'];
    $allResponse = $this->db->where('id', $id)->update('solicitud_adomicilio', array(
      "estado"  => 2
    ));
    return $response->withJson(array(
      "message"   => "Se cancelo la solicitud, con exito"
    ), 201);
  });

  /* Solicitud - Examen de cortesia */
  $app->post('/solicitud/examenes/cortesia', function(Request $request, Response $response, $arguments){
    bitacora($this, 'EXAMEN DE CORTESIA', 'ADMINSTRADOR DE SOLICITUD');
    $object = $request->getParsedBody();
    $result = $this->db->where('id', $object['id'])->update('solicitud_item_examen', array(
      'is_cortesias'  => $this->db->not()
    ));
    if($result){
      if($object['estado'] == 1){
        $sqlQuery = "UPDATE solicitud_item_examen AS item INNER JOIN solicitud ON solicitud.id = item.solicitud_id SET solicitud.monto = solicitud.monto + item.precio WHERE item.id = '".$object['id']."'";
      }else{
        $sqlQuery = "UPDATE solicitud_item_examen AS item INNER JOIN solicitud ON solicitud.id = item.solicitud_id SET solicitud.monto = solicitud.monto - item.precio WHERE item.id = '".$object['id']."'";
      }
      $object = $this->db->rawQuery($sqlQuery);
      return $response->withJson(array(
        "message"   => "Se realizo con exito su peticion"
      ), 201);
    }else{
      return $response->withJson(array(
        "message" => 'Tenemos un problema con el servidor intenta mas tarde'
      ), 401);
    }
  });

  $app->get('/solicitud/all', function(Request $request, Response $response, $arguments){
    bitacora($this, 'SOLICITUD', 'VIZUALIZAR SOLICITUD');
    $userObject = $this->session->get('userObject');
    $all =  $this->db
      ->join("pacientes AS pac", "sol.paciente_id = pac.id", "INNER")
      ->where('sucursal_id', $userObject['sucursal_id'])
      ->orderBy('sol.fecha_creacion', 'DESC')
      ->get('solicitud AS sol', 10, "sol.*, pac.lastname_pac,pac.name_pac");

    return $response->withJson(array(
      'data' => $all
    ), 201);
  });

  $app->post('/queryOptionStatus', function (Request $request, Response $response)
  {
    bitacora($this, 'SOLICITUD', 'BUSCAR ESTADO DE SOLICITUD');
    $object = $request->getParsedBody();
    $results = $this->db
        ->join('pacientes as pac', 'pac.id = sol.paciente_id', 'INNER')
        ->where('estado', $object['code'])
      ->get('solicitud as sol', 10, 'sol.*, pac.name_pac, pac.lastname_pac');
      
    return $response->withJson(array(
      'data' => $results
    ), 201);
  });

  $app->post('/solicitud/changeStatusSolicitud', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();

    bitacora($this, 'SOLICITUD', 'CAMBIAR ESTADO DE SOLICITUD');
    $sqlExamen = "SELECT COUNT(*) AS countExamen FROM solicitud_item_examen WHERE solicitud_id = '{$object['id']}'";
    $sqlExamenComplete = "SELECT COUNT(*) AS countExamenComplete FROM solicitud_item_examen WHERE solicitud_id = '{$object['id']}' AND estado = 3";

    $QueryExamenCount = $this->db->rawQuery($sqlExamen);
    $QueryExamenComplete = $this->db->rawQuery($sqlExamenComplete);

    if(intval($QueryExamenCount[0]['countExamen']) == intval($QueryExamenComplete[0]['countExamenComplete'])){
      $isObject = $this->db->where('id', $object['id'])->getOne('solicitud', 'pagoTotal');
      $isStatus = $isObject['pagoTotal'] == 1 ? 4 : 0;
      $isMessage = "Hemos descubierto que tu solicitud ya ha sido pagada previamente";
  
      $this->db->where('id', $object['id'])->update('solicitud', array(
        "estado" => $isStatus
      ));
  
      if($isStatus == 0)
        $isMessage = "Hemos cerrado con exito la solicitud.";
  
        return $response->withJson(array(
          "message"   => $isMessage,
          "response"  => $isStatus
        ), 201);
      }else{
        return $response->withJson(array(
          "message" => 'Tenemos un problema, aun no has completado todos los examenes de esta solicitud.'
        ), 401);
      }
  });

  $app->post('/solicitud/observaciones/item', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();

    bitacora($this, 'SOLICITUD', 'COMENTARIO A UN ITEM DE LA SOLICITUD');
    return $response->withJson(array(
      'data' => $this->db->where('solicitud_item_examen_id', $object['item']['id'])->getOne('solicitud_item_examen_observacion')
    ), 201);
  });

  $app->post('/solicitud/observaciones', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();

    bitacora($this, 'SOLICITUD', 'COMENTARIO A UN ITEM DE LA SOLICITUD');
    if(@$object['observacion']['id']){
      $this->db->where('id', $object['observacion']['id'])->update('solicitud_item_examen_observacion', array(
        'observacion' => $object['observacion']['observacion']
      ));
      return $response->withJson(array(
        'message' => "Hemos actualizado con exito la observacion"
      ), 201);
    }else{
      $this->db->insert('solicitud_item_examen_observacion', array(
        'solicitud_item_examen_id' => $object['item']['id'],
        'observacion' => $object['observacion']['observacion']
      ));
      $this->db->where('id', $object['item']['id'])->update('solicitud_item_examen', array(
        "estado" => 2
      ));
      return $response->withJson(array(
        'message' => "Hemos agregado con exito la observacion",
      ), 201);
    }
  });

  $app->post('/changeStatusExamenItem', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'CAMBIAR ESTADO ITEM');
    $this->db->where('id', $object['id'])->update('solicitud_item_examen', array(
      "estado" => 3
    ));
    return $response->withJson(array(
      'message' => "Hemos actualizado con exito el estado del examen",
      'isError' => false
    ), 201);
  });

  $app->post('/changeStatusExamen', function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    $object = $object['item'];
    bitacora($this, 'SOLICITUD', 'CAMBIAR ESTADO EXAMEN');

    $queryCamposExamenCount = $this->db->rawQuery("SELECT count(*) as campos FROM examen_campo WHERE examen_id = '{$object['examen_id']}'");
    $queryRespuestaCount = $this->db->rawQuery("SELECT count(*) as respuesta FROM solicitud_respuesta WHERE solicitud_item_examen_id = '{$object['id']}'");

    if($queryCamposExamenCount[0]['campos'] == $queryRespuestaCount[0]['respuesta']){
      $this->db->where('examen_id', $object['examen_id'])->update('solicitud_item_examen', array(
        "estado" => 3
      ));
      return $response->withJson(array(
        'message' => "Hemos actualizado con exito el estado del examen",
        'isError' => false
      ), 201);
    }else{
      return $response->withJson(array(
        'isError' => true,
        'data' => array($queryCamposExamenCount[0], $queryRespuestaCount[0] )
      ), 201);
    }

  });

  $app->post("/solicitud/campos", function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'AGREGAR CAMPO A LA SOLICITUD');

    $results["examen"] = $this->db->where("id", $object["item"]["examen_id"])->getOne("examenes");

    $queryExamen = "SELECT exc.id, cc.id_tipo_catalogo, cc.grupo_seleccion, cc.rango_valor, cc.unidades, cc.nombre_campo, exc.catalogo_campo_id, cc.valor_opcional FROM examen_campo AS exc INNER JOIN catalogo_campos AS cc ON exc.catalogo_campo_id = cc.id WHERE exc.examen_id = {$object['item']['examen_id']} AND id_tipo_catalogo <> 4 order by exc.orden_value";

    $results["campos"] = $this->db->rawQuery($queryExamen);

    $queryExamen = "SELECT DISTINCT cc.grupo_seleccion FROM examen_campo AS exc INNER JOIN catalogo_campos AS cc ON exc.catalogo_campo_id = cc.id WHERE exc.examen_id = {$object['item']['examen_id']}";
    $querySeleccion = "SELECT * FROM seleccion_campos WHERE grupo_seleccion IN ({$queryExamen})";
    $results["grupoSeleccion"] = $this->db->rawQuery($querySeleccion);

    $results["results"] = $this->db->where("solicitud_item_examen_id", $object['item']['id'])->get("solicitud_respuesta");

    return $response->withJson(array(
      'data' => $results
    ), 201);
  });

  $app->post("/solicitud/add/response", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'AGREGAR RESPUESTA A LA SOLICITUD');
    foreach($object["campos"] as $value){
      if(@$value["value_campos"] || @$value["value_seleccion"] || @$value["digitable_value"]){
        @$digitable = $value["digitable_value"];

        $arrayData = array(
          "catalogo_campo_id" => $value['catalogo_campo_id'],
          "solicitud_item_examen_id" => $object["solicitudItem"],
          "resultado" => !@isset($value["value_campos"]) ? (@isset($digitable) ? @$digitable : "") : @trim($value["value_campos"]),
          "seleccion_valor" => @!isset($value["value_seleccion"]) ? "" : @trim($value["value_seleccion"]),
          "criterio_rango" => @!isset($value["criterio_rango"]) ? "" : @trim($value["criterio_rango"])
        );

        if(isset($value["id_respuesta"])){
          $this->db->where('id', $value["id_respuesta"])->update('solicitud_respuesta', $arrayData);
        }else{
          $this->db->insert("solicitud_respuesta", $arrayData );
        }
      }
    }

    return $response->withJson(array(
      'message' => 'Hemos realizado con exito tu peticion'
    ), 201);
  });

  $app->post("/solicitud/search", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'BUSCAR UN SOLICITUD');
    if($object["queryOption"] == '1'){
      $objectQuery = $this->db
        ->join("pacientes as pac", 'sol.paciente_id = pac.id')
        ->where("sol.id", '%'.$object['q'].'%', 'like')->get("solicitud as sol", 10, 'sol.*, pac.name_pac, pac.lastname_pac');
    }else if($object["queryOption"] == '2') {
      $query = "SELECT sol.*, pac.name_pac, pac.lastname_pac FROM solicitud AS sol JOIN pacientes AS pac ON sol.paciente_id = pac.id WHERE (pac.name_pac LIKE '%{$object['q']}%' OR pac.lastname_pac LIKE '%{$object['q']}%' OR pac.dui LIKE '%{$object['q']}%' OR pac.carnet LIKE '%{$object['q']}%' OR pac.codigo_paciente LIKE '%{$object['q']}%') AND sol.fecha_creacion LIKE '%{$object['fechaBusquedad']}%' LIMIT 10";
      $objectQuery = $this->db->rawQuery($query);
    }
    return $response->withJson(array(
      'data' => $objectQuery
    ), 201);
  });

  $app->get('/solicitud/{id}/all', function(Request $request, Response $response, $arguments){
    bitacora($this, 'SOLICITUD', 'PRESENTAR LA INFORMACION DE UNA SOLICITUD');
    $query = "SELECT solicitud.*, pacientes.name_pac, pacientes.lastname_pac "
              ." FROM solicitud INNER JOIN pacientes ON solicitud.paciente_id = pacientes.id "
              ." WHERE solicitud.id ='" . $arguments['id'] . "'";
    $data = $this->db->rawQuery($query);

    $queryExamenes = "SELECT examenes.categoria_id, examenes.nombre_examen, examenes.precio, examenes.tipo_reporte, examenes.is_only,"
                    ."solicitud_item_examen.examen_id, solicitud_item_examen.*, categorias_examenes.nombre_categoria "
                    ."FROM solicitud_item_examen INNER JOIN examenes ON solicitud_item_examen.examen_id = examenes.id "
                    ."INNER JOIN categorias_examenes ON categorias_examenes.id = examenes.categoria_id "
                    ."WHERE solicitud_item_examen.solicitud_id = '".$arguments['id']."'";

    $data[0]['examenes'] = $this->db->rawQuery($queryExamenes);
    return $response->withJson(array(
      'data' => $data[0]
    ), 201);
  });

  $app->post('/solicitud/pagoTotal', function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'HACER EL PAGO TOTAL DE SOLICITUD');

    $this->db->where('id', $object['id'])->update('solicitud', array(
      'pagoTotal' => 1
    ));

    return $response->withJson(array(
      'message' => 'Tu pago fue realizado con exito!'
    ), 201);
  });

  $app->post("/solicitud/monto", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'AGREGAR UN MONTO A LA SOLICITUD');

    $array = array(
      "solicitud_monto"   => $object['id_solicitud'] ,
      "monto"             => $object['monto']
    );
    $isResponse = $this->db->insert('solicitud_monto', $array);
    return $response->withJson(array(
      'data' => array(
        'id' => $isResponse
      ),
      'message' => 'Hemos agregado con exito tu monto!'
    ), 201);
  });

  function onNumberFormat($number = 0){
    if($number < 9)
      return "00${number}";
    else if($number > 9 && $number < 100)
      return "0${number}";
    return "${number}";
  }

  $app->post("/solicitud/add", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'AGREGAR UNA NUEVA SOLICITUD');
    $userObject = $this->session->get('userObject');
    $result = $this->db->where('tipo_solicitud', $object['type'])
                  ->orderBy('fecha_creacion', 'DESC')->getOne('solicitud', 'id, NOW() as dia');

    if(count($result) > 0){
      $dateFromDB     = new DateTime($result['dia']);
      $fSolicitud     = substr($result['id'], 3, 8);
      $cSolicitud     = substr($result['id'], -3);
      $intSolicitud   = intval($cSolicitud);
      $newCorrelativo = null;

      if( strcmp($dateFromDB->format('dmY'), $fSolicitud) == 0 )
        $newCorrelativo = $dateFromDB->format('dmY').onNumberFormat(++$intSolicitud);
      else
        $newCorrelativo =  $dateFromDB->format('dmY').onNumberFormat(1);
    }else{
      $result = $this->db->rawQuery("SELECT NOW() as dia");
      $dateFromDB     = new DateTime($result[0]["dia"]);
      $newCorrelativo =  $dateFromDB->format('dmY').onNumberFormat(1);
    }

    switch ($object['type']) {
      case '1': return solicitud_promocion($object, $this->db, $response, $newCorrelativo, $userObject);
      case '2': return solicitud_examen($object, $this->db, $response, $newCorrelativo, $userObject);
      case '3': return solicitud_entidad($object, $this->db, $response, $newCorrelativo, $userObject);
    }
  });

  $app->post('/solicitud/facturar/code', function(Request $request, Response $response, $arguments) {
    $allPost = $request->getParsedBody();
    bitacora($this, 'SOLICITUD', 'AGREGAR UN CODIGO A LA FACTURA');

    $solicitud = $this->db
            ->join('pacientes as pac', 'pac.id = sol.paciente_id', 'INNER')
            ->where('sol.id', $allPost["id"])
            ->getOne('solicitud as sol', 'pac.name_pac, pac.lastname_pac, sol.*');

    $solicitud["abono"] = $this->db->where('solicitud_monto', $allPost["id"])->getOne('solicitud_monto');
    $solicitud["items"] = $this->db
                ->join('examenes as exa', 'exa.id = solitem.examen_id', 'INNER')
                ->where('solitem.solicitud_id', $allPost["id"])
                ->get('solicitud_item_examen as solitem', null, 'exa.nombre_examen, solitem.*');

    return $response->withJson(array(
      'data' => $solicitud
    ), 201);
  });

  $app->post('/solicitud/facturar', function(Request $request, Response $response, $arguments) {
    $allPost = $request->getParsedBody();
    $array = array();
    bitacora($this, 'SOLICITUD', 'FACTURAR UNA SOLICITUD');

    if(@$allPost['isDescuento']){
      $this->db->insert('solicitud_descuento', array(
        "solicitud_id"    => $allPost["id"],
        "descuento"       => $allPost["descuento"]
      ));

      $id = $this->db->insert('solicitud_facturar', array(
        "solicitud_id" => $allPost["id"],
        "monto"        => floatval($allPost["monto"]) - floatval($allPost["abono"]['monto']) - floatval($allPost["descuento"])
      ));
    }else{
      $id = $this->db->insert('solicitud_facturar', array(
        "solicitud_id"    => $allPost["id"],
        "monto"           => floatval($allPost["monto"]) - floatval($allPost["abono"]['monto'])
      ));
    }

    $this->db->where('id', $allPost["id"])->update('solicitud', array(
      "estado"  => 4
    ));

    return $response->withJson(array(
      'data' => $id
    ), 201);
  });

  $app->get('/solicitudes/admicilio/perfil', function(Request $request, Response $response, $arguments) {
    $session = $this->session->get('userObject');
    $solicitud = $this->db
      ->join('sucursales', 'solicitud_adomicilio.sucursal_id = sucursales.id', 'INNER')
      ->where('solicitud_adomicilio.paciente_id', $session['id'])
      ->get('solicitud_adomicilio', null, 'solicitud_adomicilio.id, solicitud_adomicilio.codigo,  solicitud_adomicilio.estado, solicitud_adomicilio.pagar, sucursales.nombre_sucursal');

    return $response->withJson(array(
      'data' => $solicitud
    ), 201);
  });

  $app->get('/solicitudes/admicilio', function(Request $request, Response $response, $arguments) {
    $session = $this->session->get('userObject');
    bitacora($this, 'SOLICITUD', 'AGREGAR UNA SOLICITUD ADOMICILIO');
    $arrayOfList = $this->db
        ->join('pacientes as pac', 'pac.id = solDom.paciente_id', 'INNER')
        ->where('sucursal_id', $session['sucursal_id'])
        ->where('estado', 1)
        ->get('solicitud_adomicilio as solDom', null, 'solDom.*, pac.name_pac, pac.lastname_pac, pac.telefono');

    return $response->withJson(array(
      'data' => $arrayOfList
    ), 201);
  });

  $app->get('/solicitudes/{id}/admicilio', function(Request $request, Response $response, $arguments) {
    $session = $this->session->get('userObject');
    bitacora($this, 'SOLICITUD', 'PRESENTAR UNA SOLICITUD ADOMICILIO');
    $arrayOfList = $this->db
        ->join('pacientes as pac', 'pac.id = solDom.paciente_id', 'INNER')
        ->where('sucursal_id', $session['sucursal_id'])
        ->where('estado', 1)
        ->where('solDom.id', $arguments['id'])
        ->getOne('solicitud_adomicilio as solDom', 'solDom.*, pac.name_pac, pac.lastname_pac, pac.telefono, pac.address_pac');

    $arrayOfList['examenesList'] = $this->db
                  ->join('examenes as ex', 'ex.id = solDom.examen_id', 'INNER')
                  ->where('solDom.solicitud_adomicilio_id', $arguments['id'])
                  ->get('solicitud_adomicilio_examenes as solDom', null, 'solDom.examen_id, ex.nombre_examen, solDom.precio');
    return $response->withJson(array(
      'data' => $arrayOfList
    ), 201);
  });

  $app->post('/solicitudes/convertirToNormal', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();
    $this->db->where('id', $object['id'])->update('solicitud_adomicilio', array(
      "estado" => 0
    ));
  });

  function solicitud_entidad($object, $db, $response, $code, $userObject){
    $idEntidad = $object['entidad']['id'];
    $isResponse = $db->where('id_entidad', $idEntidad)->getOne('entidad_monto');

    $code = "ENT".$code;
    if(floatval($isResponse['monto']) < $object['total']){
      return $response->withJson(array(
        'message' => 'Lo sentimos pero no tienes suficiente monto en tu entidad!'
      ), 401);
    }

    $idArray = array();

    $idSolicitud = $db->insert('solicitud', array(
      'tipo_solicitud'  => 3,
      'estado' => 1,
      'sucursal_id' => $userObject['sucursal_id'],
      'fecha_creacion'  => $db->now(),
      'monto'   => floatval($object['total']),
      'paciente_id' => $object['paciente'],
      'id'          => $code
    ));

    foreach ($object['listExamen'] as $value) {
      $id = $db->insert('solicitud_item_examen', array(
        'solicitud_id'    => $code,
        'examen_id'       => $value['id_examen'],
        'precio'          => $value['precio']
      ));
      array_push($idArray, $id);
    }

    $idEntidadSolicitud = $db->insert('solicitud_entidad', array(
      'solicitud_id'    => $code,
      'entidad_id'      => $idEntidad
    ));

    $db->where('id_entidad', $idEntidad)->update('entidad_monto', array(
      'monto' => $db->dec(floatval($object['total']))
    ));

    //bitacora($this, 'SOLICITUD', 'AGREGAR SOLICITUD ENTIDAD');
    return $response->withJson(array(
      'data' => array(
        'id_solicitud' => $code,
        'id_solicitud_entidad'  => $idEntidadSolicitud,
        'items_examenes'  => $idArray
      ),
      'message' => 'Hemos agregado con exito tu solicitud!'
    ), 201);
  }

  function solicitud_examen($object, $db, $response, $code, $userObject){
    $idArray = array();
    $code = "PAR".$code;
    $idSolicitud = $db->insert('solicitud', array(
      'tipo_solicitud'  => 2,
      'estado' => 1, // estado iniciado
      'fecha_creacion'  => $db->now(),
      'monto'   => floatval($object['total']),
      'paciente_id' => $object['paciente'],
      'id'          => $code,
      'sucursal_id' => $userObject['sucursal_id']
    ));

    foreach ($object['listExamen'] as $value) {
      $id = $db->insert('solicitud_item_examen', array(
        'solicitud_id'    => $code,
        'examen_id'       => $value['examen']['id'],
        'precio'          => $value['examen']['precio']
      ));
      array_push($idArray, $id);
    }

    //bitacora($this, 'SOLICITUD', 'AGREGAR SOLICITUD EXAMEN');
    return $response->withJson(array(
      'data' => array(
        'id_solicitud' => $code,
        'items_examenes'  => $idArray
      ),
      'message' => 'Hemos agregado con exito tu solicitud!'
    ), 201);
  }

  function solicitud_promocion($object, $db, $response, $code, $userObject){
    $idArray = array();
    $code = "PRO".$code;

    $idSolicitud = $db->insert('solicitud', array(
      'tipo_solicitud'  => 1,
      'estado' => 1, // estado iniciado
      'fecha_creacion'  => $db->now(),
      'monto'   => floatval($object['promocion']['precio']),
      'paciente_id' => $object['paciente'],
      'id'          => $code,
      'sucursal_id' => $userObject['sucursal_id']
    ));

    foreach ($object['promocion']['examenes'] as $value) {
      $id = $db->insert('solicitud_item_examen', array(
        'solicitud_id'    => $code,
        'examen_id'       => $value['id'],
        'precio'          => 0.00
      ));
      array_push($idArray, $id);
    }

    $idPromocionSolicitud = $db->insert('solicitud_promocion', array(
      'solicitud_id'    => $code,
      'promocion_id'    => $object['promocion']['id']
    ));

    //bitacora($this, 'SOLICITUD', 'AGREGAR SOLICITUD PROMOCION');
    return $response->withJson(array(
      'data' => array(
        'id_solicitud' => $code,
        'id_solicitud_promocion'  => @$idPromocionSolicitud,
        'items_examenes'  => $idArray
      ),
      'message' => 'Hemos agregado con exito tu solicitud!'
    ), 201);
  }

  
