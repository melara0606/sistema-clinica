<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  $app->get('/noticias', function(Request $request, Response $response) {
    bitacora($this, 'NOTICIAS', 'PRESENTAR UN NOTICIA'); 
    return $response->withJson(array(
      "data"    => $this->db->get('noticias')
    ), 201);
  });

  $app->post('/noticias', function(Request $request, Response $response){
    $object = $request->getParsedBody();
    bitacora($this, 'NOTICIAS', 'AGREGAR UN NOTICIA'); 
    $session = $this->session->get('userObject');

    $notice = $this->db->insert('noticias', array(
      "titulo"        => $object['titulo'],
      "body"          => $object['html'],
      "fecha"         => $this->db->now(),
      "created_date"  => $this->db->now(),
      "user_created"  => $session['email'],
      "user_update"   => $session['email'],
      "publisher"     => 1
    ));

    if($notice){
      return $response->withJson(array(
        "data"  => $this->db->where('id', $notice)->getOne('noticias'),
        "message"    => "Hemos agregado con exito la nueva noticia"
      ), 201);
    }else{
      return $response->withJson(array(
        "message"    => "Hemos tenido un problema intenta mas tarde"
      ), 401);
    }
  });

  $app->post('/noticias/status', function(Request $request, Response $response){
    $object = $request->getParsedBody();
    bitacora($this, 'NOTICIAS', 'ACTUALIZAR ESTADO DE UNA NOTICIA'); 
    $session = $this->session->get('userObject');

    $notice = $this->db->where('id', $object['noticia'])->update('noticias', array(
      "publisher" => $this->db->not(),
      "fecha"     => $this->db->now(),
      "user_update"   => $session['email']
    ));

    return $response->withJson(array(
      "data"  => $this->db->where('id', $object['noticia'])->getOne('noticias'),
      "message"    => "Hemos actualizado el estado de la noticia"
    ), 201);
  });


  $app->post('/noticias/update', function(Request $request, Response $response){
    $object = $request->getParsedBody();
    bitacora($this, 'NOTICIAS', 'ACTUALIZAR UNA NOTICIA'); 
    $session = $this->session->get('userObject');

   $notice = $this->db->where('id', $object['id'])->update('noticias', array(
      "titulo"        => $object['titulo'],
      "body"          => $object['html'],
      "fecha"         => $this->db->now(),
      "user_update"   => $session['email']
    ));

     if($notice){
      return $response->withJson(array(
        "data"  => $this->db->where('id', $object['id'])->getOne('noticias'),
        "message"    => "Hemos agregado con exito la nueva noticia"
      ), 201);
    }else{
      return $response->withJson(array(
        "message"    => "Hemos tenido un problema intenta mas tarde"
      ), 401);
    }
  });