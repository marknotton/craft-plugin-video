<?php
namespace Craft;

class VideoFieldType extends PlainTextFieldType {


	public function getName()	{ return Craft::t('Video');}

	// To add more video formats, you should only need to add to this array. Everything else will be managed automatically
	public $formats = array(
		'youtube' => array(
			'name' => 'YouTube',
			'regex' => '/\/\/(?:www\.)?youtu(?:\.be|be\.com)\/(?:watch\?v=|embed\/)?([a-z0-9_\-]+)/i',
			'poster' => ''
		),
		'vimeo' => array(
			'name' => 'Vimeo',
			'regex' => '/\/\/(?:www\.)?vimeo.com\/([0-9a-z\-_]+)/i',
			'poster' => 'http://vimeo.com/api/v2/video/{id}.php'
		),
		'dailymotion' => array(
			'name' => 'Dailymotion',
			'regex' => '/^.+dailymotion.com\/(?:video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/',
			'poster' => ''
		)
	);

	// Regex to validate a URL
	public $urlRegex = '/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/';


	public function getSettingsHtml()	{
		return craft()->templates->render('video/settings', array(
			'settings' => $this->getSettings()
		));
	}

	// Settings defined dynamically
	protected function defineSettings() {
		$settings = array('formats' => array(AttributeType::Mixed, 'default' => $this->formats ));
		foreach (array_keys($this->formats) as $key) {
			$settings[$key] = array(AttributeType::Bool, 'default' => 1);
		}
		return $settings;
  }

	public function defineContentAttribute() {
		return AttributeType::Mixed;
	}

	public function getInputHtml($name, $value)	{
		$id = craft()->templates->formatInputId($name);
		$namespace = craft()->templates->namespaceInputId($id);

		return craft()->templates->render('video/input', array(
			'id'        => $id,
			'name'      => $name,
			'namespace' => $namespace,
			'value'     => $value,
			'urlRegex'  => $this->urlRegex,
			'settings'  => $this->getSettings()
		));
	}

	public function prepValueFromPost($value) {
		$code   = false;
		$format = false;
		$poster = false;
		$link   = false;

		foreach($this->formats as $format => $options) {
			if (preg_match($options['regex'], $value, $matches) && count($matches) > 1) {
				$code = $matches[1];
				$format = $format;
				break;
			}
		}

		// Poster/Thumbnail information & link
		if (!empty($format) && !empty($code)) {
			switch ($format) {
			  case 'youtube':
			    $poster = array(
						'small' => 'https://img.youtube.com/vi/'.$code.'/0.jpg',
						'medium' => 'https://img.youtube.com/vi/'.$code.'/1.jpg',
						'large' => 'https://img.youtube.com/vi/'.$code.'/maxresdefault.jpg',
					);
					$link = 'https://www.youtube.com/watch?v=' . $code;
			  break;
			  case 'vimeo':
					$hash = unserialize(file_get_contents('https://vimeo.com/api/v2/video/'.$code.'.php'));
					$poster = array(
						'small' => $hash[0]['thumbnail_small'],
						'medium' => $hash[0]['thumbnail_medium'],
						'large' => $hash[0]['thumbnail_large'],
					);
					$link = 'https://vimeo.com/' . $code;
			  break;
				case 'dailymotion':
					$hash = unserialize(file_get_contents('https://api.dailymotion.com/video/'.$code.'?fields=thumbnail_small_url,thumbnail_medium_url,thumbnail_large_url'));
					$poster = array(
						'small' => $hash[0]['thumbnail_small_url'],
						'medium' => $hash[0]['thumbnail_medium_url'],
						'large' => $hash[0]['thumbnail_large_url'],
					);
					$link = 'http://www.dailymotion.com/video/' . $code;
				break;
			}
		} else {
			return null;
		}

		// Saves this entry as both the original url and the stripped out video code
		return array(
			'url' => $value,
			'code' => $code,
			'format' => $format,
			'link' => $link,
			'poster' => $poster
		);
	}

	// Validates the URL is an acceptable video format, and is enabled in the field options
	public function validUrl($url) {
		$settings = $this->getSettings();

		if ( empty($url) ) {
			return true;
		}

		$valid = false;

		foreach($this->formats as $handle => $options) {
			if ($settings->$handle && preg_match($options['regex'], $url)) {
				$valid = true;
				break;
			}
		}

		return preg_match($this->urlRegex, $url) && $valid;
	}

	public function validate($value) {
		return isset($value['url']) && $this->validUrl($value['url']) ? true : 'Must be a valid URL';
	}

}
