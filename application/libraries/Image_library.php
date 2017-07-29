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
     * Create a new UUID for each image
     * @return type 
     */
    protected function get_uuid() {
        return uniqid(rand(), TRUE);
    }

    /**
     * Validate a row. Check if image_title and image_url exists as strings. 
     * Also check if the image url exists.
     * 
     * @param type $title
     * @param type $url
     * @return boolean
     */
    protected function validateRow($title, $url) {
        if ($title == "" || $url == "")
            return FALSE;
        return $this->checkRemoteFile($url);
    }

    /**
     * Check if the remote file exists.
     * 
     * @param type $url
     * @return boolean
     */
    private function checkRemoteFile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1); //no need to download the file. 
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (curl_exec($ch) !== FALSE)
            return true;
        return false;
    }

}
