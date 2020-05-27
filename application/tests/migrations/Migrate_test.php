<?php

class MigrateTest extends TestCase
{

    public function setUp() {

        parent::setUp();
        $this->resetInstance();

        // Set the expected initial DB state in case there were aborted previous runs
        $this->CI->load->database();
        $this->CI->db->query('DROP TABLE IF EXISTS test_table');

    }

    public function testColumnExistsWorks() {

        // Create an expected table with an expected column
        $this->CI->db->query('CREATE TABLE IF NOT EXISTS test_table(test_column varchar(15))');

        // Test that it returns 1 when the expected table and column exist
        $query = $this->CI->db->query("SELECT column_exists('test_table', 'test_column')");
        $row = $query->first_row('array');
        $tableAndColumnExist = (bool) array_shift($row);
        $this->assertTrue($tableAndColumnExist);

        // Get rid of the table
        $this->CI->db->query('DROP TABLE IF EXISTS test_table');

        // Test that it returns 0 when the expected table and column DON'T exist
        $query = $this->CI->db->query("SELECT column_exists('test_table', 'test_column')");
        $row = $query->first_row('array');
        $tableAndColumnExist = (bool) array_shift($row);
        $this->assertFalse($tableAndColumnExist);
    }

    public function testIdempotentDropColumnWorks() {

        // Create an expected table with an expected column
        $this->CI->db->query('CREATE TABLE IF NOT EXISTS test_table(other_column varchar(15), test_column varchar(15))');

        // Drop the column once
        $this->CI->db->query("CALL drop_column_if_exists('test_table', 'test_column')");

        // Check and assert that the column was dropped
        $query = $this->CI->db->query("SELECT column_exists('test_table', 'test_column')");
        $row = $query->first_row('array');
        $tableAndColumnExist = (bool) array_shift($row);
        $this->assertFalse($tableAndColumnExist);

        // Try to drop the column again and make sure there's no error
        $noErrorOnIdempotentDrop = (bool) $this->CI->db->query("CALL drop_column_if_exists('test_table', 'test_column')");

        // Check and assert that there was no error
        $this->assertTrue($noErrorOnIdempotentDrop);

        // Get rid of the table
        $this->CI->db->query('DROP TABLE IF EXISTS test_table');
    }

    public function testIdempotentAddColumnWorks() {

        // Create a table WITHOUT the column
        $this->CI->db->query('CREATE TABLE IF NOT EXISTS test_table(other_column varchar(15))');

        // Add the column and assert no errors
        $addedColumn = (bool) $this->CI->db->query("CALL add_column_if_not_exists('test_table', 'test_column', 'varchar(15)')");
        $this->assertTrue($addedColumn);

        // Test that the expected table and column exist
        $query = $this->CI->db->query("SELECT column_exists('test_table', 'test_column')");
        $row = $query->first_row('array');
        $tableAndColumnExist = (bool) array_shift($row);
        $this->assertTrue($tableAndColumnExist);

        // Add the column AGAIN and assert no errors
        $addedColumnAgainWithoutErrors = (bool) $this->CI->db->query("CALL add_column_if_not_exists('test_table', 'test_column', 'varchar(15)')");
        $this->assertTrue($addedColumnAgainWithoutErrors);

        // Get rid of the table
        $this->CI->db->query('DROP TABLE IF EXISTS test_table');
    }

    /**
     * Ensure that offices explicitly monitored by the OMB are present and being crawled
     */
    public function testOmbMonitoredOfficesArePresentAndFlaggedInDatabase()
  	{
        // Get an array of the names of all the OMB-monitored Offices in the DB, sorted
        $this->CI->db->select('*');
		$this->CI->db->from('offices');
		$this->CI->db->where('offices.omb_monitored', 'true');
		$this->CI->db->order_by("offices.name", "asc");
        $query = $this->CI->db->get();
        $results = $query->result();
        $query->free_result();

        $agencies = [];
        foreach ($results as $agency) {
            $agencies[] = $agency->name;
        }
        sort($agencies);

        // Set up an explicit list of the agencies that OMB is monitoring for comparison
        $cfo_act_agencies = [
            'Department of Agriculture',
            'Department of Commerce',
            'Department of Defense',
            'Department of Education',
            'Department of Energy',
            'Department of Health and Human Services',
            'Department of Homeland Security',
            'Department of Housing and Urban Development',
            'Department of Justice',
            'Department of Labor',
            'Department of State',
            'Department of the Interior',
            'Department of the Treasury',
            'Department of Transportation',
            'Department of Veterans Affairs',
            'Environmental Protection Agency',
            'General Services Administration',
            'National Aeronautics and Space Administration',
            'National Science Foundation',
            'Nuclear Regulatory Commission',
            'Office of Personnel Management',
            'Small Business Administration',
            'Social Security Administration',
            'U.S. Agency for International Development'
        ];

        $other_agencies = [
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
            'Farm Credit System Insurance Corporation',
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
            'Office of Special Counsel',
            'Office of the Comptroller of the Currency',
            'Peace Corps',
            'Pension Benefit Guaranty Corporation',
            'Presidio Trust',
            'Public Defender Service of the District of Columbia',
            'Railroad Retirement Board',
            'Securities and Exchange Commission',
            'Selective Service System',
            'Surface Transportation Board',
            'U.S. Access Board',
            'U.S. Commission of Fine Arts',
            'U.S. Commodity Futures Trading Commission',
            'U.S. International Trade Commission',
            'U.S. Trade and Development Agency',
            'US Agency for Global Media'
        ];

        $expected_agencies = array_merge($cfo_act_agencies, $other_agencies);
        sort($expected_agencies);

        // Check that the list from the DB matches the expected list
        $this->assertEquals($agencies, $expected_agencies);

   }

}
