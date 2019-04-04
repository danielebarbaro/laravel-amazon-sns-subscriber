<?php

namespace App\Http\Controllers;

use App\Models\SnsResponse;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
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
                    if ($result == 1) {
                        $now = Carbon::now()->toDateTimeString();
                        Log::info("SNS Subscribed. {$now}");
                    }
                }

                if ($message['Type'] === 'Notification') {
                    $this->handleNotification($message);
                }
            } catch (InvalidSnsMessageException $exception) {
                Log::error('SNS Message Validation Error: ' . $exception->getMessage());
                abort('404', "Invalid SNS Message Validation Exception {$exception->getMessage()}");
            }
        } catch (\Exception $exception) {
            Log::error("SNS Message Error: {$exception->getMessage()}");
            abort('404', "Invalid SNS Message Exception {$exception->getMessage()}");
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
        try {
            $snsResponse->delete();
        } catch (\Exception $exception) {
            abort(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, "Error: {$exception->getMessage()}");
        }
    }

    /**
     * Store a SNS Response
     * @param $notification_type
     * @param $type
     * @param $payload
     * @param $emails
     * @param $default_fields
     * @return mixed
     */
    private function storeResponse($notification_type, $type, $payload, $emails, $default_fields)
    {
        if ($emails && is_array($emails)) {
            foreach ($emails as $email) {
                $email_address = $this->getEmailAddress($email);
                if ($email_address) {
                    try {
                        SnsResponse::create(array_merge($default_fields, [
                            'email' => $email_address,
                            'notification_type' => $notification_type,
                            'type' => $type,
                            'data_payload' => $payload,
                        ]));
                    } catch (\Exception $exception) {
                        Log::error("Store SNS Error: {$notification_type} {$type} {$exception->getMessage()}");
                        abort('404', "Store SNS Error Exception: {$exception->getMessage()}");
                    }
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
                    'bounce',
                    "{$response['bounceType']}:{$response['bounceSubType']}",
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
                    'complaint',
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
                    'delivery',
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

    /**
     * @param $email
     * @return mixed
     */
    private function getEmailAddress($email)
    {
        $email_address = false;
        if (is_array($email) && array_key_exists('emailAddress', $email)) {
            $email_address = $email['emailAddress'];
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::error("Store SNS Error: There is no mail!?");
                abort('404', "Store SNS ErrorThere is no mail");
            }
        }
        return $email_address;
    }
}
