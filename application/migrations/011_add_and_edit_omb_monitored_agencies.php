<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_and_edit_omb_monitored_agencies extends CI_Migration
{
    // Add a column to indicate whether the office's crawl takes longer than 23 hours
    public function up()
    {
        // we should add these to the db as they have a CDO
        $this->db->query("INSERT INTO offices
        (id, name, abbreviation, url, notes, no_parent, parent_office_id, reporting_authority_type, cfo_act_agency, omb_monitored, long_running) VALUES
        (80003, 'Gulf Coast Ecosystem Restoration Council', 'GCERC', 'https://restorethegulf.gov', '', 'TRUE', 0, 'independent', 'FALSE', 'TRUE', 'FALSE'),
        (80004, 'Intelligence Community', 'IC', 'https://www.intelligence.gov', '', 'TRUE', 0, 'independent', 'FALSE', 'TRUE', 'FALSE')
        ");
        
        // OMB also wants to monitor these offices, already in the DB
        $this->db->query("UPDATE offices SET omb_monitored = 'TRUE' WHERE name IN (
          'Navy',
          'Securities and Exchange Commission',
          'U.S. Air Force',
          'U.S. Army')
          ");
    }
}
