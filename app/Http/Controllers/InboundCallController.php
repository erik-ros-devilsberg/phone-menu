<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InboundCallController extends Controller
{
    //
	public function handleInbound(Request $request)
	{
		$response = new \Twilio\TwiML\VoiceResponse();
		$response->say('Hello, thank you for calling CREW CRAFT. you are loved');
		$response->gather(['numDigits' => 1, 'action' => '/api/menu', 'method' => 'POST'])
			->say('For sales, press 1. For support, press 2.');
		return response($response, 200)
			->header('Content-Type', 'text/xml');
	}
}
