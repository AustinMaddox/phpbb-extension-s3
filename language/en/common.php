<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2017 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    'ACP_S3'               => 'Settings',
    'ACP_S3_SETTING_SAVED' => 'Settings have been saved successfully!',

    'ACP_S3_AWS_ACCESS_KEY_ID'         => 'AWS Access Key Id',
    'ACP_S3_AWS_ACCESS_KEY_ID_EXPLAIN' => 'Enter your AWS Access Key Id for S3, e.g.: <samp>AKIAIOSFODNN7EXAMPLE</samp>.',
    'ACP_S3_AWS_ACCESS_KEY_ID_INVALID' => '“%s” is not a valid AWS Access Key Id. It must be a combination of uppercase letters and/or numbers and exactly 20 characters.<br>Specifically, it must match the regular expression <code>/[A-Z0-9]{20})/</code>',

    'ACP_S3_AWS_SECRET_ACCESS_KEY'         => 'AWS Secret Access Key',
    'ACP_S3_AWS_SECRET_ACCESS_KEY_EXPLAIN' => 'Enter your AWS Secret Access Key for S3, e.g.: <samp>wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY</samp>.',
    'ACP_S3_AWS_SECRET_ACCESS_KEY_INVALID' => '“%s” is not a valid AWS Secret Access Key. It must be a combination of letters and/or numbers, forward slashes (/), plus signs (+), equals signs (=), and exactly 40 characters.<br>Specifically, it must match the regular expression <code>/[A-Za-z0-9\/+=]{40}/</code>',

    'ACP_S3_REGION'         => 'AWS S3 Region',
    'ACP_S3_REGION_EXPLAIN' => 'Enter the S3 region where your bucket resides, e.g.: <samp>us-west-2</samp>.',
    'ACP_S3_REGION_INVALID' => '“%s” is not a valid S3 region.',

    'ACP_S3_BUCKET'         => 'AWS S3 Bucket',
    'ACP_S3_BUCKET_EXPLAIN' => 'Enter the name of your S3 bucket, e.g.: <samp>example-bucket</samp>. The bucket must already be created in your AWS account.',
    'ACP_S3_BUCKET_INVALID' => '“%s” is not a valid S3 bucket name.',

    'ACP_S3_TEST_MY_SETTINGS'         => 'Test AWS S3 Settings',
    'ACP_S3_TEST_MY_SETTINGS_EXPLAIN' => 'Click this button to ensure your settings are configured correctly. Clicking this button will upload a test file to your S3 bucket and then subsequently delete it. If it succeeds, you are ready to begin using AWS S3 with your phpBB forum!',
    'ACP_S3_TEST_MY_SETTINGS_BUTTON'  => 'Test My Settings',
]);
