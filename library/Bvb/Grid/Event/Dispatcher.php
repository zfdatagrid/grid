<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id$
 * @link      http://zfdatagrid.com
 */
class Bvb_Grid_Event_Dispatcher
{

    /**
     * List of observers
     *
     * @var array
     */
    protected $_listeners = array();

    /**
     * Regists a new observer
     *
     * @param string  $event    Event name
     * @param calable $callback Callback to be called
     *
     * @return Bvb_Grid
     */
    public function connect($event, $callback)
    {

        if (!is_callable($callback)) {
            throw new Bvb_Grid_Exception((string) $callback . " not callable");
        }

        if (!isset($this->_listeners[$event])) {
            $this->_listeners[$event] = array();
        }

        $this->_listeners[$event][] = $callback;

        return $this;
    }

    /**
     * Returns current registered observers for one or all events
     *
     * @param string $event Event to return. Leave null to return all
     *
     * @return mixed
     */
    public function getRegisteredListeners($event=null)
    {

        if ($event === null) {
            return $this->_listeners;
        }

        if (isset($this->_listeners[$event])) {
            return $this->_listeners[$event];
        }

        return null;
    }

    /**
     *
     * @param string $name Event name
     * @param array  $data Data to be passed to object
     *
     * @return void
     */
    public function emit(Bvb_Grid_Event $event)
    {

        if (isset($this->_listeners[$event->getName()])) {

            foreach ($this->_listeners[$event->getName()] as $callback) {
                call_user_func($callback, $event);
            }
        }
    }

}
