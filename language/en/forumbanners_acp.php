<?php
/**
* Forum Banner extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 DavidIQ.com
* @license GNU General Public License, version 2 (GPL-2.0)
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// NOTE TO TRANSLATORS:  Text in parenthesis refers to keys on the keyboard

$lang = array_merge($lang, array(
	'ACP_FORUMBANNER_IMAGES'			=> 'Forum Banner images',
	'ACP_FORUMBANNER_IMAGES_EXPLAIN'	=> 'This extension will let you upload an image and display it on a per-forum basis.',

	'FORUMBANNER_LIST'					=> 'Forum Banner list',
	'FORUMBANNER_UPLOAD'				=> 'Forum Banner Upload',
	'FORUMBANNER_FORUM'					=> 'Forum for banner',
	'FORUMBANNER_FORUM_EXPLAIN'			=> 'Select the forum to which the uploaded file will be assigned.',
	'FORUMBANNER'						=> 'Forum banner',
	'FORUMBANNER_UPLOAD_BUTTON'			=> 'Upload banner',
	'FORUMBANNER_NONE_AVAILABLE'		=> 'There are no forum banners available',
	'FORUMBANNER_DISALLOWED_EXTENSION'	=> 'The extension of the file being uploaded is not allowed.',
	'FORUMBANNER_UPLOAD_ERROR'			=> 'There was an error uploading your file. Please try again. Check server logs if problem persists.',
	'FORUMBANNER_DIRECTORY_NOT_EXISTS'	=> 'The %s directory does not exist. Please create it manually.',
	'FORUMBANNER_DIRECTORY_READ_ERROR'  => 'There was an error reading from the banner images directory.',

	'FORUMBANNER_IMAGE_UPLOADED'		=> 'Forum banner image has been uploaded.',
	'FORUMBANNER_IMAGE_DELETED'			=> 'Forum banner image has been deleted.',
   'FORUMBANNER_IMAGE_DELETE_FAIL'			=> 'Could not delete forum banner image: %s',
));
