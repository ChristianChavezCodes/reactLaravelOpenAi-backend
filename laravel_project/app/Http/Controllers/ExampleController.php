<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ExampleController extends Controller
{
    protected $httpClient;
    public function __construct()
    {
        $this->httpChatClient = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->httpDalleClient = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function chatRequest(Request $request)
    {
        $inputData = $request->all();
        $keys = array_keys($inputData);
        $keys = array_map(function ($value) {
            return str_replace('_', ' ', $value);
        }, $keys);
        $userTextInput = $keys[0];

        $message = "Input: '{$userTextInput}'";

        $image = $this->httpDalleClient->post('images/generations', [
            'json' => [
                'prompt' => "{$message}. Pixel art",
                'n' => 1,
                'size' => '256x256',
            ],
        ]);
                    
        $response = $this->httpChatClient->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a pretentious know-it-all. You will assist the user in a concise manner with whatever questions they may have."],
                    ['role' => 'user', 'content' => 'Who was the first president of the united states?'],
                    ['role' => 'assistant', 'content' => "Really? You don't know? Ugh fine, I guess I will tell you. The first US president was George Washington"],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 1500
            ],
        ]);

        $imageResult = json_decode($image->getBody(), true)['data'][0]['url'];
        $chatResult = json_decode($response->getBody(), true)['choices'][0]['message']['content'];

        return [$chatResult, $imageResult];

        /*  
            The responses are going to be slow when they load on the front-end.
            We could implement some caching and embeddings to optimize the speed of results.
            As more users( and or developers) use the chatBot, the more optimal it will become.
            This warrants further investigation, but for now,
            we shall move on.
        */
    }
}