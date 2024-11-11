<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;
use Illuminate\Support\Facades\Log;

class FailedCallController extends Controller
{
    //
	public function handleFail(Request $request)
	{

		if ($request->has('ErrorCode') ) {

			// Log the failure details for further analysis
			Log::error('Call failed', [
				'CallSid' => $request->input('CallSid'),
				'From' => $request->input('From'),
				'To' => $request->input('To'),
				'ErrorCode' => $request->input('ErrorCode'),
				'ErrorMessage' => $request->input('ErrorMessage'),
			]);
		}
		else {
			Log::error('Call failed', [
				'CallSid' => $request->input('CallSid'),
				'From' => $request->input('From'),
				'To' => $request->input('To'),
			]);
		}

		$response = new VoiceResponse();

		$response->say('Sorry, I did not understand your response. Please call back later.');

		$response->hangup();

        return response($response, 200)
            ->header('Content-Type', 'text/xml');
	}

}
