<?php

class Application_Model_Docgroup
{
    protected $_id;
    protected $_user_id;
    protected $_created_at;
    protected $_modified_at;
    protected $_name;
    protected $_short_name;
    protected $_recognition_feature;
    protected $_sift_index;


    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }


    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Ungültige Docgroup Eigenschaft: ' . $name);
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Ungültige Docgroup Eigenschaft: ' . $name);
        }
        return $this->$method();
    }


    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }


    public function setId($id)
    {
        $this->_id = (int) $id;
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setUser_id($user_id)
    {
        $this->_user_id = (int) $user_id;
        return $this;
    }

    public function getUser_id()
    {
        return $this->_user_id;
    }

    public function setCreated_at($string)
    {
        $this->_created_at = (string) $string;
        return $this;
    }

    public function getCreated_at()
    {
        return $this->_created_at;
    }

    public function setModified_at($string)
    {
        $this->_modified_at = (string) $string;
        return $this;
    }

    public function getModified_at()
    {
        return $this->_modified_at;
    }

    public function setName($string)
    {
        $this->_name = (string) $string;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setRecognition_feature($string)
    {
        $this->_recognition_feature = $string;
        return $this;
    }

    public function getRecognition_feature()
    {
        return $this->_recognition_feature;
    }

    public function setSift_index($string)
    {
        $this->_sift_index = $string;
        return $this;
    }

    public function getSift_index()
    {
        return $this->_sift_index;
    }

    public function setShort_name($string)
    {
        $this->_short_name = $string;
        return $this;
    }

    public function getShort_name()
    {
        return $this->_short_name;
    }

}

