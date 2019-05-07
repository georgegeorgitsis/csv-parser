<?php

/**
 * Description of Download_images
 *
 * @author @GeorgeGeorgitsis
 */
class Download_images extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Image_model');
        $this->load->model('Request_model');
        $this->load->library('Image_library');
    }

    /**
     * Internal function to process requests for POST csv files and NOT API function.
     * This function will run with CRON.
     * Should use authentication in order to allow to exec it.
     *
     * GENERAL DESCRIPTION:
     *      For each in_progress request in `requests` in db, find the related images in `images` in db, based on `request_uuid` field
     *      and validate the images.
     *      If image is valid, status of image is 2 `completed` and row in db is updated with headers and information of the image.
     *      If image is not valid or not downloaded, status of image is 1 `failed`
     *
     * @TODO: Check for images update failures
     * @TODO: Check for requests update failures
     */
    public function processRequests()
    {
        $requests = $this->Request_model->getRequests(0);
        if (!$requests || empty($requests)) {
            return;
        }

        foreach ($requests as $request) {
            $images = $this->Image_model->getImages($request['UUID']);

            foreach ($images as $image) {
                if (!$image_data = $this->image_library->checkRemoteFile($image['url'])) {
                    $image_data['status'] = 1;
                } else {
                    $image_data['status'] = 2;
                }
                $image_data['updated_date'] = date('Y-m-d H:i:s');
                $this->Image_model->updateImageData($image['uuid'], $image_data);
            }

            $this->Request_model->updateRequestData($request['UUID'], array('status' => 2, 'updated_date' => date('Y-m-d H:i:s')));
        }
    }

}
