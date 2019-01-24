<?php

namespace App\Http\Controllers;

use App\Models\SnsResponse;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Log;

class SnsResponseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sns_result = SnsResponse::paginate(50);
        return view('sns-response.index', compact('sns_result'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $message = Message::fromRawPostData();
            try {
                $validator = new MessageValidator();
                $validator->validate($message);

                if ($message['Type'] === 'SubscriptionConfirmation') {
                    $client = new Client();
                    $result = $client->get($message['SubscribeURL']);
                    Log::info('SNS Subscribed: ', $result);
                }

                if ($message['Type'] === 'Notification') {
                    $this->handleNotification($message);
                }
            } catch (InvalidSnsMessageException $e) {
                Log::error('SNS Message Validation Error: ' . $e->getMessage());
                abort('404', "Invalid SNS Message Validation Exception {$e->getMessage()}");
            }
        } catch (\Exception $exception) {
            Log::error("SNS Message Error: {$exception->getMessage()}");
            abort('404', "Invalid SNS Message Exception {$e->getMessage()}");
        }

        return response()->json(['status' => 200, 'message' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SnsResponse $snsResponse
     * @return \Illuminate\Http\Response
     */
    public function show(SnsResponse $snsResponse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SnsResponse $snsResponse
     * @return \Illuminate\Http\Response
     */
    public function destroy(SnsResponse $snsResponse)
    {
        //
    }

    /**
     * Store a SNS Response
     * @param $type
     * @param $payload
     * @param $emails
     * @param $default_fields
     * @return mixed
     */
    private function storeResponse($type, $payload, $emails, $default_fields)
    {
        if ($emails && is_array($emails)) {
            foreach ($emails as $email) {
                try {
                    SnsResponse::create(array_merge($default_fields, [
                        'email' => $email['emailAddress'],
                        'type' => $type,
                        'data_payload' => $payload,
                    ]));
                } catch (\Exception $exception) {
                    Log::error("Store SNS Error: {$exception->getMessage()}");
                    abort('404', "Store SNS Error Exception: {$exception->getMessage()}");
                }
            }
        }
    }

    /**
     * Handle Notification
     * @param Message $message
     */
    private function handleNotification(Message $message): void
    {
        $notification_message = json_decode($message['Message'], true);
        switch ($notification_message['notificationType']) {
            case 'Bounce':
                $response = $notification_message['bounce'];
                $payload_data = (new Carbon($response['timestamp']));
                $this->storeResponse(
                    $response['bounceType'],
                    $message['Message'],
                    $response['bouncedRecipients'],
                    [
                        'source_email' => $notification_message['mail']['source'],
                        'source_arn' => $notification_message['mail']['sourceArn'],
                        'datetime_payload' => $payload_data->toDateTimeString(),
                    ]
                );
                break;
            case 'Complaint':
                $response = $notification_message['complaint'];
                $payload_data = (new Carbon($response['timestamp']));
                $this->storeResponse(
                    $response['complaintFeedbackType'],
                    $message['Message'],
                    $response['complainedRecipients'],
                    [
                        'source_email' => $notification_message['mail']['source'],
                        'source_arn' => $notification_message['mail']['sourceArn'],
                        'datetime_payload' => $payload_data->toDateTimeString(),
                    ]
                );
                break;
            case 'Delivery':
                $response = $notification_message['delivery'];
                $payload_data = (new Carbon($response['timestamp']));
                $this->storeResponse(
                    'success-delivery',
                    $message['Message'],
                    $response['recipients'],
                    [
                        'source_email' => $notification_message['mail']['source'],
                        'source_arn' => $notification_message['mail']['sourceArn'],
                        'datetime_payload' => $payload_data->toDateTimeString(),
                    ]
                );
                break;
            default:
                break;
        }
    }
}
