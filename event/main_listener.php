<?php
/**
 *
 * @package       phpBB Extension - S3
 * @copyright (c) 2017 Austin Maddox
 * @license       http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace AustinMaddox\s3\event;

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

    /** @var $phpbb_root_path */
    protected $phpbb_root_path;

    /** @var S3Client */
    protected $s3_client;

    /**
     * Constructor
     *
     * @param \phpbb\config\config     $config   Config object
     * @param \phpbb\template\template $template Template object
     * @param \phpbb\user              $user     User object
     * @param                          $phpbb_root_path
     *
     * @access public
     */
    public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path)
    {
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;
        $this->phpbb_root_path = $phpbb_root_path;

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
            'core.validate_config_variable'                 => [['validate_s3_aws_access_key_id'], ['validate_s3_aws_secret_access_key']],
            'core.modify_uploaded_file'                     => 'modify_uploaded_file',
            'core.delete_attachments_from_filesystem_after' => 'delete_attachments_from_filesystem_after',
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
     * Validate the AWS Access Key Id.
     *
     * @param object $event The event object
     *
     * @return null
     * @access public
     */
    public function validate_s3_aws_access_key_id($event)
    {
        $input = $event['cfg_array']['s3_aws_access_key_id'];

        // Check if the validate test is for s3.
        if (($event['config_definition']['validate'] == 's3_aws_access_key_id') && ($input !== '')) {
            // Store the error and input event data.
            $error = $event['error'];

            // Add error message if the input is not a valid AWS Access Key Id.
            if (!preg_match('/[A-Z0-9]{20})/', $input)) {
                $error[] = $this->user->lang('ACP_S3_AWS_ACCESS_KEY_ID_INVALID', $input);
            }

            // Update error event data.
            $event['error'] = $error;
        }
    }

    /**
     * Validate the AWS Secret Access Key.
     *
     * @param object $event The event object
     *
     * @return null
     * @access public
     */
    public function validate_s3_aws_secret_access_key($event)
    {
        $input = $event['cfg_array']['s3_aws_secret_access_key'];

        // Check if the validate test is for s3.
        if (($event['config_definition']['validate'] == 's3_aws_secret_access_key') && ($input !== '')) {
            // Store the error and input event data.
            $error = $event['error'];

            // Add error message if the input is not a valid AWS Secret Access Key.
            if (!preg_match('/[A-Za-z0-9/+=]{40}/', $input)) {
                $error[] = $this->user->lang('ACP_S3_AWS_SECRET_ACCESS_KEY_INVALID', $input);
            }

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
        $filedata = $event['filedata'];

        // Fullsize
        $key = $filedata['physical_filename'];
        $body = file_get_contents($this->phpbb_root_path . $this->config['upload_path'] . '/' . $key);
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
            $this->s3_client->deleteObject([
                'Bucket' => $this->config['s3_bucket'],
                'Key'    => $physical_file['filename'],
            ]);
        }
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
        $block_array = $event['block_array'];
        $attachment = $event['attachment'];

        $key = 'thumb_' . $attachment['physical_filename'];
        $s3_link_thumb = '//' . $this->config['s3_bucket'] . '.s3.amazonaws.com/' . $key;
        $s3_link_fullsize = '//' . $this->config['s3_bucket'] . '.s3.amazonaws.com/' . $attachment['physical_filename'];
        $local_thumbnail = $this->phpbb_root_path . $this->config['upload_path'] . '/' . $key;

        if ($this->config['img_create_thumbnail']) {

            // Existence on local filesystem check. Just in case "Create thumbnail" was turned off at some point in the past and thumbnails weren't generated.
            if (file_exists($local_thumbnail)) {

                // Existence on S3 check. Since this method runs on every page load, we don't want to upload the thumbnail multiple times.
                if (!$this->s3_client->doesObjectExist($this->config['s3_bucket'], $key)) {

                    // Upload *only* the thumbnail to S3.
                    $body = file_get_contents($local_thumbnail);
                    $this->uploadFileToS3($key, $body, $attachment['mimetype']);
                }
            }
            $block_array['THUMB_IMAGE'] = $s3_link_thumb;
            $block_array['U_DOWNLOAD_LINK'] = $s3_link_fullsize;
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
        $this->s3_client->upload($this->config['s3_bucket'], $key, $body, 'public-read', ['params' => ['ContentType' => $content_type]]);
    }
}
