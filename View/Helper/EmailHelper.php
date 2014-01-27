<?php
/**
 * EmailHelper
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

App::uses('HtmlHelper', 'View/Helper');

/**
 * Email helper
 *
 * @package       Common.Helper
 */
class EmailHelper extends HtmlHelper {

/**
 * Settings.
 *
 * @var array
 */
	public $settings = array(
		'a' => array(
			'target' => '_blank',
			'style' => array('color:orange;', 'text-decoration:none;')
		),
		'button' => array(
			'style' => array(
				'display:inline;',
				'font-family:Helvetica,Arial,sans-serif;',
				'text-decoration:none;',
				'font-weight:bold;',
				'font-style:normal;',
				'font-size:15px;',
				'line-height:32px;',
				'border:none;',
				'background-color:#336699;',
				'color:#ffffff;'
			)
		),
		'buttonLink' => array(
			'style' => array(
				'color:#ffffff;',
				'display:inline-block;',
				'font-family:Helvetica,Arial,sans-serif;',
				'width:auto;',
				'white-space:nowrap;',
				'min-height:32px;',
				'margin:5px 5px 0 0;',
				'padding:0 22px;',
				'text-decoration:none;',
				'text-align:center;',
				'font-weight:bold;',
				'font-style:normal;',
				'font-size:15px;',
				'line-height:32px;',
				'border:0;',
				'border-radius:4px;',
				'vertical-align:top;',
				'background-color:#336699;'
			)
		),
		'img' => array(
			'class' => 'image_fix'
		),
		'para' => array(
			'style' => array('margin-left:0;', 'margin-right:0;', 'margin-bottom:1em;')
		),
		'table' => array(
			'border' => 0,
			'cellpadding' => 0,
			'cellspacing' => 0,
			'style' => array('border-collapse:collapse;', 'mso-table-lspace:0pt;', 'mso-table-rspace:0pt;')
		),
		'tel' => array(
			'class' => 'mobile_link'
		),
	);

	public $type = 'html';
	public $newline = '<br />';
/**
 * Conditions to use for `EmailHelper::conditionalBlocks()`.
 *
 * @var array
 */
	protected $_conditions = array(
		'outlook' => 'gte mso 9',
		'windows_mobile' => 'IEMobile 7',
		'mobile' => array('screen', 'max-device-width: 480px'),
		'tablet' => array('screen', 'min-device-width: 768px', 'max-device-width: 1024px'),
		'iphone4' => array('screen', '-webkit-min-device-pixel-ratio: 2'),
		'android-ldpi' => array('screen', '-webkit-device-pixel-ratio:.75'),
		'android-mdpi' => array('screen', '-webkit-device-pixel-ratio:1'),
		'android-hdpi' => array('screen', '-webkit-device-pixel-ratio:1.5'),
	);

/**
 * {@inheritdoc}
 */
	public function __construct(View $View, $settings = array()) {

		$this->_tags['table'] = '<table%s>%s</table>';
		$this->_tags['tablestart'] = '<table%s>';
		$this->_tags['tableend'] = '</table>';
		$this->_tags['tel'] = '<span%s>%s</span>';

		parent::__construct($View, $settings);
	}

/**
 * Creates a button link.
 *
 * @param string $text
 * @param string $url
 * @param array $linkOptions
 * @param array $spanOptions
 * @return string
 */
	public function button($text, $url, $linkOptions = array(), $spanOptions = array()) {
		$span = sprintf(
			$this->_tags['tag'],
			'span',
			$this->_parseAttributes($this->_mergeAttributes($spanOptions, $this->settings['button'])),
			$text,
			'span'
		);

		$linkOptions['escape'] = false;

		return $this->link($span, $url, $this->_mergeAttributes($linkOptions, $this->settings['buttonLink']));
	}

/**
 * {@inheritdoc}
 */
	public function charset($charset = 'utf-8') {
		return parent::charset($charset);
	}

/**
 * HTML or CSS conditional block.
 *
 * @param mixed $condition
 * @param string $content
 * @return string
 */
	public function conditionalBlock($condition, $content) {
		if (is_string($condition) && isset($this->_conditions[$condition])) {
			$condition = $this->_conditions[$condition];
		}

		if (is_array($condition)) {
			return $this->media($condition, $content);
		}
		return "<!--[if $condition]>\n$content\n<![endif]-->";
	}

/**
 * {@inheritdoc}
 */
	public function docType($type = 'xhtml-strict') {
		return parent::docType($type);
	}

/**
 * {@inheritdoc}
 */
	public function image($path, $options = array()) {
		if ('text' == $this->type()) {
			return null;
		}
		return parent::image($path, $this->_mergeAttributes($options, $this->settings['img']));
	}

/**
 * {@inheritdoc}
 */
	public function link($title, $url = null, $options = array()) {
		if ('html' == $this->type()) {
			return parent::link($title, $url, $this->_mergeAttributes($options, $this->settings['a']));
		}

		if (empty($url)) {
			return $title;
		}
		return $title . ': ' . $url;
	}

/**
 * [media description]
 *
 * @param array $attribs
 * @param string $content
 * @return string
 */
	public function media($attribs, $content) {
		foreach ($attribs as $k => $attrib) {
			if (preg_match('/^[a-z]+$/i', $attrib)) {
				continue;
			}
			$attribs[$k] = "($attrib)";
		}

		return "@media only " . implode(' and ', $attribs) . " {\n$content\n}\n";
	}

/**
 * {@inheritdoc}
 */
	public function para($class, $text, $options = array()) {
		if ('text' == $this->type()) {
			return $this->newline . $text . $this->newline;
		}
		return parent::para($class, $text, $this->_mergeAttributes($options, $this->settings['para']));
	}

/**
 * Set tag's default attributes.
 *
 * @param string $tag
 * @param array $options
 */
	public function setDefault($tag = null, $options = null) {
		if (empty($tag)) {
			return $this->settings;
		}

		$current = array();
		if (array_key_exists($tag, $this->settings)) {
			$current = $this->settings[$tag];
		}

		if (is_null($options)) {
			return $current;
		}

		if (array_key_exists($tag, $this->settings)) {
			$current = $this->settings[$tag];
		}

		$this->settings[$tag] = Hash::merge($current, (array) $options);
	}

/**
 * Creates a telephone number link.
 *
 * @param string $number
 * @param array $options
 * @return string
 */
	public function sms($number, $options = array()) {
		return $this->tel($number, $options);
	}

/**
 * Creates table.
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function table($content, $options = array()) {
		if ('text' == $this->type()) {
			return $content;
		}

		if (false === $options) {
			return $this->_tags['tableend'];
		}

		$tag = 'table';
		if (is_null($content)) {
			$tag = 'tablestart';
		}

		return sprintf($this->_tags[$tag], $this->_parseAttributes($this->_mergeAttributes($options, $this->settings['table'])), $content);
	}

/**
 * Creates a telephone number link.
 *
 * @param string $number
 * @param array $options
 * @return string
 */
	public function tel($number, $options = array()) {
		if ('text' == $this->type()) {
			return $number;
		}

		return sprintf(
			$this->_tags['tel'],
			$this->_parseAttributes($this->_mergeAttributes($options, $this->settings['tel'])),
			$number
		);
	}

/**
 * Detects current email type and defines the new line string accordingly.
 *
 * @return string Either 'text' or 'html'.
 */
	public function type() {
		$type = next(explode(DS, $this->_View->layoutPath));
		$this->newline = 'text' == $type ? PHP_EOL : '<br />';
		return $type;
	}

/**
 * Viewport meta.
 *
 * @param mixed $content
 * @return string
 */
	public function viewport($content = null) {
		if (empty($content)) {
			$content = 'width=device-width, initial-scale=1.0';
		}
		if (is_array($content)) {
			$content = implode(', ', $content);
		}
		return $this->meta(array('name' => 'viewport', 'content' => $content));
	}

/**
 * Merge attributes.
 *
 * @param array $attrs Passed attributes.
 * @param array $merge Default attributes.
 * @return array
 */
	protected function _mergeAttributes($attrs, $merge) {
		$appendable = array(
			'class' => array('separator' => ' ', 'match' => 'full'),
			'style' => array('separator' => ';', 'match' => 'part'),
		);

		foreach ($merge as $attr => $values) {
			if (
				!array_key_exists($attr, $attrs)
				|| empty($attrs[$attr]) && false !== $attrs[$attr]
			) {
				$attrs[$attr] = $values;
				continue;
			} else if (!in_array($attr, array_keys($appendable))) {
				continue;
			}

			if (!is_array($attrs[$attr])) {
				$attrs[$attr] = explode($appendable[$attr]['separator'], $attrs[$attr]);
				$implode = true;
			}

			if (!is_array($values)) {
				$values = explode($appendable[$attr]['separator'], $attrs[$attr]);
			}

			switch ($appendable[$attr]['match']) {

				case 'full':
					foreach ($values as $value) {
						if (!in_array($value, $attrs[$attr])) {
							$attrs[$attr][] = $value;
						}
					}
				break;

				case 'part':
					foreach ($attrs[$attr] as $k => $haystack) {
						if (empty($haystack)) {
							unset($attrs[$attr][$k]);
							continue;
						}

						if (false === strpos($haystack, $appendable[$attr]['separator'])) {
							$attrs[$attr][$k] = $haystack . $appendable[$attr]['separator'];
						}

						foreach ($values as $n => $value) {
							$needle = current(explode(':', $value)) . ':';
							if (0 === strpos($haystack, $needle) || false !== strpos($haystack, ';' . $needle) || false !== strpos($haystack, '; ' . $needle)) {
								unset($values[$n]);
							}
						}
					}

					foreach (array_keys($values) as $key) {
						if (false === strpos($values[$key], $appendable[$attr]['separator'])) {
							$values[$key] = $values[$key] . $appendable[$attr]['separator'];
						}
						$attrs[$attr][] = $values[$key];
					}
				break;

				default:
			}

			if (isset($implode)) {
				$attrs[$attr] = implode(' ', $attrs[$attr]);
			}
		}

		return $attrs;
	}

}
