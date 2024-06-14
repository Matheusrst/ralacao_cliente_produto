<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class CircuitBreaker
{
    protected $client;
    protected $serviceName;
    protected $failureThreshold;
    protected $retryTimePeriod;
    protected $failureCount;
    protected $lastFailureTime;

    public function __construct($serviceName, $failureThreshold = 5, $retryTimePeriod = 60)
    {
        $this->client = new Client();
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->retryTimePeriod = $retryTimePeriod;
        $this->failureCount = Cache::get($serviceName . '_failure_count', 0);
        $this->lastFailureTime = Cache::get($serviceName . '_last_failure_time', 0);
    }

    public function isAvailable()
    {
        if ($this->failureCount >= $this->failureThreshold) {
            if (time() - $this->lastFailureTime > $this->retryTimePeriod) {
                $this->reset();
                return true;
            }
            return false;
        }
        return true;
    }

    public function success()
    {
        $this->reset();
    }

    public function failure()
    {
        $this->failureCount++;
        $this->lastFailureTime = time();
        Cache::put($this->serviceName . '_failure_count', $this->failureCount);
        Cache::put($this->serviceName . '_last_failure_time', $this->lastFailureTime);
    }

    protected function reset()
    {
        $this->failureCount = 0;
        $this->lastFailureTime = 0;
        Cache::put($this->serviceName . '_failure_count', $this->failureCount);
        Cache::put($this->serviceName . '_last_failure_time', $this->lastFailureTime);
    }

    public function call($method, $url, $options = [])
    {
        if ($this->isAvailable()) {
            try {
                $response = $this->client->request($method, $url, $options);
                if ($response->getStatusCode() === 200) {
                    $this->success();
                    return json_decode($response->getBody(), true);
                } else {
                    $this->failure();
                    return ['error' => 'Serviço indisponível'];
                }
            } catch (\Exception $e) {
                $this->failure();
                return ['error' => 'Erro ao conectar com o serviço'];
            }
        } else {
            return ['error' => 'Serviço temporariamente indisponível'];
        }
    }
}
