<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Image_model
 *
 * @author GeorgeGeorgitsis
 */
class Image_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Using CASE to return in_progress, failed or completed download. Should use constants and parse it in Controller.
     * @return result_array or boolean
     */
    public function getImages($request_uuid = null)
    {
        $this->db->select("images.uuid,images.title,images.remote_url as url,images.description,"
            . "CASE status WHEN 0 THEN 'in_progress'
                     WHEN 1 THEN 'failed'
                     ELSE 'completed'
    END as status")
            ->from('images')
            ->where('is_deleted', 0)
            ->order_by('created_date');

        if (!is_null($request_uuid)) {
            $this->db->where('request_uuid', $request_uuid);
        }

        $qry = $this->db->get();
        if ($qry->num_rows() > 0) {
            return $qry->result_array();
        }

        return false;
    }

    /**
     *
     * @param type $uuid
     * @param type $title
     *
     * @return row_array or boolean
     */
    public function getImage($uuid)
    {
        $this->db->select("images.uuid,images.title,images.remote_url as url,images.description,images.local_name,"
            . "CASE status WHEN 0 THEN 'in_progress'
                     WHEN 1 THEN 'failed'
                     ELSE 'completed'
    END as status")
            ->from('images')
            ->where('is_deleted', 0);

        if (!is_null($uuid)) //if uuid passed add it to where
        {
            $this->db->where('uuid', $uuid);
        }

        $qry = $this->db->get();

        if ($qry->num_rows() == 1) {
            return $qry->row_array();
        }

        return false;
    }

    /**
     * Batch insert images in `images` table using transaction.
     *
     * @TODO: Rollback is failure
     *
     * @param type $images
     */
    public function batchInsertImages($images)
    {
        $this->db->trans_start();
        $this->db->insert_batch('images', $images);
        $this->db->trans_commit();

        return true;
    }

    /**
     * Update image data based on UUID of image
     *
     * @param type $image_uuid
     * @param type $image_data
     *
     * @return boolean
     */
    public function updateImageData($image_uuid, $image_data)
    {
        $this->db->where('uuid', $image_uuid)->update('images', $image_data);
        if ($this->db->affected_rows() == 1) {
            return true;
        }

        return false;
    }

}
