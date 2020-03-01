<?php
/**
 * Forum Banner extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 DavidIQ.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace davidiq\ForumBanners\acp;

class forumbanners_module
{
    /** @var \phpbb\config\config */
    protected $config;

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

    /** @var \phpbb\language\ */
    protected $lang;

    /** @var string */
    protected $phpbb_root_path;

    /** @var string */
    protected $php_ext;

    /** @var string */
    protected $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');

    /** @var string */
    public $u_action;

    /** @var string */
    public $tpl_name;

    /** @var string */
    public $page_title;

    function main($id, $mode)
    {
        global $user, $template, $config, $phpbb_root_path, $phpEx, $phpbb_container, $request, $db;

        $this->config = $config;
        $this->phpbb_container = $phpbb_container;
        $this->log = $this->phpbb_container->get('log');
        $this->lang = $phpbb_container->get('language');
        $this->db = $db;
        $this->request = $request;
        $this->template = $template;
        $this->user = $user;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $phpEx;

        $this->lang->add_lang('forumbanners_acp', 'davidiq/ForumBanners');

        $this->tpl_name = 'forumbanners';
        $this->page_title = 'ACP_FORUMBANNER_IMAGES';

        $banners_dir = $this->phpbb_root_path . $this->config['forum_banners_path'];
        $form_name = 'acp_forumbanners';
        add_form_key($form_name);
        $delete = $this->request->is_set_post('delete');
        $upload = $this->request->is_set_post('upload');

        if (($delete || $upload) && !check_form_key($form_name))
        {
            trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
        }

        if ($delete)
        {
            $delete_banners = $this->request->variable('delete_banner', array(0));

            //Perform the requested action
            if (count($delete_banners))
            {
                foreach ($delete_banners as $delete_banner)
                {
                    $file = glob($banners_dir . '/' . $delete_banner . '.*');
                    $deleted = @unlink($file[0]);
                    if ($deleted)
                    {
                        $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUMBANNER_DELETED');
                    } else
                    {
                        trigger_error(sprintf($this->lang->lang('FORUMBANNER_IMAGE_DELETE_FAIL'), $file[0]) . adm_back_link($this->u_action), E_USER_WARNING);
                    }
                }
                trigger_error($this->lang->lang('FORUMBANNER_IMAGE_DELETED') . adm_back_link($this->u_action));
            }
        }
        else if ($upload)
        {
            $upload_banner = $this->request->file('upload_banner');

            if (!empty($upload_banner['name']))
            {
                $upload = $this->phpbb_container->get('files.factory')->get('upload')
                    ->set_allowed_extensions($this->allowed_extensions)
                    ->set_disallowed_content((isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));
                $file = $upload->handle_upload('files.types.form', 'upload_banner');
                $destination = $this->config['forum_banners_path'];

                // Adjust destination path (no trailing slash)
                if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
                {
                    $destination = substr($destination, 0, -1);
                }

                // Move file and overwrite any existing image and check it is indeed an image
                $file->move_file($destination, true, true);

                if (count($file->error))
                {
                    $file->remove();
                    trigger_error($file->error . adm_back_link($this->u_action), E_USER_WARNING);
                }

                $selected_forum = $this->request->variable('forumbanner_forum_list', 0);
                $destination_path = $file->get('destination_path');
                $file_extension = $file->get('extension');
                $destination_file = $file->get('destination_file');

                $new_destination_file = $destination_path . '/' . $selected_forum . '.' . $file_extension;

                if (rename($destination_file, $new_destination_file))
                {
                    /** @var $file_system \phpbb\filesystem\filesystem */
                    $file_system = $this->phpbb_container->get('filesystem');
                    $file_system->phpbb_chmod($new_destination_file, \phpbb\filesystem\filesystem_interface::CHMOD_READ | \phpbb\filesystem\filesystem_interface::CHMOD_WRITE);
                    $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_FORUMBANNER_UPLOADED');
                    trigger_error($this->lang->lang('FORUMBANNER_IMAGE_UPLOADED') . adm_back_link($this->u_action));
                } else
                {
                    $file->remove();
                    trigger_error($this->lang->lang('FORUMBANNER_UPLOAD_ERROR') . adm_back_link($this->u_action), E_USER_WARNING);
                }
            }
        }

        if (!file_exists($banners_dir))
        {
            @mkdir($banners_dir, 0777);

            if (!file_exists($banners_dir))
            {
                trigger_error(sprintf($this->lang->lang('FORUMBANNER_DIRECTORY_NOT_EXISTS'), $banners_dir), E_USER_WARNING);
            }
        }

        $file_list = @scandir($banners_dir);

        if (count($file_list))
        {
            $sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . "
				ORDER BY forum_id";
            $result = $this->db->sql_query($sql);
            $forums_list = array();

            while ($row = $this->db->sql_fetchrow($result))
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
                    $this->template->assign_block_vars('forumbanners', [
                        'FORUMBANNER_SRC' => $file,
                        'FORUM_ID' => $forum_id,
                        'FORUM_NAME' => $forums_list[$forum_id],
                    ]);
                }
            }
        } else
        {
            trigger_error(sprintf($this->lang->lang('FORUMBANNER_DIRECTORY_READ_ERROR'), $banners_dir) . adm_back_link($this->u_action), E_USER_WARNING);
        }

        if (!function_exists('make_forum_select'))
        {
            include($this->phpbb_root_path . 'includes/functions_display.' . $this->php_ext);
        }
        $forum_box = make_forum_select(0, false, false, false, false);

        $this->template->assign_vars([
            'S_FORM_ENCTYPE' => ' enctype="multipart/form-data"',
            'S_FORUM_BOX' => $forum_box,
            'U_ACTION' => $this->u_action,
        ]);
    }
}
