<?php
function date_convert($date,$type){
  $date_year=substr($date,0,4);
  $date_month=substr($date,5,2);
  $date_day=substr($date,8,2);
  if($type == 1):
  	// Returns the year Ex: 2003
  	$date=date("Y", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 2):
  	// Returns the month Ex: January
  	$date=date("F", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 3):
  	// Returns the short form of month Ex: Jan
  	$date=date("M", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 4):
  	// Returns numerical representation of month with leading zero Ex: Jan = 01, Feb = 02
  	$date=date("m", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 5):
  	// Returns numerical representation of month without leading zero Ex: Jan = 1, Feb = 2
  	$date=date("n", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 6):
  	// Returns the day of the week Ex: Monday
  	$date=date("w", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 7):
  	// Returns the day of the week in short form Ex: Mon, Tue
  	$date=date("D", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 8):
  	// Returns a combo ExL Wed,Nov 12th,2003
  	$date=date("l, F jS", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 9):
  	// Returns a combo Ex: November 12th,2003
  	$date=date("F j, Y", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 10):
	// Returns a combo Ex: November 12th,2003
	$date=date("l", mktime(0,0,0,$date_month,$date_day,$date_year));
  elseif($type == 11):
	// Returns a combo Ex: November 12th,2003
  	$date=date("F j, Y H:m:s", mktime(0,0,0,$date_month,$date_day,$date_year));
  endif;
  return $date;
};

// cut the description off at a given number of words

function process_string($string,$count) {

$word_limit = $count;
$string = str_replace("\n"," ",$string);
$string = str_replace(" "," ",$string);
$word_array = explode(" ", $string);
$num_of_words = count($word_array);
$word_array_trimmed = array_splice ($word_array, 0, $word_limit);
$final_string = implode(" ",$word_array_trimmed);

return $final_string;
}

function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// end word cutoff

function filename_safe($userfile) {
		$temp = $userfile;
		// Lower case
		$temp = strtolower($temp);

		// Replace spaces with a ’_’
		$temp = str_replace(' ', '_', $temp);
		$result = $temp;

// Return filename
return $result;
}

function StripUrl($title)

{

$title = str_replace("#", "sharp", $title);

$title = str_replace("/", "or", $title);

$title = str_replace("$", "", $title);

$title = str_replace("&amp;", "and", $title);

$title = str_replace("&", "and", $title);

$title = str_replace("+", "plus", $title);

$title = str_replace(",", "", $title);

$title = str_replace(":", "", $title);

$title = str_replace(";", "", $title);

$title = str_replace("=", "equals", $title);

$title = str_replace("?", "", $title);

$title = str_replace("@", "at", $title);

$title = str_replace("<", "", $title);

$title = str_replace(">", "", $title);

$title = str_replace("%", "", $title);

$title = str_replace("{", "", $title);

$title = str_replace("}", "", $title);

$title = str_replace("|", "", $title);

$title = str_replace("\\", "", $title);

$title = str_replace("^", "", $title);

$title = str_replace("~", "", $title);

$title = str_replace("[", "", $title);

$title = str_replace("]", "", $title);

$title = str_replace("`", "", $title);

$title = str_replace("'", "", $title);

$title = str_replace("\"", "", $title);

$title = str_replace(" ", "_", $title);

return $title;

}

function getIP() {

	$ip;
	if (getenv("HTTP_CLIENT_IP"))
		$ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR"))
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR"))
		$ip = getenv("REMOTE_ADDR");
	else
		$ip = "UNKNOWN";

	return $ip;

}

function TrimChannel($channel)

{

	$channel = str_replace(".0", "", $channel);

	return $channel;

}


// crop thumbnail to 120 x 75



function cropImage($nw, $nh, $source, $stype, $dest) {

    $size = getimagesize($source);
    $w = $size[0];
    $h = $size[1];

    switch($stype) {
        case 'gif':
        $simg = imagecreatefromgif($source);
        break;
        case 'jpg':
        $simg = imagecreatefromjpeg($source);
        break;
        case 'png':
        $simg = imagecreatefrompng($source);
        break;
    }

    $dimg = imagecreatetruecolor($nw, $nh);

// 	$ratio = $w/$h;

    $wm = $w/$nw;
    $hm = $h/$nh;

    $h_height = $nh/2;
    $w_height = $nw/2;

    if($ratio > 1) {

        $adjusted_width = $w / $hm;
        $half_width = $adjusted_width / 2;
        $int_width = $half_width - $w_height;

        imagecopyresampled($dimg,$simg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);

    } elseif(ratio < 1) {

        $adjusted_height = $h / $wm;
        $half_height = $adjusted_height / 2;
        $int_height = $half_height - $h_height;

        imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);

    } else {
        imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
    }

    imagejpeg($dimg,$dest,65);
}

// random password generator
function assign_rand_value($num)
{
// accepts 1 - 36
  switch($num)
  {
    case "1":
     $rand_value = "a";
    break;
    case "2":
     $rand_value = "b";
    break;
    case "3":
     $rand_value = "c";
    break;
    case "4":
     $rand_value = "d";
    break;
    case "5":
     $rand_value = "e";
    break;
    case "6":
     $rand_value = "f";
    break;
    case "7":
     $rand_value = "g";
    break;
    case "8":
     $rand_value = "h";
    break;
    case "9":
     $rand_value = "i";
    break;
    case "10":
     $rand_value = "j";
    break;
    case "11":
     $rand_value = "k";
    break;
    case "12":
     $rand_value = "l";
    break;
    case "13":
     $rand_value = "m";
    break;
    case "14":
     $rand_value = "n";
    break;
    case "15":
     $rand_value = "o";
    break;
    case "16":
     $rand_value = "p";
    break;
    case "17":
     $rand_value = "q";
    break;
    case "18":
     $rand_value = "r";
    break;
    case "19":
     $rand_value = "s";
    break;
    case "20":
     $rand_value = "t";
    break;
    case "21":
     $rand_value = "u";
    break;
    case "22":
     $rand_value = "v";
    break;
    case "23":
     $rand_value = "w";
    break;
    case "24":
     $rand_value = "x";
    break;
    case "25":
     $rand_value = "y";
    break;
    case "26":
     $rand_value = "z";
    break;
    case "27":
     $rand_value = "0";
    break;
    case "28":
     $rand_value = "1";
    break;
    case "29":
     $rand_value = "2";
    break;
    case "30":
     $rand_value = "3";
    break;
    case "31":
     $rand_value = "4";
    break;
    case "32":
     $rand_value = "5";
    break;
    case "33":
     $rand_value = "6";
    break;
    case "34":
     $rand_value = "7";
    break;
    case "35":
     $rand_value = "8";
    break;
    case "36":
     $rand_value = "9";
    break;
  }
return $rand_value;
}

function get_rand_id($length)
{
  if($length>0)
  {
  $rand_id="";
   for($i=1; $i<=$length; $i++)
   {
   mt_srand((double)microtime() * 1000000);
   $num = mt_rand(1,36);
   $rand_id .= assign_rand_value($num);
   }
  }
return $rand_id;
}

// takes a binary DB response and returns "Yes" for 1 and "No" for 0
function yesno($response)
{
	if($response == 1) {
		$answer = "Yes";
	}
	else if ($response == 0) {
		$answer = "No";
	}

	return $answer;

}

// takes a binary response and returns the description provided by the $itemdesc parameter below
function listitem($item, $itemdesc)
{
	if($item == 1) {
		$response = $itemdesc . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	else if($item == 0) {
		$response = "";
	}

	return $response;

}

// takes a binary response and returns the description provided by the $itemdesc parameter below
function yesnocustom($result, $choice1, $choice2)
{
	if($result == 1) {
		$custom = $choice1;
	}
	else if($result == 0) {
		$custom = $choice2;
	}

	return $custom;

}


// takes a binary DB response and inserts check or X graphic
function yesnoimg($response)
{
	if($response == 1) {
		$answer = "<img src=\"images/greencheck.gif\" width=\"15\" height=\"14\" />";
	}
	else if ($response == 0) {
		$answer = "<img src=\"images/red_x.gif\" width=\"15\" height=\"14\" />";
	}

	return $answer;

}

function nl2p($string, $line_breaks = false, $xml = true)
{
    // Remove existing HTML formatting to avoid double-wrapping things
    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

    // It is conceivable that people might still want single line-breaks
    // without breaking into a new paragraph.
    if ($line_breaks == true)
        return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '<br'.($xml == true ? ' /' : '').'>'), trim($string)).'</p>';
    else
        return '<p>'.preg_replace("/([\n]{1,})/i", "</p>\n<p>", trim($string)).'</p>';
}

function formatPhone($number) { // accepts a 10-digit phone number and makes it pretty

	if($number == "") {

		$phone = "n/a";

	} else {

		// split phone number into area code, exchange and local number
		$ph1 = substr($number,0,3);
		$ph2 = substr($number,3,3);
		$ph3 = substr($number,6,4);

		$phone = "(" . $ph1 . ") " . $ph2 . "-" . $ph3;

	}

	return $phone;

}
?>