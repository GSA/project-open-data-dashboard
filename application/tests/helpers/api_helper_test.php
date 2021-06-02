<?php

use PHPUnit\Framework\TestCase;
use APIHelper\APIHelper;

class APIHelperTest extends TestCase
{
    use \phpmock\phpunit\PHPMock;

    public function setUp(): void {
        $CI =& get_instance();
        $CI->load->helper('api');
    }

    public function testMockingGlobalsWorks() {
        $testURL = "https://localhost"; // Use a hostname that's definitely resolvable
        $dns_get_record = $this->getFunctionMock("APIHelper", "dns_get_record");
        $dns_get_record->expects($this->once())->willReturn(false); // Mock that it's not resolvable
        $this->assertEquals(false, APIHelper::filter_remote_url($testURL));
    }

    public function testFilterRemoteUrlRejectsUnresolvableHostnames() {
        $url = "http://some.unresolvable.hostname.fer-reals/data.json";
        $this->assertSame(false, APIHelper::filter_remote_url($url));
    }

    public function testFilterRemoteUrlRejectsUnusualPorts() {
        $this->assertSame(false, APIHelper::filter_remote_url("https://www.google.com:567/data.json"));
    }

    public function testFilterRemoteUrlAcceptsExpectedPorts() {
        $this->assertSame("https://www.google.com/data.json", APIHelper::filter_remote_url("https://www.google.com/data.json"));
        $this->assertSame("https://www.google.com:443/data.json", APIHelper::filter_remote_url("https://www.google.com:443/data.json"));
        $this->assertSame("http://www.google.com:80/data.json", APIHelper::filter_remote_url("http://www.google.com:80/data.json"));
        $this->assertSame("http://www.google.com:8080/data.json", APIHelper::filter_remote_url("http://www.google.com:8080/data.json"));
    }

    // A null hostname should explicitly return null
    public function testFilterRemoteUrlRejectsNullHostnames() {
        $url = null;
        $this->assertSame(null, APIHelper::filter_remote_url($url));
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testFilterRemoteUrlRejectsNonAndBadHostnames($url, $mockedRecord) {
        if ($mockedRecord) {
            // Mock out the DNS request with the expected value
            $dns_get_record = $this->getFunctionMock("APIHelper", "dns_get_record");
            $dns_get_record->expects($this->once())->willReturn($mockedRecord);
        }
        $this->assertSame(false, APIHelper::filter_remote_url($url));
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testSafeCurlExecBarfsOnSsrfInRedirect($ssrfUrl, $mockedRecord) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_FILETIME, true);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIE, "");

        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

        // Provide a response body with a redirect to our $ssrfUrl
        $redirectBody = "HTTP/1.1 301 Moved Permanently\nLocation: $ssrfUrl\n";
        $curl_exec = $this->getFunctionMock("APIHelper", "curl_exec");
        $curl_exec->expects($this->once())->willReturn($redirectBody); // 301 redirect $ssrfUrl

        $urlParts = parse_url($ssrfUrl);
        unset($urlParts['scheme']);
        $redirectUrl = "http://ssrf.redirecting.host/".implode($urlParts);

        // $filter_remote_url = $this->getFunctionMock("APIHelper", APIHelper::filter_remote_url);
        $filter_remote_url = $this->getMockBuilder(APIHelper\APIHelper::class)
             ->setMethods(['filter'])
             ->getMock();
        $filter_remote_url->expects($this->exactly(2))->will($this->onConsecutiveCalls($redirectUrl, false)); // 301 redirect $ssrfurl

        $this->expectException(\Exception::class);
        APIHelper::safe_curl_exec($redirectUrl, $ch, true);
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testCurlHeaderIsNotSusceptibleToSsrfDuringRedirect($ssrfUrl, $mockedRecord) {

        // Expected sequence:
        // 1) dns_get_record for the ssrf.redirecting.host gets a mocked record
        // 2) curl_exec (to the mocked IP) gets a mocked "301 to $ssrfUrl" response
        // 3) dns_get_record for the $ssrfurl host gets mocked record (if provided)


        // (2)
        // Expect that curl_exec will get called once with the special IP
        // Provide a response body with a redirect to our $ssrfurl
        $redirectBody = "HTTP/1.1 301 Moved Permanently\nLocation: $ssrfUrl";
        $curl_exec = $this->getFunctionMock("APIHelper", "curl_exec");
        $curl_exec->expects($this->once())->willReturn($redirectBody); // 301 redirect $ssrfurl

        $dnsRecordMap = [
            // (1)
            // Make sure the first request to resolve the ssrf.redirecting.host succeeds
            ["ssrf.redirecting.host", [['type' => 'A', 'ip' => '1.2.3.4']]]
        ];
        if ($mockedRecord) {
            // (3)
            // Make sure the subsequent request for the $ssrfurl host resolves to mocked records, if provided.
            $dnsRecordMap[] = [
                [$ssrfUrl, $mockedRecord]
            ];
        }
        $dns_get_record = $this->getFunctionMock("APIHelper", "dns_get_record");
        $dns_get_record->expects($this->once())->will($this->returnValueMap($dnsRecordMap));

        $urlParts = parse_url($ssrfUrl);
        unset($urlParts['scheme']);
        $redirectUrl = "http://ssrf.redirecting.host/".implode($urlParts);

        $this->expectException(\Exception::class);
        curl_header($redirectUrl, true);
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testCurlHeadShimIsNotSusceptibleToSsrfDuringRedirect($url, $mockedRecord) {
        $CI =& get_instance();
        $this->expectException(\Exception::class);
        curl_head_shim($url, true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badUrlProvider
     */
    public function testCurlFromJsonIsNotSusceptibleToSsrfDuringRedirect($url, $mockedRecord) {
        $this->expectException(\Exception::class);
        curl_from_json($url);
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeaderIgnoresBadProtocols($protocol) {
        $this->expectException(\Exception::class);
        curl_header($protocol."://127.0.0.1");
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeadShimIgnoresBadProtocols($protocol) {
        $CI =& get_instance();
        $this->expectException(\Exception::class);
        curl_head_shim($protocol."://127.0.0.1", true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlFromJsonIgnoresBadProtocols($protocol) {
        $this->expectException(\Exception::class);
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

    // Examples of SSRF URLs we should never follow
    // The first array element is a URL that should be filtered out
    // The second argument is a mock array of records for dns_get_record() to return
    public function badUrlProvider() {

        $badRedirects["ip: 127.0.0.1"] = array("http://127.0.0.1", false);

        // Anything that resolves to zero needs to be tested carefully due to falsiness
        $badRedirects["ip: 0"] = array("http://0/data.json", false);

        // Hex is bad
        $badRedirects["ip: hex"] = array("http://0x7f000001/data.json", false);

        // So is octal
        $badRedirects["ip: octal"] = array("http://0123/data.json", false);

        // We don't like mixed hex and octal either
        $badRedirects["ip: mixed hex/octal/decimal"] = array("http://0x7f.0x0.0.0/data.json", false);

        // We don't even like dotted-quads
        $badRedirects["ip: dotted-quad"] = array("http://111.22.34.56/data.json", false);

        // Don't you come around here with any of that IPv6 crap
        $badRedirects["ipv6: localhost"] = array("http://[::1]", false);

        // Domains that resolve to IPv4 localhost? NFW.
        $badRedirects["localhost.ip4"] = array("https://localhost.ip4/", [['type' => 'A', 'ip' => '127.0.0.1']]);

        // Domains that resolve to IPv6 localhost? Get out!
        $badRedirects["localhost.ip6"] = array("https://localhost.ip6:443", [['type' => 'AAAA', 'ipv6' => '::1']]);

        // Domains that resolve to IPv6 addresses that represent IPv4 private ranges? Not on our watch!
        $badRedirects["localhost2.ip6"] = array("http://localhost2.ip6:80", [['type' => 'AAAA', 'ipv6' => '::ffff:127.0.0.1']]);
        $badRedirects["private1.ip6"] = array("https://private1.ip6:80", [['type' => 'AAAA', 'ipv6' => '::ffff:192.168.1.18']]);
        $badRedirects["private2.ip6"] = array("https://private2.ip6:80", [['type' => 'AAAA', 'ipv6' => '::ffff:10.0.0.1']]);

        // Domains that resolve to IPv6 link-local adddresses? Hell no!
        $badRedirects["linklocal.ip6"] = array("https://linklocal.ip6/", [['type' => 'AAAA', 'ipv6' => 'fe80::']]);

        return $badRedirects;
    }

}
