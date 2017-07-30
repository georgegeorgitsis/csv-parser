<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 003_api_calls
 *
 * @author @GeorgeGeorgitsis
 */
class Migration_api_calls extends CI_Migration {

    public function up() {
        //Create table Requests to save the API calls
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'BIGINT',
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'UUID' => array(
                'type' => 'VARCHAR',
                'constraint' => '50',
            ),
            'request' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'method' => array(
                'type' => 'VARCHAR',
                'constraint' => '15',
            ),
            'csv_file' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'status' => array(
                'type' => 'MEDIUMINT',
                'default' => 0
            ),
            'updated_date' => array(
                'type' => 'DATETIME',
                'default' => NULL
            )
        ));
        $this->dbforge->add_field('created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('UUID', TRUE);
        $this->dbforge->create_table('requests');


        //Alter Table Images to handle which request_id is each image
        $fields = array(
            'request_uuid' => array('type' => 'VARCHAR', 'constraint' => '50',)
        );
        $this->dbforge->add_column('images', $fields);
    }

}
