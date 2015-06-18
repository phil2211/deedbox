<?php

class SearchController extends Zend_Controller_Action
{

	/**
	* Hier kommen spezifische Sachen rein, die allen Actions helfen können.
	* Wie eigene Scripts / CSS usw..
	*/
	public function init()
	{
		$this->view->showSidebar = false;
		$this->view->headScript()->appendFile('/js/jquery.imgareaselect.js');
		$this->view->headScript()->appendFile('/js/pdfobject.js');
		$this->view->headScript()->appendFile('/lib/js/db_docsearch.js');
	}

    public function indexAction()
    {
        $form = new Application_Form_Search();
        $form->setAction('/search/searchresult');
        $this->view->form = $form;
    }

    public function searchresultAction()
    {
        //document Model initialisieren
        $documentMapper = new Application_Model_DocumentMapper();
        $document = new Application_Model_Document();

        $form = new Application_Form_Search();

        //Index Handler initialisieren
        $indexHandler = Application_Model_User::getIndexHandler();

        if (strlen(trim($this->_request->getParam('suchbegriff'))) > 0) {
            $indexHandler->addSearchText($this->_request->getParam('suchbegriff'));
            $form->getElement('suchbegriff')->setValue($this->_request->getParam('suchbegriff'));
        }

        if ($this->_request->getParam('documentGroup') > 0) {
            $indexHandler->addSearchTerm(array('group_id'=>$this->_request->getParam('documentGroup')));
            $form->getElement('documentGroup')->setValue($this->_request->getParam('documentGroup'));
        }

        if ($this->_request->getParam('documentSpec') > 0) {
            $indexHandler->addSearchTerm(array('spec_id'=>$this->_request->getParam('documentSpec')));
            $form->getElement('documentSpec')->setValue($this->_request->getParam('documentSpec'));
        }

        $form->getElement('from')->setValue($this->_request->getParam('from'));
        $form->getElement('to')->setValue($this->_request->getParam('to'));

        $indexHandler->addRangeTerm(date('Ymd', $this->_request->getParam('from')),
                                    date('Ymd', $this->_request->getParam('to')),
                                    'date');
        $indexHandler->query();
        $results = $indexHandler->getSearchResult();

        $this->view->form = $form;
        $this->view->resultCount = count($results);

        if (count($results) > 0) {
            $lastId = 0;
            foreach ($results as $result) {
                if ($lastId != $result->document_id) {
                    $documentMapper->find($result->document_id, $document);
                    $searchResult[] = array('docid'      => $document->getId(),
                                            'doctitle'   => $document->getFile_name(),
                                            'groupname'  => $document->getGroup_name(),
                                            'target'     => '/search/docview/id/' . $document->getId());
                }
                $lastId = $result->document_id;
            }
            $this->view->flowContent = $searchResult;
        } else {
            $this->view->flowContent = array(array('docid' => 0));
        }

    }

    public function docviewAction()
    {
        $this->view->docid = $this->_request->getParam('id');
    }

    public function statsAction()
    {
        $document = new Application_Model_Document();
        $documentMapper = new Application_Model_DocumentMapper();

        $documentMapper->find($this->_request->getParam('document'), $document);

        $stats = json_decode($document->getDocument_stats(), TRUE);

        arsort($stats['subimage'], SORT_NUMERIC);
        $parts = array();
        foreach ($stats['subimage'] as $key=>$value) {
            $parts[] = array('key'=>$key, 'value'=>$value . '%');
        }
        $this->view->subimage = $parts;

        arsort($stats['specs'], SORT_NUMERIC);
        $parts = array();
        foreach ($stats['specs'] as $key=>$value) {
            $parts[] = array('key'=>$key, 'value'=>$value);
        }
        $this->view->docspec = $parts;

        $groupBoxplot = APPLICATION_PATH . '/tmp/' . 'groupbox' . $document->getId() . '.png';
        $specBoxplot = APPLICATION_PATH . '/tmp/' . 'specbox' . $document->getId() . '.png';

        file_put_contents($groupBoxplot, base64_decode($stats['boxplot']['groupstats']));
        if (array_key_exists('specstats_rgx', $stats['boxplot']))
            file_put_contents($specBoxplot, base64_decode($stats['boxplot']['specstats_rgx']));
        else
            file_put_contents($specBoxplot, base64_decode($stats['boxplot']['specstats_sim']));

        $this->view->specstats = pathinfo($specBoxplot, PATHINFO_BASENAME);
        $this->view->groupstats = pathinfo($groupBoxplot, PATHINFO_BASENAME);
        $this->view->docname = $document->getFile_name();
        $this->view->groupAccuracy = $document->getGroup_accuracy();
        $this->view->specAccuracy = $document->getSpec_accuracy();
        $this->view->groupName = $document->getGroup_name();
        $this->view->specName = $document->getSpec_name();
    }


}





