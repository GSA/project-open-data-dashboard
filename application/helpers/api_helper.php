<?php

function curl_from_json($url) {

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$data=curl_exec($ch);
	curl_close($ch);


	return json_decode($data, true);	

}


function curl_header($url) {
	$info = array();
	
	$curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FILETIME, true);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $info['header'] = curl_exec($curl);
    $info['info'] = curl_getinfo($curl);
    curl_close($curl);

	return $info;
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