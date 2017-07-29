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
     * Filter data in row. In this case, we only trim. We can add more filters in a later phase for security reasons and more.
     * 
     * @param type $row
     * @return type
     */
    public function filterData($row) {
        foreach ($row as $row_key => $row_val) { //loop through data and trim 
            $row[$row_key] = trim($row_val);
        }
        return $row;
    }

    /**
     * Validate a row. Check if image_title and image_url exists as strings. 
     * Also check if the image url is valid and exists.
     * 
     * @param type $title
     * @param type $url
     * @return boolean
     */
    public function validateRow($title, $url) {
        if ($title == "" || $url == "") //check if title and url are empty
            return FALSE;

        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) //check if the url is valid
            return FALSE;

        if (!$image_headers = $this->checkRemoteFile($url))
            return FALSE;

        return $image_headers;
    }

    /**
     * Check if the remote file exists and is an image
     * 
     * @param type $url
     * @return array or boolean
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
         * Here we check the from headers and return type and name through path info
         */
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        $type = explode('/', $contentType);
        $filePath = $this->filePath($url);
        if ($type[0] == "image") {
            if ($local_name = $this->downloadImage($url, $filePath['basename'], $filePath['extension']))
                return array('type' => $filePath['extension'], 'size' => $contentLength, 'name' => $filePath['basename'], 'local_name' => $local_name);
        }
        return FALSE;
    }

    /**
     * Create the array for the db
     * 
     * @param type $row
     * @return type
     */
    public function manipulateRow($images, $row) {
        if (!$this->checkDuplicateTitle($images, $row))
            return FALSE;

        //prepare the array for db
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

    public function downloadImage($url, $name, $type) {
        $this->ci = & get_instance();
        $download_dir = $this->ci->config->item('download_dir');
        if ($download_dir != "" && $download_dir) {
            $local_name = $this->get_uuid() . $name;
            $fullpath = $download_dir . '/' . $local_name;
            copy($url, $fullpath);
            return $local_name;
        }
        return FALSE;
    }

}
