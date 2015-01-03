<?php

namespace Gourmet\Email\Network\Email;

use Cake\ORM\TableRegistry;
use Gourmet\Email\Network\Exception\QueueTransportException;
use Gourmet\Email\Network\Exception\QueueTransportUnknownException;
use Gourmet\Email\Model\Table\EmailQueuesTable;

class DatabaseQueueTransport extends AbstractQueueTransport
{
    protected $_defaultConfig = [
        'model' => 'Gourmet/Email.EmailQueues',
        'profile' => null,
    ];

    /**
     * Push email to queue.
     *
     * @return array Queued message in text, html or both.
     */
    protected function _push()
    {
        $table = TableRegistry::get($this->config('model'));

        if (!($table instanceof EmailQueuesTable)) {
            throw new QueueTransportException();
        }

        $data = [
            'profile' => $this->_getProfile(),
            'send_at' => $this->_headers['Date'],
            '_email' => $this->_cakeEmail
        ];

        $email = $table->newEntity($data);
        if (!$table->save($email)) {
            throw new QueueTransportUnknownException($email);
        }

        $format = $this->_cakeEmail->emailFormat();
        if (in_array($format, ['html', 'both'])) {
            $message['html'] = $this->_messageToString('html');
        }
        if (in_array($format, ['text', 'both'])) {
            $message['text'] = $this->_messageToString('text');
        }

        return $message;
    }
}
