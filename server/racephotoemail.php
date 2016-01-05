<?php
header("Access-Control-Allow-Origin: *");
//header("Accept-Encoding: gzip,deflate");
//header("Content-Encoding: gzip");
header("Content-Type: application/json; charset=UTF-8");


# Include the Autoloader (see "Libraries" for install instructions)
set_include_path('/home/ubuntu');
require 'vendor/autoload.php';
use Mailgun\Mailgun;

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if (!isset($_POST['to'])) {
	return;
}

if (!isset($_POST['photos'])) {
	return;
}

$to = $_POST['to'];

$subject = "Your " . count($_POST['photos']) ." Photos from RacePhoto.org.";

$body = "Thanks for using RacePhoto.org!\n\n";
$body .= "Please download these photos in 24 hours:\n";

foreach ($_POST['photos'] as $key => $value) {
	$body .= "\t $value\n";
}

$body .= "\nAll the best,\nRace Photo\n";

# Instantiate the client.
$mgClient = new Mailgun('key-5feb0245349a1e6a92bf539c2a069733');
$domain = "racephotos.org";

# Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
    'from'    => 'Race Photos <mailgun@racephoto.org>',
    'to'      => $to,
    'subject' => $subject,
    'text'    => $body
));

print (json_encode($result,JSON_UNESCAPED_UNICODE));

//print_r($result);

?>