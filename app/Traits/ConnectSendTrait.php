<?php


namespace App\Traits;


use App\Models\User;
use App\Services\MarkerApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ConnectSendTrait
{
    private $settings = [
        'verify' => false,
        'base_uri' => 'https://api.smartdev.ml/',
        'headers' => [
            'Content-Type' => 'application/json'
        ]];
    private $api_token = '';

    public function send($method, $url, $data = [], $max_feed = 3)
    {
        if ($url != '/WebMarker/login') $this->NewConnection();
        $http = new \GuzzleHttp\Client($this->settings);
        try {
            $response = $http->request($method, $url, $data);
            $response = json_decode((string)$response->getBody(), true);
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error((string)$response->getStatusCode() . ';' . $response);
            return $response;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error((string)$e->getCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            if (401 == $e->getResponse()->getStatusCode() && $url != '/WebMarker/login' && $max_feed > 0) {
                $mar = new MarkerApi();
                $mar->Login();
                $max_feed--;
                $this->send($method, $url, $data, $max_feed);
            }
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error((string)$e->getResponse()->getStatusCode() . ';' . $responseBodyAsString);
        } catch (\Exception $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error($e->getMessage(),$e->getTrace());
        }
    }

    public function NewConnection()
    {
        if (!empty($this->api_token)) return;
        if (Auth()->user()) {
            $user = Auth::user();
            $this->api_token = $user->api_token;
        } else {
            $user = User::where('id', 1)->first();
            $this->api_token = $user->api_token;
        }
        $this->settings['headers']['Authorization'] = 'Bearer ' . $this->api_token;
    }
}
