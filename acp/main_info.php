<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2020 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace austinmaddox\s3\acp;

class main_info
{
	function module()
	{
		return [
			'filename' => '\austinmaddox\s3\acp\main_module',
			'title'    => 'ACP_S3_TITLE',
			'version'  => '1.0.5',
			'modes'    => [
				'settings' => [
					'title' => 'ACP_S3',
					'auth'  => 'ext_austinmaddox/s3 && acl_a_board',
					'cat'   => ['ACP_S3_TITLE'],
				],
			],
		];
	}
}
