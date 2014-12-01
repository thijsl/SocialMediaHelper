SocialMediaHelper
=================

A collection of PHP helper classes to utilize different social media API's.

This helper class enables you to use methods to access check-ins, twitter feeds, and so on, after creating an instance of a specific social media class. To create a valid instance, you have to -of course- pass on some valid parameters, such as an access token for example.

The advantage is that you don't need specific understanding of JSON, Curl, ... and that the API-integration of the different social media classes is done for you.

# Target audience
These helper classes aren't meant for the expert API web developer, but for the user who  is searching for quick solutions to integrate different social media aspects in their website. For example: you are creating a blog website and you want to include that 5 last tweets on your website. However, you don't want to read up on the Twitter API. Solution: you include the SocialMediaHelper.php file, you create a Twitter($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret) object and you use the getTweets('myScreenName', 5) method to retrieve an array of your last 5 tweets.

## Foursquare
Create a Foursquare object by specifying an access token.

## Twitter
Create a Twitter object by specifying the access token, the access token secret, the consumer key and the consumer secret.

