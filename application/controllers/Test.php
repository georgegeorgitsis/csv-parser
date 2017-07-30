<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Test
 *
 * @author @GeorgeGeorgitsis
 */
class Test extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Example to test POST API CALL. 
     * 
     * Sample csv must be located in /csv_files directory 
     */
    public function postCSV() {
        $csv_path = $this->config->item('download_csv_dir');
        $csv_file = $csv_path . 'images_data.csv';
        $post = array('images' => $csv_file);

        $url = base_url('api/image/insert');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        echo $result;

        curl_close($ch);
    }

    /**
     * Example to test GET API CALL for all images
     */
    public function getImages() {
        $url = base_url('api/image/getImages');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);

        echo $result;
        curl_close($ch);
    }

    /**
     * Because UUID of images is generated on the fly
     * this function will not work without valid uuid in db
     */
    public function getImage() {
        $url = base_url('api/image/getImage?uuid=123');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);

        echo $result;
        curl_close($ch);
    }

}
