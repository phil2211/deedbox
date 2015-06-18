<?php

class Application_Model_Document
{
    protected $_id;
    protected $_user_id;
    protected $_created_at;
    protected $_modified_at;
    protected $_last_found;
    protected $_file_md5;
    protected $_original_filename;
    protected $_path;
    protected $_file_name;
    protected $_tag = null;
    protected $_content;
    protected $_thumbnail;
    protected $_preview;
    protected $_fk_doc_spec = null;
    protected $_spec_accuracy = null;
    protected $_fk_doc_group = null;
    protected $_group_accuracy = null;
    protected $_group_name;
    protected $_spec_name;
    protected $_document_date;
    protected $_document_IBAN;
    protected $_document_amount;
    protected $_document_stats;


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
            throw new Exception('Ungültige Document Eigenschaft: ' . $name);
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Ungültige Document Eigenschaft: ' . $name);
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

    public function setLast_found($string)
    {
        $this->_last_found = (string) $string;
        return $this;
    }

    public function getLast_found()
    {
        return $this->_last_found;
    }


    public function setFile_md5($string)
    {
        $this->_file_md5 = (string) $string;
        return $this;
    }

    public function getFile_md5()
    {
        return $this->_file_md5;
    }

    public function setPath($string)
    {
        $this->_path = (string) $string;
        return $this;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function setFile_name($string)
    {
        $this->_file_name = (string) $string;
        return $this;
    }

    public function getFile_name()
    {
        return $this->_file_name;
    }

    public function setTag($string)
    {
        $this->_tag = $string;
        return $this;
    }

    public function getTag()
    {
        return $this->_tag;
    }

    public function setContent($string)
    {
        $this->_content = (string) $string;
        return $this;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setThumbnail($string)
    {
        $this->_thumbnail = (string) $string;
        return $this;
    }

    public function getThumbnail()
    {
        return $this->_thumbnail;
    }

    public function setPreview($string)
    {
        $this->_preview = (string) $string;
        return $this;
    }

    public function getPreview()
    {
        return $this->_preview;
    }

    public function setFk_doc_spec($int)
    {
        $this->_fk_doc_spec = $int;
        return $this;
    }

    public function getFk_doc_spec()
    {
        return $this->_fk_doc_spec;
    }

    public function setFk_doc_group($int)
    {
        $this->_fk_doc_group = $int;
        return $this;
    }

    public function getFk_doc_group()
    {
        return $this->_fk_doc_group;
    }

    public function setOriginal_filename($string)
    {
        $this->_original_filename = $string;
        return $this;
    }

    public function getOriginal_filename()
    {
        return $this->_original_filename;
    }

    public function setSpec_accuracy($int)
    {
        $this->_spec_accuracy = $int;
        return $this;
    }

    public function getSpec_accuracy()
    {
        return $this->_spec_accuracy;
    }

    public function setGroup_accuracy($int)
    {
        $this->_group_accuracy = $int;
        return $this;
    }

    public function getGroup_accuracy()
    {
        return $this->_group_accuracy;
    }

    public function setGroup_name($string)
    {
        $this->_group_name = $string;
        return $this;
    }

    public function getGroup_name()
    {
        return $this->_group_name;
    }

    public function setSpec_name($string)
    {
        $this->_spec_name = $string;
        return $this;
    }

    public function getSpec_name()
    {
        return $this->_spec_name;
    }

    public function setDocument_date($string)
    {
        $this->_document_date = $string;
        return $this;
    }

    public function getDocument_date()
    {
        return $this->_document_date;
    }

    public function setDocument_IBAN($string)
    {
        $this->_document_IBAN = $string;
        return $this;
    }

    public function getDocument_IBAN()
    {
        return $this->_document_IBAN;
    }

    public function setDocument_amount($string)
    {
        $this->_document_amount = $string;
        return $this;
    }

    public function getDocument_amount()
    {
        return $this->_document_amount;
    }

    public function setDocument_stats($string)
    {
        $this->_document_stats = $string;
        return $this;
    }

    public function getDocument_stats()
    {
        return $this->_document_stats;
    }
}

