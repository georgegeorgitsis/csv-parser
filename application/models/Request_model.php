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
class Request_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function insertRequest($request) {
        $this->db->insert('requests', $request);
        if ($this->db->affected_rows() == 1)
            return TRUE;
        return FALSE;
    }

    public function getRequests($status) {
        $qry = $this->db->select('*')
                ->from('requests')
                ->where('status', $status)
                ->get();
        if ($qry->num_rows() > 0)
            return $qry->result_array();
        return FALSE;
    }

    public function updateRequestData($request_uuid, $request_data) {
        $this->db->where('uuid', $request_uuid)->update('requests', $request_data);
        if ($this->db->affected_rows() == 1)
            return TRUE;
        return FALSE;
    }

}
