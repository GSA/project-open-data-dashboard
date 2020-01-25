<?php

class OfficesTest extends TestCase
{

    public function testOfficeDetailsPageIsValidWithoutCrawls() {
        // We can improve this test by explicitly ensuring that there are no crawls present first,
        // but at least in our dev/test environments, no crawls have run yet, so the DB should be empty.
        $this->request('GET', 'offices/detail/49015');
        $this->assertResponseCode(200);
    }

    public function testOfficesListIncludesOmbMonitoredOffices() {
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
