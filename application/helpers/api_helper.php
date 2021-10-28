<?php

namespace APIHelper {

    use Symfony\Component\HttpFoundation\IpUtils;
    use FROG\PhpCurlSAI\SAI_Curl;
    use FROG\PhpCurlSAI\SAI_CurlInterface;


    class APIHelper {

        // Define the variable to hold the cURL instance that will make a connection
        protected $cURL;

        public function __construct(SAI_CurlInterface $cURL = null) {
            // If no curl instance is provided, the one that makes the actual connection shall be used
            if ($cURL == null) $this->cURL = new SAI_Curl();
            else
                $this->cURL = $cURL;
        }

        /*
        * Check if a URL is "safe", that is, whether it's not going to result in an SSRF attack.
        * Optionally set a passed reference to a specific IP that was resolved.
        * The format of the string is suitable for use with CURLOPT_RESOLVE.
        */
        public function filter_remote_url($url, &$curlopt_resolve = null) {
            if (empty($url)) {
                return null;
            }
            $url = filter_var($url, FILTER_VALIDATE_URL);

            // We only accept http/https
            $allowed_schemes = array('http', 'https');
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (!in_array($scheme, $allowed_schemes)) {
                return false;
            }

            $host = parse_url($url, PHP_URL_HOST);

            // We don't accept raw IP addresses
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                return false;
            }

            // ...or octal/hex numbers
            if (filter_var($host, FILTER_VALIDATE_INT) === 0 || filter_var($host, FILTER_VALIDATE_INT)) {
                return false;
            }

            // ...or domains where any sub-element is just a dec/oct/hex number
            if(preg_match('/(^|\.)((0x[[:xdigit:]]+)|\d+)(\.|$)/', $host) === 1) {
                return false;
            }

            // ...or unresolvable hostnames
            $resolved = dns_get_record($host.".", DNS_A + DNS_AAAA);
            if (!$resolved) {
                return false;
            }

            // We only accept reasonable ports
            $port = parse_url($url, PHP_URL_PORT);
            if ($port != null && $port != 80 && $port != 443 && $port != 8080) {
                return false;
            }

            // Check the array of A and AAAA records to make sure they don't resolve to private ranges to protect against SSRF
            for ($i=0; $i < count($resolved); $i++)
            {
                if ($resolved[$i]["type"] === "A") {
                    if (!filter_var($resolved[$i]["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )) {
                        return false;
                    }
                    $lastValidIPV4 = $resolved[$i]["ip"];
                }

                if ($resolved[$i]["type"] === "AAAA") {
                    if (!filter_var($resolved[$i]["ipv6"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE )) {
                        return false;
                    }

                    // FILTER_FLAG_NO_PRIV_RANGE doesn't check for private-range IPv4 addresses mapped in the IPv6 namespace:
                    // https://www.php.net/manual/en/filter.filters.validate.php#125006
                    // So we have to check for this case explicitly.
                    // The suggestion to use the (tested, supported) Symfony IpUtils function is from:
                    // https://stackoverflow.com/a/36152302
                    if (IpUtils::checkIp6($resolved[$i]["ipv6"], '::ffff:10.0.0.0/104')     ||  // 10.0.0.0/8
                        IpUtils::checkIp6($resolved[$i]["ipv6"], '::ffff:172.16.0.0/108')   ||  // 172.16.0.0/12
                        IpUtils::checkIp6($resolved[$i]["ipv6"], '::ffff:192.168.0.0/112')  ||  // 192.168.0.0/16
                        IpUtils::checkIp6($resolved[$i]["ipv6"], '::ffff:127.0.0.1/104')) {     // 127.0.0.1/8
                        return false;
                    }

                    $lastValidIPV6 = $resolved[$i]["ipv6"];
                }
            }

            // A return ref was provided, so give callers a string they can use to make sure they're hitting the IP that we approved
            if (func_num_args() > 1) {
                $curlopt_resolve = $host
                . ':' . ($port ? $port : ($scheme == 'https' ? '443' : '80'))
                . ':'
                . ($lastValidIPV4 ? $lastValidIPV4 : $lastValidIPV6);
            }

            // filter xss
            if (function_exists('xss_clean')) {
                $url = xss_clean($url);
            }
            return $url;
        }

        function curl_from_json($url, $array=false, $decode=true) {

            $ch = $this->cURL->curl_init();
            $this->cURL->curl_setopt($ch, CURLOPT_USERAGENT, 'Data.gov data.json crawler');

            $this->cURL->curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $this->cURL->curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $this->cURL->curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $this->cURL->curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            $this->cURL->curl_setopt($ch, CURLOPT_FILETIME, true);

            $this->cURL->curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            $this->cURL->curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            $this->cURL->curl_setopt($ch, CURLOPT_COOKIE, "");

            $this->cURL->curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            $this->cURL->curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

            $data = $this->safe_curl_exec($url, $ch, true);

            if ($this->cURL->curl_errno($ch)) {
                log_message('error', "curl_from_json error: " . $this->cURL->curl_error($ch));
                throw new \Exception($this->cURL->curl_error($ch), $this->cURL->curl_errno($ch));
            }

            $this->cURL->curl_close($ch);

            if($decode == true) {
                ini_set('mbstring.substitute_character', "none");
                $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
                return json_decode($data, $array);
            } else {
                return $data;
            }


        }


        function curl_header($url, $follow_redirect = true, $tmp_dir = null, $force_shim = false) {

            if ($force_shim) {
                return $this->curl_head_shim($url, $follow_redirect, $tmp_dir);
            }

            $info = array();

            $ch = $this->cURL->curl_init();

            $this->cURL->curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');

            $this->cURL->curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $this->cURL->curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

            $this->cURL->curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $this->cURL->curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            $this->cURL->curl_setopt($ch, CURLOPT_FILETIME, true);

            $this->cURL->curl_setopt($ch, CURLOPT_NOBODY, true);
            $this->cURL->curl_setopt($ch, CURLOPT_HEADER, true);

            $this->cURL->curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            $this->cURL->curl_setopt($ch, CURLOPT_COOKIE, "");

            $this->cURL->curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            $this->cURL->curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

            $http_heading = $this->safe_curl_exec($url, $ch, $follow_redirect);

            $info['header'] = http_parse_headers($http_heading);
            $info['info'] = $this->cURL->curl_getinfo($ch);
            if(is_bool($info['info'])) {
                // There's some problem with the wrapper; call curl_info() directly.
                $info['info'] = curl_getinfo($ch);
            }
            // echo "\n\n\n===\ninfo[info]: ".print_r($info['info'], true)."===\n"; ob_flush();
            $this->cURL->curl_close($ch);


            // If the server didn't support HTTP HEAD, use the shim.
            if( (!empty($info['header']['X-Error-Message']) && trim($info['header']['X-Error-Message']) == 'HEAD is not supported')
            OR empty($info['header']['Content-Type'])) {
                return $this->curl_head_shim($url, $follow_redirect, $tmp_dir);
            } else {
                return $info;
            }

        }


        function curl_head_shim($url, $follow_redirect = true, $tmp_dir = '') {

            $info = array();

            $ch = $this->cURL->curl_init();

            $output = fopen('/dev/null', 'w');
            $header_dir = $tmp_dir . '/curl_header';
            $headerfile = fopen($header_dir, 'w+');

            $this->cURL->curl_setopt($ch, CURLOPT_FILE, $output);

            $this->cURL->curl_setopt($ch, CURLOPT_WRITEHEADER, $headerfile);
            $this->cURL->curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $this->cURL->curl_setopt($ch, CURLOPT_HEADER, true);

            $this->cURL->curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');

            $this->cURL->curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
            $this->cURL->curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

            $this->safe_curl_exec($url, $ch, $follow_redirect);

            if ($this->cURL->curl_errno($ch)) {
                log_message('error', "curl_head_shim error: " . $this->cURL->curl_error($ch));
                throw new \Exception($this->cURL->curl_error($ch), $this->cURL->curl_errno($ch));
            }

            fclose($headerfile);

            $http_heading = file_get_contents($header_dir);
            unset($header_dir);

            $info['info'] = $this->cURL->curl_getinfo($ch);
            if(empty($info['info'])) {
                // There's some problem with the curl_info() wrapper; call it directly.
                $info['info'] = curl_getinfo($ch);
            }
            // echo "\n\n\n===\ninfo[info]: ".print_r($info['info'], true)."\n===\n"; ob_flush();

            $this->cURL->curl_close($ch);

            $info['header'] = http_parse_headers($http_heading);

            return $info;

        }

        // Do a (potentially recursive) curl request while defending against SSRF attacks
        // TODO https://github.com/GSA/datagov-deploy/issues/1759
        function safe_curl_exec($url, $ch, $follow_redirect = true, $maxRedirs = 10) {

            // We take care of redirects ourselves to deflect SSRF attempts
            $this->cURL->curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

            $numRedirects = 0;
            do {
                if (!$this->filter_remote_url($url, $ipresolution)) {
                    throw new \Exception("Encountered bad URL during curl request: ".$url);
                }

                // Make sure that the IP curl actually hits is the one that we just validated as OK
                // This combats DNS Rebinding attacks by binding our request to the IP iniitially resolved.
                $this->cURL->curl_setopt($ch, CURLOPT_RESOLVE, array($ipresolution));

                // Set the target URL
                $this->cURL->curl_setopt($ch, CURLOPT_URL, $url);
                $http_heading = $this->cURL->curl_exec($ch);

                // Watch out for problems
                if ($this->cURL->curl_errno($ch)) {
                    log_message('error', "curl_header error: " . $this->cURL->curl_error($ch));
                    throw new \Exception($this->cURL->curl_error($ch), $this->cURL->curl_errno($ch));
                }

                // Check for a redirect
                $info = $this->cURL->curl_getinfo($ch);
                if(!is_bool($info)) {
                    $url = $this->cURL->curl_getinfo($ch, CURLINFO_REDIRECT_URL);
                } else {
                    // There's some problem with the curl_info() wrapper; call it directly.
                    $url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
                }

            } while ($follow_redirect           // Continue if redirects were requested
            && $url != ''                       // ...and we got a redirect
            && $numRedirects++ < $maxRedirs);   // ...and we haven't we reached the maximum

            return $http_heading;
        }

    }

}

namespace {

if (!function_exists('http_parse_headers')) {

    function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = ''; // [+]

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                }
                else
                {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            }
            else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
                elseif (!$key) // [+]
                $headers[0] = trim($h[0]);trim($h[0]); // [+]
            } // [+]
        }

        return $headers;
    }
}


/**
* This mashes together two arrays with the same keys
* It fills in any empty values, but gives precedence to the $primary array
*/

function array_mash($primary, $secondary) {
    $primary = (array)$primary;
    $secondary = (array)$secondary;
    $out = array();
    foreach($primary as $name => $value) {
        if ( array_key_exists($name, $secondary) && !empty($secondary[$name]) && empty($value)) {
            $out[$name] = $secondary[$name];
        }
        else {
            $out[$name] = $value;
        }
    }
    return $out;
}


function get_between($input, $start, $end)
{
    $substr = substr($input, strlen($start)+strpos($input, $start), (strlen($input) - strpos($input, $end))*(-1));
    return $substr;
}


/**
* Performs the same function as array_search except that it is case
* insensitive
* @param mixed $needle
* @param array $haystack
* @return mixed
*/

function array_nsearch($needle, array $haystack) {
    $it = new IteratorIterator(new ArrayIterator($haystack));
    foreach($it as $key => $val) {
        if(strcasecmp($val,$needle) === 0) {
            return $key;
        }
    }
    return false;
}


/**
* Provides a human readable file size from an integer of bytes
*/
function human_filesize($size,$unit="") {
    if( (!$unit && $size >= 1<<30) || $unit == "GB")
    return number_format($size/(1<<30),2)."GB";
    if( (!$unit && $size >= 1<<20) || $unit == "MB")
    return number_format($size/(1<<20),2)."MB";
    if( (!$unit && $size >= 1<<10) || $unit == "KB")
    return number_format($size/(1<<10),2)."KB";
    return number_format($size)." bytes";
}


function make_utf8 ($input) {
    return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($input));
}


function is_json($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}


function json_text_filter($datajson) {

    // Clean up the data a bit

    /*
    This is to help accomodate encoding issues, eg invalid newlines. See:
    http://forum.jquery.com/topic/json-with-newlines-in-strings-should-be-valid#14737000000866332
    http://stackoverflow.com/posts/17846592/revisions
    */
    $datajson = preg_replace('/[ ]{2,}|[\t]/', ' ', trim($datajson));
    //$data = str_replace(array("\r", "\n", "\\n", "\r\n"), " ", $data);
    //$data = preg_replace('!\s+!', ' ', $data);
    //$data = str_replace(' "', '"', $data);

    $datajson = preg_replace('/,\s*([\]}])/m', '$1', utf8_encode($datajson));


    /*
    This is to replace any possible BOM "Byte order mark" that might be present
    See: http://stackoverflow.com/questions/10290849/how-to-remove-multiple-utf-8-bom-sequences-before-doctype
    and
    http://stackoverflow.com/questions/3255993/how-do-i-remove-i-from-the-beginning-of-a-file
    */
    // $bom = pack('H*','EFBBBF');
    // $datajson = preg_replace("/^$bom/", '', $datajson);
    $datajson = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $datajson);

    return $datajson;

}


function filter_json( $source_datajson, $dataset_array = false ) {

    if ($dataset_array) {
        $datasets = $source_datajson->dataset;
    } else {
        $datasets = $source_datajson;
    }

    foreach ($datasets as $key => $dataset) {

        foreach ($dataset as $field_name => $field_value) {

            if (is_string($field_value)) {
                $field_value =  filter_var( $field_value, FILTER_SANITIZE_STRING );
            }

            $dataset->$field_name = $field_value;

        }

    }

    if ($dataset_array) {
        $source_datajson->dataset = $datasets;
    } else {
        $source_datajson = $datasets;
    }

    return $source_datajson;
}

function linkToAnchor($text) {

    $convertedText = preg_replace( '@(?<![.*">])\b(?:(?:https?|ftp|file)://|[a-z]\.)[-A-Z0-9+&#/%=~_|$?!:,.]*[A-Z0-9+&#/%=~_|$]@i', '<a href="\0" target="_blank">\0</a>', $text );

    return $convertedText;

}

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $prev_char = '';
    $in_quotes = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if( $char === '"' && $prev_char != '\\' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
        $prev_char = $char;
    }

    return $result;
}
}
?>
