<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 002_init_image
 *
 * @author @GeorgeGeorgitsis
 */
class Migration_size extends CI_Migration {

    public function up() {
        $fields = array(
            'size' => array('type' => 'INT')
        );
        $this->dbforge->add_column('images', $fields);
    }

}
