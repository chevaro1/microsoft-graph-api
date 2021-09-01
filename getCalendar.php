<?php

/**
So heres what were going to do:
we want a class that:
  - you input the email address
  - it finds the calendar id of the subject and saves it
  - methods for:
    - get calendar
    - add event
    - delete event


**/

require __DIR__ . '/vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;





#$graph = new Graph();
#$graph->setAccessToken($accessToken);

class calendar {
  public $emailAddress;
  public $calendarId;
  public $tenantId = "5b7af124-89bd-41c5-8176-97edc7306c18";
  public $clientId = "a01f90ac-b9ad-4acd-8915-f329556496e8";
  public $clientSecret = "p~p_eu.LwLxb7~DXr.3J4t8Vj51IQnThY6";
  public $graph;

  public function __construct($emailAddress){
    echo "calendar created: \n";
    $this->graph = new Microsoft\Graph\Graph();
    $this->graph->setAccessToken($this->get_access_token());
    $this->emailAddress = $emailAddress;
    $this->calendarId = $this->get_calendar_id($emailAddress);
  }

  public function get_access_token(){
    $guzzle = new \GuzzleHttp\Client();
    $url = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/token?api-version=1.0';
    $token = json_decode($guzzle->post($url, [
        'form_params' => [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'resource' => 'https://graph.microsoft.com/',
            'grant_type' => 'client_credentials',
        ],
    ])->getBody()->getContents());
    $accessToken = $token->access_token;
    return $accessToken;
  }

  public function get_calendar_id($emailAddress){
    $url = "/users/$emailAddress/calendars";
    $response = $this->graph->createRequest("GET", $url)
                      ->setReturnType(\Microsoft\Graph\Model\User::class)
                      ->execute();

    foreach($response as $calendar){
      $calendarName = $calendar->getProperties()["name"];
      if($calendarName == "Calendar") {
        return $calendar->getProperties()["id"];
      }
    }
  }

  public function add_event($subject, $start, $end, $body = '', $timezone = 'Europe/London'){
    $data = [
        'Subject' => $subject,
        'Body' => [
            'ContentType' => 'HTML',
            'Content' => $body,
        ],
        'Start' => [
            'DateTime' => $start,
            'TimeZone' => $timezone,
        ],
        'End' => [
            'DateTime' => $end,
            'TimeZone' => $timezone,
        ],
    ];

    $url = "/users/" . $this->emailAddress . "/calendars/" . $this->calendarId . "/events";
    $response = $this->graph->createRequest("POST", $url)
        ->attachBody($data)
        ->setReturnType(\Microsoft\Graph\Model\Event::class)
        ->execute();
  }

  public function delete_event($eventId){

    $url = "/users/" . $this->emailAddress . "/calendars/$eventId";
    $response = $this->graph->createRequest("DELETE", $url)
                      ->setReturnType(\Microsoft\Graph\Model\Event::class)
                      ->execute();
  }

  public function get_calendar($startDate, $endDate, $startTime = '00:00:00', $endTime = '23:59:00'){
    $results = [];
    $startDateTime = $startDate . 'T' . $startTime;
    $endDateTime = $endDate . 'T' . $endTime;
    $calendarView = $this->graph->createRequest("GET", "/users/" . $this->emailAddress . "/calendarview?startDateTime=$startDateTime&endDateTime=$endDateTime")
                          ->setReturnType(\Microsoft\Graph\Model\User::class)
                          ->execute();

    foreach($calendarView as $event){
      array_push($results, $event->getProperties());
    }
    return $results;
  }

}



$mycal = new calendar("wgalvin@gnil.net");
#echo $mycal->get_calendar_id("wgalvin@gnil.net");
#echo $mycal->calendarId;


$dates = $mycal->get_calendar("2021-06-01", "2021-06-01");
foreach($dates as $date){
  print_r($date);
  #echo $date["id"];
  echo "\n";
}


#$mycal->add_event("running from php", "2021-06-01T17:01:00", "2021-06-01T17:31:00");

#$mycal->delete_event("AAMkADhhZTk1ZTU5LWQ5MTUtNDQ1My04YzY0LWIxYTAyN2VlYzdlMgBGAAAAAADOAt7Lk2kNQpvDJR5NxeCQBwDf3PMgJVeESIB9aC3AQuNcAAAAAAENAADf3PMgJVeESIB9aC3AQuNcAAC3k431AAA=");








 ?>
