=== URL Image Uploader ===
Contributors: Abrar Ahmed
Donate link: http://devabrar.com/
Tags: URL, Image, Upload, Uploader, Helper
Requires at least: 4.6
Tested up to: 5.0
Stable tag: 4.3
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This function is a tool for developers to help them upload images from URL to the WordPress site.


== Description ==

This plugin is particularly designed for developers so that they can easily upload images via URL.

Features of this plugin:
1.  You can give URL to this plugin then this will upload the image to WP and will return attachment ID.
2.  You can give the same URL multiple times but it will not upload an image each time, it will upload an image once and will return attachment ID each time when it is called.
3.  If image URL is change or size of the image on URL is different then just on that condition image will be uploaded to WordPress

It has some more functions too that will help developers for getting details about image and size as well.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress



== Frequently Asked Questions ==

= Can there be duplicate images while using the same URL? =

No. You can not have duplicate entries in your WordPress. Images will be unique till you force your self to duplicate.

= What is Key in this plugin? =

The key is a great concept. You can make different keys in this plugin. Each time you create an object of this plugin class you define your key. And Image URL will be checked in that key. So you can make multiple copies of images with different keys as per your needs.

== Screenshots ==

1.  No screenshots

== Changelog ==

= 1.0 =
* This is the main first version of the plugin.

== Upgrade Notice ==

= 1.0 =
You should check updates if it is available for this plugin.

== Arbitrary section ==

The following features of this plugin are going to be defined for developer understandings.
== A brief Markdown Example ==

Unordered list:

* Function iUploadImageByURL  This function is the basic function that can be used while upload image from URL. It will return Attachment_ID after creating an attachment from image URL. Image from URL will be uploaded once if URL and size of the image are same. If the same URL with the same size of the image given then already created attachment of that image will be uploaded.
* Function iGetFileSize This function can be used to get the size of the image from URL.
* Function sFormatSize This function will return measurement format size of the image.
