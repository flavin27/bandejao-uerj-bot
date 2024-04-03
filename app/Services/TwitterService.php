<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\JsonResponse;

class TwitterService
{
    /*
     *  METHOD ENDPOINTS
     *  GET    users/me
     *  POST   tweets
     *  DELETE tweets/:id
     */



    private string $apiKey;
    private string $apiSecret;
    private string $accessToken;
    private string $accessTokenSecret;

    public function __construct() {
        $this->apiKey = env('API_KEY');
        $this->apiSecret = env('API_SECRET_KEY');
        $this->accessToken = env('ACCESS_KEY');
        $this->accessTokenSecret = env('ACCESS_SECRET');
    }

    public function get_api(): TwitterOAuth {
        $connection = new TwitterOAuth($this->apiKey, $this->apiSecret, $this->accessToken, $this->accessTokenSecret);
        $connection->setApiVersion('2');
        return $connection;
    }

    public function post_tweet(string $payload): JsonResponse {
        $api = $this->get_api();
        $response = $api->post("tweets", ["text" => $payload]);
        $response = json_encode($response);
        $response = json_decode($response, true);
        if ($response) {
            $data = [
                'success' => true,
                'message' => 'tweet posted',
                'tweet_id' => $response['data']['id'],
            ];
            return response()->json($data);
        } else {
            $data = [
                'success' => false,
                'message' => 'Failed to post'
            ];
            return response()->json($data, 417);
        }

    }

    public function post_reply(string $payload, string $id_string): JsonResponse {
        $api = $this->get_api();
        $response = $api->post("tweets", ["text" => $payload, "reply" => ["in_reply_to_tweet_id" => $id_string]]);
        $response = json_encode($response);
        $response = json_decode($response, true);
        if ($response) {
            $data = [
                'success' => true,
                'message' => 'reply posted',
                'tweet_id' => $response['data']['id']
            ];
            return response()->json($data);
        } else {
            $data = [
                'success' => false,
                'message' => 'Failed to post reply'
            ];
            return response()->json($data, 417);
        }
    }

    public function getLastTweet(): string {
        $api = $this->get_api();
        $id = 1774216838626295809;
        $api->setApiVersion('2');
        $response = $api->get('tweets/' . $id);
        $response = json_encode($response);
        $response = json_decode($response, true);
        if ($response) {
            $data = [
                'stauts' => 'success',
                'message' => 'tweet fetched',
                'tweet_id' => $response['data']['id']
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Failed to fetch'
            ];
        }
        return json_encode($data);
    }
}
