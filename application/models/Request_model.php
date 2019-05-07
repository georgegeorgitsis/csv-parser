<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Request_model
 *
 * @author @GeorgeGeorgitsis
 */
class Request_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert single request in db
     *
     * @param type $request
     *
     * @return boolean
     */
    public function insertRequest($request)
    {
        $this->db->insert('requests', $request);
        if ($this->db->affected_rows() == 1) {
            return true;
        }

        return false;
    }

    /**
     * Get all requests per status.
     *
     * @param type $status
     *
     * @return boolean
     */
    public function getRequests($status)
    {
        $qry = $this->db->select('*')
            ->from('requests')
            ->where('status', $status)
            ->get();
        if ($qry->num_rows() > 0) {
            return $qry->result_array();
        }

        return false;
    }

    /**
     * Update request data per request UUID
     *
     * @param type $request_uuid
     * @param type $request_data
     *
     * @return boolean
     */
    public function updateRequestData($request_uuid, $request_data)
    {
        $this->db->where('uuid', $request_uuid)->update('requests', $request_data);
        if ($this->db->affected_rows() == 1) {
            return true;
        }

        return false;
    }

}
