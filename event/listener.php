<?php
/**
* Forum Banner extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 DavidIQ.com
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace davidiq\ForumBanner\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, $php_ext, $phpbb_root_path)
	{
		$this->config = $config;
		$this->template = $template;
		$this->user = $user;
		$this->php_ext = $php_ext;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewforum_get_topic_data'				=> 'add_banner',
		);
	}

	/**
	* Add the forum banner
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function add_banner($event)
	{
		$forum_id = $event['forum_id'];
		$banner_src = false;

		$file_check = glob($this->phpbb_root_path . $this->config['forum_banners_path'] . '/' . $forum_id . '.*');

		if (sizeof($file_check) && getimagesize($file_check[0]))
		{
			$banner_src = $file_check[0];
		}

		//Standard template variables
		$this->template->assign_vars(array(
			'FORUM_BANNER_SRC'		=> $banner_src,
		));
	}
}
