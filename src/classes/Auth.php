<?php 
  namespace Auths;

  use \Firebase\JWT\JWT;
  use \Firebase\JWT\SignatureInvalidException;

  class Auth {
    private $secret_key = "BCryptRequires22Chrcte/VlQH0piJtjXl.0t1XkA8pw9dMXTpOq";
    private $encrypt = 'HS512';

    public function sign_in( $request = array() ) {
      $tokenId    = base64_encode(mcrypt_create_iv(32));
      $issuedAt   = time();
      $notBefore  = $issuedAt + 10;
      $expire     = $notBefore + 7200;
      $serverName = 'http://localhost/app';

      $data = [
          'iat'  => $issuedAt,
          'jti'  => $tokenId,
          'iss'  => $serverName,
          'nbf'  => $notBefore,
          'exp'  => $expire,
          'data' => $request
      ];

      $jwt = JWT::encode( $data, base64_encode($this->secret_key), $this->encrypt);
      return array( "expires" => $expire, "token" => $jwt );
    }

    public function get_token( $request ) {
      $token = $request->getHeaderLine('Authorization');
      if(empty($token))
        throw new ForbiddenException("No tenemos un token para la peticion");

      $tokenRequest = trim(preg_split("/Bearer+/", $token)[1]);

      JWT::$leeway = 60;
      try{
        $tokenData = JWT::decode( $tokenRequest, base64_encode($this->secret_key), [$this->encrypt] );
      }catch(SignatureInvalidException $e){
        throw new ForbiddenException("Invalido token suplicamos uno real.");
      }
      return (array) $tokenData;
    }

    public function has_verify($request, $scope)
    {
      try{
        $TokenData = $this->get_token($request);
        return in_array($scope, $TokenData['data']->scopes) ? [ 'ok' => true] : [ 'ok' => false, 'message' => 'No tiene accesos a este modulo' ];
      }catch(ForbiddenException $e) {
        return $e->error();
      }
    }

    public function json_response ($response, $data = array(), $status = 201) {
      return $response->withStatus($status)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
  }
?>
