<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SnsResponseCollection;
use App\Models\SnsResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SnsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param $type
     * @return SnsResponseCollection
     */
    public function index($type)
    {
        $filter = ['bounce', 'delivery', 'complaint'];
        if (in_array($type, $filter)) {
            $filter = [$type];
        }

        $sns_result = SnsResponse::select([
            'uuid',
            'email',
            'notification_type',
            'type',
            'source_email',
            'source_arn',
            'datetime_payload',
        ])->whereIn('notification_type', $filter)->get();
        return new SnsResponseCollection($sns_result);
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
            return response()->json([
                'status' => 'success',
                'message' => $exception->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Resource Deleted.'
        ], JsonResponse::HTTP_OK);
    }
}
