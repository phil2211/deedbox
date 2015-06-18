<?php

/**
 * Verarbeitet die Hintergrundprozesse der DeedBox
 *
 * @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
 * @license    GPL
 * @since      Klasse vorhanden seit Release 0.1
 */

class DeedBox_JobProcessor
{

    /**
     * Fragt die nächsten Messages aus der Queue ab und
     * führt für jedes Dokument einen Analysevorgang durh
     *
     * @throws Exception
     */
    public function handleDocuments()
    {
        //Holt sich die Instanz der Queue aus der Zend_Registry
        $queue = Zend_Registry::get('queue');
        //Fragt die nächsten 10 Nachrichten in der Queue ab
        $messages = $queue->receive(20);

        //Loop über jede gefundenen Message
        foreach($messages as $i => $message) {

            //liest die Message im JSON Format ein und zerlegt diese in ihre Bestandteile
            $arrMessage = json_decode($message->body, TRUE);

            if (!isset($arrMessage['userId'])) {
                continue;
            }

            Application_Model_User::logout();
            Application_Model_User::initUserById($arrMessage['userId']);

            echo "[".($i+1)." / ".$messages->count()."] Working on Message #" . $message->message_id . " with body: " . $message->body ."\n";

            $filePath = $arrMessage['file'];

            // file objekt erstellen
            $file = new Application_Model_File($arrMessage['file'], $arrMessage['storagePath']);
            $file->setStorage(Application_Model_User::getStorage());

            $isAnalyzable = true;
            
            // gibt es dieses file eventuell schon in der db zum user?
            $docMapper = new Application_Model_DocumentMapper();
            $docByPath = $docMapper->getByStoragePath($arrMessage['storagePath']);

            if ($docByPath[0] instanceof Application_Model_Document && $docByPath[0]->getId() > 0) {
                // haben wir schon -> nicht nochmal verarbeiten!
                $isAnalyzable = false;
            }

            //Text aus dem PDF extrahieren
            $pdf2Text = new DeedBox_Pdf2Text($filePath);
            if (Zend_Registry::get('config')->get('pdftotext')) {
                $pdf2Text->setConverterTool(Zend_Registry::get('config')->get('pdftotext'));
            }

            $pdfText = $pdf2Text->getPdfText(TRUE);
            
            if (strlen($pdfText) < 10 && $isAnalyzable) {
                // file ist nicht analysierbar -> failen!
                $file->fail();
                
                $isAnalyzable = false;
            }

            if ($isAnalyzable) {
                //Benutzt den ImageHandler um Preview und Thumbnail des Dokuments zu erstellen
                $imageHandler = new DeedBox_ImageHandler();
                $imageHandler->setFileName($filePath)
                                ->createPreview();
                $preview = $imageHandler->getImage();

                $imageHandler = new DeedBox_ImageHandler();
                $imageHandler->setFileName($filePath)
                                ->createThumbnail();
                $thumbnail = $imageHandler->getImage();

                //Speichert das Dokument in der DB
                $document = new Application_Model_Document(array(
                    'user_id'           => $arrMessage['userId'],
                    'file_md5'          => $file->getMd5Sum(),
                    'path'              => $file->getStoragePath(),
                    'file_name'         => '',
                    'original_filename' => $file->getDisplayName(),
                    'thumbnail'         => $thumbnail,
                    'preview'           => $preview,
                    'content'           => $pdfText
                ));

                $docMapper = new Application_Model_DocumentMapper();
                $docMapper->save($document);

                //Startet den Analysevorgang für die Gruppen-/Klassenzuteilung für dieses Dokument
                $this->analyzeDocuments($document->getId());
            }

            $queue->deleteMessage($message);

            // file bei uns löschen!
            $file->unlink();
        }
    }


    /**
     * Analysiert die noch nicht zugeteilten Dokumente anhand der Gruppen-
     * und Klassifizierungsmerkmale. Wenn eine DocId uebergeben wird, wird nur
     * dieses Dokument analysiert
     *
     * @param int $documentId Id eines Dokuments. In diesem Fall wird nur dieses analysiert
     * @return array Array mit allen Gruppen- und Klassengewinnern
     */
    public function analyzeDocuments($documentId)
    {

        //Datenbankzugriff initialisieren
        $docGroupMapper = new Application_Model_DocgroupMapper();
        $docGroup = new Application_Model_Docgroup();
        $groupRowset = $docGroupMapper->fetchAll();

        $document = new Application_Model_Document();
        $documentMapper = new Application_Model_DocumentMapper();

        $docSpecMapper = new Application_Model_DocspecMapper();
        $docSpec = new Application_Model_Docspec();
        $docSpecsRowset = $docSpecMapper->fetchAll();

        $documentMapper->find($documentId, $document);
        $docRowset[] = $document;

        //Die DocumentAnalyzer Klasse wird instanziert
        $analyzer = new DeedBox_DocumentAnalyzer();
        $analyzer->setSiftMatch(Zend_Registry::get('config')->get('siftmatch'));

        //Die ImageHandler Klasse wird instanziert
        $imageHandler = new DeedBox_ImageHandler();
        $imageHandler->setSiftTool(Zend_Registry::get('config')->get('siftfeat'));

        //alle SIFT-Indices der Logos dem Analyzer uebergeben
        foreach ($groupRowset as $group) {
            $analyzer->addSubimageSifts(array('key'     =>  $group->getId(),
                                              'value'   =>  $group->getSift_index(),
                                              'name'    =>  $group->getName()));
        }

        //alle Dokument-Klassifizierungsmerkmale dem Analyzer uebergeben

        // dieser loop geht über die specs (specs = document types! [rechnung, usw])
        foreach ($docSpecsRowset as $spec) {
            /** das hier sind die regexes aus der db **/
            $analyzer->addDocumentSpecs(array('key'             =>  $spec->getId(),
                                              'name'            =>  $spec->getName(),
                                              'recogFeatures'   =>  $spec->getRecog_features()));

            /** das sind die vorher richtig klassifizierten (100%ig) dokumente zu diesem doc type! **/
            /** -> anhand dem content von denen kann man via similartext das matchen **/
            $specSamples = $documentMapper->getSpecSamples($spec->getId());
            foreach ($specSamples as $sample) {
                $analyzer->addDocspecSample(array($spec->getId()=>$sample->getContent()));
            }
        }

        //initialisieren des Arrays für die Gruppen-/Klassengewinner
        $winners = array();
        //ueber alle Dokumente Loopen und Gruppen-/Klassenerkennung ausführen
        foreach ($docRowset as $doc) {

            //Den SIFT-Index des Dokumentes berechnen
            $imageHandler->setImage($doc->preview)
                         ->createSiftIndex();

            //Dem Analyzer werden jetzt der SIFT Index des Dokuments und der
            //Text des Dokumentes zur Analyse uebergeben
            $analyzer->setDocumentSift($imageHandler->getSiftFeat())
                     ->analyzeDocumentGroup()
                     ->setDocumentText($doc->content)
                     ->alanyzeDocumentSpec()
                     ->findDate();
                     //->findIBAN();

            //Den Gruppen und Klassengewinner vom Analyzer holen
            $groupWinner = $analyzer->getGroupWinner();
            $specWinner = $analyzer->getSpecWinner();

            //Die Gewinner in der Datenbank speichern und in einem Array merken
            if ($specWinner !== NULL || $groupWinner !== NULL) {
                $documentMapper->find($doc->id, $document);
                if ($groupWinner !== NULL) {
                    $document->setFk_doc_group(key($groupWinner));
                    $document->setGroup_accuracy(end($groupWinner));
                    $winners[$doc->id]['group'] = key($groupWinner);
                }

                if ($specWinner !== NULL) {
                    $document->setFk_doc_spec(key($specWinner));
                    $document->setSpec_accuracy(end($specWinner));
                    $winners[$doc->id]['spec'] = key($specWinner);
                }
            }

            //Wenn SPEC und GROUP zugeteilt werden koennen, Dokument Taggen und
            //Datei in den korrekten Ordner verschieben
            if ($specWinner !== NULL && $groupWinner !== NULL) {

                // file umbenennen!
                $oldPath = $document->getPath();

                $docGroupMapper->find(key($groupWinner), $docGroup);
                $docSpecMapper->find(key($specWinner), $docSpec);

                $ext = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));

                $newName = date('Ymd', strtotime($analyzer->getDocumentDate())).' - '.
                        $docGroup->getName().'.'.$ext;

                $newPath = '/'.$docSpec->getCode() . '_' . $docSpec->getName() . '/' . $docGroup->getShort_name() . '/' .$newName;

                // umbenennen!
                if (Application_Model_User::getStorage()->renameFile($oldPath, $newPath, $document->getFile_md5())) {
                    $document->setPath($newPath);
                }
            }

            //IBAN und Datum ablegen
            $document->setDocument_date($analyzer->getDocumentDate())
                     ->setDocument_IBAN($analyzer->getIban())
                     ->setDocument_stats(json_encode($analyzer->getDocumentStats()));

            $documentMapper->save($document);
        }

        return $winners;
    }

    /**
     * Diese Methode wurde versuchsweise eingebaut um sämtliche Logos
     * von der Internetseite brandsoftheworld.com abzurufen. Sie wird daher nicht
     * mehr weiter kommentiert
     *
     */
    public function getBrands()
    {
        $client = new Zend_Http_Client();
        $client->setHeaders(array(
        'Host' => 'www.example.com',
        'Accept-encoding' => 'deflate',
        'X-Powered-By' => 'Zend Framework'));


        for($i=9;$i<19;$i++) {

            $client->setUri('http://www.brandsoftheworld.com/logos/countries/ch?page=' . $i);

            echo $client->getUri() . "\n";

            $response = $client->request();

            preg_match_all('/(src=")(http:\/\/www\.example\.com\/sites\/default\/files\/styles\/logo-thumbnail\/public\/.*\/.*)(\.png|\.gif)(".*)(alt="Logo of )(.*)(")/', $response->getRawBody(), $matches);

            $logos = array();

            foreach($matches[2] as $key=>$match) {
                $logos[$key]['url'] = $match;
            }

            foreach($matches[3] as $key=>$match) {
                $logos[$key]['ending'] = $match;
            }

            foreach($matches[6] as $key=>$match) {
                $logos[$key]['name'] = $match;
            }

            $docGroupMapper = new Application_Model_DocgroupMapper();

            foreach($logos as $logo) {
                $docGroup = new Application_Model_Docgroup();
                $imageHandler = new DeedBox_ImageHandler();
                $client->setUri(str_replace('www.example.com', 'www.brandsoftheworld.com', $logo['url']).$logo['ending']);

                $logoImg = $client->request();

                try {
                $imageHandler->setSiftTool(Zend_Registry::get('config')->get('siftfeat'))
                            ->setImage($logoImg->getRawBody())
                            ->convertToPng()
                            ->createSiftIndex();
                } catch (Exception $exc) {
                    echo $exc->getTraceAsString();
                    continue;
                }

                $docGroup->setName($logo['name']);
                $docGroup->setRecognition_feature($logoImg->getRawBody());
                $docGroup->setSift_index($imageHandler->getSiftFeat());
                $docGroupMapper->save($docGroup);
                echo $logo['name'] . "\n";
            }
        }
    }
}
