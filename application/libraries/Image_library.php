<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Image_library
 *
 * @author @GeorgeGeorgitsis
 */
class Image_library {

    /**
     * Sanitize data in row. In this case, we only trim. We can add more filters in a later phase for security reasons and more.
     * 
     * @param type $row
     * @return type
     */
    public function sanitizeData($row) {
        foreach ($row as $row_key => $row_val) { //loop through data and trim 
            $row[$row_key] = trim($row_val);
        }
        return $row;
    }

    /**
     * Validate a row. Check if image_title and image_url exists. All other validations we decide to add, should be placed here.
     * Because some images on web, don't have the .format, we let them pass the validation.
     * 
     * The CRON will handle if the URL is valid and valid image or not. If it passes the filter_var we accept it.
     * If it is not valid URL or valid image, the CRON will update to status=1 `failed`
     * 
     * @param type $title
     * @param type $url
     * @return boolean
     */
    public function validateRow($title, $url) {
        if ($title == "" || $url == "") //check if title and url are empty
            return FALSE;

        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) //check if url's syntax
            return FALSE;

        return TRUE;
    }

    /**
     * Prepare the array for db.
     * 
     * As per requirement "If a consecutive load of the data contains already existing elements, the data will be overwritten with the newest version"
     * check for this csv only and NOT IN DB for other images, if the title is duplicated in images to be uploaded.
     * 
     * @param type $row
     * @return type
     */
    public function prepareRow($images, $row) {
        if (!$this->checkDuplicateTitle($images, $row)) //check dublicate titles
            return FALSE;

        //prepare the array for db to be inserted. $row[0],[1] etc. can be defined in constants.
        $array['uuid'] = $this->get_uuid(); //get the UUID
        $array['title'] = $row[0];
        $array['remote_url'] = $row[1];
        $array['description'] = (isset($row[2]) && $row[2] != "") ? $row[2] : "";

        return $array;
    }

    /**
     * As per requirement "If a consecutive load of the data contains already existing elements, the data will be overwritten with the newest version"
     * check if the title is duplicated in images to be uploaded.
     * 
     * @param type $images
     * @param type $row
     * @return boolean
     */
    public function checkDuplicateTitle($images, $row) {
        if (empty($images))
            return TRUE;

        foreach ($images as $image) {
            if ($image['title'] == $row[0]) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Returns information of the file.
     * 
     * @param type $filePath
     * @return type
     */
    public function filePath($filePath) {
        $fileParts = pathinfo($filePath);

        if (!isset($fileParts['filename'])) {
            $fileParts['filename'] = substr($fileParts['basename'], 0, strrpos($fileParts['basename'], '.'));
        }

        return $fileParts;
    }

    /**
     * Create a new UUID for each image. Remove the dot created by default when you pass prefix in uniqid.
     * @return type 
     */
    public function get_uuid() {
        return str_replace('.', '', uniqid(rand(), TRUE));
    }

    /**
     * Check if the remote file exists and is an image.
     * 
     * Download image and return headers and information.
     * 
     * @param type $url
     * @return array|boolean
     */
    public function checkRemoteFile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $image = curl_exec($ch);

        /*
         * Check content type of image from headers.
         * We can use both pathinfo and content types to be sure about the image type
         * because some servers send raw data for images or different content types.
         * Also some images on web doesn't have the .type of them and we choose to download them.
         * Here we check the $type headers but return type and name through path info
         */
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        $type = explode('/', $contentType);
        $filePath = $this->filePath($url);
        if ($type[0] == "image") {
            if ($local_name = $this->copyImage($url, $filePath['basename'], $filePath['extension'])) //Check if image is downloaded
                return array('type' => $filePath['extension'], 'size' => $contentLength, 'name' => $filePath['basename'], 'local_name' => $local_name); //return info of image
        }
        return FALSE;
    }

    /**
     * Download the image from URL and upload it to server.
     * 
     * We use the copy() function of PHP to download image.
     * 
     * TODO: use CURL to download image for security reasons and validations.
     * 
     * @param type $url
     * @param type $name
     * @param type $type
     * @return boolean|string
     */
    public function copyImage($url, $name, $type) {
        $this->ci = & get_instance();
        $download_dir = $this->ci->config->item('download_images_dir'); //Get path to download images from application/config/config.php
        if ($download_dir != "" && $download_dir && is_dir($download_dir)) { //If the PATH is set
            $local_name = $this->get_uuid() . $name; //Create unique local name of image. An image with the same name can be in many URLs.
            $fullpath = $download_dir . '/' . $local_name;
            copy($url, $fullpath); //Copy image to PATH. Check TODO.
            return $local_name;
        }
        return FALSE;
    }

}
