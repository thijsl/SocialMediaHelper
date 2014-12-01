<?php
header('Content-Type: text/html; charset=utf-8');

/**
	This is a helper class for the Foursquare API v2.
	You create a Foursquare object by instancing it with an access token.
*/
class Foursquare {

	public $accessToken;
	
	public $version = '20140504';

	public function __construct($accessToken) {
		$this->accessToken = $accessToken;
	}
	
	public function getAccessToken() {
		return $this->accessToken;
	}
	
	public function getLastCheckIn($userId) {
		$result = file_get_contents('https://api.foursquare.com/v2/users/'.$userId.'/checkins?oauth_token='.$this->accessToken.'&v='.$this->version);
		$lastCheckIn = json_decode($result)->response->checkins->items[0];
		return $lastCheckIn;
	}
	
	public function getLastCheckIns($userId, $amount) {
		$result = file_get_contents('https://api.foursquare.com/v2/users/'.$userId.'/checkins?oauth_token='.$this->accessToken.'&v='.$this->version);
		for ($i=0; $i < $amount; $i++) {
			$lastCheckIn[$i] = json_decode($result)->response->checkins->items[$i];
		}
		return $lastCheckIn;
	}
	
	public function getVenue($venueId) {
		$venueData = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/'.$venueId.'?oauth_token='.$this->accessToken.'&v='.$this->version))->response;
		$venue = new Venue($venueData);
		return $venue;
	}

}

/**
	This is a Venue class. The Foursquare and Facebook classes can instance objects of this class.
*/
class Venue {

	public $data;

	public $id;
	public $name;
	public $location;
	public $url;
	public $categories;
	public $tips;
	
	public function __construct($venueData) {
		$this->id = $id;
		$this->buildFoursquareVenue($venueData);
	}
	
	private function buildFoursquareVenue($venueData) {
		$this->data = $venueData;
		$this->id = $venueData->venue->id;
		$this->name = $venueData->venue->name;
		$this->location = $venueData->venue->location;
		$this->url = $venueData->venue->url;
		$this->categories = $venueData->venue->categories;
		$this->tips = $venueData ->venue->tips;
	}
	
}

/*$fq = new Foursquare("KWBKDEIM2CD1HBUW2MESPMNMG33SMGYE5B5DDB1NO21AMPR5");
$venue = $fq->getVenue('4b9ad03cf964a5204dd835e3');

print_r($venue->tips);*/


?>
