<?php
/**
 * CakeEmail configuration.
 *
 * @see http://book.cakephp.org/2.0/en/core-utility-libraries/email.html#configurations
 */
class EmailConfig {

  public $default = array(
    'transport' => 'Smtp',
    'port' => 1025,
    'host' => 'localhost',
    'timeout' => 30,
  );

  /**
   * Constructor
   */
  public function __construct() {
    foreach (Common::read('Emails.configurations', array()) as $key => $config) {
      $this->{$key} = $config;
    }

    $config = Configure::read('Emails.config');
    $fromEmail = Common::read('Emails.from', Configure::read('App.defaultEmail'));
    $fromName = Common::read('Emails.name', Configure::read('App.title'));
    $name = Configure::read('App.title');
    $xmailer = '%s Mail Agent';

    if (!empty($config)) {
      $this->default = array_merge($this->default, $this->{$config});

      if (Configure::read('debug')) {
        $xmailer .= " (via $config)";
      }
    }

    $this->default += array(
      'emailFormat' => 'both',
      'from' => array($fromEmail => $fromName),
      'sender' => array($fromEmail => $fromName),
      'replyTo' => array($fromEmail => $fromName),
      'template' => '',
      'layout' => 'Emails.Emails.boilerplate',
      'headers' => array('X-Mailer' => sprintf($xmailer, $name))
    );
  }
}

Configure::write('Emails.configurations.sendgrid', array(
  'port' => '587',
  'host' => 'smtp.sendgrid.net',
  'transport' => 'Smtp',
  'client' => Common::read('Emails.sengrid.client', null),
  'username' => Common::read('Emails.sengrid.username', null),
  'password' => Common::read('Emails.sengrid.password', null),
));
