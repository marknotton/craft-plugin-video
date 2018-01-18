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

  public $fields = array();

  public function getEntryTableAttributeHtml(EntryModel $entry, $attribute) {
    // If custom field, get field handle
    if (strncmp($attribute, 'field:', 6) === 0)   {
      $fieldId = substr($attribute, 6);
      if(!isset($this->fields[$fieldId])) {
        $this->fields[$fieldId] = craft()->fields->getFieldById($fieldId);
      }
      $type = $this->fields[$fieldId]->getFieldType()->getName();
      // Get html by attribute (field handle)
      switch ($type)  {
        case 'Video': {
          $attribute = $this->fields[$fieldId]->handle;
          return is_array($entry->$attribute) ? ucwords($entry->$attribute['format']).": <a href=".$entry->$attribute['link']." target='_blank'>".$entry->$attribute['code']."</a>" : $entry->$attribute;
        }
      }
    }
  }
}
