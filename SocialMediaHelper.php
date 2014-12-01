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

/**
	This is a helper class for the Twitter REST API 1.1.
	Hugely inspired by https://github.com/J7mbo/twitter-api-php. A lot of credit belongs to them!
*/
class Twitter extends SocialMedia {

	private $accessToken;
	private $accessTokenSecret;
	private $consumerKey;
	private $consumerSecret;
	
	private $postfields;
	private $getfield;
	protected $oauth;
	public $url;

	public function __construct($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret) {
		if (!in_array('curl', get_loaded_extensions())) 
        {
            throw new Exception('You need to install cURL, see: http://curl.haxx.se/docs/install.html');
        }
        
        if (!isset($accessToken)
            || !isset($accessTokenSecret)
            || !isset($consumerKey)
            || !isset($consumerSecret))
        {
            throw new Exception('Make sure you are passing in the correct parameters');
        }
	
		$this->accessToken = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
	}
	
	public function getTweets($screenName, $amount) {
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?screen_name='.$screenName.'&count='.$amount;
		$requestMethod = 'GET';

		$tweets = $this->setGetfield($getfield)
				 ->buildOauth($url, $requestMethod)
				 ->performRequest();
		return $tweets = json_decode($tweets);
	}
	
	/**
     * Set postfields array, example: array('screen_name' => 'J7mbo')
     * 
     * @param array $array Array of parameters to send to API
     * 
     * @return TwitterAPIExchange Instance of self for method chaining
     */
    public function setPostfields(array $array) {
        if (!is_null($this->getGetfield())) 
        { 
            throw new Exception('You can only choose get OR post fields.'); 
        }
        
        if (isset($array['status']) && substr($array['status'], 0, 1) === '@')
        {
            $array['status'] = sprintf("\0%s", $array['status']);
        }
        
        $this->postfields = $array;
        
        return $this;
    }
    
    /**
     * Set getfield string, example: '?screen_name=J7mbo'
     * 
     * @param string $string Get key and value pairs as string
     * 
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function setGetfield($string) {
        if (!is_null($this->getPostfields())) 
        { 
            throw new Exception('You can only choose get OR post fields.'); 
        }
        
        $search = array('#', ',', '+', ':');
        $replace = array('%23', '%2C', '%2B', '%3A');
        $string = str_replace($search, $replace, $string);  
        
        $this->getfield = $string;
        
        return $this;
    }
    
    /**
     * Get getfield string (simple getter)
     * 
     * @return string $this->getfields
     */
    public function getGetfield() {
        return $this->getfield;
    }
    
    /**
     * Get postfields array (simple getter)
     * 
     * @return array $this->postfields
     */
    public function getPostfields() {
        return $this->postfields;
    }
    
    /**
     * Build the Oauth object using params set in construct and additionals
     * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
     * 
     * @param string $url The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
     * @param string $requestMethod Either POST or GET
     * @return \TwitterAPIExchange Instance of self for method chaining
     */
    public function buildOauth($url, $requestMethod) {
        if (!in_array(strtolower($requestMethod), array('post', 'get')))
        {
            throw new Exception('Request method must be either POST or GET');
        }
        
        $consumer_key = $this->consumerKey;
        $consumer_secret = $this->consumerSecret;
        $oauth_access_token = $this->accessToken;
        $oauth_access_token_secret = $this->accessTokenSecret;
        
        $oauth = array( 
            'oauth_consumer_key' => $consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $oauth_access_token,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
        
        $getfield = $this->getGetfield();
        
        if (!is_null($getfield))
        {
            $getfields = str_replace('?', '', explode('&', $getfield));
            foreach ($getfields as $g)
            {
                $split = explode('=', $g);
                $oauth[$split[0]] = $split[1];
            }
        }
        
        $base_info = $this->buildBaseString($url, $requestMethod, $oauth);
        $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
        $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
        $oauth['oauth_signature'] = $oauth_signature;
        
        $this->url = $url;
        $this->oauth = $oauth;
        
        return $this;
    }
    
    /**
     * Perform the actual data retrieval from the API
     * 
     * @param boolean $return If true, returns data.
     * 
     * @return string json If $return param is true, returns json data.
     */
    public function performRequest($return = true) {
        if (!is_bool($return)) 
        { 
            throw new Exception('performRequest parameter must be true or false'); 
        }
        
        $header = array($this->buildAuthorizationHeader($this->oauth), 'Expect:');
        
        $getfield = $this->getGetfield();
        $postfields = $this->getPostfields();

        $options = array( 
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        );

        if (!is_null($postfields))
        {
            $options[CURLOPT_POSTFIELDS] = $postfields;
        }
        else
        {
            if ($getfield !== '')
            {
                $options[CURLOPT_URL] .= $getfield;
            }
        }

        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $json = curl_exec($feed);
        curl_close($feed);

        if ($return) { return $json; }
    }
    
    /**
     * Private method to generate the base string used by cURL
     * 
     * @param string $baseURI
     * @param string $method
     * @param array $params
     * 
     * @return string Built base string
     */
    private function buildBaseString($baseURI, $method, $params) {
        $return = array();
        ksort($params);
        
        foreach($params as $key=>$value)
        {
            $return[] = "$key=" . $value;
        }
        
        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return)); 
    }
    
    /**
     * Private method to generate authorization header used by cURL
     * 
     * @param array $oauth Array of oauth data generated by buildOauth()
     * 
     * @return string $return Header used by cURL for request
     */    
    private function buildAuthorizationHeader($oauth) {
        $return = 'Authorization: OAuth ';
        $values = array();
        
        foreach($oauth as $key => $value)
        {
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        }
        
        $return .= implode(', ', $values);
        return $return;
    }
	

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
