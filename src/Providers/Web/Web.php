<?php

namespace Sevming\UnionPay\Providers\Web;

use \Exception;
use Sevming\UnionPay\Events\{PayStarted, PayStarting};
use Sevming\UnionPay\Providers\BaseClient;

class Web extends BaseClient
{
    public const WXPAY = 'WXPay.jsPay';
    public const ALIPAY = 'trade.jsPay';
    public const QUICKPASS = 'acp.jsPay';

    public const PAY_URI = 'netpay-portal/webpay/pay.do';

    /**
     * Wechat pay.
     *
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function wxpay(array $params)
    {
        $params['msgType'] = static::WXPAY;
        return $this->pay($params);
    }

    /**
     * Alipay.
     *
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function alipay(array $params)
    {
        $params['msgType'] = static::ALIPAY;
        return $this->pay($params);
    }

    /**
     * Quick pass.
     *
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function quickpass(array $params)
    {
        $params['msgType'] = static::QUICKPASS;
        return $this->pay($params);
    }

    /**
     * Pay an order.
     *
     * @param array $params
     *
     * @return string
     * @throws Exception
     */
    public function pay(array $params)
    {
        $this->app->events->dispatch(new PayStarting(static::classBasename(), $params));

        $params['merOrderId'] = $this->spliceMsgSrcId($params['merOrderId']);
        $payload = array_merge($this->createPayload(), [
            'notifyUrl' => $this->app->config->get('notify_url'),
            'returnUrl' => $this->app->config->get('return_url'),
        ], $params);
        $payload['sign'] = $this->generateSign($payload);

        $this->app->events->dispatch(new PayStarted(static::classBasename(), static::PAY_URI, $payload));

        return $this->getBaseUri() . static::PAY_URI . '?' . http_build_query($payload);
    }

    /**
     * Generate sign.
     *
     * @param array $data
     *
     * @return string
     */
    protected function generateSign(array $data)
    {
        $buff = '';
        ksort($data);
        foreach ($data as $k => $v) {
            $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k . '=' . $v . '&' : '';
        }

        $buff = rtrim($buff, '&');

        return strtoupper(hash('SHA256', $buff . $this->app->config->get('key')));
    }

    /**
     * @return string
     */
    protected function getSignType()
    {
        return 'SHA256';
    }
}
