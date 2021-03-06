<?php
/*
 * This file is part of the DebugBar package.
 *
 * (c) 2013 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DebugBar;

/**
 * Handler to list and open saved dataset
 */
class OpenHandler
{
    protected $debugBar;

    /**
     * @param DebugBar $debugBar
     */
    public function __construct(DebugBar $debugBar)
    {
        if (!$debugBar->isDataPersisted()) {
            throw new DebugBarException("DebugBar must have a storage backend to use OpenHandler");
        }
        $this->debugBar = $debugBar;
    }

    /**
     * Handles the current request
     * 
     * @param array $request Request data
     */
    public function handle(array $request = null, $echo = true, $sendHeader = true)
    {
        if ($request === null) {
            $request = $_REQUEST;
        }
        if (!isset($request['op'])) {
            $request['op'] = 'find';
        }
        if (!in_array($request['op'], array('find', 'get', 'clear'))) {
            throw new DebugBarException("Invalid operation '{$request['op']}'");
        }

        if ($sendHeader) {
            header('Content-Type: application/json');
        }
        
        $response = json_encode(call_user_func(array($this, $request['op']), $request));
        if ($echo) {
            echo $response;
        }
        return $response;
    }

    /**
     * Find operation
     */
    protected function find(array $request)
    {
        $max = 20;
        if (isset($request['max'])) {
            $max = $request['max'];
        }

        $offset = 0;
        if (isset($request['offset'])) {
            $offset = $request['offset'];
        }

        $filters = array();
        foreach (array('utime', 'datetime', 'ip', 'uri') as $key) {
            if (isset($request[$key])) {
                $filters[$key] = $request[$key];
            }
        }

        return $this->debugBar->getStorage()->find($filters, $max, $offset);
    }

    /**
     * Get operation
     */
    protected function get(array $request)
    {
        if (!isset($request['id'])) {
            throw new DebugBarException("Missing 'id' parameter in 'get' operation");
        }
        return $this->debugBar->getStorage()->get($request['id']);
    }

    /**
     * Clear operation
     */
    protected function clear(array $request)
    {
        $this->debugBar->getStorage()->clear();
        return array('success' => true);
    }
}