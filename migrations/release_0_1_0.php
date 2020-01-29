<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2020 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace AustinMaddox\s3\migrations;

class release_0_1_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return [
			['config.add', ['s3_aws_access_key_id', '']],
			['config.add', ['s3_aws_secret_access_key', '']],
			['config.add', ['s3_region', '']],
			['config.add', ['s3_bucket', '']],

			[
				'module.add',
				[
					'acp',
					'ACP_CAT_DOT_MODS',
					'ACP_S3_TITLE',
				],
			],
			[
				'module.add',
				[
					'acp',
					'ACP_S3_TITLE',
					[
						'module_basename' => '\AustinMaddox\s3\acp\main_module',
						'modes'           => ['settings'],
					],
				],
			],
		];
	}
}
