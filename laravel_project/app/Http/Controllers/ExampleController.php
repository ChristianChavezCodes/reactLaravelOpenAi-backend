<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ExampleController extends Controller
{
    protected $httpClient;
    public function __construct()
    {
        $this->httpClient = new Client([
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
                    
        $response = $this->httpClient->post('chat/completions', [
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => "You are a helpful chat bot for the following website: 'https://soaren.io/'. You will assist
                      the user in a friendly and concise manner with whatever questions they may have. The questions will always pertain to the company Soaren Management."],
                    ['role' => 'user', 'content' => 'Where are the Arizona offices located?'],
                    ['role' => 'assistant', 'content' => 'The Arizona offices are located in the following address: 7020 E Acoma Dr 
                    Scottsdale, AZ 85254.  Anything else I can assist you with today?'],
                    ['role' => 'user', 'content' => $message],
                ],
                'max_tokens' => 1000
            ],
        ]);
        $result = json_decode($response->getBody(), true)['choices'][0]['message']['content'];

        return $result;

        /*  
            The responses are going to be slow when they load on the front-end.
            We could implement some caching and embeddings to optimize the speed of results.
            As more users( and or developers) use the chatBot, the more optimal it will become.
            This warrants further investigation, but for now,
            we shall move on.
        */
    }
}