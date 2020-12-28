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
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Controller {

    /**
     *
     * @var Service
     */
    private $_commonService;

    /**
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * @var Authorize
     */
    private $_authorize;

    const DIR = __DIR__.'/../../../firmwares';

    public function __construct (Container $container) {
        $this->_commonService = $container->get(Service::class);
        $this->_mqtt = $container->get('mqtt');
        $this->_authorize = $container->get('authorize');
    }

    public function firmwares (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(Firmware::class);
        $firmwares = $this->_commonService->find($query);

        $data = array_map(function(Firmware $firmware) {
            return [
                'firmware' => $firmware,
            ];
        }, $firmwares);

        return $response->withJson($data);
    }

    public function create (Request $request, Response $response) {
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

    public function update (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);
        $data = $request->getParsedBody();

        $query = EntityQuery::create(Firmware::class, [], ['id' => $data['firmware']['id']]);
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

    public function delete (Request $request, Response $response, array $params) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $query = EntityQuery::create(Firmware::class, [], ['id' => $params['id']]);
        $firmware = $this->_commonService->findOne($query); /* @var $firmware Firmware */

        if ($firmware) {
            if (file_exists($firmware->getDir().'/'.$firmware->getFilename())) {
                unlink($firmware->getDir().'/'.$firmware->getFilename());
            }

            $this->_commonService->remove($firmware, true);
        }

        return $response->withStatus(HttpStatusCode::OK);
    }

    public function upload (Request $request, Response $response) {
        $this->_authorize->checkPermissions($request, [Permission::TYPE_SECTION_ADMIN]);

        $tmpDir = self::DIR.'/tmp';
        if (!file_exists($tmpDir)) {
            mkdir($tmpDir);
        }

        $file = $request->getUploadedFiles()['file']; /* @var $file UploadedFile */
        $file->moveTo($tmpDir.'/'.$file->getClientFilename());

        return $response->withJson(['filename' => $file->getClientFilename()]);
    }

    private function _saveFile (Firmware $firmware) {
        $filename = $firmware->getFilename();
        $targetDir = $firmware->getDir();
        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }

        rename(self::DIR.'/tmp/'.$filename, $targetDir.'/'.$filename);

        return $this;
    }

}
