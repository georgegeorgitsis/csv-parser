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
     * Filter data in row. In this case, we only trim. We can add more filters in a later phase
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

        return TRUE;
    }

    
    /**
     * Check if the remote file exists and is image
     * 
     * @param type $url
     * @return boolean
     */
    public function checkRemoteFile($url) {
        $url = str_replace("https", "http", $url); //Without SSL it is forbidden to curl to image. Temporary replace https with http

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        
        $type = split('/', $contentType);
        if ($type[0] == "image")
            return array('type'=>$type[1],'');
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
            if ($image['title'] == $row['title']) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Create a new UUID for each image
     * @return type 
     */
    public function get_uuid() {
        return uniqid(rand(), TRUE);
    }

}
