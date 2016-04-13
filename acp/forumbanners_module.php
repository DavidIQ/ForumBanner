<?php
/**
* Forum Banner extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 DavidIQ.com
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace davidiq\ForumBanners\acp;

class ForumBanners_module
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var ContainerInterface */
	protected $phpbb_container;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');

	/** @var string */
	public $u_action;

	function main($id, $mode)
	{
		global $user, $template, $cache, $config, $phpbb_root_path, $phpEx, $phpbb_container, $request, $db;

		$this->config = $config;
		$this->phpbb_container = $phpbb_container;
		$this->config_text = $this->phpbb_container->get('config_text');
		$this->log = $this->phpbb_container->get('log');
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->user->add_lang_ext('davidiq/ForumBanners', 'forumbanners_acp');

		$this->tpl_name = 'forumbanners';
		$this->page_title = 'ACP_FORUMBANNER_IMAGES';

		$banners_dir = $this->phpbb_root_path . $this->config['forum_banners_path'];
		$form_name = 'acp_forumbanners';
		add_form_key($form_name);

		$delete_banners = $this->request->variable('delete_banner', array(0));
		$upload_banner = $request->file('upload_banner');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error($this->user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			//Perform the requested action
			if (sizeof($delete_banners))
			{
				foreach ($delete_banners as $delete_banner)
				{
					$file = glob($banners_dir . '/' . $delete_banner . '.*');
					unlink($file[0]);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUMBANNER_DELETED');
				}
				trigger_error($user->lang['FORUMBANNER_IMAGE_DELETED'] . adm_back_link($this->u_action));
			}

			if (!empty($upload_banner['name']))
			{
				$rhea = version_compare(PHPBB_VERSION, '3.2', '>=');
				if (!$rhea)
				{
					global $phpbb_container;
					$upload = $phpbb_container->get('files.factory')->get('upload')
								->set_allowed_extensions($this->allowed_extensions)
								->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));
					$file = $upload->handle_upload('files.types.form', 'upload_banner');
				}
				else
				{
					include($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
					$upload = new \fileupload('FORUMBANNER_', $this->allowed_extensions);
					$file = $upload->form_upload('upload_banner');
				}
				$destination = $this->config['forum_banners_path'];

				// Adjust destination path (no trailing slash)
				if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
				{
					$destination = substr($destination, 0, -1);
				}

				// Move file and overwrite any existing image and check it is indeed an image
				$file->move_file($destination, true, true);

				if (sizeof($file->error))
				{
					$file->remove();
					trigger_error($file->error . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$selected_forum = $this->request->variable('forumbanner_forum_list', 0);
				$destination_path = $file_extension = $destination_file = '';
				if ($rhea)
				{
					$destination_path = $file->get('destination_path');
					$file_extension = $file->get('extension');
					$destination_file = $file->get('destination_file');
				}
				else
				{
					$destination_path = $file->destination_path;
					$file_extension = $file->extension;
					$destination_file = $file->destination_file;
				}
				
				$new_destination_file = $destination_path . '/' . $selected_forum . '.' . $file_extension;
				
				if (rename($destination_file, $new_destination_file))
				{
					phpbb_chmod($new_destination_file, CHMOD_READ | CHMOD_WRITE);
					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUMBANNER_UPLOADED');
					trigger_error($user->lang['FORUMBANNER_IMAGE_UPLOADED'] . adm_back_link($this->u_action));
				}
				else
				{
					$file->remove();
					trigger_error($this->user->lang('FORUMBANNER_UPLOAD_ERROR') . adm_back_link($this->u_action), E_USER_WARNING);
				}
			}
		}

		if (!file_exists($banners_dir))
		{
			@mkdir($banners_dir, 0777);

			if (!file_exists($banners_dir))
			{
				trigger_error(sprintf($this->user->lang('FORUMBANNER_DIRECTORY_NOT_EXISTS'), $banners_dir), E_USER_WARNING);
			}
		}

		$file_list = scandir($banners_dir);

		if (sizeof($file_list))
		{
			$sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . "
				ORDER BY forum_id";
			$result = $this->db->sql_query($sql);
			$forums_list = array();

			while ($row = $db->sql_fetchrow($result))
			{
				$forums_list[$row['forum_id']] = $row['forum_name'];
			}

			foreach ($file_list as $file)
			{
				$file = $banners_dir . '/' . $file;
				$file_info = pathinfo($file);

				if (isset($file_info['filename']) && isset($forums_list[(int)$file_info['filename']]))
				{
					$forum_id = (int)$file_info['filename'];
					$this->template->assign_block_vars('forumbanners', array(
						'FORUMBANNER_SRC'		=> $file,
						'FORUM_ID'				=> $forum_id,
						'FORUM_NAME'			=> $forums_list[$forum_id])
					);
				}
			}
		}

		include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
		$forum_box = make_forum_select(0, false, false, false, false);

		$template->assign_vars(array(
			'S_FORM_ENCTYPE'	=> ' enctype="multipart/form-data"',
			'S_FORUM_BOX'				=> $forum_box,
			'U_ACTION'					=> $this->u_action,
		));
	}
}
