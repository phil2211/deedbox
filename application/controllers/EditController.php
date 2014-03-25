<?php

class EditController extends Zend_Controller_Action
{

	public function init()
	{
		$this->view->showSidebar = false;
		$this->view->headScript()->appendFile('/js/jquery.imgareaselect.js');
		$this->view->headScript()->appendFile('/js/pdfobject.js');
	}

    public function indexAction()
    {
        $documents = array();
        $document = new Application_Model_DocumentMapper();
        $rowset = $document->getUnrecognizedDocuments();
        foreach ($rowset as $row) {
            $documents[] = array('docid'    => $row->getId(),
                                 'doctitle' => $row->getFile_name(),
                                 'groupname'=> $row->getGroup_name(),
                                 'target'   => '/edit/showbig/documentId/' . $row->getId());
        }

        $this->view->unrecognizedDocuments = $documents;
    }

    public function newgroupAction()
    {
        $form = new Application_Form_Newgroup();
        $form->setAction('/edit/savenewgroup');
        $this->view->form = $form;
        $this->view->docid = $this->_request->getParam('documentId');
    }

    public function savenewgroupAction()
    {
        $documentMapper = new Application_Model_DocumentMapper();
        $document = new Application_Model_Document();
        $documentMapper->find($this->_request->getParam('document'), $document);

        $imageHandler = new DeedBox_ImageHandler();
        $imageHandler->setImage($document->getPreview())
                     ->cropImage($this->_request->getParam('width'),
                                 $this->_request->getParam('height'),
                                 $this->_request->getParam('x'),
                                 $this->_request->getParam('y'));


        $docGroup = new Application_Model_Docgroup();
        $docGroup->setUser_id(Application_Model_User::getId());
        $docGroup->setName($this->_request->getParam('groupname'));
        $docGroup->setRecognition_feature($imageHandler->getImage());

        $imageHandler->setSiftTool(Zend_Registry::get('config')->get('siftfeat'))
                     ->createSiftIndex();

        $docGroup->setSift_index($imageHandler->getSiftFeat());

        $docGroupMapper = new Application_Model_DocgroupMapper();
        $docGroupMapper->save($docGroup);

        //Erkennungsjob für dieses Dokument ausführen und Resultat prüfen
        $jobProc = new DeedBox_JobProcessor();
        $result = $jobProc->analyzeDocuments($document->getId());

        if ($result[$document->getId()]['group'] != $docGroup->getId()) {
            $where = Zend_Registry::get('db')->quoteInto('id = ?', $docGroup->getId());
            $docGroupMapper->getDbTable()->delete($where);
            throw new Exception('Dokument kann mit diesem Erkennungsmerkmal nicht zugteilt werden');
        }

    }

    public function showbigAction()
    {
        $this->view->docid = $this->_request->getParam('documentId');
    }


}






