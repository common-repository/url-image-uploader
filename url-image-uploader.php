<?php
/**
 * @package URL_Image_Uploader
 * @version 1.0
 */
/*
Plugin Name: URL Image Uploader
Plugin URI: http://wordpress.org/plugins/url-image-uploader/
Description: This plugin is designed to upload images from URL only once. This plugin is an add-on for developer when developer wants to upload image and want to get its ID later on just ID will be returned but image will not be uploaded again and again if image URL and size same.
Author: Abrar Ahmed
Version: 1.0
License: GPLv2 or later
Author URI: http://devabrar.ga
*/


/*
    This class is primarly intended to be used to upload images via URL to WordPress site.
    Here UrlIU_Helper stands for URL Image Uploader Helper.
*/
if (!class_exists("UrlIU_Helper")) {
    class UrlIU_Helper
    {
        private $sKey = "URLImageFiles";

        /*
            This is contructor function of class used to set initial settings of class.

            @since 1.0

            @param  $sKey  This is basically key that will be used to check if any image from URL is uploaded via this Key. If someone wants to change key or make different keys to check if using some key image is uploaded or not.
        */
        public function __construct($sKey = false)
        {
            if ($sKey !== false) {
                $this->sKey = $sKey;
            }
        }

        /*
        This function will help to upload image via URL.

        @since 1.0

        @param $sURL This is URL of image that will be uploaded.
        @param $bMetaData This is boolean used if user wants to make meta data of attachment of image.
        @param $bReplaceUpload This is boolean. If set TRUE then image for URL will be uploaded despite of checking whether that exists already or not.
        @param $iParenPostID  This is ID of post with which this image from URL will be attached.
        @param $bSetThumbnail This is the boolean which can be used to tell system to set Thumbnail of Post which is given via $iParenPostID

        @return $iAttachmentID  It will return ID of attachment which is made via URL after upload. If image URL and size are same then image will be not uploaded twice same but it will return ID of attachment which was uploaded previously of that image URL.
        */
        public function iUploadImageByURL($sURL, $bMetaData = false, $bReplaceUpload = false, $iParenPostID = 0, $bSetThumbnail = true)
        {
            $iAttachmentID = false;

            $aUploadFile = $this->aUploadFileOnServerViaURL($sURL);

            if (!$bReplaceUpload) {
                $iAttachmentID = $this->iGetAttachmentIDFromURL($sURL, $aUploadFile);
            }

            //  Uploading Image
            if ($iAttachmentID === false) {
                $iAttachmentID = $this->iUploadImageFromURL($sURL, $aUploadFile, $bMetaData, $iParenPostID, $bSetThumbnail);
            } else {
                unlink($aUploadFile[ 'FilePath' ]);
            }

            return $iAttachmentID;
        }

        /*
            This function is general purpose function and can be used standalone.
            This function will return size of image after calculating from URL of image.
            This is not basically download file to check size instead that it will check directly from URL before downloading file.

            @since 1.0

            @param $sURL This is URL of which user can check size of image.

            @return $iSize Size of image from URL will be returned in bytes.
        */
        public function iGetFileSize($sURL)
        {
            $aHead = array_change_key_case(get_headers($sURL, 1));
            // content-length of download (in bytes), read from Content-Length: field

            $iSize = isset($aHead[ 'content-length' ]) ? $aHead[ 'content-length' ] : 0;

            // cannot retrieve file size, return "-1"
            if (!$iSize) {
                return false;
            }

            return $iSize;
            // return size in bytes
        }

        /*
            This function will help to get size of file or image in measures.

            @since 1.0

            @param $iSize This is size of file or image in bytes

            @return $sSize This will return size of image in measurable entities.
        */
        public function sFormatSize($iSize)
        {
            $sSize = $iSize;
            switch ($iSize) {
                case $iSize < 1024:
                    $sSize = $iSize . ' B';
                    break;
                case $iSize < 1048576:
                    $sSize = round($iSize / 1024, 2) . ' KB';
                    break;
                case $iSize < 1073741824:
                    $sSize = round($iSize / 1048576, 2) . ' MB';
                    break;
                case $iSize < 1099511627776:
                    $sSize = round($iSize / 1073741824, 2) . ' GB';
                    break;
            }

            return $sSize;
            // return formatted size
        }

        /*
            This is a private function used to check if URL of image is already been uploaded using defined key in contruct function.
            If any image is deleted from WordPress admin panel or from uploads folder then this function will also remove details of already uploaded file because file existance and WordPress attachment is mandatory to be used.

            @since 1.0

            @param $sURL This is URL of image that is going to be checked.
            @param $aUploadFile This is an array in which there is index FileSize which is storing size of image URL in bytes.

            @return $iAttachmentID  This function will return attachment ID of image if that exists in system, if not then it will return FALSE
        */
        private function iGetAttachmentIDFromURL($sURL, $aUploadFile = array())
        {
            $aUploadedFiles = get_option($this->sKey);

            $bFileFound    = false;
            $iAttachmentID = false;
            $bUpdateRecord = false;

            $iFileSize = false;

            if ($aUploadFile) {
                $iFileSize = $aUploadFile[ 'FileSize' ];
            }
            if (!$iFileSize) {
                $iFileSize = $this->iGetFileSize($sURL);
            }

            if ($aUploadedFiles !== false && count($aUploadedFiles)) {
                if (isset($aUploadedFiles[ $sURL ]) && count($aUploadedFiles[ $sURL ]) > 0) {
                    foreach ($aUploadedFiles[ $sURL ] as $iIndex => $aFile) {

                        //  File Found
                        $bFileFound = true;

                        //  Check if attachment id is not deleted or media is not deleted
                        if ($bFileFound) {
                            $oPost = get_post($aUploadedFiles[ $sURL ][ $iIndex ][ 'AttachID' ]);
                            if (gettype($oPost) != "object") {
                                $bFileFound    = false;
                                unset($aUploadedFiles[ $sURL ][ $iIndex ]);
                                $bUpdateRecord = true;
                            }
                        }

                        //  Check if file exists of this attachment
                        if ($bFileFound) {
                            $bFileExists = file_exists($aUploadedFiles[ $sURL ][ $iIndex ][ 'FilePath' ]);
                            if (!$bFileExists) {
                                $bFileFound    = false;
                                unset($aUploadedFiles[ $sURL ][ $iIndex ]);
                                $bUpdateRecord = true;
                            }
                        }

                        //  Check File Size Is Change
                        if ($bFileFound) {
                            if ($iFileSize != $aUploadedFiles[ $sURL ][ $iIndex ][ 'FileSize' ]) {
                                $bFileFound = false;
                            }
                        }

                        if ($bFileFound) {
                            $iAttachmentID = $aUploadedFiles[ $sURL ][ $iIndex ][ 'AttachID' ];
                            break;
                        }
                    }
                }
            }

            if ($bUpdateRecord) {
                update_option($this->sKey, $aUploadedFiles);
            }

            return $iAttachmentID;
        }

        /*
            This function is private used to store image details that is uploaded in with of URL that is given.

            @since 1.0

            @param $sURL This is URL of image that is uploaded in WOrdPress.
            @param $aDetails This is an array which set details stored of image that is uploaded.

            @return boolen it will return number of records updated. and false then on failure.
        */
        private function bAddImageDetails($sURL, $aDetails)
        {
            $aUploadedFiles = get_option($this->sKey);

            if ($aUploadedFiles === false) {
                $aUploadedFiles = array();
            }

            $aUploadedFiles[ $sURL ][] = $aDetails;

            return update_option($this->sKey, $aUploadedFiles);
        }

        /*
            This function is programmed to upload image from URL

            @since 1.0

            @param $sURL This is URL of image that is going to be uploaded.
            @param $aUploadFile This is an array which has details of image that is being uploaded.
            @param $bMetaData This is boolean used if user wants to make meta data of attachment of image.
            @param $iParenPostID  This is ID of post with which this image from URL will be attached.
            @param $bSetThumbnail This is the boolean which can be used to tell system to set Thumbnail of Post which is given via $iParenPostID

            @return $iAttachmentID  It will return ID of attachment which is made via URL after upload. If image URL and size are same then image will be not uploaded twice same but it will return ID of attachment which was uploaded previously of that image URL.
        */
        private function iUploadImageFromURL($sURL, $aUploadFile, $bMetaData = false, $iParenPostID = 0, $bSetThumbnail = true)
        {
            $iFileSize = $aUploadFile[ 'FileSize' ];
            $sFileName = $aUploadFile[ 'FileName' ];
            $sFilePath = $aUploadFile[ 'FilePath' ];
            //  When file could not be uploaded return false.
            if ($iFileSize === false) {
                return false;
            }

            $iAttachmentID = $this->iMakeAttchementOfFile($sFileName, $sFilePath, $iParenPostID, $bMetaData);
            $iMetaDataID   = 0;
            if ($bMetaData) {
                $iMetaDataID   = $this->iSetAttachmentThumbnailMetaData($iAttachmentID, $sFilePath);
            }

            if ($bSetThumbnail && $iParenPostID > 0) {
                $result = set_post_thumbnail($iParenPostID, $iAttachmentID);
            }

            $aUploadFile[ 'AttachID' ] = $iAttachmentID;
            $aUploadFile[ 'MetaID' ]   = $iMetaDataID;

            $this->bAddImageDetails($sURL, $aUploadFile);

            return $iAttachmentID;
        }

        /*
            This function will make thumbnails of image and store in attachment meta data.

            @since 1.0

            @param $iAttachmentID This is the attachment ID of image that is uploaded in WOrdPress.
            @param $sFilePath THis is the absolute path of image file stored in WordPress uploads folder. Path must be valid.

            @return $iMetaID The meta ID of attachment will be returned.
        */
        private function iSetAttachmentThumbnailMetaData($iAttachmentID, $sFilePath)
        {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($iAttachmentID, $sFilePath);

            //  Returns meta_id if the meta doesn't exist, otherwise returns true on success and false on failure.
            $iMetaID = wp_update_attachment_metadata($iAttachmentID, $attach_data);

            return $iMetaID;
        }

        /*
            This function will help to make attachment in WordPress of file that is uploaded in Uploads folder of WordPress.

            @since 1.0

            @param $sFileName  This is the file name of which attachment will be created.
            @param $sFilePath  This is the absolute path of file that dwells in your WordPress uploads folder.
            @param $iParenPostID  This is the ID of post with which attachment will be linked. If set 0 then it will just make attachment but it will not link with existing post.

            @return $iAttachmentID This is attachment ID which is generated for the image file.
        */
        private function iMakeAttchementOfFile($sFileName, $sFilePath, $iParenPostID = 0)
        {
            $wp_filetype = wp_check_filetype($sFileName, null);
            $attachment  = array(
                'post_mime_type' => $wp_filetype[ 'type' ],
                'post_title'     => sanitize_file_name($sFileName),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            //  $iParenPostID
            //  Attachments may be associated with a parent post or page. Specify the parent's post ID, or 0 if unattached. Default: 0

            $iAttachmentID = wp_insert_attachment($attachment, $sFilePath, $iParenPostID);

            return $iAttachmentID;
        }

        /*
            This is the main file which uploads file from URL to the WordPress uploads folder.

            @since 1.0

            @param $sURL This is URL of image that will be downloaded to WordPress.

            @return array This will return an array with all details required for file that is uploaded to server.
        */
        private function aUploadFileOnServerViaURL($sURL)
        {
            $aUploadDir = wp_upload_dir();
            $dImageData = wp_remote_retrieve_body(wp_remote_get($sURL));
            $sURL       = urldecode($sURL);
            $iRandom    = time() . mt_rand(2000, 9999);
            $sFileName  = $iRandom . "-" . basename($sURL);

            if (wp_mkdir_p($aUploadDir[ 'path' ])) {
                //  Current set folder in uploads folder like 11 or 12 which are stored month wise
                $sFilePath = $aUploadDir[ 'path' ] . '/' . $sFileName;
                $sFileURL  = $aUploadDir[ 'url' ] . '/' . $sFileName;
            } else {
                //  Main upload folder
                $sFilePath = $aUploadDir[ 'basedir' ] . '/' . $sFileName;
                $sFileURL  = $aUploadDir[ 'baseurl' ] . '/' . $sFileName;
            }

            $iFileSize = file_put_contents($sFilePath, $dImageData);

            return array( "FileSize" => $iFileSize, "FileName" => $sFileName, "FilePath" => $sFilePath, "FileURL" => $sFileURL );
        }
    }
}
