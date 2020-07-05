<?php

class APIHelperTest extends TestCase
{

    public function setUp() {
        $CI =& get_instance();
        $CI->load->helper('api');
    }

    // TODO Use https://packagist.org/packages/remotelyliving/php-dns to mock DNS
    public function testRedirectServicesUsedInTestsAreAvailable() {
        $this->assertInternalType('array', dns_get_record('ip6.name', DNS_AAAA));
        // $this->assertInternalType('array', dns_get_record('xip.io', DNS_A));
    }

    public function testFilterRemoteUrlRejectsUnresolvableHostnames() {
        $url = "http://some.unresolvable.hostname.fer-reals/data.json";
        $this->assertSame(false, filter_remote_url($url));
    }

    public function testFilterRemoteUrlRejectsUnusualPorts() {
        $this->assertSame(false, filter_remote_url("https://www.google.com:567/data.json"));
    }

    public function testFilterRemoteUrlAcceptsExpectedPorts() {
        $this->assertSame("https://www.google.com/data.json", filter_remote_url("https://www.google.com/data.json"));
        $this->assertSame("https://www.google.com:443/data.json", filter_remote_url("https://www.google.com:443/data.json"));
        $this->assertSame("http://www.google.com:80/data.json", filter_remote_url("http://www.google.com:80/data.json"));
        $this->assertSame("http://www.google.com:8080/data.json", filter_remote_url("http://www.google.com:8080/data.json"));
    }

    // A null hostname should explicitly return null
    public function testFilterRemoteUrlRejectsNullHostnames() {
        $url = null;
        $this->assertSame(null, filter_remote_url($url));

    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testFilterRemoteUrlRejectsNonHostnames($url) {
        $this->assertSame(false, filter_remote_url($url));
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlHeaderIsNotSusceptibleToSsrfDuringRedirect($url) {
        $this->expectException(Exception::class);
        curl_header($url, true);
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlHeadShimIsNotSusceptibleToSsrfDuringRedirect($url) {
        $CI =& get_instance();
        $this->expectException(Exception::class);
        curl_head_shim($url, true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlFromJsonIsNotSusceptibleToSsrfDuringRedirect($url) {
        $this->expectException(Exception::class);
        curl_from_json($url);
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

    // These are all the protocols that libcurl supports that we don't want to be valid
    public function badProtocolProvider() {

        $protocols = explode(' ', "dict file ftp ftps gopher imap imaps ldap ldaps pop3 pop3s rtmp rtsp scp sftp smb smtp smtps");
        foreach($protocols as $protocol) {
            $protocolarray[] = array($protocol);
        }
        return $protocolarray;
    }

    // This data provider puts each of the bad URL examples we're trying to avoid in the middle of a redirect
    public function badRedirectProvider() {
        $badUrls = $this->badUrlProvider();
        $badRedirects = array();
        foreach($badUrls as $badUrl) {
            $urlParts = parse_url(array_shift($badUrl));
            unset($urlParts['scheme']);

            // The redir.xpoc.pro tool is provided by sp1d3R in the HackerOne Bug Bounty program
            // If it goes away, we'll need some other way to easily generate a redirect to an internal URL
            $badRedirects[] = array("http://redir.xpoc.pro/".implode($urlParts));
        }
        return $badRedirects;
    }

    // Examples of SSRF URLs we should never follow
    public function badUrlProvider() {

        $badRedirects[] = array("http://127.0.0.1");

        // Anything that resolves to zero needs to be tested carefully due to falsiness
        $badRedirects[] = array("http://0/data.json");

        // Hex is bad
        $badRedirects[] = array("http://0x7f000001/data.json");

        // So is octal
        $badRedirects[] = array("http://0123/data.json");

        // We don't like mixed hex and octal either
        $badRedirects[] = array("http://0x7f.0x0.0.0/data.json");

        // We don't even like dotted-quads
        $badRedirects[] = array("http://111.22.34.56/data.json");

        // Don't you come around here with any of that IPv6 crap
        $badRedirects[] = array("http://[::1]");

        // ip6.name dynamically creates an IPv6 DNS entry that resolves to the subdomain
        $badRedirects[] = array("https://x.1.ip6.name/");

        // xip.io dynamically creates a IPv4 DNS entry that resolves to the subdomain
        // Should change to local DNS service, see here for details: https://github.com/GSA/datagov-deploy/issues/1760
        $badRedirects[] = array("https://127.0.0.1.xip.io/");

        return $badRedirects;
    }

}
