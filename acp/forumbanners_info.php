<?php
/**
 * Forum Banner extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 DavidIQ.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace davidiq\ForumBanners\acp;

class forumbanners_info
{
    function module()
    {
        return [
            'filename' => '\davidiq\ForumBanners\acp\forumbanners_module',
            'title' => 'ACP_FORUMBANNER_IMAGES',
            'modes' => [
                'main' => [
                    'title' => 'ACP_FORUMBANNER_IMAGES',
                    'auth' => 'ext_davidiq/ForumBanners && acl_a_fauth',
                    'cat' => ['ACP_CAT_FORUMBANNERS'],
                ],
            ],
        ];
    }
}
