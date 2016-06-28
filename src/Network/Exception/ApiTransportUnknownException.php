<?php

namespace Gourmet\Email\Network\Exception;

class ApiTransportUnknownException extends ApiTransportException
{
    protected $_messageTemplate = 'Unknown %s API transport exception.';
}
