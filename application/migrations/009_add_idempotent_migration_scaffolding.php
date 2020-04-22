<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_idempotent_migration_scaffolding extends CI_Migration
{

    public function up()
    {
        return $this->create_idempotent_helpers();
    }

    public function down()
    {
        return $this->drop_idempotent_helpers();
    }

    private function drop_idempotent_helpers() {
        $this->db->query('DROP FUNCTION IF EXISTS column_exists');
        $this->db->query('DROP PROCEDURE IF EXISTS drop_column_if_exists');
        $this->db->query('DROP PROCEDURE IF EXISTS add_column_if_not_exists');
        return true;
    }

    /*
     * Create functions and stored procedures useful for writing idempotent DDL statements
     * in future migrations.
     *
     * Example usage:
     *  SELECT column_exists('my_table', 'my_column');       -- 0
     *  CALL add_column_if_not_exists('my_table', 'my_column', varchar(15)); -- success
     *  SELECT column_exists('my_table', 'my_column');       -- 1
     *  CALL add_column_if_not_exists('my_table', 'my_column', varchar(15)); -- success
     *  SELECT column_exists('my_table', 'my_column');       -- 1
     *  CALL drop_column_if_exists('my_table', 'my_column'); -- success
     *  SELECT column_exists('my_table', 'my_column');       -- 0
     *  CALL drop_column_if_exists('my_table', 'my_column'); -- success
     *  SELECT column_exists('my_table', 'my_column');       -- 0
     *
     * Source for these procedures: https://stackoverflow.com/a/49676339
    */
    private function create_idempotent_helpers() {

        // column_exists: test whether a table already has a column
        $this->db->query('DROP FUNCTION IF EXISTS column_exists');
        $this->db->query('CREATE FUNCTION column_exists(
            tname VARCHAR(64),
            cname VARCHAR(64)
          )
            RETURNS BOOLEAN
            READS SQL DATA
            BEGIN
              RETURN 0 < (SELECT COUNT(*)
                          FROM `INFORMATION_SCHEMA`.`COLUMNS`
                          WHERE `TABLE_SCHEMA` = SCHEMA()
                                AND `TABLE_NAME` = tname
                                AND `COLUMN_NAME` = cname);
            END');

        // drop_column_if_exists: idempotently drop a column from a table
        $this->db->query('DROP PROCEDURE IF EXISTS drop_column_if_exists');
        $this->db->query('CREATE PROCEDURE drop_column_if_exists(
            tname VARCHAR(64),
            cname VARCHAR(64)
          )
            BEGIN
              IF column_exists(tname, cname) THEN
                SET @drop_column_if_exists = CONCAT(\'ALTER TABLE `\', tname, \'` DROP COLUMN `\', cname, \'`\');
                PREPARE drop_query FROM @drop_column_if_exists;
                EXECUTE drop_query;
              END IF;
            END');

        // Similar function for only adding a column if it doesn't already exist
        // add_column_if_not_exists: idempotently add a column to a table
        $this->db->query('DROP PROCEDURE IF EXISTS add_column_if_not_exists');
        $this->db->query('CREATE PROCEDURE add_column_if_not_exists(
            tname VARCHAR(64),
            cname VARCHAR(64),
            cdef VARCHAR(255)
            )
            BEGIN
                IF NOT column_exists(tname, cname) THEN
                    SET @statement = \'ALTER TABLE `tname` ADD COLUMN `cname` cdef\';
                ELSE
                    -- Make sure the column is defined the way we want
                    SET @statement = \'ALTER TABLE `tname` CHANGE `cname` `cname` cdef\';
                END IF;

                SET @statement = REPLACE(@statement, \'tname\', tname);
                SET @statement = REPLACE(@statement, \'cname\', cname);
                SET @statement = REPLACE(@statement, \'cdef\', cdef);
                PREPARE change_query FROM @statement;
                EXECUTE change_query;

            END');

        return true;
    }

}
