<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Migrate
 *
 * @author GeorgeGeorgitsis
 */
class Migrate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('migration');
    }

    public function index() {
        if (!$this->migration->latest()) {
            show_error($this->migration->error_string());
        }
    }

}
