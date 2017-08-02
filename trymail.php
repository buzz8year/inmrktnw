<?php

// multiple recipients
$to  = 'dk.pochtamp@gmail.com'; // note the comma
// $to  = 'dk.pochtamp@gmail.com' . ', '; // note the comma
// $to .= 'wez@example.com';

// subject
$subject = 'Mail debugging';

// message
$message = 'Hello!';

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: Denis <dk.pochtamp@gmail.com>' . "\r\n";
$headers .= 'From: Birthday Reminder <info@inmrkt.ml>' . "\r\n";

// Mail it
// mail($to, $subject, $message, $headers);
$mail = mail($to, $subject, $message, $headers);

if (mail($to, $subject, $message, $headers)) {
	echo($to . '<br/>' . $subject . '<br/>' . $message . '<br/>' . $headers);
} else {
	var_dump(error_get_last());
}