<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;

  use \PayPal\Api\Item;
  use \PayPal\Api\Payer;
  use \PayPal\Api\Payment;
  use \PayPal\Api\Amount;
  use \PayPal\Api\Details;
  use \PayPal\Api\ItemList;
  use \PayPal\Rest\ApiContext;
  use \PayPal\Api\Transaction;
  use \PayPal\Api\RedirectUrls;
  use \PayPal\Auth\OAuthTokenCredential;

  $app->post('/paypal', function (Request $request, Response $response, $arguments) {
    $ApiContext = credetential();
    $object = $request->getParsedBody();
    $user = $this->session->get('userObject');

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');

    $articulo = new Item();
    $articulo->setName($object['codigo'])
             ->setCurrency('USD')
             ->setQuantity(1)
             ->setPrice($object['pagoTotal']);

    $listaArticulos = new ItemList();
    $listaArticulos->setItems(array($articulo));

    $cantidad = new Amount();
    $cantidad->setCurrency('USD')
              ->setTotal($object['pagoTotal']);

    $transacion = new Transaction();
    $transacion->setAmount($cantidad)
               ->setItemList($listaArticulos)
               ->setInvoiceNumber($user['id'])
               ->setDescription('Pago ');

    $redireccion = new RedirectUrls();
    $redireccion->setReturnUrl($this->baseUrl."pago_finaliza/true/{$object['codigo']}")
                ->setCancelUrl($this->baseUrl."pago_finaliza/false/{$object['codigo']}");

    $pago = new Payment();
    $pago->setIntent('sale')
        ->setPayer($payer)
        ->setRedirectUrls($redireccion)
        ->setTransactions(array($transacion));

    try {
      $pago->create($ApiContext);
    } catch (PayPal\Exception\PayPalConnectionException $pce) {
      echo "<pre>";
      print_r($pce->getData());
      echo "</pre>";
      exit;
    }
    return $response->withStatus(302)->withHeader('Location', $pago->getApprovalLink());
  });

  $app->get('/pago_finaliza/{isValid}/{codigo_solicitud}', function (Request $request, Response $response, $arguments){
    $parameters = $request->getQueryParams();

    $paypal = $this->db->insert('solicitud_online', array(
      'solicitud_id'  => $arguments["codigo_solicitud"],
      "pay_id"        => $parameters['paymentId'],
      "pay_token"     => $parameters['token'],
      "payer_id"      => $parameters['PayerID']
    ));

    $this->db->where('id', $arguments["codigo_solicitud"])->update('solicitud', array(
      'estado'  => 4
    ));

    return $this->view->render($response, 'pago_online.phtml', [
      'isResponse'  => $arguments["isValid"]
    ]);
  });

  function credetential() {
    return new ApiContext(
      new OAuthTokenCredential(
          'AcYQdRPgvM1IV7aVn78r9QnJlF6C5pLGy4bQNEzzdgs3A8FvbbetTRcM8bOp29GhT2LDlHXdhDclHwrM',
          'EFR81eqpTV7on4WH4uvdnYrSiOnmPWMU2jqVxRnOMn0ycmFgz1qCZsomJKl-39zgSf0gs0rqUjeKO_30'
      )
    );
  }
?>
