<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;
use GuzzleHttp\Client;


class InboundCallController extends Controller
{
    //
	public function handleInbound(Request $request)
	{
		$response = new VoiceResponse();

		$gather = $response->gather([
			'input' => 'speech',
			'action' => '/api/chat',
			'language' => 'en-US',
			'speechTimeout' => 'auto'
		]);

		$gather->say('Hello, thank you for calling CREW CRAFT. Is there anything I can help you with?');

		return response($response, 200)
			->header('Content-Type', 'text/xml');
	}


	protected function getAiResponse($message) {

		$client = new Client();
		$apiKey = env('OPENAI_API_KEY');

		$response = $client->post('https://api.openai.com/v1/chat/completions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $apiKey,
				'Content-Type' => 'application/json',
			],
			'json' => [
				'model' => 'gpt-3.5-turbo',
				'messages' => [
					['role' => 'system', 'content' => 'You are a representative of CREW CRAFT. you are awsering the phone.'],
					['role' => 'user', 'content' => $message],
				],
				'max_tokens' => 50,
				'temperature' => 0.7,
			],
		]);

		$result = json_decode($response->getBody(), true);

		
		return $result['choices'][0]['message']['content'];
	}
	
	public function chat(Request $request)
	{
		$response = new VoiceResponse();
    
		if ($request->has('SpeechResult')) {
			$message = $request->input('SpeechResult');

			// Generate AI response using OpenAI
			$aiResponse = $this->getAiResponse($message);

			$response->say($aiResponse);
		}
		else
		{
			$response->say('I am sorry, I did not understand that. Please try again.');				
		}
		
		$response->gather([
			'input' => 'speech',
			'action' => '/api/chat',
			'language' => 'en-US',
			'speechTimeout' => 'auto'
		])->say('ok?');

		return response($response, 200)
			->header('Content-Type', 'text/xml');
	
	}

	public function ask(Request $request)
	{
		
		if ($request->has('message')) {
			$message = $request->input('message');
		}
		else {
			$message = 'I need Love';
		}

		$client = new Client();
		$apiKey = env('OPENAI_API_KEY');

		$response = $client->post('https://api.openai.com/v1/chat/completions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $apiKey,
				'Content-Type' => 'application/json',
			],
			'json' => [
				'model' => 'gpt-3.5-turbo',
				'messages' => [
					['role' => 'system', 'content' => 'You are a representative of CREW CRAFT. you are awsering the phone.'],
					['role' => 'user', 'content' => $message],
				],
				'max_tokens' => 50,
				'temperature' => 0.7,
			],
		]);

		$result = json_decode($response->getBody(), true);
		return response()->json(['response' => $result['choices'][0]['message']['content']]);


	}
}
