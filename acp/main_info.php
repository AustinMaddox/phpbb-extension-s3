<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2016 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace AustinMaddox\s3\acp;

class main_info
{
    function module()
    {
        return [
            'filename' => '\AustinMaddox\s3\acp\main_module',
            'title'    => 'ACP_S3_TITLE',
            'version'  => '0.1.2',
            'modes'    => [
                'settings' => [
                    'title' => 'ACP_S3',
                    'auth'  => 'ext_AustinMaddox/s3 && acl_a_board',
                    'cat'   => ['ACP_S3_TITLE'],
                ],
            ],
        ];
    }
}
