<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InboundCallController extends Controller
{
    //


	public function handleInbound(Request $request)
	{
		$response = new VoiceResponse();
		/*
		$gather = $response->gather([
			'input' => 'speech',
			'action' => '/api/chat',
			'language' => 'en-US',
			'speechTimeout' => 'auto'
		]);

		$gather->say('Hello, thank you for calling CREW CRAFT. Is there anything I can help you with?');
		*/

		$response->say('Hello, thank you for calling CREW CRAFT. Is there anything I can help you with?');

		$response->record([
			'action' => 'https://phone.crew-craft.cc/api/chat', // Twilio sends recording here
			'method' => 'POST',
			'playBeep' => true,
			'maxLength' => 60,
			'timeout' => 10,
		]);


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
	
	public function sendError($message){
		$response = new VoiceResponse();
		$response->say($message);
		$response->hangup();
		return response($response, 200)
			->header('Content-Type', 'text/xml');
	}

	public function chat(Request $request)
	{
		if ($request->has('RecordingUrl')) {
			$recordingUrl = $request->input('RecordingUrl');
		}
		else {
			$recordingUrl = 'No recording found';
		}

		// Once transcribed, send the text to ChatGPT
		$responseText = $this->getAiResponse('success!: ' . $recordingUrl);

		$response = new VoiceResponse();
		$response->say($responseText);
		$response->hangup();

		// Use Whisper to transcribe the audio
		$transcription = $this->transcribeWithWhisper($recordingUrl);

		if ($transcription == 'Failed to transcribe audio' || $transcription == 'Failed to fetch audio content') {
			$this->sendError($transcription);
		}

		// Once transcribed, send the text to ChatGPT
		$responseText = $this->getAiResponse($transcription);

		$response = new VoiceResponse();
		$response->say($responseText);
		$response->hangup();


/*		
		$response->record([
			'action' => 'https://phone.crew-craft.cc/api/chat', // Twilio sends recording here
			'method' => 'POST',
			'playBeep' => true,
			'maxLength' => 60,
			'timeout' => 10,
		]);
*/

		return response($response, 200)
			->header('Content-Type', 'text/xml');
	}
	
	private function transcribeWithWhisper($audioUrl)
	{
		$apiKey = env('OPENAI_API_KEY');
		$url = "https://api.openai.com/v1/audio/transcriptions";

		try {
			$audioContent = file_get_contents($audioUrl);
		} catch (\Exception $e) {
			Log::error('Failed to fetch audio content', [
				'error' => $e->getMessage(),
			]);
			return 'Failed to fetch audio content';
		}
	
		$data = [
			'file' => $audioContent,
			'model' => 'whisper-1', // or whatever version is current
		];
	
		try{
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: multipart/form-data',
				'Authorization: Bearer ' . $apiKey,
			]);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
			$response = curl_exec($ch);
			curl_close($ch);
		} catch (\Exception $e) {
			Log::error('Failed to transcribe audio', [
				'error' => $e->getMessage(),
			]);
			return 'Failed to transcribe audio';
		}
	
		$decodedResponse = json_decode($response, true);

		Log::error('transcription result', [
			'transcription' => $decodedResponse,
		]);

		return $decodedResponse['text'];
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
