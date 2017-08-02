<?php

// multiple recipients
$to  = 'dk.pochtamp@gmail.com'; // note the comma
// $to  = 'dk.pochtamp@gmail.com' . ', '; // note the comma
// $to .= 'wez@example.com';

// subject
$subject = 'Birthday Reminders for August';

// message
$message = 'Hello!';

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
$headers .= 'From: Birthday Reminder <birthday@example.com>' . "\r\n";
$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

// Mail it
// mail($to, $subject, $message, $headers);
mail($to, $subject, $message);