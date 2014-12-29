<?php

namespace Gourmet\Email\Test\Sample;

use Cake\Network\Email\Email;
use Cake\TestSuite\TestCase;

class EmailTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        Email::configTransport('test', ['className' => 'Debug']);
        $this->Email = new Email([
            'transport' => 'test',
            'to' => 'jane@doe.com',
            'from' => 'john@doe.com',
            'helpers' => ['Html', 'Gourmet/Email.Email'],
            'layout' => 'Gourmet/Email.default',
            'emailFormat' => 'both'
        ]);
    }

    public function tearDown()
    {
        parent::tearDown();
        Email::dropTransport('test');
        unset($this->Email);
    }

    public function testSend()
    {
        $this->Email->viewVars(['user' => ['username' => 'janedoe']]);
        $this->Email->template('welcome');

        $result = $this->Email->send();
        $expected = ['headers', 'message'];

        $this->assertEquals($expected, array_keys($result));
    }
}
