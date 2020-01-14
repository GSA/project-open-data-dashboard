<?php

class OfficesTest extends TestCase
{
    public function testOfficeDetailsPageIsValidWithoutCrawls() {
        // We can improve this test by explicitly ensuring that there are no crawls present first,
        // but at least in our dev/test environments, no crawls have run yet, so the DB should be empty.
        $this->request('GET', 'offices/detail/49015');
        $this->assertResponseCode(200);
    }

}
