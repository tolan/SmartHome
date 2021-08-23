<?php

namespace SmartHome\Enum;

/**
 * This file defines class for enum of cache.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Cache {

    // Scopes
    const SCOPE_USER      = 'user';
    const SCOPE_DEVICE    = 'device';
    const SCOPE_PROCESS   = 'process';
    const SCOPE_SCHEDULER = 'scheduler';

    // TTLs
    const TTL_7_DAYS = ( 3600 * 24 * 7 );
    const TTL_1_DAY  = ( 3600 * 24 );

}
