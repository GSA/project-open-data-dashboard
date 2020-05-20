<?php

class APIHelperTest extends TestCase
{

    public function setUp() {
        $CI =& get_instance();
        $CI->load->helper('api');
    }

    public function testFilterRemoteUrlRejectsUnresolvableHostnames() {
        $url = "http://some.unresolvable.hostname.fer-reals/data.json";
        $this->assertSame(false, filter_remote_url($url));
    }

    public function testFilterRemoteUrlRejectsNonHostnames() {

        // A null hostname should explicitly return null
        $url = null;
        $this->assertSame(null, filter_remote_url($url));

        // Anything that resolves to zero needs to be tested carefully due to falsiness
        $url = "http://0/data.json";
        $this->assertSame(false, filter_remote_url($url));

        // Hex is bad
        $url = "http://0x7f000001/data.json";
        $this->assertSame(false, filter_remote_url($url));

        // So is octal
        $url = "http://0123/data.json";
        $this->assertSame(false, filter_remote_url($url));

        // We don't even like dotted-quads
        $url = "http://111.22.34.56/data.json";
        $this->assertSame(false, filter_remote_url($url));
    }

}
