<?php

class OfficesTest extends TestCase
{
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
