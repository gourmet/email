<?php

namespace Gourmet\Email\Network\Email;

use Cake\Network\Email\Email;
use Gourmet\Email\Network\Exception\ApiTransportException;
use Gourmet\Email\Network\Exception\ApiTransportUnknownException;

/**
 * SendGrid API Transport.
 *
 * Transparently send emails using the SendGrid API.
 *
 * @link https://sendgrid.com/docs/API_Reference/Web_API/mail.html
 * @todo debug mode
 */
class SendGridApiTransport extends AbstractApiTransport
{
    /**
     * API's endpoint.
     *
     * @var string
     */
    protected $_apiEndpoint = 'mail.send.json';

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'host' => 'api.sendgrid.com/api',
        'ssl' => true,
        'apiUser' => null,
        'apiKey' => null,
        'debug' => false,
    ];

    /**
     * Prepares API payload in a supported format.
     *
     * @return array Payload.
     * @todo inline media content, batch
     */
    protected function _preparePayload()
    {
        // Auth
        $payload = [
            'api_user' => $this->config('apiUser'),
            'api_key' => $this->config('apiKey'),
        ];

        // Main recipient(s)
        $payload += $this->_prepareRecipientAddress('to');

        // Subject
        $payload['subject'] = mb_decode_mimeheader($this->_headers['Subject']);

        // From
        $payload += $this->_prepareRecipientAddress('from');

        // Body
        $format = $this->_cakeEmail->emailFormat();
        if (in_array($format, ['html', 'both'])) {
            $payload['html'] = implode('', $this->_cakeEmail->message('html'));
        }
        if (in_array($format, ['text', 'both'])) {
            $payload['text'] = $this->_messageToString('text');
        }

        // Extra recipients
        $payload += $this->_prepareRecipientAddress('cc');
        $payload += $this->_prepareRecipientAddress('bcc');

        // ReplyTo
        if ($replyTo = $this->_cakeEmail->replyTo()) {
            $payload['replyto'] = current(array_keys($replyTo));
        }

        // Headers
        $payload['headers'] = ['MessageID' => $this->_prepareMessageId()];
        foreach ($this->_headers as $k => $v) {
            if ('X-Tag' == $k) {
                $payload['x-smtpapi']['category'] = $v;
                continue;
            }
            if (strpos($k, 'x-smtpapi') === 0) {
                $payload['x-smtpapi'][$k] = $v;
                continue;
            }
            if (strpos($k, 'X-') === 0) {
                $payload['headers'][$k] = $v;
            }
        }

        // Attachments
        foreach ((array) $this->_cakeEmail->attachments() as $file => $info) {
            $payload['files[' . $file . ']'] = '@' . $info['file'];
        }

        foreach (['x-smtpapi', 'headers'] as $k) {
            if (!empty($payload[$k])) {
                $payload[$k] = json_encode($payload[$k]);
            }
        }

        return array_filter($payload);
    }

    /**
     * Prepare payload for recipients of all types according to API specs.
     *
     * @param string $recipientType One of `to`, `from`, `cc`, `bcc`.
     * @return array Payload.
     */
    protected function _prepareRecipientAddress($recipientType)
    {
        $method = strtolower($recipientType);
        $recipients = $this->_cakeEmail->{$method}();

        $nonames = $emails = $names = [];
        foreach ($recipients as $email => $name) {
            if ($email == $name) {
                $nonames[] = $email;
                continue;
            }
            $emails[] = $email;
            $names[] = $name;
        }

        $emails = array_merge($emails, $nonames);

        $payload = [
            $recipientType => $emails,
            $recipientType . 'name' => $names
        ];

        if (count($emails) === 1) {
            $payload = [
                $recipientType => current($emails),
                $recipientType . 'name' => current($names),
            ];
        }

        return array_filter($payload);
    }

    /**
     * Builds attachments' part of the payload.
     *
     * @return array
     */
    protected function _buildAttachments()
    {
        $attachments = [];
        $i = 0;

        foreach ($this->_cakeEmail->attachments() as $file => $info) {
            // TODO complete
        }

        return $attachments;
    }

    /**
     * Handles API response.
     *
     * @return bool True if the request was successful.
     */
    protected function _handleResponse($response)
    {
        if (empty($response) || !$decoded = (array) @json_decode($response)) {
            // something went wrong
            throw new ApiTransportUnknownException('SendGrid');
        }

        // has errors?
        if ('success' != $decoded['message']) {
            $error = array_shift($decoded['error']);
            throw new ApiTransportException(
                $error,
                400 // FIXME retrieve response code from client
            );
        }

        return $decoded;
    }
}
