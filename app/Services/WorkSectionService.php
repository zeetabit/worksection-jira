<?php

namespace App\Services\Api;

use App\Exceptions\ApiException;
use App\Exceptions\ApiNotAuthorizedException;
use App\User;
use GuzzleHttp\Client;

use GuzzleHttp\Exception\ClientException;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

class WorkSectionService
{
    private $client = null;

    private $apiPoint = '';

  /**
   * ApiService constructor.
   * @throws \Exception
   */
  public function __construct()
    {
        $config = [];
        if (getenv('APP_LOG_LEVEL') == 'debug') {
            $config = array_merge($this->initLogMessageFormatter(), $config);
        }
        $this->client = new Client($config);
        $this->apiPoint = config('ws.api_point');

        return $this;
    }


  /**
   * @return array
   * @throws \Exception
   */
    private function initLogMessageFormatter()
    {
        $config = [];

        $stack = HandlerStack::create();
        $logger = new Logger('Logger');
        $logger->pushHandler(new StreamHandler(storage_path('logs/guzzle-' . date('Y-m-d') . '.log')));

        $stack->push(
            Middleware::log(
                $logger,
                new MessageFormatter('{method} {target} {req_body} - {res_body}')
            )
        );

        $config['handler'] = $stack;
        return $config;
    }

    /**
     * @param       $page
     * @param       $action
     * @param array $fields
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws ApiException
     */
    private function jsonPost($page, $action, $fields = [])
    {
        $fields['hash'] = md5( (
          isset($fields['page']) && strlen($fields['page']) > 0 ? $fields['page'] : $page
          ) . $action . config('ws.token'));

        if (strlen($action) > 0)
            $fields['action'] = $action;

        $body = json_encode($fields, JSON_PRETTY_PRINT);

        $response = $this->jsonPostToUrl($this->apiPoint . '?action=' . $action . '&hash=' . $fields['hash'] . '&page=' . $fields['page'], $body);

        return $response;
    }

    /**
     * @param $url
     * @param $body
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws ApiException
     */
    private function jsonPostToUrl($url, $body)
    {
      $response = $this->client->post($url, [
        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        'body' => $body,
      ]);

      $data = json_decode($response->getBody(), true);

      if(isset($data['status']) && $data['status'] == 'error') {
        throw new ApiException('code: ' . $data['status_code'] . ' ' . $data['message']);
      }

      return $response;
    }

    /**
     * @param       $page
     * @param       $action
     * @param array $fields
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws ApiException
     */
    private function jsonGet($page, $action, $fields = [])
    {
        $fields['hash'] = md5( (
          isset($fields['page']) && strlen($fields['page']) > 0 ? $fields['page'] : $page
          ) . $action . config('ws.token'));

        if (strlen($action) > 0)
            $fields['action'] = $action;

        $response = $this->client->get($this->apiPoint . $page, [
            'query' => $fields,
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode($response->getBody(), true);

        if(isset($data['status']) && $data['status'] == 'error') {
            throw new ApiException('code: ' . $data['status_code'] . ' ' . $data['message']);
        }

        return $response;
    }

    public function getProjects()
    {
        $response = $this->jsonGet('', 'get_projects');

        $projects = json_decode($response->getBody(), true);

        return $projects['data'];
    }

    /**
     * @param $page string project page
     *
     * @return mixed
     * @throws ApiException
     */
    public function getTasks($page)
    {
        $response = $this->jsonGet('', 'get_tasks', compact('page'));

        $tasks = json_decode($response->getBody(), true);

        return $tasks['data'];
    }

    /**
     * @param $page
     *
     * @return mixed
     * @throws ApiException
     */
    public function getTimeMoney($page)
    {
        $response = $this->jsonGet('', 'get_timemoney', compact('page'));

        $timemoneys = json_decode($response->getBody(), true);

        return $timemoneys['data'];
    }

    /**
     * @param        $email_user_from
     * @param        $email_user_manager
     * @param        $email_user_to
     * @param        $members
     * @param        $title
     * @param string $text
     *
     * @param string $company
     *
     * @return  $page
     * @throws ApiException
     */
    public function createProject($email_user_from, $email_user_manager, $email_user_to, $members, $title, $text = "", $company = "")
    {
        $response = $this->jsonGet('', 'post_project', compact('email_user_from', 'email_user_manager', 'email_user_to', 'members', 'title', 'text', 'company'));

        $data = json_decode($response->getBody(), true);

        return $data['url'];
    }

    public function createTask($fields)
    {
        $fields['text'] = mb_substr($fields['text'], 0, 4000);
        $response = $this->jsonGet('', 'post_task', $fields);

        $data = json_decode($response->getBody(), true);

        return $data['url'];
    }

    public function updateTask($fields)
    {
        $response = $this->jsonGet('', 'update_task', $fields);

        $data = json_decode($response->getBody(), true);

        return $data;
    }

    public function completeTask($fields)
    {
        $response = $this->jsonGet('', 'complete_task', $fields);

        $data = json_decode($response->getBody(), true);

        return $data;
    }

    public function createTimeMoney($fields)
    {
        $response = $this->jsonGet('', 'post_timemoney', $fields);

        $data = json_decode($response->getBody(), true);

        return $data['status'] == 'ok';
    }
}