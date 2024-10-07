<?php

class StockTransferOrdersHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        
    }

}

if (!class_exists('DisplayException')) {

    class DisplayException extends Exception {

        function set($key, $value) {
            $this->$key = $value;
        }

        function get($key) {
            return $this->$key;
        }

        function setDisplayMessage($message) {
            $this->set('display_message', $message);
        }

        function getDisplayMessage() {
            return $this->get('display_message');
        }

    }

}