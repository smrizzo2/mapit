<?php

/*	This PHP page accepts two parameters on the query string, "location" and "call.
 *	e.g. https://library.monmouth.edu/stack_maps/index.php?location=main&call=BD304.1
 *
 *      Note: you will need to modify the switch() statement at the end to handle all of 
 *            the various locations you have in your library.
 */ 

require('lc-sort.php');		// LC sorting functions; so we can compare calls
require('sierra_maps_db.php');	// Contains arrays which list which calls are on which shelves

/* Helper functions */
function main_sort($call, $shelves) {
    foreach ($shelves as $key => $element) {
	$compare = array($key => $element);
	$unknown = array($call => '?');
	$compare = array_merge($compare, $unknown);
	$ret = uksort($compare, "SortLC"); //sort the list
	reset($compare);
	if (key($compare) == key($unknown) )  {
		end($compare);
		return current($compare);
	}
    }
    return("?");
}

function serve_image($file_name)  {
	/* INPUT: actual file name we want to serve up (i.e. main-5.jpg).
	 * OUTPUT: an HTTP object with the appropriate headers for an image.
	 */
	$image_base_path="images/";
	$file_name = $image_base_path . $file_name;
	header('Content-Type:'.$type);

	if (file_exists($file_name) ) {
		//print "Serving $filename";
		header("Content-Length:" . filesize($file_name));
		readfile($file_name);
	} else {
		//print "Unknown served. looked for $file_name";
		header("Content-Length:" . filesize("images/unknown.png"));
		readfile("images/unknown.png");
	}
}//function

/*
 ******************************************************************************
 *                                      MAIN 
 ******************************************************************************
 */

/*  Two GET attributes are passed in. input filters are ASCII chars 32-127
	location=<location>  and call=<number>
*/
if (! array_key_exists("location", $_GET) )
	die("location value needs to be passed via GET");
if (! array_key_exists("call", $_GET) )
	die("call value needs to be passed via GET");
$location = strtolower($_GET["location"]);
$call_naked = $_GET["call"];
$call = strtolower($call_naked);


switch ($location)  {
	/* Whatever location= parameter you use in the URL must be addressed down below here.  Some locations
	 * only have 1 shelf, in which case, theres no need to figure out a shelf #, so we just assume Shelf 1.
	 * For most locations, we need to figure out the shelf #, so we use main_sort() and pass to it the call
	 * we're looking for, and the appropriate array (defined in sierra_maps_db.php) for that location.
	 * 
	 * Occasionally, theres a location that doesn't use Library of Congress calls.. like our Juvenile (juv)
	 * location.  In that case, we can't use main_sort(), so we came up with some special rules (if/then/else)
	 * that figures out what is on each of the 3 shelves in that location.  
	 */ 
	// locations that only have one stack.  we dont care what the call # is.  picture will be  <location>-1.gif.
	case 'main':	// main
		serve_image($location . "-" . main_sort($call_naked,$main_shelves) . ".png");
		break;
	case 'ref':	// reference
		serve_image($location . "-" . main_sort($call_naked,$ref_shelves) . ".png");
		break;
	case 'overs':	// oversize
		serve_image($location . "-" . main_sort($call_naked,$oversize_shelves) . ".png");
		break;
	case 'njcol':	// nj collection
		serve_image($location . "-" . main_sort($call_naked,$njcol_shelves) . ".png");
		break;
	case 'ovnj':	// nj oversize
		serve_image($location . "-" . main_sort($call_naked,$ovnj_shelves) . ".png");
		break;
	case 'juv':
		$l = substr($call, 0 , 1);
		if ($l == "f")
			serve_image("juv-1.png");
		else
			if (strcmp($call , '818W1') > 0)
				serve_image("juv-3.png");
			else
				serve_image("juv-2.png");
		break;
	default: //anything else.. maybe someone passed a weird location= to us.. can serve a default image, or just do nothing
                serve_image($location .  "-1.png");
                break;
}
?>
