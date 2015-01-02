<?php

namespace Gourmet\Email\Network\Email;

use Cake\Network\Email\AbstractTransport;
use Cake\Network\Email\Email;
use Cake\Network\Http\Client;

abstract class AbstractApiTransport extends AbstractTransport
{
    /**
     * API's expected request method.
     *
     * @var string
     */
    protected $_apiRequestMethod = 'POST';

    /**
     * API's endpoint.
     *
     * @var string
     */
    protected $_apiEndpoint;

    /**
     * Email instance.
     *
     * @var \Cake\Network\Email\Email
     */
    protected $_cakeEmail;

    /**
     * The response of the last API request.
     *
     * @var array
     */
    protected $_lastResponse = [];

    /**
     * The last message sent through the API.
     *
     * @var array
     */
    protected $_message;

    /**
     * Sends email.
     *
     * @param \Cake\Network\Email $email Cake email instance.
     * @return array Formatted response.
     */
    public function send(Email $email)
    {
        $this->_cakeEmail = $email;
        $this->_headers = $email->getHeaders(['from', 'sender', 'replyTo', 'readReceipt', 'to', 'cc', 'subject']);

        $client = $this->_prepareHttpClient();
        $method = strtolower($this->_apiRequestMethod);

        $endpoint = $this->_apiEndpoint;
        $payload = $this->_preparePayload();
        $options = $this->_prepareRequestOptions();

        $this->_lastResponse = $this->_handleResponse($client->{$method}($endpoint, $payload, $options));

        return [
            'headers' => $this->_headersToString($this->_headers),
            'message' => $this->_message
        ];
    }

    /**
     * Gets HTTP client used to process requests.
     *
     * @return \Cake\Network\Http\Client Client.
     */
    protected function _prepareHttpClient()
    {
        $client = $this->config('client');

        if (empty($client)) {
            $client = [
                'host' => $this->config('host'),
                'scheme' => $this->config('ssl') ? 'https' : 'http',
            ];
        }

        if (is_array($client)) {
            $client = new Client($client);
        }

        if (!($client instanceof Client)) {
            throw new \Exception();
        }

        return $client;
    }

    /**
     * Cleans up the message ID.
     *
     * @return string.
     */
    protected function _prepareMessageId()
    {
        return substr($this->_headers['Message-ID'], 1, -1);
    }

    /**
     * Returns extra options to use by the client when executing the request.
     *
     * @return array Request options.
     */
    protected function _prepareRequestOptions()
    {
        return [];
    }

    /**
     * Checks headers to see if it's a batch request.
     *
     * @return bool True if it is.
     */
    protected function _isBatchRequest()
    {
        return false;
    }

    /**
     * Checks headers to see if it's a tagged request.
     *
     * @return mixed False if not, tag otherwise.
     */
    protected function _isTaggedRequest()
    {
        $tag = !empty($this->_headers['X-Tag']);

        if ($tag && $tag = $this->_headers['X-Tag']) {
            unset($this->_headers['X-Tag']);
        }

        return $tag;
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

    /**
     * Prepares API payload in a supported format.
     *
     * @return mixed Payload.
     */
    abstract protected function _preparePayload();

    /**
     * Handles the API response.
     *
     * @param string $response Raw API response.
     * @return array Decoded response.
     */
    abstract protected function _handleResponse($response);
}
