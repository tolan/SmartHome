<?php

namespace SmartHome\Rest\Device\Helper;

use SmartHome\Common\MQTT;
use SmartHome\Common\Service;
use SmartHome\Database\EntityQuery;
use SmartHome\Common\Utils\{
    JSON,
    DateTime
};
use SmartHome\Enum\{
    Topic,
    ControlType
};
use SmartHome\Entity\{
    Device,
    Module,
    Control as ControlEntity,
    Timer
};

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Control {

    public static function sendControlUpdate (MQTT $mqtt, Device $device, Module $module = null, ControlEntity $control = null) {
        $data = [
            'device' => $device,
            'module' => $module,
            'control' => $control,
        ];

        if ($control->getType() === ControlType::FADE) {
            $controlData = $control->getControlData();
            $controlData['created'] = new DateTime();
            $control->setControlData($controlData);

            $timer = new Timer();
            $timer->setName('device_control_fade_'.$module->getId());
            $timer->setTargetTopic(Topic::DEVICE_CONTROL_FADE);
            $timer->setContent($data);
            $timer->setTimeout('1sec');
            $timer->setRepeated(true);

            $mqtt->publish(Topic::TIMER_START, JSON::encode($timer));
        } elseif ($control->getType() === ControlType::MQTT) {
            $mqtt->publish(Topic::DEVICE_CONTROL_MQTT, JSON::encode($data));
        } else {
            $mqtt->publish(Topic::DEVICE_CONTROL, JSON::encode($data));
        }
    }

    public static function syncControls (Service $commonService, Device $device, Module $module, ControlEntity $control) {
        $commonService->flush();
        $commonService->clear();

        $query = EntityQuery::create(Module::class, [[ControlEntity::class]], ['id' => $module->getId()]);
        $module = $commonService->findOne($query); /* @var $module Module */
        $controls = $module->getControls();

        switch ($control->getType()) {
            case ControlType::SWITCH:
                $controlData = $control->getControlData();
                $pwm = $controls->filter(function(ControlEntity $element) {
                            return $element->getType() === ControlType::PWM;
                        })->first(); /* @var $pwm ControlEntity */

                if ($pwm && $controlData['value']) {
                    $resolution = $module->getSettingsData()['resolution'] ?? 8;
                    $controlData['value'] = $pwm->getControlData()['value'] ? $pwm->getControlData()['value'] : (2 ^ $resolution - 1);
                    $control->setControlData($controlData);

                    if (!$pwm->getControlData()['value']) {
                        $pwmControlData = $pwm->getControlData();
                        $pwmControlData['value'] = $controlData['value'];
                        $pwm->setControlData($pwmControlData);
                        $commonService->persist($pwm, true);
                    }
                }

                break;
            case ControlType::PWM:
                $controlData = $control->getControlData();
                $switch = $controls->filter(function(ControlEntity $element) {
                            return $element->getType() === ControlType::SWITCH;
                        })->first(); /* @var $switch ControlEntity */
                if ($switch && (bool)$switch->getControlData()['value'] !== (bool)$controlData['value']) {
                    $switchControlData = $switch->getControlData();
                    $switchControlData['value'] = (bool)$controlData['value'];
                    $switch->setControlData($switchControlData);
                    $commonService->persist($switch, true);
                }

                $fade = $controls->filter(function(ControlEntity $element) {
                            return $element->getType() === ControlType::FADE;
                        })->first(); /* @var $fade ControlEntity */

                if ($fade) {
                    $fadeControlData = $fade->getControlData();
                    $fadeControlData['running'] = false;
                    $fade->setControlData($fadeControlData);
                    $commonService->persist($fade, true);
                }

                break;
            case ControlType::FADE:
                $controlData = $control->getControlData();
                $pwm = $controls->filter(function(ControlEntity $element) {
                            return $element->getType() === ControlType::PWM;
                        })->first(); /* @var $pwm ControlEntity */

                if ($pwm) {
                    $controlData['initValue'] = $pwm->getControlData()['value'];
                    $control->setControlData($controlData);
                }

                break;
        }
    }

    public static function sendRemoteControl (Service $commonService, MQTT $mqtt, string $moduleId, string $type, string $value, array $data = []) {
        if (preg_match('/^([+-])(\d+)$/', $value, $matches)) {
            list(, $way, $value) = $matches;
            $query = EntityQuery::create(Module::class, [], ['id' => $moduleId]);
            $module = $commonService->findOne($query); /* @var $module Module */
            $moduleData = $module->getSettingsData();

            $controls = $module->getControls();
            $control = $controls->filter(function(ControlEntity $element) use($type) {
                        return $element->getType() === $type;
                    })->first(); /* @var $control ControlEntity */

            $controlData = $control->getControlData();
            $maxValue = pow(2, $moduleData['resolution'] ?? 8) - 1;

            if ($way === '+') {
                $value = min($maxValue, $controlData['value'] + $value);
            } else {
                $value = max(0, $controlData['value'] - $value);
            }
        }

        $mqtt->publish(Topic::DEVICE_CONTROL_REMOTE.'/'.$moduleId.'/'.$type, JSON::encode(['value' => $value, 'data' => $data]));
    }

}
