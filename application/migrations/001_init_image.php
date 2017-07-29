<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Migration_init_image
 *
 * @author GeorgeGeorgitsis
 */
class Migration_init_image extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'UUID' => array(
                'type' => 'BIGINT',
                'unsigned' => TRUE,
            ),
            'title' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '255',
            ),
            'remote_url' => array(
                'type' => 'TEXT',
            ),
            'type' => array(
                'type' => 'VARCHAR',
                'constraint' => '8',
                'default' => NULL
            ),
            'description' => array(
                'type' => 'TEXT',
                'default' => NULL
            ),
            'is_deleted' => array(
                'type' => 'SMALLINT',
                'constraint' => '1',
                'default' => 0
            )
        ));
        $this->dbforge->add_field('created_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        $this->dbforge->add_key('UUID', TRUE);
        $this->dbforge->create_table('images');

        $sql = "CREATE INDEX uuid ON images(UUID)";
        $this->db->query($sql);
    }

}
