<?php

namespace Sevming\UnionPay\Providers;

use \Throwable;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Sevming\Support\{Str, Collection};
use Sevming\Foundation\Exceptions\{Exception as FoundationException, HttpException};
use Sevming\Foundation\Supports\Response;
use Sevming\UnionPay\UnionPay;
use Sevming\UnionPay\Exceptions\InvalidSignException;
use Sevming\UnionPay\Listeners\LogSubscriber;
use Sevming\UnionPay\Events\{MethodCalled, Notify, Requested, Requesting, SignFailed};

abstract class BaseClient
{
    /**
     * @var UnionPay
     */
    protected $app;

    protected const MODE_PROD = 'prod';
    protected const MODE_TEST = 'test';
    protected const PROD_URL = 'https://qr.chinaums.com/';
    protected const TEST_URL = 'https://qr-test2.chinaums.com/';
    protected const API_URI = 'netpay-route-server/api/';

    /**
     * Wap constructor.
     *
     * @param UnionPay $app
     */
    public function __construct(UnionPay $app)
    {
        $this->app = $app;
        $this->app->events->addSubscriber(new LogSubscriber($this->app->log));
    }

    /**
     * Pay an order.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract public function pay(array $params);

    /**
     * Generate sign.
     *
     * @param array $data
     *
     * @return string
     */
    abstract protected function generateSign(array $data);

    /**
     * @return string
     */
    abstract protected function getSignType();

    /**
     * Verify data.
     *
     * @param null $data
     *
     * @return Collection
     * @throws Throwable
     */
    public function verify($data = null)
    {
        $data = $data ?? $this->app->request->request->all();
        $this->app->events->dispatch(new Notify('', $data));

        if ($this->verifySign($data)) {
            return new Collection($data);
        }

        throw new InvalidSignException('INVALID SIGN: Sign verify failed.', $data);
    }

    /**
     * Query an order.
     *
     * @param string $orderId
     *
     * @return array
     * @throws Throwable
     */
    public function find(string $orderId)
    {
        $payload = array_merge($this->createPayload(), [
            'msgType' => 'query',
            'merOrderId' => $this->spliceMsgSrcId($orderId),
        ]);
        $payload['sign'] = $this->generateSign($payload);
        $this->app->events->dispatch(new MethodCalled('Find', static::API_URI, $payload));

        return $this->json(static::API_URI, $payload);
    }

    /**
     * Refund an order.
     *
     * @param array $params
     *
     * @return array
     * @throws Throwable
     */
    public function refund(array $params)
    {
        $params['merOrderId'] = $this->spliceMsgSrcId($params['merOrderId']);
        $params['refundOrderId'] = $this->spliceMsgSrcId($params['refundOrderId']);
        $payload = array_merge($this->createPayload(), [
            'msgType' => 'refund',
        ], $params);
        $payload['sign'] = $this->generateSign($payload);
        $this->app->events->dispatch(new MethodCalled('Refund', static::API_URI, $payload));

        return $this->json(static::API_URI, $payload);
    }

    /**
     * Query refund an order.
     *
     * @param string $refundOrderId
     *
     * @return array
     * @throws Throwable
     */
    public function queryRefund(string $refundOrderId)
    {
        $payload = array_merge($this->createPayload(), [
            'msgType' => 'refundQuery',
            'merOrderId' => $this->spliceMsgSrcId($refundOrderId),
        ]);
        $payload['sign'] = $this->generateSign($payload);
        $this->app->events->dispatch(new MethodCalled('QueryRefund', static::API_URI, $payload));

        return $this->json(static::API_URI, $payload);
    }

    /**
     * Close an order
     *
     * @param string $orderId
     *
     * @return array
     * @throws Throwable
     */
    public function close(string $orderId)
    {
        $payload = array_merge($this->createPayload(), [
            'msgType' => 'close',
            'merOrderId' => $this->spliceMsgSrcId($orderId),
        ]);
        $payload['sign'] = $this->generateSign($payload);
        $this->app->events->dispatch(new MethodCalled('Close', static::API_URI, $payload));

        return $this->json(static::API_URI, $payload);
    }

    /**
     * Reply Success.
     *
     * @return SymfonyResponse
     */
    public function success()
    {
        return new SymfonyResponse('SUCCESS');
    }

    /**
     * Reply Fail.
     *
     * @return SymfonyResponse
     */
    public function fail()
    {
        return new SymfonyResponse('FAIL');
    }

    /**
     * @param string $merOrderId
     *
     * @return string
     */
    public function spliceMsgSrcId(string $merOrderId)
    {
        $msgSrcId = $this->app->config->get('msg_src_id');
        if (!Str::contains($merOrderId, $msgSrcId)) {
            $merOrderId = $msgSrcId . $merOrderId;
        }

        return $merOrderId;
    }

    /**
     * @param string $merOrderId
     *
     * @return string
     */
    public function removeMsgSrcId(string $merOrderId)
    {
        $msgSrcId = $this->app->config->get('msg_src_id');
        return Str::replaceFirst($msgSrcId, '', $merOrderId);
    }

    /**
     * Verify sign.
     *
     * @param array $data
     *
     * @return bool
     */
    protected function verifySign(array $data)
    {
        if ($data['sign'] !== $this->generateSign($data)) {
            $this->app->events->dispatch(new SignFailed('', $data));
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getBaseUri()
    {
        return $this->app->config->get('mode') === static::MODE_TEST ? static::TEST_URL : static::PROD_URL;
    }

    /**
     * @return array
     */
    protected function createPayload()
    {
        return [
            'msgSrc' => $this->app->config->get('msg_src'),
            'instMid' => $this->app->config->get('inst_mid'),
            'mid' => $this->app->config->get('mid'),
            'tid' => $this->app->config->get('tid'),
            'requestTimestamp' => date('Y-m-d H:i:s'),
            'signType' => $this->getSignType(),
        ];
    }

    /**
     * Get request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return array
     * @throws GuzzleException|FoundationException
     */
    public function get(string $url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * Json request.
     *
     * @param string $url
     * @param array  $data
     * @param array  $query
     *
     * @return array
     * @throws GuzzleException|FoundationException
     */
    public function json(string $url, array $data = [], array $query = [])
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * Request.
     *
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return array
     * @throws GuzzleException|FoundationException
     */
    public function request(string $url, string $method = 'GET', array $options = [])
    {
        $this->app->events->dispatch(new Requesting('', $this->getBaseUri() . $url, $options));
        $options['base_uri'] = $this->getBaseUri();
        $response = $this->app->http->request($url, $method, $options, true);
        $result = Response::resolveData($response, 'array');
        $this->app->events->dispatch(new Requested('', $this->getBaseUri() . $url, $result));

        if (!$this->isSuccess($result)) {
            throw new HttpException(\json_encode([
                'url' => $url,
                'method' => $method,
                'options' => $options,
                'contents' => $result,
            ]), $response->getStatusCode(), $response);
        }

        return $result;
    }

    /**
     * @param array $response
     *
     * @return bool
     */
    protected function isSuccess(array $response)
    {
        if (!isset($response['errCode']) || 'SUCCESS' !== $response['errCode']) {
            return false;
        }

        return true;
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     *
     * @return string
     */
    protected static function classBasename($class = null)
    {
        is_null($class) && ($class = static::class);
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
