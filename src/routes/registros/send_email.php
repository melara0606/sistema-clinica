<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  $app->get('/send_email/{ id_compra }', function(Request $request, Response $response, $arguments) {
      $array = array(
        "compra" => array(),
        "items"  => array()
      );

      bitacora($this, 'ENVIAR UN CORREO', 'SE ENVIO UN CORREO ELECTRONICO');
      $array["compra"] = $this->db
        ->join("proveedores as prv", "prv.id = cpm.proveedor_id ", "INNER")
        ->join("sucursales as suc", "suc.id = cpm.sucursal_id", "INNER")
        ->where("cpm.id", $arguments['id_compra'])
        ->getOne("compras as cpm", "suc.nombre_sucursal, prv.id as prvId, prv.nombre_proveedor, prv.email_representante,cpm.*");

      $array["items"]["isEntregado"] = $this->db
        ->join("materiales as mat", "mat.id = cpm.material_id", "INNER")
        ->where("cpm.compra_id", $arguments['id_compra'])
        ->where("cpm.estado", 0)
        ->get("compras_items as cpm", null, "mat.nombre_material, cpm.* ");

      $array["items"]["noIsEntregado"] = $this->db
        ->join("materiales as mat", "mat.id = cpm.material_id", "INNER")
        ->where("cpm.compra_id", $arguments['id_compra'])
        ->where("cpm.estado", 1)
        ->get("compras_items as cpm", null, "mat.nombre_material, cpm.* ");

      $nameFile = "OrdenCompra-".date('Ymdhis');
      $isResponse = $this->renderer->render($response, 'reportes/pdf/compras.php', [
        "data" => $array,
        "isSave" => true,
        "fileName"  => $nameFile,
        "dir" => $this->dir
      ]);

      if($isResponse->getStatusCode() === 200){
        $routeDir = "{$this->dir}/send/{$nameFile}.pdf";
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
            $mail->addAddress( $array["compra"]['email_representante'] , $array["compra"]['nombre_proveedor']);

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

      echo "<script type='text/javascript'>window.location.href='{$this->baseUrl}/send_email/accepts/response'</script>";
  });

  $app->get('/send_email/accepts/response', function(Request $request, Response $response, $arguments) {
    return $this->view->render($response, 'send_email_accepts.phtml');
  });
?>
