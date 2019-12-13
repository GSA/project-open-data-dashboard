<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_create_logging_table extends CI_Migration
{

    public function up()
    {
        // Create new table for capturing SQL query logs
        $this->db->query('
        CREATE TABLE IF NOT EXISTS ci_logs (
            ip                      VARCHAR(10) NOT NULL,
            page                    VARCHAR(255) NOT NULL,
            user_agent              VARCHAR(255) NOT NULL,
            referrer                VARCHAR(255) NOT NULL,
            logged                  TIMESTAMP NOT NULL
                                    default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
            username                VARCHAR(255) NOT NULL,
            memory                  INT UNSIGNED NOT NULL,
            render_elapsed          FLOAT NOT NULL,
            ci_elapsed              FLOAT NOT NULL,
            controller_elapsed      FLOAT NOT NULL,
            mysql_elapsed           FLOAT NOT NULL,
            mysql_count_queries     TINYINT UNSIGNED NOT NULL,
            mysql_queries           TEXT NOT NULL
        ) ENGINE=ARCHIVE;
        ');
        $this->db->query('ALTER TABLE ci_logs CHANGE referrer referrer VARCHAR(255) DEFAULT NULL;');
        $this->db->query('ALTER TABLE ci_logs CHANGE username username VARCHAR(255) DEFAULT NULL;');


    }
}



