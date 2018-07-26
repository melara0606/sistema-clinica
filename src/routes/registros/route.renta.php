<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get("/renta/configuration", function (Request $request, Response $response, $arguments){
    $all = $this->db->get('renta_valores');
    bitacora($this, 'RENTA', 'VIZUALIZAR LOS DATOS DE LAS RENTAS');

    return $response->withJson(array(
      "data" => $all
    ), 201);
  });

  $app->get("/renta/correlativos", function (Request $request, Response $response, $arguments){
    $all = $this->db->get('configuration');

    bitacora($this, 'RENTA', 'VER LOS CORRELATIVOS DE LA RENTA');
    return $response->withJson(array(
      "data" => $all
    ), 201);
  });

  $app->post('/renta/update', function(Request $request, Response $response, $arguments) {
    $object = $request->getParsedBody();

    bitacora($this, 'RENTA', 'ACTUALIZAR LOS DATOS DE LA RENTA');
    $item = $object["item"];
    $itemCurrent = $object['itemCurrent'];

    $isResponse = $this->db->where('id', $itemCurrent['id'])->update('renta_valores', array(
      "hasta_renta"         => $itemCurrent['hasta_renta'],
      "porcentaje_renta"    => $itemCurrent['porcentaje_renta'],
      "sobre_exceso"        => $itemCurrent['sobre_exceso'],
      "cuota_fija_renta"    => $itemCurrent['cuota_fija_renta']
    ));

    if($isResponse){
      $idNext = intval($item['id']) + 1;
      if($idNext <= 4){
        $nextMoney = floatval($itemCurrent['hasta_renta']) + 0.01;
        $this->db->where('id', $idNext)->update('renta_valores', array(
          "desde_renta"   => $nextMoney
        ));
      }
      return $response->withJson(array(
        "message" => "Hemos realizado con exito tu peticion",
        "getData" => $this->db->where('id', $item['id'])->getOne('renta_valores')
      ), 201);
    }else{
      return $response->withJson(array(
        "data" => "Lo sentimos pero tenemos un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });

  $app->post("/modificaciones/isss", function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'RENTA', 'CONFIGURACION LOS PORCENTAJES DEL ISSS');

    $isResponse = $this->db->where('configuration_id', 4)->update('configuration', array(
      "value_campus"  => $object["value"]
    ));

    if($isResponse){
      return $response->withJson(array(
        "message" => "Hemos realizado con exito tu peticion",
        "getData" => $this->db->where('configuration_id', 4)->getOne('configuration')
      ), 201);
    }else{
      return $response->withJson(array(
        "data" => "Lo sentimos pero tenemos un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });

  $app->post('/modificaciones/afp', function(Request $request, Response $response, $arguments){
    $object = $request->getParsedBody();
    bitacora($this, 'RENTA', 'MODIFICACION DEL PORCENTAJE DE EL AFP');

    $isResponse = $this->db->where('configuration_id', 5)->update('configuration', array(
      "value_campus"  => $object["value"]
    ));

    if($isResponse){
      return $response->withJson(array(
        "message" => "Hemos realizado con exito tu peticion",
        "getData" => $this->db->where('configuration_id', 5)->getOne('configuration')
      ), 201);
    }else{
      return $response->withJson(array(
        "data" => "Lo sentimos pero tenemos un problema con el servidor, intenta mas tarde"
      ), 401);
    }
  });
?>
