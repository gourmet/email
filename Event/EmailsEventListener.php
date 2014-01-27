<?php
/**
 * EmailsEventListener
 *
 * PHP 5
 *
 * Copyright 2013, Jad Bitar (http://jadb.io)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2013, Jad Bitar (http://jadb.io)
 * @link          http://github.com/gourmet/common
 * @since         0.1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('CakeEventListener', 'Event');
App::uses('Navigation', 'Common.Lib');

/**
 * Emails event listener
 *
 * Collection of emails events to load.
 *
 * @package       Emails.Event
 */
class EmailsEventListener implements CakeEventListener {

	public function implementedEvents() {
		return array(
			'Controller.constructClasses' => array('callable' => 'controllerConstructClasses'),
		);
	}

	public function controllerConstructClasses(CakeEvent $Event) {
		Navigation::add('Admin.emails', array(
			'access' => 'User.admin',
			'title' => __d('emails', "Emails"),
			'url' => array('plugin' => 'emails', 'controller' => 'emails_preview', 'prefix' => 'admin', 'admin' => true),
			'weight' => 9040
		));
	}

}
