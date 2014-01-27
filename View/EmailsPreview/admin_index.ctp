<?php
/**
 * EmailPreview index view.
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
?>
<fieldset>

	<?php
	echo $this->Form->create(null, array('target' => '_blank', 'class' => 'well form-horizontal'));

	echo $this->Form->hidden('preview', array('value' => 'true'));

	echo $this->Form->input('view');
	echo $this->Form->input('layout');
	echo $this->Form->input('format', array('options' => array('text' => 'TEXT', 'html' => 'HTML'), 'default' => 'html'));
	echo $this->Form->input('data', array('after' => '<em style="display:block">INI string</em>', 'type' => 'textarea'));

	echo $this->Form->submit(__d('emails', "Preview"), array('class' => 'btn btn-primary'));
	echo $this->Form->end();
	?>

</fieldset>
