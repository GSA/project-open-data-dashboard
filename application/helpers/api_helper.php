<?php

function curl_from_json($url, $array=false, $decode=true) {

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');
	curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);	
	
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_FILETIME, true);
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
    
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_COOKIE, "");
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);    
    

	$data=curl_exec($ch);
	curl_close($ch);

    if($decode == true) {
      $data = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($data));
	    return json_decode($data, $array);	        
    } else {
        return $data;
    }


}


function curl_header($url, $follow_redirect = true, $tmp_dir = null, $force_shim = false) {

  if ($force_shim) {
    return curl_head_shim($url, $follow_redirect, $tmp_dir);
  }

  $info = array();
  
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');
  curl_setopt($ch, CURLOPT_URL, $url);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);  

  curl_setopt($ch, CURLOPT_TIMEOUT, 10);  

  curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
  curl_setopt($ch, CURLOPT_FILETIME, true);

  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  
  curl_setopt($ch, CURLOPT_COOKIESESSION, true);
  curl_setopt($ch, CURLOPT_COOKIE, "");

  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_redirect);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

  $http_heading = curl_exec($ch);

  $info['header'] = http_parse_headers($http_heading);
  $info['info'] = curl_getinfo($ch);
  curl_close($ch);


  // If the server didn't support HTTP HEAD, use the shim. 
  if( (!empty($info['header']['X-Error-Message']) && trim($info['header']['X-Error-Message']) == 'HEAD is not supported')
      OR empty($info['header']['Content-Type'])) {   
    return curl_head_shim($url, $follow_redirect, $tmp_dir);
  } else {
    return $info;
  }

}



function curl_head_shim($url, $follow_redirect = true, $tmp_dir = '') {

  $info = array();

  $ch = curl_init();

  $output = fopen('/dev/null', 'w');
  $header_dir = $tmp_dir . '/curl_header';
  $headerfile = fopen($header_dir, 'w+');

  curl_setopt($ch, CURLOPT_URL, $url);

  curl_setopt($ch, CURLOPT_FILE, $output);

  curl_setopt($ch, CURLOPT_WRITEHEADER, $headerfile);
  curl_setopt($ch, CURLOPT_TIMEOUT, 3); 
  curl_setopt($ch, CURLOPT_HEADER, true); 

  curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');

  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow_redirect);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

  curl_exec($ch);

  fclose($headerfile);

  $http_heading = file_get_contents($header_dir);
  unset($header_dir);

  $info['info'] = curl_getinfo($ch);

  curl_close($ch);

  $info['header'] = http_parse_headers($http_heading);

  return $info;

}


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


function filter_remote_url($url, $allowed_schemes = array('http', 'https')) {
    if (empty($url)) {
        return null ;
    }
    $url = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED);
    // ban non http/https:
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if ($url && !in_array($scheme, $allowed_schemes)) {
        $url = false;
    }

    // ban localhost/portscan/ssrf

    if ($url) {

        $host = parse_url($url, PHP_URL_HOST);
        /* We should check if host is IP first*/
        if (filter_var($host, FILTER_VALIDATE_IP))// is ip
        {
            $url = false;
        }
        /*We should check if it is a hostname - resolving url*/
        else
        {
            // If record resolved
            $resolved = dns_get_record($host, DNS_A + DNS_AAAA);
            if ($resolved)
            {
                // We should read the array of A and AAAA records, and check them against private ranges
                for ($i=0; $i < count($resolved); $i++)
                {
                    if ($resolved[$i]["type"] === "A")
                    if (!filter_var($resolved[$i]["ip"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ))
                        $url = false;
                    if ($resolved[$i]["type"] === "AAAA")
                    if (!filter_var($resolved[$i]["ipv6"], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ))
                        $url = false;
                }

            }
            else
                $url = false;
        }


    }
    // filter xss
    if ($url && function_exists('xss_clean')) {
        $url = xss_clean($url);
    }
    return $url;
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

?>
