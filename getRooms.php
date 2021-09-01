<?php

require __DIR__ . '/vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;





#$graph = new Graph();
#$graph->setAccessToken($accessToken);

class calendar {
  public $tenantId = "5b7af124-89bd-41c5-8176-97edc7306c18";
  public $clientId = "a01f90ac-b9ad-4acd-8915-f329556496e8";
  public $clientSecret = "p~p_eu.LwLxb7~DXr.3J4t8Vj51IQnThY6";
  public $graph;

  public function __construct(){
    echo "room finder created: \n";
    $this->graph = new Microsoft\Graph\Graph();
    $this->graph->setAccessToken($this->get_access_token());
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

  public function add_event($emailAddress, $subject, $start, $end, $body = '', $timezone = 'Europe/London'){
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

    $url = "/users/" . $emailAddress . "/calendars/" . $this->calendarId . "/events";
    $response = $this->graph->createRequest("POST", $url)
        ->attachBody($data)
        ->setReturnType(\Microsoft\Graph\Model\Event::class)
        ->execute();
  }

  public function delete_event($emailAddress, $eventId){

    $url = "/users/" . $emailAddress . "/calendars/$eventId";
    $response = $this->graph->createRequest("DELETE", $url)
                      ->setReturnType(\Microsoft\Graph\Model\Event::class)
                      ->execute();
  }

  public function get_calendar($emailAddress, $startDate, $endDate, $startTime = '00:00:00', $endTime = '23:59:00'){
    $results = [];
    $startDateTime = $startDate . 'T' . $startTime;
    $endDateTime = $endDate . 'T' . $endTime;
    #echo "start date $startDateTime \n";
    #echo "end date $endDateTime \n";
    $calendarView = $this->graph->createRequest("GET", "/users/" . $emailAddress . "/calendarview?startDateTime=$startDateTime&endDateTime=$endDateTime")
                          ->setReturnType(\Microsoft\Graph\Model\User::class)
                          ->execute();

    foreach($calendarView as $event){
      array_push($results, $event->getProperties());
    }
    return $results;
  }

  public function free($emailAddress, $dateTime){
    #free(room,datetime) is it busy at that time
    $events = $this->get_calendar($emailAddress, $dateTime->format('Y-m-d'), $dateTime->format('Y-m-d'), $dateTime->format('H:i:s'), $dateTime->add(new DateInterval('PT1H'))->format('H:i:s'));
    if(count($events) == 0) {
      return false;
    } else {
      return true;
    }
  }

  public function freeUntil($emailAddress, $dateTime){
    #freeUntil(room,datetime) when is next event for that room after that time
    $events = $this->get_calendar($emailAddress, $dateTime->format('Y-m-d'), $dateTime->add(new DateInterval('P10D'))->format('Y-m-d'), $dateTime->format('H:i:s'));
    if(count($events) == 0) {
      return null;
    } else {
      return $events[0]['start']['dateTime'];
    }
  }

  public function freeBefore($emailAddress, $dateTime){
    #freeBefore(room,time) when did last event finish before that time
    $events = $this->get_calendar($emailAddress, $dateTime->sub(new DateInterval('P10D'))->format('Y-m-d'), $dateTime->add(new DateInterval('P10D'))->format('Y-m-d'), $dateTime->format('H:i:s'), $dateTime->format('H:i:s'));
    if(count($events) == 0) {
      return null;
    } else {
      $end = end($events);
      return $end['start']['dateTime'];
    }
  }

  public function findAllRooms(){
    $results = [];
    $rooms = $this->graph->createRequest("GET", "/places/microsoft.graph.room")
                  ->setReturnType(\Microsoft\Graph\Model\Place::class)
                  ->execute();
    print_r($rooms);
    foreach($rooms as $room){
      array_push($results, $room->getProperties()["emailAddress"]);
    }
    return $results;
  }

  public function freeBetweenBool($emailAddress, $startDateTime, $endDateTime){
    $events = $this->get_calendar($emailAddress, $startDateTime->format('Y-m-d'), $endDateTime->format('Y-m-d'), $startDateTime->format('H:i:s'), $endDateTime->format('H:i:s'));
    if(count($events) == 0) {
      return true;
    } else {
      return false;
    }
  }

  public function freeBetween($emailAddress, $startDateTime, $endDateTime){
    $events = $this->get_calendar($emailAddress, $startDateTime->format('Y-m-d'), $endDateTime->format('Y-m-d'), $startDateTime->format('H:i:s'), $endDateTime->format('H:i:s'));
    if(count($events) == 0) {
      return null;
    } else {
      $results = [];
      foreach($events as $event){
        $ev = [
          "subject" => $event["subject"],
          "startTime" => $event["start"]["dateTime"],
          "endTime" => $event["end"]["dateTime"]
        ];
        array_push($results, $ev);
      }
      return $results;
    }
  }
}


$date = new DateTime('2021-07-01 12:20:00');
$endDate = new DateTime('2021-07-03 12:20:00');

$mycal = new calendar();
#echo $mycal->free("wgalvin@gnil.net", $date);
#echo $mycal->freeUntil("wgalvin@gnil.net", $date);
#echo $mycal->freeBefore("wgalvin@gnil.net", $date);

/**
$rom = $mycal->findAllRooms();
foreach($rom as $ro){
  print_r($ro);
  #echo $date["id"];
  echo "\n";
}
**/

$evs = $mycal->freeBetween("wgalvin@gnil.net", $date, $endDate);
foreach($evs as $ev){
  print_r($ev);
  #echo $date["id"];
  echo "\n";
}

echo $mycal->freeBetweenBool("wgalvin@gnil.net", $date, $endDate);


#echo $mycal->get_calendar_id("wgalvin@gnil.net");
#echo $mycal->calendarId;




 ?>
