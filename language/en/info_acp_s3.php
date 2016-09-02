<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2016 Austin Maddox
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
    'ACP_S3_AWS_ACCESS_KEY_ID'         => 'AWS Access Key Id',
    'ACP_S3_AWS_ACCESS_KEY_ID_EXPLAIN' => 'Enter your AWS Access Key Id for S3, e.g.: <samp>AKIAIOSFODNN7EXAMPLE</samp>.',
    'ACP_S3_AWS_ACCESS_KEY_ID_INVALID' => '“%s” is not a valid AWS Access Key Id.',

    'ACP_S3_AWS_SECRET_ACCESS_KEY'         => 'AWS Secret Access Key',
    'ACP_S3_AWS_SECRET_ACCESS_KEY_EXPLAIN' => 'Enter your AWS Secret Access Key for S3, e.g.: <samp>wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY</samp>.',
    'ACP_S3_AWS_SECRET_ACCESS_KEY_INVALID' => '“%s” is not a valid AWS Secret Access Key.',

    'ACP_S3_REGION'         => 'AWS S3 Region',
    'ACP_S3_REGION_EXPLAIN' => 'Enter the S3 region where your bucket resides, e.g.: <samp>us-west-2</samp>.',
    'ACP_S3_REGION_INVALID' => '“%s” is not a valid S3 region.',

    'ACP_S3_BUCKET'         => 'AWS S3 Bucket',
    'ACP_S3_BUCKET_EXPLAIN' => 'Enter the name of your S3 bucket, e.g.: <samp>example-bucket</samp>. The bucket must already be created in your AWS account.',
    'ACP_S3_BUCKET_INVALID' => '“%s” is not a valid S3 bucket name.',
]);
