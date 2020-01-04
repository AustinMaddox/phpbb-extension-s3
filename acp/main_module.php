<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2020 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace austinmaddox\s3\acp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang('acp/common');
		$this->tpl_name = 's3_body';
		$this->page_title = $user->lang('ACP_S3_TITLE');
		add_form_key('austinmaddox/s3');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('austinmaddox/s3'))
			{
				trigger_error('FORM_INVALID');
			}

			$errors = [];
			if (!preg_match('/[A-Z0-9]{20}/', $request->variable('s3_aws_access_key_id', '')))
			{
				$errors[] = $user->lang('ACP_S3_AWS_ACCESS_KEY_ID_INVALID', $request->variable('s3_aws_access_key_id', ''));
			}

			if (!preg_match('/[A-Za-z0-9\/+=]{40}/', $request->variable('s3_aws_secret_access_key', '')))
			{
				$errors[] = $user->lang('ACP_S3_AWS_SECRET_ACCESS_KEY_INVALID', $request->variable('s3_aws_secret_access_key', ''));
			}

			if (empty($request->variable('s3_region', '')))
			{
				$errors[] = $user->lang('ACP_S3_REGION_INVALID');
			}

			if (empty($request->variable('s3_bucket', '')))
			{
				$errors[] = $user->lang('ACP_S3_BUCKET_INVALID');
			}

			// If we have no errors so far, let's ensure our AWS credentials are actually working.
			if (!count($errors))
			{
				try
				{
					// Instantiate an AWS S3 client.
					$s3_client = new \Aws\S3\S3Client([
						'credentials' => [
							'key'    => $request->variable('s3_aws_access_key_id', ''),
							'secret' => $request->variable('s3_aws_secret_access_key', ''),
						],
						'http'        => [
							'verify' => false,
						],
						'region'      => $request->variable('s3_region', ''),
						'version'     => 'latest',
					]);

					// Upload a test file to ensure credentials are valid and everything is working properly.
					$s3_client->upload($request->variable('s3_bucket', ''), 'test.txt', 'test body');

					// Delete the test file.
					$s3_client->deleteObject([
						'Bucket' => $request->variable('s3_bucket', ''),
						'Key'    => 'test.txt',
					]);
				}
				catch (\Aws\S3\Exception\S3Exception $e)
				{
					$errors[] = $e->getMessage();
				}
			}

			// If we still don't have any errors, it is time to set the database config values.
			if (!count($errors))
			{
				$config->set('s3_aws_access_key_id', $request->variable('s3_aws_access_key_id', ''));
				$config->set('s3_aws_secret_access_key', $request->variable('s3_aws_secret_access_key', ''));
				$config->set('s3_region', $request->variable('s3_region', ''));
				$config->set('s3_bucket', $request->variable('s3_bucket', ''));
				$config->set('s3_cdn_domain', $request->variable('s3_cdn_domain', ''));
				$config->set('s3_is_enabled', 1);

				trigger_error($user->lang('ACP_S3_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$template->assign_vars([
			'U_ACTION'                 => $this->u_action,
			'S3_ERROR'                 => isset($errors) ? ((count($errors)) ? implode('<br /><br />', $errors) : '') : '',
			'S3_AWS_ACCESS_KEY_ID'     => $config['s3_aws_access_key_id'],
			'S3_AWS_SECRET_ACCESS_KEY' => $config['s3_aws_secret_access_key'],
			'S3_REGION'                => $config['s3_region'],
			'S3_BUCKET'                => $config['s3_bucket'],
			'S3_CDN_DOMAIN'            => $config['s3_cdn_domain'],
			'S3_IS_ENABLED'            => ($config['s3_is_enabled']) ? 'Enabled' : 'Disabled',
		]);
	}
}
