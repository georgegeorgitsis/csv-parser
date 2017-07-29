<?php

/**
 * Description of Image
 *
 * @author GeorgeGeorgitsis
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class Image extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->load->helper('utils');
        $this->load->library('Image_library');

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key
    }

    /**
     * SEGMENTS OF URL AFTER base_url(): image/insert
     * METHOD: POST
     * GENERAL DESCRIPTION: Get the csv file through $_FILES and insert into database
     */
    public function insert_post() {
        $image = $_FILES['images'];
        $csv = fopen($image['tmp_name'], 'r');

        $row = 1;
        $images_temp = array();
        while ($row = fgetcsv($csv, 1000, '|')) {
            if ($row == 1) //It is a requirement that the first row of the csv must be ignored
                continue;

            $valid_row = $this->image_library->validateRow(trim($row[0]), trim($row[1])); //Check if row is valid. A valid row must have an image URL and a title

            $row_image = array();
            $row_image['uuid'] = $this->utils->get_uuid();
            $row_image['title'] = trim($row[0]);
            $row_image['remote_url'] = trim($row[1]);
            var_dump($row);

            $row++;
        }

        $this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

}
