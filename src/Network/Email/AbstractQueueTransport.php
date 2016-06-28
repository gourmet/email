<?php

namespace Gourmet\Email\Network\Email;

use Cake\Network\Email\AbstractTransport;
use Cake\Network\Email\Email;

abstract class AbstractQueueTransport extends AbstractTransport
{
    protected $_headers;
    protected $_message;

    public function send(Email $email)
    {
        $this->_cakeEmail = $email;
        $this->_headers = $email->getHeaders(['from', 'sender', 'replyTo', 'readReceipt', 'to', 'cc', 'subject']);

        $message = $this->_push();

        return [
            'headers' => $this->_headersToString($this->_headers),
            'message' => $message,
        ];
    }

    /**
     * Push email to queue.
     *
     * @return array Queued message in text, html or both.
     */
    abstract protected function _push();

    protected function _getProfile()
    {
        $profile = $this->config('profile');

        if (empty($profile)) {
            throw new QueueTransportException();
        }

        if ($profile == $this->_cakeEmail->profile()) {
            throw new QueueTransportException();
        }

        return $profile;
    }

    /**
    * Concatenate's message's lines into string.
    *
    * @param string $format Either text or html.
    * @return string Message body.
    */
    protected function _messageToString($format)
    {
        return $this->_message[$format] = implode("\r\n", $this->_cakeEmail->message($format));
    }

}
