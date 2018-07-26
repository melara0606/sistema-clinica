<?php 
 function onLoadGetData($db, $object = array(), $response, $isError = false, $msgError = null){
  if(!$isError){
   $data = $db->where($object['key'], $object['value'])->getOne($object['table']);
   $data["message"] = "Hemos realizado con exito el proceso";
   $data['responseStatus']  = 'ok';
    return $response->withJson($data, 201);
  }else{
   return $response->withJson(array(
    'message' => 'Tenemos un problema con el servidor, prueba mas tarde',
    'status'  => 'problems',
    'data'    => $msgError
   ), 404);
  }
}

  function onLoad( $oConfiguration = array(), $message = null){
    if($oConfiguration['condition']){
      return onLoadGetData($oConfiguration['db'], array( 
        "key" 	=> $oConfiguration['key'],
        "value"	=> $oConfiguration['value'],
        "table"		=> $oConfiguration['table']
      ), $oConfiguration['response']);			
    }else{
      return onLoadGetData(null, null, $oConfiguration, true, $message);
    }
  }

  function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
      return $_SERVER['HTTP_CLIENT_IP'];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
      return $_SERVER['HTTP_X_FORWARDED_FOR'];

    return $_SERVER['REMOTE_ADDR'];
  }

  function bitacora($reference = null, $recurso = '', $operacion = ''){
    $ip = getIP();
    $session = $reference->session->get('userObject');
    $user = empty($session['id_user']) ? $session['codigo_paciente'] : $session['id_user'];
    $reference->db->insert('bitacora', array(
      "recurso"   => $recurso,
      "operacion" => $operacion,
      "fecha"     => $reference->db->now(),
      "host"      => strcmp($ip, '::1') == 0 ? 'localhost' : $ip,
      "user_id"   => $user,
    ));
  }
?>