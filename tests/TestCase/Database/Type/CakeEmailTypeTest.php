<?php

namespace Gourmet\Email\Test\TestCase\Database\Type;

use Cake\Network\Email\Email;
use Cake\TestSuite\TestCase;
use Gourmet\Email\Database\Type\CakeEmailType;

class CakeEmailTypeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->CakeEmailType = new CakeEmailType('cake_email');
        $this->Driver = $this->getMock('Cake\Database\Driver');
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->CakeEmailType, $this->Driver);
    }

    protected function _getEmail($date)
    {
        $email = new Email();
        $email->from('noreply@cakephp.org', 'CakePHP Test');
        $email->returnPath('pleasereply@cakephp.org', 'CakePHP Return');
        $email->to('cake@cakephp.org', 'CakePHP');
        $email->cc(['mark@cakephp.org' => 'Mark Story, Jr.', 'juan@cakephp.org' => 'Juan Basso']);
        $email->bcc('phpnut@cakephp.org');
        $email->emailFormat('html');
        $email->messageID('<4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost>');
        $email->subject('Testing Queue');
        $email->setHeaders([
            'X-Mailer' => 'CakePHP Email',
            'Date' => $date,
            'X-Tag' => 'sometag',
        ]);
        $email->domain('test.email');
        $email->template('default', 'default1');
        return $email;
    }

    protected function _getEmailConfig($date)
    {
        return [
            'to' => ['cake@cakephp.org' => 'CakePHP'],
            'from' => ['noreply@cakephp.org' => 'CakePHP Test'],
            'cc' => [
                'mark@cakephp.org' => 'Mark Story, Jr.',
                'juan@cakephp.org' => 'Juan Basso',
            ],
            'bcc' => ['phpnut@cakephp.org' => 'phpnut@cakephp.org'],
            'subject' => 'Testing Queue',
            'returnPath' => ['pleasereply@cakephp.org' => 'CakePHP Return'],
            'template' => ['template' => 'default', 'layout' => 'default1'],
            'viewRender' => 'Cake\\View\\View',
            'helpers' => ['Html'],
            'emailFormat' => 'html',
            'domain' => 'test.email',
            'messageId' => '<4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost>',
            'getHeaders' => [
                'X-Mailer' => 'CakePHP Email',
                'Date' => $date,
                'X-Tag' => 'sometag',
                'Message-ID' => '<4d9946cf-0a44-4907-88fe-1d0ccbdd56cb@localhost>',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Transfer-Encoding' => '8bit',
            ],
            'charset' => 'UTF-8',
            'headerCharset' => 'UTF-8'
        ];
    }

    public function testToPHP()
    {
        $date = gmdate('Y-m-d H:i:s');
        $value = json_encode($this->_getEmailConfig($date));
        $result = $this->CakeEmailType->toPHP($value, $this->Driver);
        $expected = $this->_getEmail($date);
        $this->assertEquals($expected, $result);
    }

    public function testToDatabase()
    {
        $date = gmdate('Y-m-d H:i:s');
        $value = $this->_getEmail($date);
        $result = $this->CakeEmailType->toDatabase($value, $this->Driver);
        $expected = json_encode($this->_getEmailConfig($date));
        $this->assertEquals($expected, $result);
    }
}
