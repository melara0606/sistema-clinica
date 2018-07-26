<?php

namespace Auths;

class ForbiddenException extends \Exception
{
  public function error() {
    return [
      'ok' => false,
      'message' => $this->getMessage()
    ];
  }
}
