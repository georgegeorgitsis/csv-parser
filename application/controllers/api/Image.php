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

            if ($image_headers = $this->image_library->validateRow($filtered_row[0], $filtered_row[1])) { //Check if row is valid per title and url. Check if image is valid and return headers.
                $row_data = $this->image_library->manipulateRow($this->images_to_upload, $filtered_row);
                $image_data = array_merge($row_data, $image_headers);
                array_push($this->images_to_upload, $image_data);
            }

            $row_counter++;
        }


        var_dump($this->images_to_upload);

        $this->Image_model->batchInsertImages($this->images_to_upload);
//$this->set_response($message, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
    }

    public function getImages_get() {
        $images = $this->Image_model->getImages();

        if ($images && !empty($images)) {
            $this->response($images, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'error' => 'No images found'
                    ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function getImage_get() {
        $uuid = trim($this->get('uuid'));

        $image = $this->Image_model->getImage($uuid, null);

        if ($image && !empty($image)) {
            $image['url'] = base_url('images/' . $image['local_name']);
            unset($image['local_name']);
            $this->response($image, REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'error' => 'No image found'
                    ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

}
