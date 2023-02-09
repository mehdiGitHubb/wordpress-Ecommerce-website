=== Themify Metabox ===
Contributors: themifyme
Plugin Name: Themify Metabox
Tags: metabox, meta-box, fields, settings, option, custom-fields, admin
Requires at least: 3.0
Tested up to: 5.1
Stable tag: 1.0.4

Metabox creation tool with advanced features.

== Description ==

Easily create metaboxes for your posts and custom post types, or for user profiles.
Please note, this plugin is intended for developers, to see how to use this plugin you can checkout the "example-functions.php" file inside the plugin.

Supported field types

* <strong>textbox</strong>: general text input
* <strong>image</strong>: image uploader, allows user to upload images directly or use the WordPress media browser
* <strong>video</strong>: same as "image" but only allows uploading video files
* <strong>audio</strong>: same as "image" but only allows uploading audio files
* <strong>dropdown</strong>: multiple choice select field
* <strong>radio</strong>
* <strong>checkbox</strong>
* <strong>color</strong>: color picker
* <strong>date</strong>: date and time picker, with options to change the formatting of the date
* <strong>gallery_shortcode</strong>: upload and select multiple images
* <strong>multi</strong>: allows displaying multiple fields in the same row

== Installation ==

1. Upload the whole plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0.3 =
* fix assets not loading on admin screen
* revised JavaScript API for field types
* new "repeater" field type
* fix notice messages with empty panel_options
* fix issue with custom fields display
* fix compatibility with third party plugins

= 1.0.2 =
* optimize the data saving for custom fields
* add "default" parameter to meta fields: the custom field is not saved if it's equivalent to default parameter
* fix gallery field type showing double label
* update minicolors script
* add rgba support to colorpicker inputs
* fix compatibility with EDD plugin

= 1.0.1 =
* support for custom fields in user profile screen
* refactor fields API
* update example-functions.php file with more fields
* Fix image field type not showing preview
* Fix audio and video field showing error in console after media browse
* allow adjusting 'size' and 'rows' attributes for textarea field type