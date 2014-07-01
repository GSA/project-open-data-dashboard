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


function filter_json( $datajson ) {


  foreach ($datajson as $key => $dataset) {

    foreach ($dataset as $field_name => $field_value) {

      if (is_string($field_value)) {
        $field_value =  filter_var( $field_value, FILTER_SANITIZE_STRING );
      } 

      $dataset->$field_name = $field_value;

    }


  }

    return $datajson; 
}



function linkToAnchor($text) {
// The Regular Expression filter
$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

// The Text you want to filter for urls

// Check if there is a url in the text
if(preg_match_all($reg_exUrl, $text, $url)) {
       // make the urls hyper links
       $matches = array_unique($url[0]);
       foreach($matches as $match) {
            $replacement = "<a href=".$match.">{$match}</a>";
            $text = str_replace($match,$replacement,$text);
       }
       return $text;
} else {

       // if no urls in the text just return the text
       return $text;

}
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