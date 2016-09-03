<?php
/**
 *
 * @package       phpBB Extension - AWS S3
 * @copyright (c) 2013 phpBB Group
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
    'ACP_S3_TITLE'             => 'S3 Module',
    'ACP_S3'                   => 'Settings',
    'ACP_S3_SETTING_SAVED'     => 'Settings have been saved successfully!',
]);
