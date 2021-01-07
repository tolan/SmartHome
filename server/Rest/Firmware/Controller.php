<?php

namespace SmartHome\Rest\Firmware;

use DI\Container;
use Slim\Http\ServerRequest as Request;
use Slim\Http\Response as Response;
use Slim\Psr7\UploadedFile;
use SmartHome\Common\MQTT;
use SmartHome\Entity\Firmware;
use SmartHome\Enum\HttpStatusCode;
use SmartHome\Rest\Firmware\Helper\Upload;
use SmartHome\Authorization\Authorize;
use SmartHome\Enum\Permission;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Service;

/**
 * This file defines class for Firmware controller.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     * Common service instance
     *
     * @var Service
     */
    private $_commonService;

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * Authorization instance
     *
     * @var Authorize
     */
    private $_authorize;

    const DIR = __DIR__.'/../../../firmwares';

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_commonService = $container->get(Service::class);
        $this->_mqtt          = $container->get('mqtt');
        $this->_authorize     = $container->get('authorize');
    }

    /**
     * Gets list of firmwares
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function firmwares(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query     = EntityQuery::create(Firmware::class);
        $firmwares = $this->_commonService->find($query);

        $data = array_map(function(Firmware $firmware) {
            $data = [
                'firmware' => $firmware,
            ];
            return $data;
        }, $firmwares);

        return $response->withJson($data);
    }

    /**
     * Creates firmware
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function create(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $firmware = new Firmware();
        $firmware->setName($data['firmware']['name']);
        $firmware->setFilename($data['firmware']['filename']);

        $this->_commonService->persist($firmware, true);

        $this->_saveFile($firmware);

        Upload::sentFirmwareUpdate($firmware, $this->_mqtt);

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Updates firmware
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function update(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query    = EntityQuery::create(Firmware::class, [], ['id' => $data['firmware']['id']]);
        $firmware = $this->_commonService->findOne($query); /* @var $firmware Firmware */

        if ($firmware) {
            $filename = $data['firmware']['filename'];

            $firmware->setName($data['firmware']['name']);
            if (!empty($filename)) {
                $targetFilename = $firmware->getDir().'/'.$firmware->getFilename();
                if (file_exists($targetFilename)) {
                    unlink($targetFilename);
                }

                $firmware->setFilename($filename);
                $this->_saveFile($firmware);

                Upload::sentFirmwareUpdate($firmware, $this->_mqtt);
            }

            $this->_commonService->persist($firmware, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Deletes firmware
     *
     * @param Request  $request  Request
     * @param Response $response Response
     * @param array    $params   Parameters (id)
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query    = EntityQuery::create(Firmware::class, [], ['id' => $params['id']]);
        $firmware = $this->_commonService->findOne($query); /* @var $firmware Firmware */

        if ($firmware) {
            if (file_exists($firmware->getDir().'/'.$firmware->getFilename())) {
                unlink($firmware->getDir().'/'.$firmware->getFilename());
            }

            $this->_commonService->remove($firmware, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    /**
     * Uploads firmware binary
     *
     * @param Request  $request  Request
     * @param Response $response Response
     *
     * @return Response
     */
    public function upload(Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $tmpDir = self::DIR.'/tmp';
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir);
        }

        $file = $request->getUploadedFiles()['file']; /* @var $file UploadedFile */
        $file->moveTo($tmpDir.'/'.$file->getClientFilename());

        return $response->withJson(['filename' => $file->getClientFilename()]);
    }

    /**
     * Saves binary file
     *
     * @param Firmware $firmware Firmware
     *
     * @return $this
     */
    private function _saveFile(Firmware $firmware) {
        $filename  = $firmware->getFilename();
        $targetDir = $firmware->getDir();
        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }

        rename(self::DIR.'/tmp/'.$filename, $targetDir.'/'.$filename);

        return $this;
    }

}
