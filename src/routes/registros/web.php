<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  function orderPromocionExamen ($data = array()) {
    $array = array();
    foreach ($data as $key => $value) {
      if(!array_key_exists($value["id"], $array)){
        $array[$value["id"]] = array(
          "nombre"  => $value["nombre"],
          "precio"  => $value["precio"],
          "items"   => array()
        );
      }
      array_push($array[$value["id"]]["items"], $value["nombre_examen"]);
    }
    return $array;
  }

  $app->get('/', function(Request $request, Response $response){
    $notices = $this->db->where('publisher', 1)->get('noticias');
    return $this->view->render($response, 'web/index.twig', [
      "base"      => $this->baseUrl,
      "noticias"  => $notices
    ]);
  });

  $app->get('/services', function(Request $request, Response $response){
    $QueryExamenes = "SELECT categorias_examenes.nombre_categoria as nombre, examenes.nombre_examen, examenes.precio, examenes.categoria_id as id FROM categorias_examenes Inner Join examenes ON categorias_examenes.id = examenes.categoria_id order by categorias_examenes.nombre_categoria asc";

    $QueryPromociones = "SELECT promociones.nombre_promocion as nombre, promociones.precio, promociones.id, promocion_examenes.promocion_id as id, promocion_examenes.examen_id, examenes.nombre_examen FROM promociones Inner Join promocion_examenes ON promocion_examenes.promocion_id = promociones.id Inner Join examenes ON promocion_examenes.examen_id = examenes.id where promociones.estado = 1";

    return $this->view->render($response, 'web/services.twig', [
      "base" => $this->baseUrl,
      "examenes"  => orderPromocionExamen($this->db->rawQuery($QueryExamenes)),
      "promociones" => orderPromocionExamen($this->db->rawQuery($QueryPromociones))
    ]);
  });

  $app->get('/contact', function(Request $request, Response $response){
    return $this->view->render($response, 'web/contact.twig', [
      "base" => $this->baseUrl,
      "sucursales" => $this->db->where('status', 1)->get('sucursales')
    ]);
  });

  $app->get('/about', function(Request $request, Response $response){
    return $this->view->render($response, 'web/about.twig', [
      "base" => $this->baseUrl
    ]);
  });