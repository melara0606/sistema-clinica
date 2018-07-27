<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  function send_email_solicitudType($isResponse, $dir, $nameFile, $baseUrl){
    if($isResponse->getStatusCode() === 200){
      $routeDir = "{$dir}/send/solicitud/{$nameFile}.pdf";
      $mail = new PHPMailer(false);
      $isTrue = false;
      try {
          $mail->SMTPDebug = 2;
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'mmfisherst@gmail.com';
          $mail->Password = 'mmfisherst12345';
          $mail->SMTPSecure = 'ssl';
          $mail->Port = 465;
      
          //Recipients
          $mail->setFrom('mmfisherst@gmail.com', utf8_decode( "Laboratorio Clínico MM FISHER'ST"));
          // $mail->addAddress( $array["compra"]['email_representante'] , $array["compra"]['nombre_proveedor']);
          $mail->addAddress( "melara0606@gmail.com" , "Edwin Melara Landaverde");

          $mail->addAttachment($routeDir);

          $mail->isHTML(true);
          $mail->Subject = utf8_decode('Order de compra');
          $mail->Body    = 'Esta es la orden de compra de productos solicitados.';

          $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
          );
          echo "<div style='display:none'>";
            $mail->send();
          echo "</div>";
          $isTrue = true;
      } catch (Exception $e) {
          echo 'Message could not be sent.';
          echo 'Mailer Error: ' . $mail->ErrorInfo;
      }
    }else{
      echo "tenemos un problema por el momento";
    }
    echo "<script type='text/javascript'>window.location.href='{$baseUrl}/send_email/accepts/response'</script>";
  }

  $app->get('/send/solicitud/email/options5', function(Request $request, Response $response) {
    $arrayResponse = array();
    $QueryParams = $request->getQueryParams();
    $session = $this->session->get('userObject');
    
    $arrayResponse["sucursal"] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales');
    $arrayResponse['paciente'] = $this->db->rawQuery("SELECT TIMESTAMPDIFF(YEAR, pac.date_pac, CURDATE()) as edad, pac.name_pac, pac.lastname_pac, pac.codigo_paciente as carnet, pac.genero_paciente, sol.id, sol.fecha_creacion FROM pacientes as pac inner join solicitud as sol on pac.id = sol.paciente_id where sol.id = '".$QueryParams['solicitud']."'");
  
    $arrayResponse['results'] = array();
  
    $items_examenes = $this->db
      ->join('solicitud_item_examen', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
      ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
      ->where('solicitud_item_examen.solicitud_id', $QueryParams['solicitud'])
      ->where('examenes.categoria_id', $QueryParams['categoria'])
      ->getOne('examenes', 'examenes.nombre_examen, solicitud_item_examen.examen_id, solicitud_item_examen.solicitud_id, examenes.categoria_id, solicitud_item_examen.id, examenes.tipo_reporte, examenes.is_only, categorias_examenes.nombre_categoria');
  
    $arrayResponse['results'] = $this->db
      ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
      ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id ', 'INNER')
      ->where('solicitud_item_examen.id', $items_examenes['id'])
      ->where('catalogo_campos.id_tipo_catalogo', 4, '<>')
      ->get('solicitud_item_examen', null, 'catalogo_campos.rango_valor, catalogo_campos.unidades, catalogo_campos.nombre_campo, solicitud_respuesta.resultado, solicitud_respuesta.seleccion_valor, solicitud_respuesta.criterio_rango, catalogo_campos.grupo_seleccion');
  
    $arrayResponse['multiples'] = array();
    $arrayResponse['categorias'] = array();
  
    $items = $this->db
      ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
      ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id', 'INNER')
      ->join('categoria_grupo_seleccion', 'categoria_grupo_seleccion.id = catalogo_campos.categoria_seleccion ', 'INNER')
      ->where('catalogo_campos.id_tipo_catalogo', 4)
      ->where('solicitud_item_examen.id', $items_examenes['id'])
      ->orderby('catalogo_campos.categoria_seleccion', 'asc')
      ->get('solicitud_item_examen', null, 'DISTINCT catalogo_campos.categoria_seleccion, categoria_grupo_seleccion.nombre_categoria_grupo');
  
      
    foreach ($items as $value) {
      $iData = $this->db
        ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
        ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id', 'INNER')
        ->where('catalogo_campos.id_tipo_catalogo', 4)
        ->where('solicitud_item_examen.id', $items_examenes['id'])
        ->where('catalogo_campos.categoria_seleccion', $value['categoria_seleccion'])
      ->get('solicitud_item_examen', null, 'catalogo_campos.nombre_campo, solicitud_respuesta.resultado,catalogo_campos.categoria_seleccion');
  
      $arrayResponse['multiples'][ $value['nombre_categoria_grupo'] ] = array(
        $iData
      );
  
      $item = $this->db
        ->join('seleccion_campos as sc', 'gcs.grupo_id = sc.grupo_seleccion', 'INNER')
        ->where('gcs.categoria_grupo_seleccion_id', $value['categoria_seleccion'])
        ->orderBy('sc.nombre_grupo')
      ->get('grupo_categoria_seleccion as gcs', null, 'DISTINCT (sc.nombre_grupo)');
      
      $value['seleccion'] = $item;
      array_push($arrayResponse['categorias'], $value);
    }
  
    $arrayResponse['CURRENT_DATE'] = $this->db->rawQuery('SELECT CURRENT_DATE() AS fecha');

    $nameFile = "solicitud-".date('Ymdhis')."-".$QueryParams['solicitud'];
    $isResponse = $this->renderer->render($response, '/reportes/examenes/examenes_heces.php', [
      "arrayOfResponse" => $arrayResponse,
      "fileName"        => $nameFile,
      "dir"             => $this->dir,
      "isSave"          => true
    ]);

    send_email_solicitudType($isResponse, $this->dir, $nameFile, $this->baseUrl);
  });

  $app->get('/send/solicitud/email/options3', function(Request $request, Response $response ) {
    $arrayResponse = array();
    $QueryParams = $request->getQueryParams();
    $session = $this->session->get('userObject');    

    $arrayResponse["sucursal"] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales');
    $arrayResponse['paciente'] = $this->db->rawQuery("SELECT TIMESTAMPDIFF(YEAR, pac.date_pac, CURDATE()) as edad, pac.name_pac, pac.lastname_pac, pac.codigo_paciente as carnet, pac.genero_paciente, sol.id, sol.fecha_creacion FROM pacientes as pac inner join solicitud as sol on pac.id = sol.paciente_id where sol.id = '".$QueryParams['solicitud']."'");

    $arrayResponse['results'] = array();

    $items_examenes = $this->db
      ->join('solicitud_item_examen', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
      ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
      ->where('solicitud_item_examen.solicitud_id', $QueryParams['solicitud'])
      ->where('examenes.categoria_id', $QueryParams['categoria'])
      ->getOne('examenes', 'examenes.nombre_examen, solicitud_item_examen.examen_id, solicitud_item_examen.solicitud_id, examenes.categoria_id, solicitud_item_examen.id, examenes.tipo_reporte, examenes.is_only, categorias_examenes.nombre_categoria');
    
    /* PARA LOS CAMPOS QUE TIENE UNA SELECCION ASIGNADA */
    $arrayResponse['results']['valor'] = $this->db
      ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
      ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id ', 'INNER')
      ->where('catalogo_campos.unidades', '')
      ->where('solicitud_item_examen.id', $items_examenes['id'])
      ->orderBy('catalogo_campos.grupo_seleccion')
      ->get('solicitud_item_examen', null, 'catalogo_campos.rango_valor, catalogo_campos.unidades, catalogo_campos.nombre_campo, solicitud_respuesta.resultado, solicitud_respuesta.seleccion_valor, solicitud_respuesta.criterio_rango, catalogo_campos.grupo_seleccion');

    $arrayResponse['results']['grupos_seleccion'] = $this->db
      ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
      ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id ', 'INNER')
      ->where('catalogo_campos.unidades', '', '<>')
      ->where('solicitud_item_examen.id', $items_examenes['id'])
      ->orderBy('solicitud_respuesta.seleccion_valor')
      ->get('solicitud_item_examen', null, 'catalogo_campos.rango_valor, catalogo_campos.unidades, catalogo_campos.nombre_campo, solicitud_respuesta.resultado, solicitud_respuesta.seleccion_valor, solicitud_respuesta.criterio_rango, catalogo_campos.grupo_seleccion');

      $arrayResponse['CURRENT_DATE'] = $this->db->rawQuery('SELECT CURRENT_DATE() AS fecha');

      $nameFile = "solicitud-".date('Ymdhis')."-".$QueryParams['solicitud'];
      $isResponse = $this->renderer->render($response, '/reportes/examenes/examenes_bacteriologia_urologia.php', [
        "arrayOfResponse" => $arrayResponse,
        "fileName"        => $nameFile,
        "dir"             => $this->dir,
        "isSave"          => true
      ]);

      send_email_solicitudType($isResponse, $this->dir, $nameFile, $this->baseUrl);
  });

  $app->get('/send/solicitud/email/options2', function(Request $request, Response $response ) {
    $arrayResponse = array();
    $QueryParams = $request->getQueryParams();
    $session = $this->session->get('userObject');

    $arrayResponse["sucursal"] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales');
    $arrayResponse['paciente'] = $this->db->rawQuery("SELECT TIMESTAMPDIFF(YEAR, pac.date_pac, CURDATE()) as edad, pac.name_pac, pac.lastname_pac, pac.codigo_paciente as carnet, pac.genero_paciente, sol.id, sol.fecha_creacion FROM pacientes as pac inner join solicitud as sol on pac.id = sol.paciente_id where sol.id = '".$QueryParams['solicitud']."'");
    $arrayResponse['results'] = array();

    $items_examenes = $this->db
        ->join('solicitud_item_examen', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
        ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
        ->where('solicitud_item_examen.solicitud_id', $QueryParams['solicitud'])
        ->where('examenes.categoria_id', $QueryParams['categoria'])
        ->getOne('examenes', 'examenes.nombre_examen, solicitud_item_examen.examen_id, solicitud_item_examen.solicitud_id, examenes.categoria_id, solicitud_item_examen.id, examenes.tipo_reporte, examenes.is_only, categorias_examenes.nombre_categoria');

    $results = $this->db
      ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
      ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id', 'INNER')
      ->join('examen_campo', 'examen_campo.catalogo_campo_id = catalogo_campos.id ', 'INNER')
      ->where('solicitud_item_examen.id', $items_examenes['id'])
      ->groupBy('examen_campo.orden_value')
      ->orderBy('examen_campo.orden_value', 'asc')
      ->get('solicitud_item_examen', null, 'catalogo_campos.rango_valor, catalogo_campos.unidades, catalogo_campos.nombre_campo,solicitud_respuesta.resultado, solicitud_respuesta.seleccion_valor, solicitud_respuesta.criterio_rango, examen_campo.orden_value, catalogo_campos.id_tipo_catalogo');


    array_push($arrayResponse['results'], array(
      "data" => $items_examenes,
      "response" => $results
    ));

    $arrayResponse['CURRENT_DATE'] = $this->db->rawQuery('SELECT CURRENT_DATE() AS fecha');
    
    $nameFile = "solicitud-".date('Ymdhis')."-".$QueryParams['solicitud'];
    $isResponse = $this->renderer->render($response, '/reportes/examenes/examenes_orina.php', [
      "arrayOfResponse" => $arrayResponse,
      "fileName"        => $nameFile,
      "dir"             => $this->dir,
      "isSave"          => true
    ]);

    send_email_solicitudType($isResponse, $this->dir, $nameFile, $this->baseUrl);
  });

  $app->get('/send/solicitud/email/{type}', function(Request $request, Response $response ) {
    $arrayResponse = array();
    $QueryParams = $request->getQueryParams();
    $session = $this->session->get('userObject');
    $arrayResponse['view'] = true;

    $arrayResponse["sucursal"] = $this->db->where('id', $session['sucursal_id'])->getOne('sucursales');
    $arrayResponse['paciente'] = $this->db->rawQuery("SELECT TIMESTAMPDIFF(YEAR, pac.date_pac, CURDATE()) as edad, pac.name_pac, pac.lastname_pac, pac.codigo_paciente as carnet, pac.genero_paciente, sol.id, sol.fecha_creacion FROM pacientes as pac inner join solicitud as sol on pac.id = sol.paciente_id where sol.id = '".$QueryParams['solicitud']."'");

    $arrayResponse['results'] = array();
    $items_examenes = $this->db
        ->join('solicitud_item_examen', 'solicitud_item_examen.examen_id = examenes.id', 'INNER')
        ->join('categorias_examenes', 'examenes.categoria_id = categorias_examenes.id', 'INNER')
        ->where('solicitud_item_examen.solicitud_id', $QueryParams['solicitud'])
        ->where('examenes.categoria_id', $QueryParams['categoria'])
        ->where('examenes.tipo_reporte', 1)
        ->get('examenes', null, 'examenes.nombre_examen, solicitud_item_examen.examen_id, solicitud_item_examen.solicitud_id, examenes.categoria_id, solicitud_item_examen.id, examenes.tipo_reporte, examenes.is_only, categorias_examenes.nombre_categoria');

    if(count($items_examenes) > 0){
      foreach ($items_examenes as $value) {
        $results = $this->db
          ->join('solicitud_respuesta', 'solicitud_respuesta.solicitud_item_examen_id = solicitud_item_examen.id', 'INNER')
          ->join('catalogo_campos', 'solicitud_respuesta.catalogo_campo_id = catalogo_campos.id', 'INNER')
          ->where('solicitud_item_examen.id', $value['id'])
          ->get('solicitud_item_examen', null, 'catalogo_campos.rango_valor, catalogo_campos.unidades, catalogo_campos.nombre_campo,solicitud_respuesta.resultado, solicitud_respuesta.seleccion_valor, solicitud_respuesta.criterio_rango');

        array_push($arrayResponse['results'], array(
          "data" => $value,
          "results" => $results
        ));
      }  
      $arrayResponse['CURRENT_DATE'] = $this->db->rawQuery('SELECT CURRENT_DATE() AS fecha');
    }else{
      $arrayResponse['view'] = false;
    }

    $nameFile = "solicitud-".date('Ymdhis')."-".$QueryParams['solicitud'];
    $isResponse = $this->renderer->render($response, '/reportes/examenes/examenes_generales.php', [
      "isSave"          => true,
      "fileName"        => $nameFile,
      "dir"             => $this->dir,
      "arrayOfResponse" => $arrayResponse
    ]);
    send_email_solicitudType($isResponse, $this->dir, $nameFile, $this->baseUrl);
  });

