<?php

/**
 * Description of Image
 *
 * @author GeorgeGeorgitsis
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

class Image extends REST_Controller
{

    protected $images_to_upload = array();

    function __construct()
    {
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
    public function insert_post()
    {
        $csv_result = $this->handleCSV();
        if (!$request_id = $this->saveUploadRequest($csv_result)) {
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        $csv = fopen($csv_result, 'r');

        $first_row = true;
        while ($row = fgetcsv($csv, 1000, '|')) {
            if ($first_row) {
                $first_row = false;
                continue;
            }

            $filtered_row = $this->image_library->sanitizeData($row); //Sanitize the data before any action.
            if (!$this->image_library->validateRow($filtered_row[0], $filtered_row[1])) {
                continue;
            }

            if (!$this->isDuplicatedTitle($row)) {
                $row_data = $this->image_library->prepareRow($this->images_to_upload, $filtered_row);
                $row_data['request_uuid'] = $request_id;
                array_push($this->images_to_upload, $row_data);
            }
        }

        if ($this->Image_model->batchInsertImages($this->images_to_upload)) {
            $images = $this->Image_model->getImages($request_id);
            $this->set_response($images, REST_Controller::HTTP_CREATED);
        } else {
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * URL: image/getImages
     * METHOD: GET
     * GENERAL DESCRIPTION: Get all images.
     *
     * @TODO: Should use some kind of API_KEY or REQUEST_UUID to return images per user.
     * For test reasons we return all images in db.
     *
     */
    public function getImages_get()
    {
        $images = $this->Image_model->getImages();

        if ($images && !empty($images)) {
            $this->response($images, REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'error' => 'No images found'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * URL: images/getImage
     * METHOD: GET
     * GENERAL DESCRIPTION: Get single image per UUID.
     *
     * @TODO: Should access other identifiers to get an image.
     * @TODO: Check if local image exists, otherwise return the remote url
     *
     * For test reasons we accept only uuid parameter.
     * For test reasons we assume that local image is always available.
     */
    public function getImage_get()
    {
        $uuid = trim($this->get('uuid'));
        if (!$uuid) {
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST);
        }

        $image = $this->Image_model->getImage($uuid);

        if ($image && !empty($image)) {
            $image['url'] = base_url('images/' . $image['local_name']);
            unset($image['local_name']);
            $this->response($image, REST_Controller::HTTP_OK);
        } else {
            $this->response(['status' => false, 'error' => 'No image found'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /**
     * Handles the uploaded csv file.
     *
     * @return string
     */
    private function handleCSV()
    {
        if (!isset($_FILES['images']) || empty($_FILES)) {
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST);
        }

        $target_dir = $this->config->item('download_csv_dir'); //Find in /applications/config/config.php the csv_dir
        $file_name = $this->image_library->get_uuid() . '_' . basename($_FILES['images']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

        if ($imageFileType != 'csv') {
            $this->set_response(array(), REST_Controller::HTTP_BAD_REQUEST);
        }

        if (!move_uploaded_file($_FILES['images']['tmp_name'], $target_file)) {
            $this->set_response(array(), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $target_file;
    }

    /**
     * Function to save the upload csv request.
     *
     * TODO: Save all other requests in db too and not only the POST.
     *
     * @param type $csv_file
     *
     * @return boolean|varchar
     */
    private function saveUploadRequest($csv_file)
    {
        $request_data['csv_file'] = pathinfo($csv_file, PATHINFO_BASENAME);
        $request_data['UUID'] = $this->image_library->get_uuid();
        $request_data['request'] = 'upload_images';
        $request_data['method'] = 'POST';
        $request_data['status'] = 0;

        if ($this->Request_model->insertRequest($request_data)) {
            return $request_data['UUID'];
        }

        return false;
    }

    /**
     * As per requirement "If a consecutive load of the data contains already existing elements, the data will be overwritten with the newest version"
     * check if the title is duplicated in images to be uploaded.
     * If is duplicated, replace existing data with new.
     *
     * @param type $row
     *
     * @return boolean
     */
    public function isDuplicatedTitle($row)
    {
        if (empty($this->images_to_upload)) {
            return false;
        }

        foreach ($this->images_to_upload as $image_key => $image) {
            if ($image['title'] == $row[0]) {
                $this->images_to_upload[$image_key]['url'] = $row[1];
                $this->images_to_upload[$image_key]['description'] = $row[2];
                break;

                return true;
            }
        }

        return false;
    }

}
