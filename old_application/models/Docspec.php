<?php

class Application_Model_Docspec
{
    protected $_id;
    protected $_created_at;
    protected $_modified_at;
    protected $_name;
    protected $_code;
    protected $_sort;
    protected $_recog_features = array();


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
    
    public function setCode($string)
    {
        $this->_code = $string;
        return $this;
    }
    
    public function getCode()
    {
        return $this->_code;
    }
    
    public function setSort($string)
    {
        $this->_sort = $string;
        return $this;
    }
    
    public function getSort()
    {
        return $this->_sort;
    }
    
    public function setRecog_features(array $array)
    {
        $this->_recog_features = $array;
        return $this;
    }
    
    public function getRecog_features()
    {
        return $this->_recog_features;
    }

}

