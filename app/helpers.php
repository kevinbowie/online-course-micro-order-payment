<?php

use Illuminate\Support\Facades\Http;

function createPremiumAccess($data) {
    $url = env('SERVICE_COURSE_URL') . 'api/my-courses/premium';
    try {
        $res = Http::post($url, $data);
        $data = $res->json();
        $data['http_code'] = $res->getStatusCode();
        return $data;
    } catch (\Throwable $th) {
        return [
            'status' => 'error',
            'http_code' => 500,
            'message' => 'service course unavailable'
        ];
    }
}