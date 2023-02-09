<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WPMailSMTP\Vendor\Monolog\Handler;

use WPMailSMTP\Vendor\Monolog\Logger;
use WPMailSMTP\Vendor\Monolog\Formatter\NormalizerFormatter;
use WPMailSMTP\Vendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \WPMailSMTP\Vendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\WPMailSMTP\Vendor\Doctrine\CouchDB\CouchDBClient $client, $level = \WPMailSMTP\Vendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \WPMailSMTP\Vendor\Monolog\Formatter\NormalizerFormatter();
    }
}
