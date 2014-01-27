# CakePHP Emails Plugin

Makes CakePHP's `CakeEmail` even easier to use by adding:

* Default configurations for: MockSmtp, Sendgrid (and more to come).
* Email previewing

## Install

### Composer package

First, add this plugin as a requirement to your `composer.json`:

	{
		"require": {
			"cakephp/emails": "*"
		}
	}

And then update:

	php composer.phar update

That's it! You should now be ready to start configuring your channels.

### Submodule

	$ cd /app
	$ git submodule add git://github.com/gourmet/emails.git Plugin/Emails

### Clone

	$ cd /app/Plugin
	$ git clone git://github.com/gourmet/emails.git

## Configuration

You need to enable the plugin your `app/Config/bootstrap.php` file:

	CakePlugin::load('Emails');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.

## Usage

Symlink the plugin's default email configuration:

	$ cd /app
	$ ln -s Plugin/Emails/config/email.php config/email.php

Add new email configuration(s):

	Configure::write('Emails.configs', array('custom' => array(...));

@todo write more usage example(s)

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

## Bugs & Feedback

http://github.com/gourmet/emails/issues

## License

Copyright 2013, [Jad Bitar](http://jadb.io)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.
