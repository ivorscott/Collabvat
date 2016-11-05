<?php
/**
 * Print array contents for debugging purposes.
 * @param array $data The data to inspect
 */
function dBug($data) {
 	echo "<pre>".var_export($data,1)."</pre>";
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address
 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole $img True to return a complete IMG tag False for just the URL
 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
 * @return String containing either just a URL or a complete image tag
 * @source http://gravatar.com/site/implement/images/php/
 */
function get_gravatar( $email, $defaultImage, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";

    if ($img) {
         $url = '<img src="' . $url . '"';
         foreach ( $atts as $key => $val ) 
             $url .= ' ' . $key . '="' . $val . '"';
         $url .= ' />';
     }

     $isImageResult = is_gravatar_image($url);
     //echo $isImageResult;

    if ($isImageResult == 'true') {
		return $url;
	} else {
		return $defaultImage;
	}
}

/**
 * Verify Gravatar image url.
 * 
 * When a Gravatar image is not found using the email provided, Gravatar gives us a missing person
 * image instead. The md5 encoding of this missing person image is either 
 * '07117d43e2abe74182618d277d813fe8' or '0bca52afdb2b9998132355d716390c9f' (it changes). 
 *
 * @param string $imageUrl The image url 
 * @return String containing either true or false
 * @source Anthony Toorie - toorie@devpie.co
 */
function is_gravatar_image($imageUrl) {

	$image = md5(file_get_contents($imageUrl));
	// echo "</br>";
	//echo $image . "</br>";
	if(($image) == '07117d43e2abe74182618d277d813fe8' || ($image) == '0bca52afdb2b9998132355d716390c9f'){
		return 'false';
	}else{
		return 'true';
	}
}