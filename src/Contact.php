<?php
namespace App;

class Contact {
    protected $id, $name, $email, $phone, $created;

    //Using  Magic methods __get
    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    //Using  Magic methods __set
    public function __set($property, $value) {
        if (property_exists($this, $property && $property != 'id')) {
            $this->$property = $value;
        }
    }
}