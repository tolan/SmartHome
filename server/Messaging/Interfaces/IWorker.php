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
     * @param boolean $loop Run in loop?
     *
     * @return void
     */
    public function proc($loop = true);

}
