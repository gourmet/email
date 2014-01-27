<?php
/**
 * EmailPreviewController class
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

App::uses('EmailsAppController', 'Emails.Controller');

/**
 * EmailPreview Controller
 *
 * @package       Emails.Controller
 */
class EmailsPreviewController extends EmailsAppController {

/**
 * {@inheritdoc}
 */
	public $helpers = array('Emails.Email');

/**
 * {@inheritdoc}
 */
	public $uses = false;

/**
 * {@inheritdoc}
 */
	public function beforeFilter() {
		if (Configure::read('debug') < 2) {
			throw new NotFoundException();
		}

		parent::beforeFilter();
	}

/**
 * [index description]
 * @return [type]
 */
	public function admin_index() {
		if (!$this->request->is('post')) {
			return;
		}

		$defaults = array(
			'layout' => 'Emails.boilerplate',
			'layoutPath' => 'Emails' . DS . ':format',
			'plugin' => null,
			'view' => 'default',
			'viewPath' => 'Emails' . DS . ':format',
		);

		$values = array();

		if (!empty($this->data['data'])) {

			$contents = parse_ini_string($this->data['data'], true);

			foreach ($contents as $section => $attribs) {
				if (is_array($attribs)) {
					$values[$section] = $this->_parseNestedValues($attribs);
				} else {
					$parse = $this->_parseNestedValues(array($attribs));
					$values[$section] = array_shift($parse);
				}
			}

			if (!empty($values['data'])) {
				foreach ($values['data'] as $key => $value) {
					$this->request->data($key, $value);
				}
			}
		}

		$this->set($values);

		foreach ($defaults as $key => $value) {
			$value = String::insert($value, array('format' => $this->data['format']));
			if (!empty($this->data[$key])) {
				$value = $this->data[$key];
			}

			$this->{$key} = $value;
		}

		if ('text' == $this->data['format']) {
			$this->response->type('txt');
		}
	}

	protected function _constructCrumbs() {
		if (
			$this instanceof CakeErrorController
			|| false === Common::read('Layout.showCrumbs', true)
			|| false === $this->breadCrumbs
		) {
			return;
		}
		parent::_constructCrumbs();
		$this->breadCrumbs[$this->plugin] = array();
		array_pop($this->breadCrumbs);
	}
/**
 * Parses nested values out of keys.
 *
 * @param array $values Values to be exploded.
 * @return array Array of values exploded
 * @see Configure.IniReader
 */
	protected function _parseNestedValues($values) {
		foreach ($values as $key => $value) {
			if ($value === '1') {
				$value = true;
			}
			if ($value === '') {
				$value = false;
			}
			unset($values[$key]);
			if (strpos($key, '.') !== false) {
				$values = Hash::insert($values, $key, $value);
			} else {
				$values[$key] = $value;
			}
		}
		return $values;
	}

}
