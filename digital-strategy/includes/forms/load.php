<?php
/**
 * Autoloads classes
 */
function fgAutoloader($class){
	$utils = array('Html', 'Form');
	$ds = DIRECTORY_SEPARATOR;
	
	if(in_array($class, $utils)){
		$file = 'Util' . $ds . $class;
	}else{
		$pieces = explode('_', $class);
		$count = count($pieces);
		$lastIndex = $count-1;
		if($count > 1 && $pieces[0] == 'FG'){
			$last = $pieces[$lastIndex];
			
			if($last != $pieces[1]){
				$pieces[$lastIndex] = $class;
			}
			unset($pieces[0]);
			$file = join($ds, $pieces);
		}
	}
	
	if(isset($file))
		require_once dirname(__FILE__) . $ds . $file . '.php';
}
spl_autoload_register('fgAutoloader');