<?php

namespace SmartHome\Scheduler;

use MongoDB\BSON\UTCDateTime;
use DI\Container;
use SmartHome\Common\Abstracts\Document;
use SmartHome\Documents\Scheduler\{
    Task,
    Abstracts\AOutput,
    Abstracts\ATrigger
};

/**
 * This file defines class for scheduler context.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Context {

    /**
     * Container instance
     *
     * @var Container
     */
    private $_container = null;

    /**
     * Task
     *
     * @var Task
     */
    private $_task = null;

    /**
     * Scheduler document (Task or ATrigger)
     *
     * @var Document
     */
    private $_document = null;

    /**
     * List of outputs
     *
     * @var AOutput[]
     */
    private $_outputs = [];

    /**
     * Datetime
     *
     * @var UTCDateTime
     */
    private $_date = null;

    /**
     * Trace
     *
     * @var Trace
     */
    private $_trace = null;

    /**
     * Sets container
     *
     * @param Container $container Container
     *
     * @return Context
     */
    public function setContainer(Container $container): Context {
        $this->_container = $container;
        return $this;
    }

    /**
     * Gets container
     *
     * @return Container
     */
    public function getContainer(): Container {
        return $this->_container;
    }

    /**
     * Sets task
     *
     * @param Task $task Task document
     *
     * @return Context
     */
    public function setTask(Task $task): Context {
        $this->_task = $task;
        return $this;
    }

    /**
     * Gets task
     *
     * @return Task
     */
    public function getTask(): Task {
        return $this->_task;
    }

    /**
     * Adds output
     *
     * @param AOutput $output Output
     *
     * @return Context
     */
    public function addOutput(AOutput $output): Context {
        $this->_outputs[] = $output;
        return $this;
    }

    /**
     * Gets list of outputs
     *
     * @return AOutput[]
     */
    public function getOutputs(): array {
        return $this->_outputs;
    }

    /**
     * Sets document
     *
     * @param Task|ATrigger $document Document (Task or ATrigger)
     *
     * @return Context
     */
    public function setDocument(Document $document): Context {
        $this->_document = $document;
        return $this;
    }

    /**
     * Gets document
     *
     * @return Task|ATrigger
     */
    public function getDocument(): Document {
        return $this->_document;
    }

    /**
     * Sets datetime
     *
     * @param UTCDateTime $date Datetime
     *
     * @return Context
     */
    public function setDate(UTCDateTime $date): Context {
        $this->_date = $date;
        return $this;
    }

    /**
     * Gets datetime
     *
     * @return UTCDateTime
     */
    public function getDate(): UTCDateTime {
        return $this->_date;
    }

    /**
     * Sets trace
     *
     * @param Trace $trace Trace instance
     *
     * @return Context
     */
    public function setTrace(Trace $trace): Context {
        $this->_trace = $trace;
        return $this;
    }

    /**
     * Gets trace
     *
     * @return Trace
     */
    public function getTrace(): Trace {
        return $this->_trace;
    }

}
