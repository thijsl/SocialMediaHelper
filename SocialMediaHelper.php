<?php
header('Content-Type: text/html; charset=utf-8');


class SocialMedia {
	
	public $name;
	public $version;
	public $documentationURL;

}

/**
	This is a helper class for the Foursquare API v2.
	You create a Foursquare object by instancing it with an access token.
*/
class Foursquare extends SocialMedia {

	public $accessToken;
	
	public $name = 'Foursquare';
	public $version = '20140504';
	public $documentationURL = 'https://developer.foursquare.com/overview/';
	
	
	public function __construct($accessToken) {
		$this->accessToken = $accessToken;
	}
	
	public function getAccessToken() {
		return $this->accessToken;
	}
	
	public function getLastCheckIn($userId) {
		$result = file_get_contents('https://api.foursquare.com/v2/users/'.$userId.'/checkins?oauth_token='.$this->accessToken.'&v='.$this->version);
		$lastCheckInData = json_decode($result)->response->checkins->items[0];
		return new CheckIn($lastCheckInData, $userId);
	}
	
	public function getLastCheckIns($userId, $amount) {
		$result = file_get_contents('https://api.foursquare.com/v2/users/'.$userId.'/checkins?oauth_token='.$this->accessToken.'&v='.$this->version);
		for ($i=0; $i < $amount; $i++) {
			$lastCheckIn[$i] = new CheckIn(json_decode($result)->response->checkins->items[$i]);
		}
		return $lastCheckIn;
	}
	
	public function getVenue($venueId) {
		$venueData = json_decode(file_get_contents('https://api.foursquare.com/v2/venues/'.$venueId.'?oauth_token='.$this->accessToken.'&v='.$this->version))->response;
		$venue = new Venue($venueData);
		return $venue;
	}

}

class Twitter extends SocialMedia {

}

class Instagram extends SocialMedia {

}

class YouTube extends SocialMedia {

}

class Spotify extends SocialMedia {

}

/**
	This is a Venue class. The Foursquare and Facebook classes can instance objects of this class.
*/
class Venue {

	private $data;

	public $id;
	public $name;
	public $location;
	public $url;
	public $categories;
	public $tips;
	
	public function __construct($venueData) {
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

/**
	This is a CheckIn class.
*/
class CheckIn {

	private $data;

	public $id;
	public $time;
	public $venue;
	public $like;
	public $likes;
	public $sticker;
	public $photos;
	public $posts;
	public $comments;
	
	public $userId;
	
	public function __construct($checkInData, $userId) {
		$this->buildFoursquareCheckin($checkInData, $userId);
	}
	
	private function buildFoursquareCheckin($checkInData, $userId) {
		$this->data = $checkInData;
		
		$this->id = $checkInData->venue->id;
		$this->time = $checkInData->createdAt;
		$this->venue = new Venue($checkInData->venue);
		$this->like = $checkInData->like;
		$this->likes = $checkInData->likes;
		$this->sticker = $checkInData->sticker;
		$this->photos = $checkInData->photos;
		$this->posts = $checkInData->posts;
		$this->comments = $checkInData->comments;
		
		$this->userId = $userId;
	}
	
}

?>
