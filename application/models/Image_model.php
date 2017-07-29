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
class Image_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * @return result_array or boolean
     */
    public function getImages() {
        $this->db->select('images.uuid,images.title,images.remote_url as url,images.description')
                ->from('images')
                ->where('is_deleted', 0)
                ->order_by('created_date');

        $qry = $this->db->get();
        if ($qry->num_rows() > 0)
            return $qry->result_array();
        return FALSE;
    }

    /**
     * 
     * @param type $uuid
     * @param type $title
     * @return row_array or boolean
     */
    public function getImage($uuid = null, $title = null) {
        $this->db->select('images.uuid,images.title,images.description,images.local_name,images.type')
                ->from('images')
                ->where('is_deleted', 0);

        if (!is_null($uuid)) //if uuid passed add it to where
            $this->db->where('uuid', $uuid);

        if (!is_null($title)) //if title passed add it to where
            $this->db->where('title', $title);

        $qry = $this->db->get();

        if ($qry->num_rows() == 1)
            return $qry->row_array();
        return FALSE;
    }

    public function batchInsertImages($images) {
        $this->db->trans_start();
        $this->db->insert_batch('images', $images);
        $this->db->trans_commit();
    }

}
