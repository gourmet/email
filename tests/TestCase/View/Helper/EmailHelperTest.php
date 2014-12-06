<?php

namespace Gourmet\Email\Test\TestCase\View\Helper;

use Cake\Core\Configure;
use Cake\Network\Email\Email;
use Cake\Network\Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\HtmlHelper;
use Gourmet\Email\View\Helper\EmailHelper;

class TestEmailHelper extends EmailHelper {
    public function setType($type) {
        $this->_emailType = $type;
    }
}

class EmailHelperTest extends TestCase {

    public function setUp() {
        parent::setUp();
        $controller = $this->getMock('Cake\Controller\Controller', ['redirect']);
        $this->View = $this->getMock('Cake\View\View', array('append'));
        $this->Email = new TestEmailHelper($this->View);
        $this->Email->request = new Request();
        $this->Email->request->webroot = '';
        $this->Email->Url->request = $this->Email->request;
        $this->Html = new HtmlHelper($this->View);

        Configure::write('App.namespace', 'TestApp');
    }

    public function tearDown() {
        parent::tearDown();
        unset($this->Email, $this->Html, $this->View);
    }

    public function testBeforeRenderFile() {
        $event = $this->getMock('Cake\Event\Event', [], [$this->View]);
        $viewFile = '/path/to/app/Template/Email/text/welcome.ctp';

        $this->Email->beforeRenderFile($event, $viewFile);

        $this->assertEquals('text', $this->Email->getType());

        $viewFile = '/path/to/app/Template/Email/html/welcome.ctp';

        $this->Email->beforeRenderFile($event, $viewFile);

        $this->assertEquals('html', $this->Email->getType());
    }

    public function testLink() {
        $url = Router::url('/', ['full' => true]);

        $this->Email->setType('html');
        $result = $this->Email->link('Home', '/');
        $expected = '<a href="' . $url . '" target="_blank">Home</a>';

        $this->assertEquals($expected, $result);

        $this->Email->setType('text');
        $result = $this->Email->link('Home', '/');
        $expected = 'Home: ' . $url;

        $this->assertEquals($expected, $result);
    }

    public function testImage() {
        $this->Email->setType('html');
        $result = $this->Email->image('sample.jpg');
        $expected = $this->Html->image('sample.jpg', (array) $this->Email->config('attributes.image'));

        $this->assertEquals($result, $expected);

        $this->Email->setType('text');
        $result = $this->Email->image('sample.jpg');

        $this->assertEmpty($result);
    }

    public function testMedia() {
        $this->Email->setType('html');
        $result = $this->Email->media('sample.mov');
        $expected = $this->Html->media('sample.mov', (array) $this->Email->config('attributes.media'));

        $this->assertEquals($result, $expected);

        $this->Email->setType('text');
        $result = $this->Email->media('sample.mov');

        $this->assertEmpty($result);
    }

    public function testPara() {
        $this->Email->setType('html');
        $result = $this->Email->para(null, 'lorem ipsum');
        $expected = $this->Html->para(null, 'lorem ipsum', (array) $this->Email->config('attributes.para'));

        $this->assertEquals($result, $expected);

        $eol = $this->Email->config('templates.eoltext');

        $this->Email->setType('text');
        $result = $this->Email->para(null, 'lorem ipsum');
        $expected = $eol . $eol . 'lorem ipsum' . $eol . $eol;

        $this->assertEquals($result, $expected);
    }

}
