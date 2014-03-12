<?php

/**
 * Sendy tests
 *
 * This Sendy PHP Class connects to the Sendy API that sends mails using Amazon SES.
 *
 * @author Jeroen Desloovere <jeroen@siesqo.be>
 */
 
// require
require_once '../src/SendyPHP.php';

// define credentials
$apiKey = '';
$apiUrl = '';
$listId = '';

// init api
$api = new SendyPHP($apiKey, $apiUrl, $listId)

// has subscribers?
$result = $api->hasSubscribers();

// dump result
print_r($result);
