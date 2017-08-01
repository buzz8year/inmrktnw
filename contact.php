<?php

$json = array();

if (!empty($_POST['email']) && !empty($_POST['message'])) {

    $to_inmrkt = 'info@inmrkt.ml';
    $to_gmail = 'dk.pochtamp@gmail.com';

    $from = $_POST['email'];
    $phone = $_POST['phone'];
    // $name = $_POST['name'];

    $headers = 'From:' . $from;
    $subject = 'Message via form at INMRKT.ML';
    $message = 'Sender wrote the following:' . '\n\n' . $_POST['message'];
    // $message = 'Sender wrote the following:' . '\n\n' . $_POST['message'] . ($phone ? ('\n\n' . $phone) : '');

    $headers_for_sender = 'From:' . $to_inmrkt;
    $subject_for_sender = 'Your message has been recieved';
    $message_for_sender = 'Here is a copy of your message \n\n' . $_POST['message'];

    mail($to_inmrkt, $subject, $message, $headers);
    mail($to_gmail, $subject, $message, $headers);

    mail($from, $subject_for_sender, $message_for_sender, $headers_for_sender);

    if (@mail($to_gmail, $subject, $message, $headers)) {

        $json['response'] = 'Message sent, thank you! We would greatly appreciate it if you give us about 24 hours to respond.';

    } else {

        $json['response'] = 'Message not sent.';

    }



} else {

    if (empty($_POST['email'])) {
        $json['error']['error_email'] = 'Please, enter your E-mail *';
    }

    if (empty($_POST['message'])) {
        $json['error']['error_message'] = 'Please, write a couple of words *';
    }

    $json['response'] = 'Data was not posted.';

}

header('Content-Type: application/json');

echo json_encode($json);

// var_dump($_POST);

?>
