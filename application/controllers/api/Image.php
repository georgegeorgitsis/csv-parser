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

    protected $images_to_upload = array(); //variable to add the images to batch insert in db, instead of inserting 1 by 1 for time reasons.

    function __construct() { // Construct the parent class, load models and the library
        parent::__construct();
        $this->load->model('Image_model');
        $this->load->model('Request_model');
        $this->load->library('Image_library');
    }

    /**
     * URL: image/insert
     * METHOD: POST
     * GENERAL DESCRIPTION: Upload the images through csv file. 
     */
    public function insert_post() {
        $csv_result = $this->handleCSV(); //Validate the csv. It will not continue if there are errors.
        if (!$request_id = $this->saveUploadRequest($csv_result)) { //Save the request in db.
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR); //Unable to save in db, HTTP CODE 500
            die(); //Instead of die, log to file with the error
        }
       
        $csv = fopen($csv_result, 'r'); //open the local csv file

        $first_row = true; //It is a requirement that the first row of the csv must be ignored. We can first delete the first row and then save the csv or skip the first row, like here.
        while ($row = fgetcsv($csv, 1000, '|')) { //delimeter is hardcoded as `|`. Can be defined in constants.
            if ($first_row) { //skip the first row
                $first_row = false;
                continue;
            }

            $filtered_row = $this->image_library->sanitizeData($row); //Sanitize the data before any action.
            if (!$this->image_library->validateRow($filtered_row[0], $filtered_row[1])) //Check if row is valid per title and url.
                continue;

            if (!$this->isDuplicatedTitle($row)) { //Check if title is duplicated
                $row_data = $this->image_library->prepareRow($this->images_to_upload, $filtered_row); //Create array based on database to use Active Record.
                $row_data['request_uuid'] = $request_id; //add in which request_id the images belong.
                array_push($this->images_to_upload, $row_data); //push the image in this row in images_to_upload, for batch insert
            }
        }
        
        if ($this->Image_model->batchInsertImages($this->images_to_upload)) { //batch insert images. If success, return images. Otherwise HTTP CODE 500.
            $images = $this->Image_model->getImages($request_id); //Return the saved images
            $this->set_response($images, REST_Controller::HTTP_CREATED); // CREATED (201) being the HTTP response code
        } else {
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR); //Unable to save images in db, HTTP CODE 500
            die(); //Instead of die, log to file with the error
        }
    }

    /**
     * URL: image/getImages
     * METHOD: GET
     * GENERAL DESCRIPTION: Get all images.
     * 
     * TODO: Should use some kind of API_KEY or REQUEST_UUID to return images per user.
     * For test reasons we return all images in db.
     * 
     */
    public function getImages_get() {
        $images = $this->Image_model->getImages(); //Get all images from DB. Check TODO.

        if ($images && !empty($images)) { //If images to return
            $this->response($images, REST_Controller::HTTP_OK); //Found images, HTTP CODE 200
        } else {
            $this->response(['status' => FALSE, 'error' => 'No images found'], REST_Controller::HTTP_NOT_FOUND); //No images found, HTTP CODE 404
        }
        die();
    }

    /**
     * URL: images/getImage
     * METHOD: GET
     * GENERAL DESCRIPTION: Get single image per UUID.
     * 
     * TODO: Should access other identifiers to get an image.
     * TODO: Check if local image exists, otherwise return the remote url
     * 
     * For test reasons we accept only uuid parameter.
     * For test reasons we assume that local image is always available.
     */
    public function getImage_get() {
        $uuid = trim($this->get('uuid')); //
        if (!$uuid) { //Check the uuid get parameter
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST); // No uuid parameter, HTTP CODE 400
            die(); //Instead of die, log to file with the error
        }

        $image = $this->Image_model->getImage($uuid); //find the image based on uuid

        if ($image && !empty($image)) { //If image to return, return the local url of the image.
            $image['url'] = base_url('images/' . $image['local_name']); //Create local url of the image. 
            unset($image['local_name']); //No reason to return the image local name
            $this->response($image, REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => FALSE, 'error' => 'No image found'], REST_Controller::HTTP_NOT_FOUND); //No image found, HTTP CODE 404
        }
        die();
    }

    /**
     * Handles the uploaded csv file.
     * 
     * @return string
     */
    private function handleCSV() {
        if (!isset($_FILES['images']) || empty($_FILES)) { //Check the $_FILES for the csv. The csv must be uploaded in key `images`.
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST); // No csv file uploaded, HTTP CODE 400
            die(); //Instead of die, log to file with the error
        }

        $target_dir = $this->config->item('download_csv_dir'); //Find in /applications/config/config.php the csv_dir
        $file_name = $this->image_library->get_uuid() . '_' . basename($_FILES['images']['name']); //create unique name for the csv
        $target_file = $target_dir . $file_name; //prepare the file to save
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION); //find the path of the file. 

        if ($imageFileType != 'csv') { //Check the file type
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST); // The file is not csv, HTTP CODE 400
            die(); //Instead of die, log to file with the error
        }

        if (!move_uploaded_file($_FILES['images']['tmp_name'], $target_file)) { //Upload the file. If failed, HTTP CODE 500.
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR); //Unable to upload csv file, HTTP CODE 500
            die(); //Instead of die, log to file with the error
        }
        return $target_file; //return the filepath
    }

    /**
     * Function to save the upload csv request. 
     * 
     * TODO: Save all other requests in db too and not only the POST.
     * 
     * @param type $csv_file
     * @return boolean|varchar
     */
    private function saveUploadRequest($csv_file) {
        $request_data['csv_file'] = pathinfo($csv_file, PATHINFO_BASENAME); //save only the basename of csv
        $request_data['UUID'] = $this->image_library->get_uuid(); //create uuid for the request
        $request_data['request'] = 'upload_images'; //Hardcoded data. Check TODO.
        $request_data['method'] = 'POST'; //Hardcoded data. Check TODO.
        $request_data['status'] = 0; //Status is in_progress. Can be defined in constants.

        if ($this->Request_model->insertRequest($request_data)) //Insert the request in db
            return $request_data['UUID'];
        return FALSE;
    }

    /**
     * As per requirement "If a consecutive load of the data contains already existing elements, the data will be overwritten with the newest version"
     * check if the title is duplicated in images to be uploaded.
     * If is duplicated, replace existing data with new.
     * 
     * @param type $row
     * @return boolean
     */
    public function isDuplicatedTitle($row) {
        if (empty($this->images_to_upload)) //Nothing to compare
            return FALSE;

        foreach ($this->images_to_upload as $image_key => $image) { //For each of images to upload, check the exact title
            if ($image['title'] == $row[0]) { //If title is duplicated, replace data with new
                $this->images_to_upload[$image_key]['url'] = $row[1];
                $this->images_to_upload[$image_key]['description'] = $row[2];
                break;
                return TRUE;
            }
        }
        return FALSE;
    }

}
