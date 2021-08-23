<?php

namespace SmartHome\Scheduler\Actions;

use Exception;
use Laminas\Http\Client;
use SmartHome\Scheduler\Abstracts\AAction;
use SmartHome\Enum\Scheduler\Action\Http\Method;

/**
 * This file defines class for HTTP action.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Http extends AAction {

    /**
     * Performs HTTP request action.
     *
     * @return bool
     */
    public function execute(): bool {
        $data    = $this->getAction()->getData();
        ['method' => $method, 'uri' => $uri, 'params' => $params, 'body' => $body] = $data;

        $success = true;
        try {
            $client = new Client();
            $client->setMethod($method);
            $client->setUri($uri);
            $client->setParameterGet(
                array_reduce($params, function (array $acc, array $param) {
                    $acc[$this->translateValue($param['key'])] = $this->translateValue($param['value']);
                    return $acc;
                }, [])
            );
            $client->setOptions([
                'timeout' => 10,
            ]);

            if (in_array($method, [Method::POST, Method::PUT])) {
                $client->setRawBody($body);
            }

            $client->send();
        } catch (Exception $e) {
            $this->getContext()->getContainer()->get('logger')->error('Error in HTTP action: '.$e->getMessage(), [$e]);
            $success = false;
        }

        return $success;
    }

    /**
     * Returns whether the actions are executable
     *
     * @return bool
     */
    public function isExecutable(): bool {
        return true;
    }

}
