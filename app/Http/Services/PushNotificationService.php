<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PushNotificationService
{
    public function sendPushNotification($visitingDetails, $topicName)
    {
        if (!empty($topicName)) {
            $topic = env('FCM_TOPIC') . '_' . str_replace(['@', '.', '+'], ['_', '_', ''], $topicName);
        } else {
            $topic = env('FCM_TOPIC');
        }

        $final = array(
            'to' => '/topics/' . $topic,
            'priority' => 'high',
            'notification' => [
                "title" => "New Visitor #" . $visitingDetails->visitor->name,
                "body" => 'You have a new visitor named ' . $visitingDetails->visitor->name. ' The visitor phone number is ' . $visitingDetails->visitor->phone,
                'sound' => 'Default',
                'image' => $visitingDetails->images
            ],
        );
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($final));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
      
        curl_close($ch);
        return $result;
    }


    public function fcmSubscribe($request)
    {

        $deviceToken = $request->device_token;
        $topic = env('FCM_TOPIC') . '_' . str_replace(['@', '.', '+'], ['_', '_', ''], $request->topic);


        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );
        $this->fcmGlobalSubscribe($request);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1/$deviceToken/rel/topics/$topic");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return response()->json([
                'status' => 200,
                'message' => 'Subscribed',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }


    public function fcmGlobalSubscribe($request)
    {
        $deviceToken = $request->device_token;
        $topic = env('FCM_TOPIC');

        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/iid/v1/$deviceToken/rel/topics/$topic");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            return response()->json([
                'status' => 200,
                'message' => 'Global Subscription',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }


    public function fcmUnsubscribe($request)
    {
        $request->validate([
            'device_token' => 'required',
            'topic' => 'nullable',
        ]);

        $deviceToken = $request->token;

        $headers = array(
            'Authorization: key=' . env('FCM_SECRET_KEY'),
            'Content-Type: application/json'
        );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://iid.googleapis.com/v1/web/iid/$deviceToken");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);

            return response()->json([
                'status' => 200,
                'message' => 'Unsubscribed',
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'status'  => 401,
                'message' => $exception,
            ], 401);
        }
    }

    public function sendWebNotification($visitingDetails)
    {
        if (! $visitingDetails->employee || ! $visitingDetails->employee->user) {
            return false;
        }

        return $this->sendWebNotificationToUsers(
            $visitingDetails,
            collect([$visitingDetails->employee->user]),
            'New Visitor #' . $visitingDetails->visitor->name,
            'You have a new visitor named ' . $visitingDetails->visitor->name . ' The visitor phone number is ' . $visitingDetails->visitor->phone
        );
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>|array<int, User>  $users
     */
    public function sendWebNotificationToUsers($visitingDetails, $users, ?string $title = null, ?string $body = null)
    {
        $tokens = collect($users)
            ->pluck('web_token')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($tokens === []) {
            return false;
        }

        $visitorName = optional($visitingDetails->visitor)->name ?? 'visitante';
        $visitorPhone = optional($visitingDetails->visitor)->phone ?? '';

        $url = 'https://fcm.googleapis.com/fcm/send';
        $serverKey = env('FCM_SECRET_KEY');
        $data = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title ?? ('New Visitor #' . $visitorName),
                'body' => $body ?? ('You have a new visitor named ' . $visitorName . ' The visitor phone number is ' . $visitorPhone),
                'sound' => 'default',
                'icon' => public_path('images/fav.png'),
            ],
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        $result = curl_exec($ch);
        if ($result === false) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        return true;
    }
}
