<?php
/**
 * Email default layout's content.
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

/**
 * Detect current email type (html or text) to prepend common blocks' names.
 *
 * If the block names are not prepended with the email type, certain blocks are rendered
 * with the wrong email type since they are only defined by this element only and only if
 * they are empty. So, when rendering an html and text email, the block is rendered for
 * the first type and skipped the next time around. By prepending the block's name, every
 * common block is rendered in every type.
 */
	$__emailType = $this->Email->type();

/**
 * Default 'salutation' block by email type.
 */
	$this->startIfEmpty($__emailType . 'Salutation');
	echo ltrim($this->Email->para(null, String::insert(__d('emails', "Hi :username,"), $recipient)));
	$this->end();

/**
 * Default 'signature' block by email type.
 */
	$this->startIfEmpty($__emailType . 'Signature');
	$__emailSignature = (array) Configure::read('Email.signature');
	$__emailSignature[] = Configure::read('App.title');
	echo $this->Email->para(null, implode($this->Email->newline, $__emailSignature));
	unset($__emailSignature);
	$this->end();

/**
 * Render email blocks and add whitespace beween blocks where necessary.
 */
	$__prevBlock = null;
	foreach (array('salutation', 'content', 'signature', 'unsubscribe', 'contact') as $__block) {
		if (in_array($__emailType . ucfirst($__block), $this->blocks())) {
			$__block = $__emailType . ucfirst($__block);
		}

		$__block = $this->fetch($__block);

		if (
			!empty($__prevBlock)
			&& !preg_match("@(" . PHP_EOL . "|<br\s?/?>|</p>)$@i", $__prevBlock)
			&& !preg_match("@(" . PHP_EOL . "|<p>|<br\s?/?>)$@i", $__block)
		) {
			echo $this->Email->newline;
		}

		$__prevBlock = $__block;
		echo $__block;
	}

/**
 * Unset local variables.
 */
	unset($__block, $__emailType, $__prevBlock);
