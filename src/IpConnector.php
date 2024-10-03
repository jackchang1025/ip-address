<?php

namespace Weijiajia;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class IpConnector extends Connector
{
    use HasLogger;
    use AcceptsJson;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return '';
    }
}
