<?php

namespace OpenEMR\Cqm;

use GuzzleHttp\Client;
use OpenEMR\Common\Http\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use function GuzzleHttp\Psr7\str;

/**
 * Class CqmClient
 *
 * @package OpenEMR\Cqm
 * @author Ken Chapple
 */
class CqmClient extends HttpClient
{
    protected function getCommand()
    {
        $port = $this->port;
        $node = $GLOBALS['node_binary'];
        $cmd = $this->servicePath;
        return "CQM_EXECUTION_SERVICE_PORT=$port $node $cmd";
    }

    /**
     * Returns the CQM service's health.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getHealth():array
    {
        try {
            return json_decode(
                Utils::copyToString($this->request('GET', '/health')->getBody()),
                true
            );
        } catch (ConnectException $exception) {
            return [
                'uptime' => 0
            ];
        } catch (ServerException $exception) {
            return [
                'uptime' => 0
            ];
        }
    }

    /**
     * Returns CQM Service version and dependencies lookup.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getVersion():array
    {
        return json_decode(
            Utils::copyToString($this->request('GET', '/version')->getBody()),
            true
        );
    }

    /**
     * Caluculates a CQM measure given a QDM Patient, Measure and ValueSet
     *
     * @param StreamInterface $patients
     * @param StreamInterface $measure
     * @param StreamInterface $valueSets
     * @return StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function calculate(StreamInterface $patients, StreamInterface $measure, StreamInterface $valueSets)
    {
        $patients = (string)str_replace( ["\r\n", "\n", "\r"], '', (string)$patients);
        $measure = (string)str_replace( ["\r\n", "\n", "\r"], '', (string)$measure);
        $valueSets = (string)str_replace( ["\r\n", "\n", "\r"], '', (string)$valueSets);
        try {
            return json_decode(
                Utils::copyToString(
                    $this->request('POST', '/calculate', [
                        'form_params' => [
                            'patients' => $patients,
                            'measure' => $measure,
                            'valueSets' => $valueSets
                        ]]
                    )->getBody()
                ), true);
        } catch (ConnectException $exception) {
            return [$exception->getMessage()];
        } catch (ServerException $exception) {
            return [$exception->getMessage()];
        }
    }

    /**
     * Perform a graceful shutdown of cqm-service node (express) server
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function shutdown():array
    {
        return json_decode(
            Utils::copyToString($this->request('GET', '/shutdown')->getBody()),
            true
        );
    }
}
