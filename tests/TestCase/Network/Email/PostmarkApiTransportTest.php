<?php

namespace Gourmet\Email\Test\TestCase\Network\Email;

use Cake\Network\Email\Email;
use Cake\TestSuite\TestCase;
use Gourmet\Email\Network\Email\PostmarkApiTransport;

class PostmarkApiTransportTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->date = date(DATE_RFC2822);
        $this->Client = $this->getMock('Cake\Network\Http\Client', ['post']);
        $this->Postmark = new PostmarkApiTransport([
            'client' => $this->Client,
            'token' => 'foo',
        ]);
    }

    protected function _getEmailMock($methods = ['message'])
    {
        $email = $this->getMock('Cake\Network\Email\Email', $methods);
        $email->from('noreply@cakephp.org', 'CakePHP Test');
        $email->returnPath('pleasereply@cakephp.org', 'CakePHP Return');
        $email->to('cake@cakephp.org', 'CakePHP');
        $email->cc(['mark@cakephp.org' => 'Mark Story, Jr.', 'juan@cakephp.org' => 'Juan Basso']);
        $email->bcc('phpnut@cakephp.org');
        $email->emailFormat('html');
        $email->messageID('<4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost>');
        $email->subject('Testing SMTP');
        $email->setHeaders([
            'X-Mailer' => Email::EMAIL_CLIENT,
            'Date' => $this->date,
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

        $options = ['header' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Postmark-Server-Token' => 'foo',
        ]];

        $data = json_encode([
            'From' => 'CakePHP Test <noreply@cakephp.org>',
            'To' => 'CakePHP <cake@cakephp.org>',
            'Cc' => 'mark@cakephp.org,juan@cakephp.org',
            'Bcc' => 'phpnut@cakephp.org',
            'Subject' => 'Testing SMTP',
            'Tag' => 'sometag',
            'HtmlBody' => implode("\r\n", ['First Line', 'Second Line', '.Third Line', '']),
            'Headers' => [
                'MessageID' => '4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost',
                'X-Mailer' => 'CakePHP Email',
            ],
            'TrackOpens' => true,
            'Attachments' => [],
        ]);

        $response = json_encode([
            'ErrorCode' => 0,
            'Message' => 'OK',
            'MessageID' => '4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost',
            'SubmittedAt' => '',
            'To' => 'cake@cakephp.org',
        ]);


        $this->Client->expects($this->once())
            ->method('post')
            ->with('email', $data, $options)
            ->will($this->returnValue($response));

        $result = $this->Postmark->send($email);
        $this->assertTrue(is_array($result));
    }

}
