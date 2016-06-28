<?php

namespace Gourmet\Email\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use Cake\Network\Email\Email;
use PDO;

class CakeEmailType extends Type
{
    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }

        $email = new Email();
        $config = json_decode($value, true);

        foreach ($config as $k => $v) {
            switch ($k) {
                case 'template':
                    $email->template($v['template'], $v['layout']);
                    continue;

                case 'getHeaders':
                    $k = 'setHeaders';
                    $v = array_filter($v, function ($value) use ($v) {
                        $array = array_flip($v);
                        return !in_array($array[$value], [
                            'Message-ID',
                            'MIME-Version',
                            'Content-Type',
                            'Content-Transfer-Encoding'
                        ]);
                    });

                default:
                    $email->{$k}($v);
            }
        }
        return $email;
    }

    public function toDatabase($value, Driver $driver)
    {
        if (!($value instanceof Email)) {
            throw new \Exception();
        }

        $methods = [
            'to', 'from', 'replyTo', 'cc', 'bcc', 'subject', 'returnPath', 'readReceipt',
            'template', 'viewRender', 'viewVars', 'theme', 'helpers', 'emailFormat', 'transport',
            'attachments', 'domain', 'messageId', 'getHeaders', 'charset', 'headerCharset',
        ];

        foreach ($methods as $k) {
            $email[$k] = $value->{$k}();
        }

        return json_encode(array_filter($email));
    }

    public function toStatement($value, Driver $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }
}
