<?php

class APIHelperTest extends TestCase
{

    public function testFilterRemoteUrlToleratesUnresolvableHosts() {
        $CI =& get_instance();
        $CI->load->helper('api');

        $url = "http://some.unresolvable.hostname.fer-reals/data.json";
        $this->assertEquals($url, filter_remote_url($url));
    }

}
