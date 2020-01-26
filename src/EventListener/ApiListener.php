<?php
declare(strict_types=1);

namespace App\EventListener;

use App\Service\Api\ApiRequestEvent;
use App\Service\Api\ApiResponseEvent;
use App\Service\Api\Exception\JsonRpcInvalidRequestException;
use App\Service\Api\JsonRpcRequest;
use Doctrine\ORM\EntityManagerInterface;

class ApiListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onApiRequest(ApiRequestEvent $event)
    {
        $request = $event->getRequest();
        $authorizationHeader = $request->getHttpRequest()->headers->get('Authorization');
        if (empty($authorizationHeader)) {
            throw new JsonRpcInvalidRequestException('Authorization header is required');
        }
        if (0 !== strpos($authorizationHeader, 'Bearer ')) {
            throw new JsonRpcInvalidRequestException('Only Bearer authentication accepted');
        }
        /*$project = $this->entityManager->getRepository(Project::class)
            ->findOneBy(['token' => substr($authorizationHeader, 7)]);
        if (empty($project)) {
            throw new JsonRpcInvalidRequestException('Project not found');
        }
        $request->addObject($project);*/
    }

    public function onApiResponse(ApiResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $response->getRequest();
        if ($request->getMethod()) {
            list($serviceName, $methodName) = explode('.', $request->getMethod(), 2);
        } else {
            $serviceName = '';
            $methodName = '';
        }
        $project = $request->getObject(Project::class);
        $entry = [
            'project_id' => $project ? $project->getId() : '',
            'service' => $serviceName ?: '',
            'method' => $methodName ?: '',
            'request' => $this->prepareRequestParamsForLogging($request),
            'duration' => $response->getDuration()
        ];
        if ($response->getResult()) {
            $entry['response_type'] = 'success';
            $entry['response'] = json_encode($response->getResult(), JSON_UNESCAPED_UNICODE);
        } else {
            $entry['response_type'] = 'error';
            $entry['response'] = json_encode($response->getError(), JSON_UNESCAPED_UNICODE);
        }
        //$this->container->get('doctrine.dbal.default_connection')->insert('log_jsonrpc_server_pro', $entry);
        $response->getHttpResponse()->headers->add([
            'X-Api-Endpoint' => $request->getHttpRequest()->get('_route'),
            'X-Exec-Time' => $response->getDuration()
        ]);
    }

    protected function prepareRequestParamsForLogging(JsonRpcRequest $request)
    {
        $params = $request->getParams();
        foreach ($params as $key => &$value) {
            if (is_string($value) && mb_strlen($value) > 5120) {
                $value = 'LONG_STRING_STRIPPED';
            }
            if (is_array($value)) {
                foreach ($value as $subkey => &$subvalue) {
                    if (is_string($subvalue) && mb_strlen($subvalue) > 5120) {
                        $subvalue = 'LONG_STRING_STRIPPED';
                    }
                }
            }
        }
        return json_encode($params, JSON_UNESCAPED_UNICODE);
    }
}