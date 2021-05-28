<?php

class CampaignModelTest extends TestCase
{
    public function setUp(): void
    {
        $this->resetInstance();
        $this->CI->load->model('Campaign_model');
        $this->obj = $this->CI->Campaign_model;
    }

    public function testArchiveOfBadLinkFails() {
        $this->assertFalse($this->obj->archive_file('digitalstrategy', 'somefilename.json', 'https://this.is.a.bad.url/data.json'));
    }

}
