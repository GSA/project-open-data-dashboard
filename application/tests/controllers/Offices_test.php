<?php

class OfficesTest extends DbTestCase
{

    /*
        In a test environment, no crawls have completed, so there's no
        information in the datagov_campaign table. This isn't the state in a
        normal ongoing deployment, and the app logic doesn't show output in some
        cases unless there are entries. So for some tests, we need to
        prepopulate that table for the current milestone.
    */
    public function seedCampaignFixture() {

        $CI =& get_instance();

        // We need to be able to use milestone logic to find the current
        // milestone. (There might be a better way to do this that doesn't
        // loading up so much model code.)
        $CI->load->model('campaign_model', 'campaign');
        $milestones = $CI->campaign->milestones_model();
        $milestone = $CI->campaign->milestone_filter('', $milestones);
        $milestone = $milestone->current;

        // Get a list of the IDs for all the monitored offices
        $CI->db->select('offices.id');
		$CI->db->from('offices');
		$CI->db->where('offices.omb_monitored', 'true');
        $query = $CI->db->get();
        $results = $query->result();
        $query->free_result();

        // Ensure there's one row in the datagov_campaign table for the current
        // milestone for each office we're monitoring.
        foreach ($results as $agency) {
            $this->hasInDatabase('datagov_campaign',
                array('office_id' => $agency->id,
                      'milestone' => $milestone,
                      'crawl_status' => 'current'));
        }

        // All the entries added above via hasInDatabase() get removed
        // automatically during DbTestCase::tearDown()
    }


    public function testOfficeDetailsPageIsValidWithoutCrawls() {
        // We can improve this test by explicitly ensuring that there are no crawls present first,
        // but at least in our dev/test environments, no crawls have run yet, so the DB should be empty.
        $this->request('GET', 'offices/detail/49015');
        $this->assertResponseCode(200);
    }

    // Test that OMB-monitored offices are listed in a simple request
    public function testOfficeListIncludesOmbMonitoredOffices() {

        $this->seedCampaignFixture();

        $output = $this->request('GET', 'offices');
        $this->assertResponseCode(200);
        $this->assertContains('<td>Other OMB-Monitored Agencies</td>', $output);
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
