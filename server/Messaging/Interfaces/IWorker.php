<?php

namespace SmartHome\Messaging\Interfaces;

/**
 * This file defines interface for messaging worker
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IWorker {

    /**
     * Receives message
     *
     * @return void
     */
    public function proc();

}
