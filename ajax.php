<?php

require_once 'config.php';
require_once 'include/Mailin.php';

/**
 * @param $email
 * @param $subject
 * @param $body
 * @param bool $include_info_address
 * @return mixed
 */
function sendEmail($email, $subject, $body) {

    $mailin = new Mailin('https://api.sendinblue.com/v2.0', MAILIN_API_KEY);

    $data = array(
        "to" => array($email => "to whom!"),
        "bcc" => [],
        "from" => array("info@infox.com"),
        "subject" => $subject,
        "html" => $body,
        "headers" => array("Content-Type" => "text/html; charset=iso-8859-1")
    );

    $response = $mailin->send_email($data);
    return $response;
}

if(isset($_POST['email'])) {
    sendEmail($_POST['email'], $_POST['subject'], $_POST['body']);
    echo json_encode(['success'=>1]);
}

?>