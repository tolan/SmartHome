<?php

namespace SmartHome\Unit\Messaging\Workers\Device\Fade;

use SmartHome\Unit\Abstracts\TestCase;
use SmartHome\Messaging\Workers\Device\Fade;

/**
 * This file defines test class for fade transformation.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Transformation extends TestCase {

    /**
     * Tests increase values
     *
     * @return void
     */
    public function testGetValueIncrease() {
        $tr       = new Fade\Transformation(10);
        $expected = [
            16, 18, 20, 22,
            25, 28, 31,
            35, 39, 43,
            48, 53,
            59, 65,
            72, 79,
            87,
            96,
            106,
            116,
            128
        ];

        foreach ($expected as $i => $exp) {
            $this->assertEquals($exp, $tr->getValue(1024, 16, 128, ($i * 10), 200));
        }
    }

    /**
     * Tests decrease values
     *
     * @return void
     */
    public function testGetValueDecrease() {
        $tr       = new Fade\Transformation(10);
        $expected = [
            128,
            116,
            106,
            96,
            87,
            79, 72,
            65, 59,
            53, 48,
            43, 39, 35,
            31, 28, 25,
            22, 20, 18, 16,
        ];

        foreach ($expected as $i => $exp) {
            $this->assertEquals($exp, $tr->getValue(1024, 128, 16, ($i * 10), 200));
        }
    }

    /**
     * Tests random values
     *
     * @return void
     */
    public function testGetValueRandom() {
        $tr       = new Fade\Transformation(3);
        $expected = [
            0   => 0,
            900 => 1023,
        ];

        foreach ($expected as $step => $exp) {
            $this->assertEquals($exp, $tr->getValue(1023, 0, 1023, $step, 900));
        }
    }

}
