<?php

namespace SmartHome\Messaging\Workers\Device\Fade;

/**
 * This file defines class for fade transformation.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Transformation {

    /**
     * Exponent
     *
     * @var integer
     */
    private $_exp;

    /**
     * Construct method for defined exponent
     *
     * @param int $exp Exponent
     */
    public function __construct(int $exp) {
        $this->_exp = $exp;
    }

    /**
     * Get value for current step
     *
     * @param int $maxY        Max Y value
     * @param int $initY       Initial Y value
     * @param int $targetY     Target Y value
     * @param int $stepX       Current step on X
     * @param int $resolutionX Count of steps in X
     *
     * @return int
     */
    public function getValue(int $maxY, int $initY, int $targetY, int $stepX, int $resolutionX): int {
        $initX   = $this->_toX($initY / $maxY);
        $targetX = $this->_toX($targetY / $maxY);

        $currentX = ($initX + (($stepX / $resolutionX) * ($targetX - $initX)));
        $currentY = ($maxY * $this->_toY($currentX));

        return round($currentY);
    }

    /**
     * Tranforms Y to X
     *
     * @param float $y Y value
     *
     * @return float
     */
    private function _toX(float $y): float {
        return pow($y, (1 / $this->_exp));
    }

    /**
     * Transforms X to Y
     *
     * @param float $x X value
     *
     * @return float
     */
    private function _toY(float $x): float {
        return pow($x, $this->_exp);
    }

}
