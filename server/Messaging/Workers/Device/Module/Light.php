<?php

namespace SmartHome\Messaging\Workers\Device\Module;

use SmartHome\Entity\Control;
use SmartHome\Enum\ControlType;

/**
 * This file defines class for building light module data.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Light extends Abstracts\Builder {

    /**
     * Prepare module data for client
     *
     * @return Control | null
     */
    public function prepareForInit(): ?Control {
        $module   = $this->getModule();
        $controls = $module->getControls();

        $filterSw = function(Control $element) {
            return $element->getType() === ControlType::SWITCH;
        };
        $switch = $controls->filter($filterSw)->first(); /* @var $switch Control */

        if ($switch) {
            $control = clone $switch;

            $resolution           = ($module->getSettingsData()['resolution'] ?? 8);
            $controlData          = $control->getControlData();
            $controlData['value'] = ($controlData['value']) ? (pow(2, $resolution) - 1) : 0;
            unset($controlData['delay']);

            $control->setControlData($controlData);

            $filterPwm = function(Control $element) {
                return $element->getType() === ControlType::PWM;
            };
            $pwm = $controls->filter($filterPwm)->first(); /* @var $pwm Control */

            if ($pwm && $controlData['value']) {
                $control->setControlData($pwm->getControlData());
            }
        }

        return $control;
    }

    /**
     * Builds control data for client
     *
     * @param array $control Control data
     *
     * @return array
     */
    public function build(array $control): array {
        $settingsData = $this->getModule()->getSettingsData();
        $controlData  = $control['controlData'];

        return [
            'action' => $control['type'],
            'data'   => [
                'pin'        => $settingsData['pin'],
                'resolution' => ($settingsData['resolution'] ?? 8),
                'channel'    => $settingsData['channel'],
                'value'      => $controlData['value'],
                'previous'   => ($controlData['previous'] ?? $controlData['value']),
            ],
        ];
    }

}
