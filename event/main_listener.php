<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2016 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace AustinMaddox\s3\event;

use Aws\Exception\MultipartUploadException;
use Aws\S3\S3Client;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\user */
    protected $user;

    protected $s3_client;

    /**
     * Constructor
     *
     * @param \phpbb\config\config     $config   Config object
     * @param \phpbb\template\template $template Template object
     * @param \phpbb\user              $user     User object
     *
     * @return \AustinMaddox\s3\event\main_listener
     * @access public
     */
    public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user)
    {
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;

        // Instantiate an AWS S3 client.
        $this->s3_client = new S3Client([
            'credentials' => [
                'key'    => $this->config['s3_aws_access_key_id'],
                'secret' => $this->config['s3_aws_secret_access_key'],
            ],
            'debug'       => false,
            'http'        => [
                'verify' => false,
            ],
            'region'      => $this->config['s3_region'],
            'version'     => 'latest',
        ]);
    }

    static public function getSubscribedEvents()
    {
        return [
            'core.user_setup'                               => 'user_setup',
            'core.validate_config_variable'                 => 'validate_config_variable',
            'core.modify_uploaded_file'                     => 'modify_uploaded_file',
            'core.delete_attachments_from_filesystem_after' => 'delete_attachments_from_filesystem_after',
            'core.posting_modify_message_text'              => 'posting_modify_message_text',
            'core.parse_attachments_modify_template_data'   => 'parse_attachments_modify_template_data',
        ];
    }

    public function user_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'AustinMaddox/s3',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    /**
     * Validate the AWS Access Key Id
     *
     * @param object $event The event object
     *
     * @return null
     * @access public
     */
    public function validate_config_variable($event)
    {
        $input = $event['cfg_array']['s3_aws_access_key_id'];

        // Check if the validate test is for s3.
        if (($event['config_definition']['validate'] == 's3_aws_access_key_id') && ($input !== '')) {
            // Store the error and input event data.
            $error = $event['error'];

            // Add error message if the input is not a valid AWS Access Key Id.
//            if (!preg_match('/^UA-\d{4,9}-\d{1,4}$/', $input)) {
//                $error[] = $this->user->lang('ACP_S3_AWS_ACCESS_KEY_ID', $input);
//            }

            // Update error event data.
            $event['error'] = $error;
        }
    }

    /**
     * Event to modify uploaded file before submit to the post
     *
     * @param $event
     */
    public function modify_uploaded_file($event)
    {
        global $phpbb_root_path;

        $filedata = $event['filedata'];

        // Fullsize
        $key = $filedata['physical_filename'];
        $body = file_get_contents($phpbb_root_path . $this->config['upload_path'] . '/' . $key);
        $this->uploadFileToS3($key, $body, $filedata['mimetype']);
    }

    /**
     * Perform additional actions after attachment(s) deletion from the filesystem
     *
     * @param $event
     */
    public function delete_attachments_from_filesystem_after($event)
    {
        foreach ($event['physical'] as $physical_file) {
            $result = $this->s3_client->deleteObject([
                'Bucket' => $this->config['s3_bucket'],
                'Key'    => $physical_file['filename'],
            ]);
        }
    }

    /**
     * This event allows you to modify message text before parsing
     *
     * Failed attempt at catching deletes of `is_orphan` attachments.
     * https://www.phpbb.com/community/viewtopic.php?f=461&t=2380221
     *
     * @param $event
     */
    public function posting_modify_message_text($event)
    {
//        error_log(__METHOD__);
//        error_log(print_r($event['post_data'], true));
    }

    /**
     * Use this event to modify the attachment template data.
     *
     * This event is triggered once per attachment.
     *
     * @param $event
     */
    public function parse_attachments_modify_template_data($event)
    {
        global $phpbb_root_path;

        $block_array = $event['block_array'];
        $attachment = $event['attachment'];

        $key = 'thumb_' . $attachment['physical_filename'];
        $s3_link_thumb = 'http://' . $this->config['s3_bucket'] . '.s3.amazonaws.com/' . $key;
        $s3_link_fullsize = 'http://' . $this->config['s3_bucket'] . '.s3.amazonaws.com/' . $attachment['physical_filename'];
        $local_thumbnail = $phpbb_root_path . $this->config['upload_path'] . '/' . $key;

        if ($this->config['img_create_thumbnail']) {

            // Existence on local filesystem check. Just in case "Create thumbnail" was turned off at some point in the past and thumbnails weren't generated.
            if (file_exists($local_thumbnail)) {

                // Existence on S3 check. Since this method runs on every page load, we don't want to upload the thumbnail multiple times.
                if (!$this->s3_client->doesObjectExist($this->config['s3_bucket'], $key)) {

                    // Upload *only* the thumbnail to S3.
                    $body = file_get_contents($local_thumbnail);
                    $this->uploadFileToS3($key, $body, $attachment['mimetype']);
                }
                $block_array['THUMB_IMAGE'] = $s3_link_thumb;
                $block_array['U_DOWNLOAD_LINK'] = $s3_link_fullsize;
            }
        }

        $block_array['U_INLINE_LINK'] = $s3_link_fullsize;
        $event['block_array'] = $block_array;
    }

    /**
     * Upload the attachment to the AWS S3 bucket.
     *
     * @param $key
     * @param $body
     * @param $content_type
     */
    private function uploadFileToS3($key, $body, $content_type)
    {
        $result = null;
        try {
            $result = $this->s3_client->upload($this->config['s3_bucket'], $key, $body, 'public-read', ['params' => ['ContentType' => $content_type]]);
        } catch (MultipartUploadException $e) {
            error_log('MultipartUpload Failed', [$e->getMessage()]);
        }
//        $object_url = ($result['ObjectURL']) ? $result['ObjectURL'] : $result['Location'];
//        error_log($object_url);
    }
}
