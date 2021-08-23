<?php

namespace SmartHome\Messaging\Workers\Device\Module;

use SmartHome\Entity\Control;
use SmartHome\Enum\{
    ControlType,
    UpDown
};

/**
 * This file defines class for building engine module data.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Engine extends Abstracts\Builder {

    /**
     * Prepare module data for client
     *
     * @return Control | null
     */
    public function prepareForInit(): ?Control {
        $module   = $this->getModule();
        $controls = $module->getControls();

        $filterUpDown = function(Control $element) {
            return $element->getType() === ControlType::UP_DOWN;
        };
        $upDown = $controls->filter($filterUpDown)->first(); /* @var $upDown Control */

        if ($upDown) {
            $upDown               = clone $upDown;
            $controlData          = $upDown->getControlData();
            $controlData['value'] = UpDown::STOP;
            $upDown->setControlData($controlData);
        }

        return $upDown;
    }

    /**
     * Builds control data for client
     *
     * @param array $control Control data
     *
     * @return array
     */
    public function build(array $control): array {
        $module       = $this->getModule();
        $settingsData = $module->getSettingsData();
        $enabledPins  = [];
        $disablePins  = [];
        $blockPins    = [$settingsData['pinUpBlock'], $settingsData['pinDownBlock'], $settingsData['pinOthersBlock']];

        switch ($control['controlData']['value']) {
            case UpDown::UP:
                $enabledPins = [$settingsData['pinUp']];
                $disablePins = [$settingsData['pinDown']];
                break;
            case UpDown::DOWN:
                $enabledPins = [$settingsData['pinDown']];
                $disablePins = [$settingsData['pinUp']];
                break;
            case UpDown::STOP:
            default:
                $enabledPins = [];
                $disablePins = [$settingsData['pinUp'], $settingsData['pinDown']];
                break;
        }

        return [
            'action' => $control['type'],
            'data'   => [
                'enablePins'    => array_unique($enabledPins),
                'disablePins'   => array_unique($disablePins),
                'blockPins'     => array_unique($blockPins),
                'blockDuration' => $settingsData['blockDuration'],
            ],
        ];
    }

}
