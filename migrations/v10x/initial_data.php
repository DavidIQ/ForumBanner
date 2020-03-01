<?php
/**
* Forum Banner extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 DavidIQ.com
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace davidiq\ForumBanners\migrations\v10x;

/**
* Migration stage 1: Initial data changes to the database
*/
class initial_data extends \phpbb\db\migration\migration
{
	/**
	* Add Mailing List data to the database.
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return [
			['config.add', ['forum_banners_path', 'images/forums']],
		];
	}
}
