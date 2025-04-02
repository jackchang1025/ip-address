<?php

namespace Weijiajia\IpAddress\Contracts;

use Saloon\Http\Response;
use Weijiajia\IpAddress\IpResponse;

interface Request
{
    public function createResponse(Response $response): IpResponse;
}