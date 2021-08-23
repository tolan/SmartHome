<?php

namespace SmartHome\Scheduler\Actions;

use Exception;
use SmartHome\Scheduler\Abstracts\AAction;
use SmartHome\Rest\Device\Helper\Control;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;
use SmartHome\Entity;
use SmartHome\Enum\ControlType;

/**
 * This file defines class for device action.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Device extends AAction {

    /**
     * Performs action on device.
     *
     * @return bool
     */
    public function execute(): bool {
        $data    = $this->getAction()->getData();
        ['module' => $moduleId, 'action' => $action, 'value' => $value] = $data;

        $commonService = $this->getContext()->getContainer()->get(Service::class); /* @var $commonService Service */
        $entityQuery   = EntityQuery::create(Entity\Module::class, [Entity\Device::class, Entity\Control::class], ['id' => $moduleId]);

        $module  = $commonService->findOne($entityQuery); /* @var $module Entity\Module */
        $device  = $module->getDevice();
        $control = current(
            array_filter($module->getControls()->toArray(), function (Entity\Control $control) use ($action) {
                return $control->getType() === $action;
            })
        ); /* @var $control Entity\Control */

        $success = true;
        if ($control) {
            $transValue  = $this->translateValue($value);
            $controlData = $control->getControlData();

            switch ($action) {
                case ControlType::SWITCH:
                    $controlData['previous']       = $controlData['value'];
                    $controlData['value']          = (bool)$transValue;
                    break;
                case ControlType::PWM:
                    $controlData['value']          = $transValue;
                    break;
                case ControlType::FADE:
                    $controlData['running']        = true;
                    $controlData['value']          = $transValue;
                    $controlData['delay']['value'] = $data['delay'];
                    $controlData['delay']['unit']  = 'min';
                    break;
            }

            $control->setControlData($controlData);
            $commonService->persist($control, true);

            try {
                $mqtt = $this->getContext()->getContainer()->get('mqtt');
                Control::syncControls($commonService, $device, $module, $control);
                Control::sendControlUpdate($mqtt, $device, $module, $control, $this->getContext()->getTrace()->getId());
            } catch (Exception $e) {
                $success = fasle;
                $this->getContext()->getContainer()->get('logger')->error('Error in Device action: '.$e->getMessage(), [$e]);
            }
        }

        return $success;
    }

    /**
     * Returns whether the actions are executable
     *
     * @return bool
     */
    public function isExecutable(): bool {
        $data     = $this->getAction()->getData();
        $moduleId = $data['module'];

        return !$this->getContext()->getTrace()->has($moduleId);
    }

}
