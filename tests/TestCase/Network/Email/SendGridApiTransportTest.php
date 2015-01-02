<?php

namespace Gourmet\Email\Test\TestCase\Network\Email;

use Cake\Network\Email\Email;
use Cake\TestSuite\TestCase;
use Gourmet\Email\Network\Email\SendGridApiTransport;

class SendGridApiTransportTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->Client = $this->getMock('Cake\Network\Http\Client', ['post']);
        $this->SendGrid = new SendGridApiTransport([
            'client' => $this->Client,
            'token' => 'foo',
            'apiUser' => 'testuser',
            'apiKey' => 'testkey',
        ]);
    }

    protected function _getEmailMock($methods = ['message'])
    {
        $email = $this->getMock('Cake\Network\Email\Email', $methods);
        $email->from('noreply@cakephp.org', 'CakePHP Test');
        $email->returnPath('pleasereply@cakephp.org', 'CakePHP Return');
        $email->to('cake@cakephp.org', 'CakePHP');
        $email->cc(['mark@cakephp.org' => 'Mark Story', 'juan@cakephp.org' => 'Juan Basso']);
        $email->bcc('phpnut@cakephp.org');
        $email->emailFormat('html');
        $email->messageID('<4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost>');
        $email->subject('Testing API');
        $email->setHeaders([
            'X-Mailer' => Email::EMAIL_CLIENT,
            'X-Tag' => 'sometag',
        ]);
        $email->expects($this->at(0))
            ->method('message')
            ->with('html')
            ->will($this->returnValue(['First Line', 'Second Line', '.Third Line', '']));

        return $email;
    }

    public function testSend()
    {
        $email = $this->_getEmailMock();

        $data = [
            'api_user' => 'testuser',
            'api_key' => 'testkey',
            'to' => 'cake@cakephp.org',
            'toname' => 'CakePHP',
            'subject' => 'Testing API',
            'from' => 'noreply@cakephp.org',
            'fromname' => 'CakePHP Test',
            'html' => 'First LineSecond Line.Third Line',
            'cc' => [
                'mark@cakephp.org',
                'juan@cakephp.org',
            ],
            'ccname' => [
                'Mark Story',
                'Juan Basso',
            ],
            'bcc' => 'phpnut@cakephp.org',
            'headers' => json_encode([
                'MessageID' => '4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost',
                'X-Mailer' => 'CakePHP Email',
            ]),
            'x-smtpapi' => json_encode(['category' => 'sometag']),
        ];

        $response = json_encode([
            'message' => 'success',
        ]);


        $this->Client->expects($this->once())
            ->method('post')
            ->with('mail.send.json', $data, [])
            ->will($this->returnValue($response));

        $result = $this->SendGrid->send($email);
        $this->assertTrue(is_array($result));
    }

}
