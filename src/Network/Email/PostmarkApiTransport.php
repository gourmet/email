<?php

namespace Gourmet\Email\Network\Email;

use Cake\Network\Email\Email;
use Gourmet\Email\Network\Exception\ApiTransportException;
use Gourmet\Email\Network\Exception\ApiTransportLimitException;
use Gourmet\Email\Network\Exception\ApiTransportUnknownException;

/**
 * Postmark API Transport.
 *
 * Transparently send emails using the Postmark API. While the implementation is
 * complete, there are a few things one needs to be aware of:
 *
 *    - You MUST have a registered and confirmed sender signature with the sender email.
 *    - A single email MUST not have more than 20 recipients (to + cc + bcc)
 *    - ONLY the `From` and `To` are able to accept recipients' names.
 *    - `TextBody` and `HtmlBody` are limited to 5MB each.
 *    - `Attachements` can be up to 10MB (one or all together).
 *
 * @link http://developer.postmarkapp.com/developer-send-api.html
 */
class PostmarkApiTransport extends AbstractApiTransport
{
    /**
     * Maximum allowed number of recipients.
     *
     * @var int
     */
    const MAX_ALLOWED_RECIPIENTS = 20;

    /**
     * API's endpoint.
     *
     * @var string
     */
    protected $_apiEndpoint = 'email';

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'host' => 'api.postmarkapp.com',
        'ssl' => true,
        'token' => null,
        'trackOpens' => true,
        'debug' => false,
    ];

    /**
     * Returns extra options to use by the client when executing the request.
     *
     * @return array
     */
    protected function _prepareRequestOptions()
    {
        $token = $this->config('token');

        if ($this->config('test')) {
            $token = 'POSTMARK_API_TEST';
        }

        return [
            'header' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Postmark-Server-Token' => $token,
            ]
        ];
    }

    /**
     * Format CC and BCC according to API spec.
     *
     * @param string $recipientType One of 'cc' or 'bcc'.
     * @return string Comma-separated list of emails (no names).
     */
    protected function _prepareRecipientAddress($recipientType)
    {
        $method = strtolower($recipientType);
        return implode(',', array_keys($this->_cakeEmail->{$method}()));
    }

    /**
     * Prepares API payload in a supported format.
     *
     * @return mixed Payload.
     * @link http://developer.postmarkapp.com/developer-api-email.html
     * @todo batch
     */
    protected function _preparePayload()
    {
        $this->_validate();

        // Common
        $message = [
            'From' => $this->_headers['From'],
            'To' => $this->_headers['To'],
            'Cc' => $this->_prepareRecipientAddress('cc'),
            'Bcc' => $this->_prepareRecipientAddress('bcc'),
            'Subject' => mb_decode_mimeheader($this->_headers['Subject']),
        ];

        // Tag
        if ($tag = $this->_isTaggedRequest()) {
            $message['Tag'] = $tag;
        }

        // Body
        $format = $this->_cakeEmail->emailFormat();
        if (in_array($format, ['html', 'both'])) {
            $message['HtmlBody'] = $this->_messageToString('html');
        }
        if (in_array($format, ['text', 'both'])) {
            $message['TextBody'] = $this->_messageToString('text');
        }

        // ReplyTo
        if ($replyTo = $this->_headers['Reply-To']) {
            $message['ReplyTo'] = $replyTo;
        }

        // Headers
        $message['Headers'] = ['MessageID' => $this->_prepareMessageId()];
        foreach ($this->_headers as $k => $v) {
            if (strpos($k, 'X-') === 0) {
                $message['Headers'][$k] = $v;
            }
        }

        // Tracking
        if ($trackOpens = $this->config('trackOpens')) {
            $message['TrackOpens'] = $trackOpens;
        }

        // Attachments
        $message['Attachments'] = $this->_buildAttachments();

        return json_encode($message);
    }

    /**
     * Builds attachments' part of the payload.
     *
     * @return array Attachments' payload.
     */
    protected function _buildAttachments()
    {
        $attachments = [];
        $i = 0;

        foreach ($this->_cakeEmail->attachments() as $file => $info) {
            $attachments[$i] = [
                'Name' => $file,
                'ContentType' => $info['mimetype'],
            ];

            if (isset($info['file'])) {
                $fh = fopen($info['file'], 'rb');
                $data = chunk_split(base64_encode(fread($fh, filesize($info['file']))));
                fclose($fh);
                $attachments[$i]['Content'] = $data;
            } else if (isset($info['data'])) {
                $attachments[$i]['Content'] = $info['data'];
            }

            if (isset($info['contentId'])) {
                $attachments[$i]['ContentId'] = $info['contentId'];
            }

            $i++;
        }

        return $attachments;
    }

    /**
     * Handles the API response.
     *
     * @return array Decoded response.
     * @link http://developer.postmarkapp.com/developer-api-overview.html#error-codes
     */
    protected function _handleResponse($response)
    {
        if (empty($response) || !$decoded = (array) @json_decode($response)) {
            // something went wrong
            throw new ApiTransportUnknownException('Postmark');
        }

        // Is 422?
        if ($decoded['ErrorCode']) {
            throw new ApiTransportException(
                $decoded['Message'],
                $decoded['ErrorCode']
            );
        }

        return $decoded;
    }

    /**
     * Validates a send request not to exceed the maximum allowed number of
     * recipients.
     *
     * @return void
     */
    protected function _validate()
    {
        $count = count($this->_cakeEmail->to())
            + count($this->_cakeEmail->cc())
            + count($this->_cakeEmail->bcc());

        if ($count > self::MAX_ALLOWED_RECIPIENTS) {
            throw new ApiTransportLimitException(self::MAX_ALLOWED_RECIPIENTS);
        }
    }
}
