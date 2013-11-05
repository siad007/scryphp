<?php
/**
 * Scryphp.
 * Batch Tool for scrayping websites using xml based execution plans.
 *
 * @author Siad Ardroumli <siad.ardroumli@idealo.de>
 * @package Scryphp
 * @version 0.1
 * @since 0.1
 */

namespace Scryphp;

use Goutte\Client;
use Monolog;

class Scrype
{
    const VERSION = '0.1';

    protected $plan = null;

    protected $logger = null;

    public function __construct($plan)
    {
        try {
            $this->plan = new Plan($plan);
        } catch (Scryphp\Exception $se) {
            $this->logger->error($se->getMessage());
        }
    }

    public function getPlanConfiguration()
    {
        return $this->plan->getPlan();
    }

    public function process()
    {
        $plan = $this->plan->getPlan();
        $this->logger->info(sprintf('%s %s', $plan['method'], $plan['uri']));
        return $this->plan->process();
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(Monolog\Logger $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
