<?php
require_once('../../../wp-load.php');

$result = wp_mail('test@example.com', 'Testmail van WP', 'Hallo! Dit is een test.', ['Content-Type: text/plain; charset=UTF-8']);

if ($result) {
    echo 'Mail is verstuurd!';
} else {
    echo 'Mail is NIET verstuurd!';
}
