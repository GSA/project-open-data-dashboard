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

        // We don't like mixed hex and octal either
        $url = "http://0x7f.0x0.0.0/data.json";
        $this->assertSame(false, filter_remote_url($url));

        // We don't even like dotted-quads
        $url = "http://111.22.34.56/data.json";
        $this->assertSame(false, filter_remote_url($url));
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeaderIgnoresBadProtocols($protocol) {
        $this->expectException(Exception::class);
        curl_header($protocol."://127.0.0.1");
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeadShimIgnoresBadProtocols($protocol) {
        $CI =& get_instance();
        $this->expectException(Exception::class);
        curl_head_shim($protocol."://127.0.0.1", true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlFromJsonIgnoresBadProtocols($protocol) {
        $this->expectException(Exception::class);
        curl_from_json($protocol."://127.0.0.1");
    }

    public function badProtocolProvider() {

        // These are all the protocols that libcurl supports that we don't want to be valid
        $protocols = explode(' ', "dict file ftp ftps gopher imap imaps ldap ldaps pop3 pop3s rtmp rtsp scp sftp smb smtp smtps");
        foreach($protocols as $protocol) {
            $protocolarray[] = array($protocol);
        }
        return $protocolarray;
    }

}
