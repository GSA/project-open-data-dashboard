<?php

class OfficesTest extends DbTestCase
{

    public function seedOmbMonitoredOfficeCampaignFixtures() {

        $CI =& get_instance();

        // We need to be able to use milestone logic to find the current
        // milestone. (There might be a better way to do this that doesn't
        // require loading up so much model code.)
        $CI->load->model('campaign_model', 'campaign');
        $milestones = $CI->campaign->milestones_model();
        $milestone = $CI->campaign->milestone_filter('', $milestones);
        $milestone = $milestone->current;

        // Get a list of the IDs for all the OMB-monitored offices
        $CI->db->select('offices.id');
        $CI->db->from('offices');
        $CI->db->where('offices.omb_monitored', 'true');
        $query = $CI->db->get();
        $results = $query->result();
        $query->free_result();

        // Seed a campaign entry for each one
        foreach ($results as $agency) {
            $this->seedCampaignFixture($agency->id, $milestone, ($agency->id)%2);
        }
    }

    public function getSuccessfulDataJSONStatus() {
        // Valid/successful crawl
        return '{
        "url": "https:\/\/www.opm.gov\/data.json",
        "content_type": "application\/javascript",
        "http_code": 200,
        "header_size": 685,
        "request_size": 544,
        "filetime": 1533930113,
        "ssl_verify_result": 0,
        "redirect_count": 1,
        "total_time": 0.077116,
        "namelookup_time": 1.6e-5,
        "connect_time": 0.003334,
        "pretransfer_time": 0.013315,
        "size_upload": 0,
        "size_download": 0,
        "speed_download": 0,
        "speed_upload": 0,
        "download_content_length": 1437735,
        "upload_content_length": 0,
        "starttransfer_time": 0.064938,
        "redirect_time": 0.012125,
        "redirect_url": "",
        "primary_ip": "104.117.43.127",
        "certinfo": [],
        "primary_port": 443,
        "local_ip": "10.183.32.196",
        "local_port": 37664,
        "expected_url": "http:\/\/www.opm.gov\/data.json",
        "valid_json": true,
        "valid_schema": true,
        "total_records": 682,
        "schema_version": "federal-v1.1",
        "schema_errors": null,
        "qa": {
            "programCodes": [
                "027:000",
                "027:008",
                "027:002",
                "027:007",
                "027:005",
                "027:004",
                "027:006",
                "027:009"
            ],
            "bureauCodes": [
                "027:00"
            ],
            "accessLevel_public": 583,
            "accessLevel_restricted": 56,
            "accessLevel_nonpublic": 43,
            "accessURL_present": 478,
            "accessURL_total": 767,
            "API_total": 6,
            "API_public": 6,
            "API_restricted": 0,
            "API_nonpublic": 0,
            "collections_total": 32,
            "non_collection_total": 176,
            "validation_counts": {
                "http_5xx": 4,
                "http_4xx": 1,
                "http_3xx": 748,
                "http_2xx": 14,
                "http_0": 0,
                "pdf": 6,
                "html": 5,
                "format_mismatch": 1
            },
            "license_present": 682,
            "redaction_present": 0,
            "redaction_no_explanation": 0,
            "downloadURL_present": 473,
            "downloadURL_total": 539
        },
        "last_crawl": 1559363436,
        "error_count": 0
        }';
    }

    public function getUnsuccessfulDataJSONStatus() {
        // Invalid/unsuccessful crawl
        return '{
            "url": "https:\/\/www.justice.gov\/digitalstrategy\/data.json",
            "content_type": "text\/html; charset=utf-8",
            "http_code": 404,
            "header_size": 1528,
            "request_size": 468,
            "filetime": 1575302256,
            "ssl_verify_result": 0,
            "redirect_count": 3,
            "total_time": 1.092282,
            "namelookup_time": 0.000103,
            "connect_time": 0.14046500000000001,
            "pretransfer_time": 0.457959,
            "size_upload": 0,
            "size_download": 0,
            "speed_download": 0,
            "speed_upload": 0,
            "download_content_length": -1,
            "upload_content_length": -1,
            "starttransfer_time": 1.0921050000000001,
            "redirect_time": 0.79269000000000001,
            "redirect_url": "",
            "primary_ip": "23.50.184.10",
            "certinfo": [],
            "primary_port": 443,
            "local_ip": "10.183.32.234",
            "local_port": 59426,
            "http_version": 2,
            "protocol": 2,
            "ssl_verifyresult": 0,
            "scheme": "HTTPS",
            "expected_url": "http:\/\/www.usdoj.gov\/data.json",
            "last_crawl": 1575325730,
            "error_count": null,
            "schema_errors": null
            }';
    }


    /*
        In a test environment, no crawls have completed, so there's no
        information in the datagov_campaign table. This isn't the state in a
        normal ongoing deployment, and the app logic doesn't show output in some
        cases unless there are entries. So for some tests, we need to
        prepopulate that table.

        $milestone <= String date value like '2020-02-29'
        $valid <= Whether to simulate a valid or invalid crawl
    */
    public function seedCampaignFixture($id, $milestone, $valid = true) {
        // Ensure there's a row in the datagov_campaign table for the specified agency ID
        $this->hasInDatabase('datagov_campaign',
            array('office_id' => $id,
                'milestone' => $milestone,
                'crawl_status' => 'current',
                'datajson_status' => $valid ?
                    $this->getSuccessfulDataJSONStatus() :
                    $this->getUnsuccessfulDataJSONStatus()));
    }

    public function testOfficeDetailsPageIsValidWithoutCrawls() {
        // We can improve this test by explicitly ensuring that there are no crawls present first,
        // but at least in our dev/test environments, no crawls have run yet, so the DB should be empty.
        $this->request('GET', 'offices/detail/49015');
        $this->assertResponseCode(200);
    }

    // Test that OMB-monitored offices are listed in a simple request
    public function testOfficeListIncludesOmbMonitoredOffices() {

        // All the entries added here get removed automatically during
        // DbTestCase::tearDown()
        $this->seedOmbMonitoredOfficeCampaignFixtures();

        $output = $this->request('GET', 'offices/qa');
        $this->assertResponseCode(200);
        $this->assertContains('<td>Other Agencies</td>', $output);
    }

    /**
     * These are strapping tests that just assert that no PHP errors are encountered on clicks of nav links
     * @dataProvider badMilestoneProvider
     */
    public function testDetail404sOnBadMilestone($path)
  	{
        $this->request('GET', 'offices/detail/'.$path);
        $this->assertResponseCode(404);
    }

    public function badMilestoneProvider() {

        // Previous scans alerted on the following requests, among others,
        // all of which were tripping over the same code

        return [
            ['offices/qa'],
            ['49018/Data.json'],
            ['48027/Data.json'],
            ['48027/digitalstrategy.json'],
            ['48112/Data.json'],
            ['49015/%252527?highlight=edi'],
            ['49015/e.g'],
            ['49015/http%3a%2f%2fr87.com%2fn%3f%00.php?highlight=edi']
        ];

    }

}
