<?php

namespace Gourmet\Email\Test\TestCase\Network\Email;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Gourmet\Email\Network\Email\DatabaseQueueTransport;

class DatabaseQueueTransportTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->EmailQueues = $this->getMock('Gourmet\Email\Model\Table\EmailQueuesTable', ['newEntity', 'save']);
        TableRegistry::set('Gourmet/Email.EmailQueues', $this->EmailQueues);

        $this->Queue = new DatabaseQueueTransport([
            'profile' => 'foo',
        ]);
    }

    protected function _getEmailMock($date)
    {
        $email = $this->getMock('Cake\Network\Email\Email', ['message']);
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
        $email->expects($this->at(0))
            ->method('message')
            ->with('html')
            ->will($this->returnValue(['First Line', 'Second Line', '.Third Line', '']));

        return $email;
    }

    public function testSend()
    {
        $date = gmdate('Y-m-d H:i:s');
        $cakeEmail = $this->_getEmailMock($date);
        $emailEntity = $this->getMock('Cake\ORM\Entity');

        $data = [
            'profile' => 'foo',
            'send_at' => $date,
            '_email' => $cakeEmail
        ];

        $this->EmailQueues->expects($this->once())
            ->method('newEntity')
            ->with($data)
            ->will($this->returnValue($emailEntity));

        $this->EmailQueues->expects($this->once())
            ->method('save')
            ->with($emailEntity)
            ->will($this->returnValue(true));

        $result = $this->Queue->send($cakeEmail);
        $this->assertTrue(is_array($result));
    }

}
