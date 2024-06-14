<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductProxyController extends Controller
{
    public function index()
    {
        Log::info('ProductProxyController index method called');

        $client = new Client();
        $service = 'serviceb';
        $products = [];
        $users = User::all();

        try {
            Log::info('Attempting to retrieve products from ServiceB');
            $response = $client->get('http://127.0.0.1:8001/api/products');
            $products = json_decode($response->getBody(), true);

            // Reset Circuit Breaker counters on success
            Cache::forget("{$service}_failures");
            Cache::forget("{$service}_last_attempt");
            Log::info('Products retrieved successfully from ServiceB', ['products' => $products]);
        } catch (RequestException $e) {
            // Increment Circuit Breaker counters on failure
            Cache::increment("{$service}_failures");
            Cache::put("{$service}_last_attempt", now());
            Log::error('Failed to retrieve products from ServiceB', [
                'error' => $e->getMessage(),
                'failures' => Cache::get("{$service}_failures"),
                'last_attempt' => Cache::get("{$service}_last_attempt")
            ]);

            return response()->json(['error' => 'Service B is currently unavailable'], 503);
        }

        return view('index', ['products' => $products, 'users' => $users]);
    }
}
