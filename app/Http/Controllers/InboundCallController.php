<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;

class InboundCallController extends Controller
{
    //
	public function handleInbound(Request $request)
	{
		$response = new VoiceResponse();
		$response->say('Hello, thank you for calling CREW CRAFT. you are loved');
		$response->gather(['numDigits' => 1, 'action' => '/api/menu', 'method' => 'POST'])
			->say('For more love, press 1. For emotional support, press 2.');
		return response($response, 200)
			->header('Content-Type', 'text/xml');
	}
}
