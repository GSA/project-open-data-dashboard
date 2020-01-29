<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_omb_monitored_flag extends CI_Migration
{

    public function up()
    {
        // Add a column to indicate whether the office is one that the OMB is explicitly monitoring
        $this->db->query("ALTER TABLE `offices` ADD `omb_monitored` varchar(256) CHARACTER SET latin1 DEFAULT 'FALSE' AFTER `cfo_act_agency`;");

        // OMB wants to monitor all CFO Act agencies
        $this->db->query("UPDATE offices SET omb_monitored = 'TRUE' WHERE cfo_act_agency = 'TRUE'");

        // OMB wants to monitor these offices (for which there is no existing DB entry)
        $this->db->query("INSERT INTO offices
            (id, name, abbreviation, url, notes, no_parent, parent_office_id, reporting_authority_type, cfo_act_agency, omb_monitored) VALUES
            (80000, 'District of Columbia Courts', 'DCC', 'https://www.dccourts.gov/', '', 'TRUE', 0, 'independent', 'FALSE', 'TRUE'),
            (80001, 'Public Defender Service of the District of Columbia', 'PDSDC', 'https://www.pdsdc.org/', '', 'TRUE', 0, 'independent', 'FALSE', 'TRUE'),
            (80002, 'US Agency for Global Media', 'USAGM', 'https://www.usagm.gov/', '', 'TRUE', 0, 'independent', 'FALSE', 'TRUE')
            ");

        // OMB also wants to monitor these offices, already in the DB
        $this->db->query("UPDATE offices SET omb_monitored = 'TRUE' WHERE name IN (
            'Administrative Conference of the United States',
            'American Battle Monuments Commission',
            'Commission on Civil Rights',
            'Consumer Financial Protection Bureau',
            'Consumer Product Safety Commission',
            'Corporation for National and Community Service',
            'Court Services and Offender Supervision Agency for the District of Columbia',
            'District of Columbia Courts',
            'Equal Employment Opportunity Commission',
            'Export-Import Bank of the United States',
            'Farm Credit Administration',
            'Farm Credit System Insurance Corporation ',
            'Federal Communications Commission',
            'Federal Deposit Insurance Corporation',
            'Federal Energy Regulatory Commission',
            'Federal Housing Finance Agency',
            'Federal Maritime Commission',
            'Federal Mediation and Conciliation Service',
            'Federal Reserve System',
            'Federal Retirement Thrift Investment Board',
            'Federal Trade Commission',
            'Inter-American Foundation',
            'Merit Systems Protection Board',
            'Millennium Challenge Corporation',
            'Morris K. Udall and Stewart L. Udall Foundation',
            'National Capital Planning Commission',
            'National Credit Union Administration',
            'National Endowment for the Arts',
            'National Endowment for the Humanities',
            'National Mediation Board',
            'National Transportation Safety Board',
            'Nuclear Waste Technical Review Board',
            'Occupational Safety and Health Review Commission',
            'Office of Government Ethics',
            'Office of the Comptroller of the Currency',
            'Peace Corps',
            'Pension Benefit Guaranty Corporation',
            'Presidio Trust',
            'Public Defender Service of the District of Columbia',
            'Railroad Retirement Board',
            'Selective Service System',
            'Surface Transportation Board',
            'U.S. Access Board',
            'U.S. Commission of Fine Arts',
            'U.S. Commodity Futures Trading Commission',
            'U.S. International Trade Commission',
            'Office of Special Counsel',
            'U.S. Trade and Development Agency',
            'US Agency for Global Media')
            ");

    }
}



