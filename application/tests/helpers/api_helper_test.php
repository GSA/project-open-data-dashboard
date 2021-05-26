<?php

use PHPUnit\Framework\TestCase;
use APIHelper\APIHelper;

use Symfony\Bridge\PhpUnit\DnsMock;

/**
 * @group dns-sensitive
 */
class APIHelperTest extends TestCase
{

    public function setUp(): void {
        $CI =& get_instance();
        $CI->load->helper('api');
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
    public function testFilterRemoteUrlRejectsNonAndBadHostnames($url) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $this->assertSame(false, APIHelper::filter_remote_url($url));
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlHeaderIsNotSusceptibleToSsrfDuringRedirect($url) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $this->expectException(\Exception::class);
        curl_header($url, true);
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlHeadShimIsNotSusceptibleToSsrfDuringRedirect($url) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $CI =& get_instance();
        $this->expectException(\Exception::class);
        curl_head_shim($url, true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badRedirectProvider
     */
    public function testCurlFromJsonIsNotSusceptibleToSsrfDuringRedirect($url) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $this->expectException(\Exception::class);
        curl_from_json($url);
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeaderIgnoresBadProtocols($protocol) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $this->expectException(\Exception::class);
        curl_header($protocol."://127.0.0.1");
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlHeadShimIgnoresBadProtocols($protocol) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
        $CI =& get_instance();
        $this->expectException(\Exception::class);
        curl_head_shim($protocol."://127.0.0.1", true, $CI->config->item('archive_dir'));
    }

    /**
     * @dataProvider badProtocolProvider
     */
    public function testCurlFromJsonIgnoresBadProtocols($protocol) {
        DnsMock::withMockedHosts($this->mockedDNSEntries());
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

        // Domains that resolve to IPv4 localhost? NFW. (Mocked below.)
        $badRedirects[] = array("https://localhost.ip4/");

        // Domains that resolve to IPv6 localhost? Get out! (Mocked below.)
        $badRedirects[] = array("https://localhost.ip6/");
        $badRedirects[] = array("https://localhost2.ip6");

        // Domains that resolve to IPv6 addresses that represent IPv4 private ranges? Not on our watch! (Mocked below.)
        $badRedirects[] = array("https://localhost.ip6:443");
        $badRedirects[] = array("https://localhost2.ip6:80");
        $badRedirects[] = array("https://private1.ip6:80");
        $badRedirects[] = array("https://private2.ip6:80");

        // Domains that resolve to IPv6 link-local adddresses? Hell no! (Mocked below.)
        $badRedirects[] = array("https://linklocal.ip6/");

        return $badRedirects;
    }

    // We mock these DNS requests to hardcode particular responses
    // See https://symfony.com/doc/current/components/phpunit_bridge.html#dns-sensitive-tests for docs
    public function mockedDNSEntries() {
        return [
            'localhost.ip4' => [
                [
                    'type' => 'A',
                    'ipv6' => '127.0.0.1',
                ],
            ],
            'localhost.ip6' => [
                [
                    'type' => 'AAAA',
                    'ipv6' => '::1',
                ],
            ],
            'localhost2.ip6' => [
                [
                    'type' => 'AAAA',
                    'ipv6' => '::ffff:127.0.0.1',
                ],
            ],
            'private1.ip6' => [
                [
                    'type' => 'AAAA',
                    'ipv6' => '::ffff:192.168.1.18',
                ],
            ],
            'private2.ip6' => [
                [
                    'type' => 'AAAA',
                    'ipv6' => '::ffff:10.0.0.1',
                ],
            ],
            'linklocal.ip6' => [
                [
                    'type' => 'AAAA',
                    'ipv6' => 'fe80::',
                ],
            ],
        ];
    }

}
