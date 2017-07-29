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

    protected $images_to_upload = array();

    function __construct() { // Construct the parent class, load the model and the library
        parent::__construct();
        $this->load->model('Image_model');
        $this->load->library('Image_library');
    }

    /**
     * URL: image/insert
     * METHOD: POST
     * GENERAL DESCRIPTION: Upload the images through csv file. 
     * 
     * 
     */
    public function insert_post() {
        $image = $_FILES['images'];
        $csv = fopen($image['tmp_name'], 'r');

        $row_counter = 1;
        while ($row = fgetcsv($csv, 1000, '|')) {
            if ($row_counter == 1) { //It is a requirement that the first row of the csv must be ignored.
                $row_counter++;
                continue;
            }

            $filtered_row = $this->image_library->filterData($row); //filter the data before any action.

            if ($this->image_library->validateRow($filtered_row[0], $filtered_row[1])) { //Check if row is valid per title and url.
                array_push($this->images_to_upload, $this->image_library->manipulateRow($this->images_to_upload, $filtered_row));
            }

            $row_counter++;
        }


        var_dump($this->images_to_upload);
        //$this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    private function checkImageDB($title) {
        return ($this->image_model->getImage(null, $title)) ? TRUE : FALSE;
    }

}
