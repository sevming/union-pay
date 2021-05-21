<?php

namespace Sevming\UnionPay\Listeners;

use \Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sevming\Foundation\Providers\Log\Log;
use Sevming\UnionPay\Events;

class LogSubscriber implements EventSubscriberInterface
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * Constructor.
     *
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events\PayStarting::class => ['payStarting', 256],
            Events\PayStarted::class => ['payStarted', 256],
            Events\Requesting::class => ['requesting', 256],
            Events\Requested::class => ['requested', 256],
            Events\SignFailed::class => ['signFailed', 256],
            Events\Notify::class => ['notify', 256],
            Events\MethodCalled::class => ['methodCalled', 256],
        ];
    }

    /**
     * @param Events\PayStarting $event
     *
     * @throws Exception
     */
    public function payStarting(Events\PayStarting $event)
    {
        $this->log->debug("{$event->gateway} Starting", $event->params);
    }

    /**
     * @param Events\PayStarted $event
     *
     * @throws Exception
     */
    public function payStarted(Events\PayStarted $event)
    {
        $this->log->info("{$event->gateway} Started", [$event->endpoint, $event->payload]);
    }

    /**
     * @param Events\Requesting $event
     *
     * @throws Exception
     */
    public function requesting(Events\Requesting $event)
    {
        $this->log->debug('Requesting', [$event->endpoint, $event->payload]);
    }

    /**
     * @param Events\Requested $event
     *
     * @throws Exception
     */
    public function requested(Events\Requested $event)
    {
        $this->log->debug('Requested', [$event->endpoint, $event->result]);
    }

    /**
     * @param Events\SignFailed $event
     *
     * @throws Exception
     */
    public function signFailed(Events\SignFailed $event)
    {
        $this->log->warning('Sign verify failed', $event->data);
    }

    /**
     * @param Events\Notify $event
     *
     * @throws Exception
     */
    public function notify(Events\Notify $event)
    {
        $this->log->info('Notify', $event->data);
    }

    /**
     * @param Events\MethodCalled $event
     *
     * @throws Exception
     */
    public function methodCalled(Events\MethodCalled $event)
    {
        $this->log->info("{$event->gateway} Method Has Called", [$event->endpoint, $event->payload]);
    }
}
