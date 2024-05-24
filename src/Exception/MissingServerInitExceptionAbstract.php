<?php

namespace Nivseb\PhpMockServerConnector\Exception;

class MissingServerInitExceptionAbstract extends AbstractMockServerException
{
    public function __construct()
    {
        parent::__construct('Mock server URL is not set. First call the init method!');
    }
}
