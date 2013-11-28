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
	    return json_decode($data, $array);	        
    } else {
        return $data;
    }


}


function curl_header($url) {
	$info = array();
	
	$ch = curl_init();
//	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT,'Data.gov data.json crawler');
    curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);	
	
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_FILETIME, true);
    
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_COOKIE, "");
    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

    $info['header'] = curl_exec($ch);
    $info['info'] = curl_getinfo($ch);
    curl_close($ch);

	return $info;
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


?>