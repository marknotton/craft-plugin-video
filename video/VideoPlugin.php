<?php
namespace Craft;

class VideoPlugin extends BasePlugin {
  public function getName() {
    return Craft::t('Video');
  }

  public function getVersion() {
    return '0.1';
  }

  public function getSchemaVersion() {
    return '0.1';
  }

  public function getDescription() {
    return "A fieldtype that collects a videos id and poster images from a Youtube, Vimeo, or other video platform URL's.";
  }

  public function getDeveloper() {
    return 'Yello Studio';
  }

  public function getDeveloperUrl() {
    return 'http://yellostudio.co.uk';
  }
}
