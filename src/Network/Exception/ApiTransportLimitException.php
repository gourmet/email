<?php

namespace Gourmet\Email\Network\Exception;

class ApiTransportLimitException extends ApiTransportException
{
    protected $_messageTemplate = 'Exceeded maximum allowed number of recipients (%s).';
}
