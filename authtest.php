<?php

require __DIR__ . '/vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

$tenantId = "5b7af124-89bd-41c5-8176-97edc7306c18";

$clientId = "a01f90ac-b9ad-4acd-8915-f329556496e8";

$clientSecret = "p~p_eu.LwLxb7~DXr.3J4t8Vj51IQnThY6";

$guzzle = new \GuzzleHttp\Client();
$url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/token?api-version=1.0';
$token = json_decode($guzzle->post($url, [
    'form_params' => [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'resource' => 'https://graph.microsoft.com/',
        'grant_type' => 'client_credentials',
    ],
])->getBody()->getContents());
$accessToken = $token->access_token;

#echo $accessToken;


$graph = new Graph();
$graph->setAccessToken($accessToken);

$user = $graph->createRequest("GET", "/users")
              ->setReturnType(\Microsoft\Graph\Model\User::class)
              ->execute();

#echo $user;
$street = $user[0];
#print_r($street);

$properties = $street->getProperties();
#print_r($properties["mail"]);
#print_r($user[0]->getDisplayName());

foreach ($user as $use) {
  #print_r($use->getDisplayName());
  #echo "<br> GETTING NEXT PHP_EOL";
}


$calendar = $graph->createRequest("GET", "/users/wgalvin@gnil.net/calendar")
                  ->setReturnType(\Microsoft\Graph\Model\User::class)
                  ->execute();

#print_r($calendar);



$calendarView = $graph->createRequest("GET", "/users/wgalvin@gnil.net/calendarview?startDateTime=2021-06-28T19:00:00-08:00&endDateTime=2021-07-08T20:00:00-08:00")
                  #->addHeaders(array("Prefer" => "outlook.timezone='Europe/London'"))
                  ->setReturnType(\Microsoft\Graph\Model\User::class)
                  ->execute();

print_r($calendarView);


$data = [
    'Subject' => 'Discuss the Calendar REST API',
    'Body' => [
        'ContentType' => 'HTML',
        'Content' => 'I think it will meet our requirements!',
    ],
    'Start' => [
        'DateTime' => '2021-07-02T10:00:00',
        'TimeZone' => 'Pacific Standard Time',
    ],
    'End' => [
        'DateTime' => '2021-07-02T11:00:00',
        'TimeZone' => 'Pacific Standard Time',
    ],
];
#$baseId = 'base-ID';
#$calendarId = $baseId . 'calendar-ID';
$calendarId = "AAMkADhhZTk1ZTU5LWQ5MTUtNDQ1My04YzY0LWIxYTAyN2VlYzdlMgBGAAAAAADOAt7Lk2kNQpvDJR5NxeCQBwDf3PMgJVeESIB9aC3AQuNcAAAAAAEGAADf3PMgJVeESIB9aC3AQuNcAAAAABn2AAA=";
#$url = "/me/calendars/$calendarId/events";
$url = "/users/wgalvin@gnil.net/calendars/$calendarId/events";
$response = $graph->createRequest("POST", $url)
    ->attachBody($data)
    ->setReturnType(\Microsoft\Graph\Model\Event::class)
    ->execute();

print_r($response);


$url = "/users/wgalvin@gnil.net/calendars";
$response = $graph->createRequest("GET", $url)
                  ->setReturnType(\Microsoft\Graph\Model\User::class)
                  ->execute();

#echo "PRINTING CALEDNARS";
#print_r($response);

$emailAddress = "hithere";
$calendarId = "byethere";

#$url = "/users/" . $emailAddress . "/calendars/" . $calendarId . "/events";
#echo $url;
$eventId = "AAMkADhhZTk1ZTU5LWQ5MTUtNDQ1My04YzY0LWIxYTAyN2VlYzdlMgBGAAAAAADOAt7Lk2kNQpvDJR5NxeCQBwDf3PMgJVeESIB9aC3AQuNcAAAAAAENAADf3PMgJVeESIB9aC3AQuNcAAC1-G5bAAA=";

$url = "/users/wgalvin@gnil.net/calendars/$eventId";
$response = $graph->createRequest("DELETE", $url)
                  ->setReturnType(\Microsoft\Graph\Model\Event::class)
                  ->execute();

#print_r($response);
