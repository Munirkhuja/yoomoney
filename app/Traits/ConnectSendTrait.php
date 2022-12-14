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
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]];
    private $api_token = '';

    public function send($method, $url, $data = [], $max_feed = 3)
    {
        if ($url != '/WebMarker/login') $this->NewConnection();
        $http = new \GuzzleHttp\Client($this->settings);
        try {
            $response = $http->request($method, $url, $data);
            $response = json_decode((string)$response->getBody(), true);
            return $response;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error((string)$e->getCode());
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error((string)$e->getResponse()->getStatusCode() . ';' . $responseBodyAsString);
        } catch (\Exception $e) {
            if ($url != '/WebMarker/login' && $max_feed > 0 && isset($this->settings['headers']['Authorization']) && !empty($this->settings['headers']['Authorization'])) {
                if ($max_feed > 1) {
                    $this->RefreshApiToken();
                } else {
                    $this->Login();
                }
                $max_feed--;
                return $this->send($method, $url, $data, $max_feed);
            }
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error($e->getMessage(), $e->getTrace());
        }
        return false;
    }

    public function NewConnection()
    {
        if (Auth()->user()) {
            $user = Auth::user();
            $this->api_token = $user->api_token;
        } else {
            $user = User::where('id', 1)->first();
            $this->api_token = $user->api_token;
        }
        if (empty($this->api_token)) {
            $this->Login();
        }
        $this->settings['headers']['Authorization'] = 'Bearer ' . $this->api_token;
    }

    public function RefreshApiToken()
    {
        if (Auth()->user()) {
            $refresh_token = Auth::user()->refresh_token;
        } else {
            $refresh_token =User::where('id', 1)->first()->refresh_token;
        }
        $this->settings['headers']['Authorization'] = 'Bearer ' . $refresh_token;
        $result = $this->send('GET', '/WebMarker/token/refresh');
        if (Auth()->user()) {
            $user = User::where('id', Auth()->user()->id)->first();
        } else {
            $user =User::where('id', 1)->first();
        }
        $user->api_token = $result['access_token'];
        $user->save();
        $this->api_token = $result['access_token'];
    }

    public function Login()
    {
        unset($this->settings['headers']['Authorization']);
        $result = $this->send('POST', '/WebMarker/login', [
            'body' => json_encode(["username" => "tester", "password" => "tester"])
        ]);
        if ($result === false || !isset($result['access_token'])) {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/marker_api_con.log'),
            ])->error($result);
            return false;
        }
        if (Auth()->user()) {
            $user = User::where('id', Auth()->user()->id)->first();
            $user->api_token = $result['access_token'];
            $user->refresh_token = $result['refresh_token'];
            $user->save();
            $this->api_token = $result['access_token'];
        } else {
            $user = User::where('id', 1)->first();
            $user->api_token = $result['access_token'];
            $user->refresh_token = $result['refresh_token'];
            $user->save();
            $this->api_token = $result['access_token'];
        }
    }
}
