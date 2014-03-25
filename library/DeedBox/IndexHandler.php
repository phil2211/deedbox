<?php

/**
 * Kommuniziert mit dem Index für Suche und Indexierung
 *
 * @category
 * @package
 * @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
 * @license    GPL
 * @since      Klasse vorhanden seit Release 0.1
 */

class DeedBox_IndexHandler {

    /**
     * Beinhaltet den Pfad zu den Index Files. Diese müssen zwingend
     * auf dem lokalen Filesystem verfügbar sein da sonst Lockingprobleme
     * entstehen
     *
     * @var string
     */
    private $_indexFile;

    /**
     * Enthält das Zend_Search_Lucene Index Objekt
     *
     * @var Object
     */
    private $_zendIndex;

    /**
     * Enthält eine Query für das Abfragen des Index
     *
     * @var string
     */
    private $_query = NULL;

    /**
     * Enthält ein Arry mit den Treffern aus einer Index-Suche
     *
     * @var array
     */
    private $_hits = NULL;

    /**
     * Klassenkonstruktor
     *
     * Öffnet einen bestehenden Index oder legt einen neuen an falls keiner
     * gefunden wurde
     *
     * @param string $indexFile Pfad zu dem Index.
     */
    public function __construct($indexFile) {
        $this->_indexFile = $indexFile;

        try {
            //Versucht einen bestehenden Index zu öffnen
            $this->_zendIndex = Zend_Search_Lucene::open($this->_indexFile);
        } catch (Exception $exc) {
            if ($exc->getCode() == 0) {
                //Legt einen neuen Index an, falls das Öffnen fehlgeschlagen ist
                $this->_zendIndex = Zend_Search_Lucene::create($this->_indexFile);
            }
        }
    }

    /**
     * Fügt ein Dokument zum Index hinzu
     *
     * @param array $data (assoziatives Array mit allen Informationen zum Dokument)
     * @return \DeedBox_IndexHandler
     */
    public function add($data = array())
    {
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::keyword('document_id', $data['id']));
        $doc->addField(Zend_Search_Lucene_Field::keyword('spec_id', $data['fk_doc_spec']));
        $doc->addField(Zend_Search_Lucene_Field::keyword('group_id', $data['fk_doc_group']));
        $doc->addField(Zend_Search_Lucene_Field::keyword('date', date('Ymd', strtotime($data['document_date']))));
        $doc->addField(Zend_Search_Lucene_Field::unStored('content', $data['content'], 'utf-8'));
        $this->_zendIndex->addDocument($doc);
        $this->_zendIndex->commit();
        return $this;
    }

    /**
     * Gibt die gefundenen Suchresultate zurück
     *
     * @return type
     */
    public function getSearchResult()
    {
        return $this->_hits;
    }

    /**
     * Führt eine Suche über den Index durch
     *
     * @return \DeedBox_IndexHandler
     */
    public function query()
    {
        $this->_hits = $this->_zendIndex->find($this->_query);
        return $this;
    }

    /**
     * Fügt eine Suchanfrage anhand eines Suchtextes hinzu
     *
     * @param string $searchText (Lucene Search Query Langage)
     * @return \DeedBox_IndexHandler
     */
    public function addSearchText($searchText)
    {
        //parst die Lucene Query und erzeugt ein Subquery objekt
        $subquery = Zend_Search_Lucene_Search_QueryParser::parse($searchText);

        //Fügt die Query als Subquery der Gesamtsuche hinzu
        if (!$this->_query) {
            $this->_query = new Zend_Search_Lucene_Search_Query_Boolean();
        }
        $this->_query->addSubquery($subquery, true);
        return $this;
    }

    /**
     * Fügt einen Suchbegriff für ein bestimmtes Schlüsselwort hinzu.
     *
     * @param array $search Assoziatives Array bestehend aus Keyword=>Value
     * @return \DeedBox_IndexHandler
     * @throws Exception Wenn kein Array übergeben wurde
     */
    public function addSearchTerm($searchTerms)
    {
        if (!is_array($searchTerms)) {
            throw new Exception('Search Terms muessen alls Array uebergeben werden');
        }

        $subquery = new Zend_Search_Lucene_Search_Query_MultiTerm();

        if (!$this->_query) {
            $this->_query = new Zend_Search_Lucene_Search_Query_Boolean();
        }

        foreach ($searchTerms as $key=>$searchTerm) {
            $subquery->addTerm(new Zend_Search_Lucene_Index_Term($searchTerm, $key));
        }
        $this->_query->addSubquery($subquery, true);
        return $this;
    }

    /**
     * Fügt eine Suche über eine Zahlenspanne hinzu. Dies wird benötigt um
     * beispielsweise die Datumsspanne von/bis einzugrenzen
     *
     * @param int $from (Bei Datum Bsp: 20120112)
     * @param int $to (Bei Datum Bsp: 20120131)
     * @param string $indexField Feldname auf das sich die Range bezieht
     * @return \DeedBox_IndexHandler
     */
    public function addRangeTerm($from, $to, $indexField)
    {
        if (!$this->_query) {
            $this->_query = new Zend_Search_Lucene_Search_Query_Boolean();
        }

        $qfrom = new Zend_Search_Lucene_Index_Term($from, $indexField);
        $qto = new Zend_Search_Lucene_Index_Term($to, $indexField);
        $subquery = new Zend_Search_Lucene_Search_Query_Range($qfrom, $qto, true);
        $this->_query->addSubquery($subquery, true);
        return $this;
    }

    /**
     * Entfernt einen Eintrag aus dem Index
     *
     * @param int $docId Die ID des zu entfernenden Dokuments
     * @return \DeedBox_IndexHandler
     */
    public function remove($docId)
    {
        $delPath = 'document_id:"' . $docId .'"';
        $hits = $this->_zendIndex->find('path:' . $delPath);
        foreach ($hits as $hit) {
            $this->_zendIndex->delete($hit->id);
        }
        return $this;
    }

    /**
     * Aktualisiert einen Eintrag im Index in dem es ihn entfernt und neu anlegt
     *
     * @param array $data Alle Informationen über ein Dokument
     * @return \DeedBox_IndexHandler
     */
    public function update($data)
    {
        $this->remove($data['id']);
        $this->add($data);
        return $this;
    }
    
}
