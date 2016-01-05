<?php
header("Access-Control-Allow-Origin: *");
//header("Accept-Encoding: gzip,deflate");
//header("Content-Encoding: gzip");
header("Content-Type: application/json; charset=UTF-8");


# Include the Autoloader (see "Libraries" for install instructions)
set_include_path('/home/ubuntu');
require 'vendor/autoload.php';
use Mailgun\Mailgun;

foreach ($_POST as $key => $value) {
	echo("$key: $value");
}

return;

# Instantiate the client.
$mgClient = new Mailgun('key-5feb0245349a1e6a92bf539c2a069733');
$domain = "racephotos.org";

# Make the call to the client.
$result = $mgClient->sendMessage($domain, array(
    'from'    => 'Race Photos <mailgun@racephoto.org>',
    'to'      => '<hunkim@gmail.com>',
    'subject' => 'Hello',
    'text'    => 'Testing some Mailgun awesomness!'
));

print_r($result);

?>