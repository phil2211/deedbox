<?php
/**
* Analysiert Dokumente nach verschiedenen Kriterien
*
* @category   Library
* @package    DeedBox
* @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
* @license    All Rights Reserved
* @version    Release: @package_version@
* @since      Klasse vorhanden seit Release 0.1
* @deprecated
*/
define('DEBUG_LEVEL', Zend_Registry::get('config')->get('debuglevel'));


class DeedBox_DocumentAnalyzer {

    /**
     * Enthaelt die SIFT Merkmale des zu analysierenden Dokuments
     *
     * @var string
     */
    private $_documentSift = NULL;

    /**
     * Array enthaelt alle SIFT Merkmale der Dokument Gruppen
     *
     * @var Array
     */
    private $_subimageSifts = array();


    /**
     * Enthaelt den Text des Dokuments zur Analyse
     *
     * @var string
     */
    private $_documentText = NULL;

    /**
     * Enthaelt alle regulaeren Ausdrücke um das Dokument nach
     * Textmerkmalen zu untersuchen und zu klassifizieren
     *
     * @var Array
     */
    private $_documentSpecs = array();

    /**
     * Enhaelt den Pfad und Dateinamen des SIFT-Match Binaries
     *
     * @var string
     */
    private $_siftmatch = '/usr/local/bin/siftmatch';

    /**
     * Enthaelt alle Dokumentgruppen mit der entsprechenden Uebereinstimmung
     * mit den Subimages
     *
     * @var Array
     */
    private $_groupResults = array();

    /**
     * Enthaelt den Gewinner der Gruppenanalyse sowie den Grad der
     * Uebereinstimmung
     *
     * @var Array
     */
    private $_groupWinner = array();

    /**
     * Enthaelt den Gewinner der Textanalyse zur Klassifizierung des Dokuments
     * sowie den Grad der Uebereinstimmung
     *
     * @var Array
     */
    private $_specWinner = array();

    /**
     * Enthaelt das Datum des Dokuments oder das aktuelle Tagesdatum falls
     * kein Datum im Dokument identifiziert werden konnte
     *
     * @var integer (UNIX Timestamp)
     */
    private $_documentDate = NULL;

    /**
     * Enthaelt eine IBAN Nummer falls im Dokument eine gefunden wird
     *
     * @var string
     */
    private $_iban = NULL;


    /**
     * Enthaelt die statistischen Informationen ueber das Dokument
     *
     * @var Array
     */
    private $_documentStats = Array();

    /**
     * Hält Beispiele von allen Dokumentklassen zum inhaltlichen Vergleich
     *
     * @var Array
     */
    private $_docspecSamples = Array();

    /**
     * Hält für jede Dokumentenklasse einen Zähler mit der Anzahl der Muster
     *
     * @var Array
     */
    private $_docspecSampleCount = Array();



    /**
     * Fügt ein Klassifikationsbeispiel hinzu
     *
     * @param array $samples SpecName=>Content
     * @return \DeedBox_DocumentAnalyzer
     */
    public function addDocspecSample($sample)
    {
        if (!array_key_exists(key($sample), $this->_docspecSampleCount)) {
            $this->_docspecSampleCount[key($sample)] = 1;
        } else {
            $this->_docspecSampleCount[key($sample)] += 1;
        }

        $this->_docspecSamples[] = $sample;
        return $this;
    }


    /**
     * Gibt den Gewinner der Gruppenzuteilung sowie den Grad der Uebereinstimmung
     * zurueck
     *
     * @return Array() WinnerId=>Accuracy
     */
    public function getGroupWinner()
    {
        return $this->_groupWinner;
    }


    /**
     * Gibt ein Array mit allen Gruppenzuweisungen inkl. deren
     * Uebereinstimmungsgrad zurueck
     *
     * @return Array
     */
    public function getGroupResults()
    {
        return $this->_groupResults;
    }


    /**
     * Gibt den Gewinner der Klassenzuteilung sowie den Grad der Uebereinstimmung
     * zurueck
     *
     * @return Array() WinnerId=>Accuracy
     */
    public function getSpecWinner()
    {
        return $this->_specWinner;
    }


    /**
     * Gibt die IBAN Nummer zurueck falls eine im Dokument enthalten ist
     *
     * @return string
     */
    public function getIban()
    {
        return $this->_iban;
    }


    /**
     * Gibt das Datum des Dokuments zurueck falls dieses ermittelt werden kann
     * Ansonsten wird das Tagesdatum zurueckgegeben
     *
     * @return Integer (UNIX Timestamp)
     */
    public function getDocumentDate()
    {
        return $this->_documentDate;
    }


    /**
     * Gibt ein Array mit Statistischen Informationen ueber den Analysevorgang
     * zurueck
     *
     * @return Array
     */
    public function getDocumentStats()
    {
        return $this->_documentStats;
    }


    /**
     * Fuegt ein SIFT Merkmal einer Dokumentengruppe hinzu
     *
     * @param Array $array (key=>GruppenID, value=>SIFT-Merkmal)
     * @throws Exception
     */
    public function addSubimageSifts($array)
    {
        if (!array_key_exists('key', $array))
        {
            throw new Exception('Kein Key fuer Subimage vorhanden');
        }

        $this->_subimageSifts[] = $array;
    }


    /**
     * Fuegt ein Array mit Mustern (RegEx) zu Erkennung einer Dokumentenklasse hinzu
     *
     * @param Array $array (key=>SpecId, recogFeatures=>Array())
     * @return \DeedBox_DocumentAnalyzer
     * @throws Exception Falls das Format der Input-Array fehlerhaft ist
     */
    public function addDocumentSpecs($array)
    {
        if (!array_key_exists('key', $array)) {
            throw new Exception('Kein Key fuer DocSpec vorhanden');
        }
        $this->_documentSpecs[] = $array;
        return $this;
    }


    /**
     * Setzt den Text des Dokuments
     *
     * @param type $string
     * @return \DeedBox_DocumentAnalyzer
     */
    public function setDocumentText($string)
    {
        $this->_documentText = $string;
        return $this;
    }


    /**
     * Setzt den SIFT-Index der ersten Seite des Dokuments zum Vergleichen
     *
     * @param string $string SIFT Index des Dokuments
     * @return \DeedBox_DocumentAnalyzer
     */
    public function setDocumentSift($string)
    {
        $this->_documentSift = $string;
        return $this;
    }


    /**
     * Pfad- und Dateiname zum SIFT-Match Tool Binaries
     *
     * @param string $string
     * @return \DeedBox_DocumentAnalyzer
     */
    public function setSiftMatch($string)
    {
        $this->_siftmatch = $string;
        return $this;
    }


    /**
     * Analysiert das Dokument aufgrund von allen gegebenen Gruppen und
     * ermittelt die Gruppe welche statistisch gesehen am besten mit dem
     * Dokument uebereinstimmt
     *
     * @return \DeedBox_DocumentAnalyzer
     */
    public function analyzeDocumentGroup()
    {
        if (DEBUG_LEVEL > 0) {
            echo "
****************************\n
PROCESSING DOCUMENT GROUPING\n
****************************\n\n";
        }

        $this->_groupResults = array();

        //den SIFT Index des zu durchsuchenden Dokumentes erstellen und in
        //einem temporaeren File zwischenspeichern
        $tmpDocumentFile = tempnam(APPLICATION_PATH . '/tmp', 'deedbox_document');
        file_put_contents($tmpDocumentFile, $this->_documentSift);

        //Loop ueber alle bekannten SIFT Indices um den am besten passenden SIFT Index zu finden
        foreach ($this->_subimageSifts as $subimageSift) {
            $matches = array();

            //Den SIFT-Index des Subimages in eine Datei schreiben um ihn
            //an das Externe Tool SIFTMATCH zu uebergeben
            //SIFTMATCH gibt die Anzahl gefundener Uebereinstimmungen zurueck
            $tmpSubimageFile = tempnam(APPLICATION_PATH . '/tmp', 'deedbox_subimage');
            file_put_contents($tmpSubimageFile, $subimageSift['value']);

            //Die Anzahl der gefunden SIFT-Features aus dem Subimage extrahieren
            //Dies ist immer der erste Zahlenblock im SIFT-Index. Dies ist die
            //Anzahl der SIFT-Features welche im Dokument gefunden werden koennen
            //Je komplexer das optische Erkennungsmerkmal, desto hoeher ist dieser
            //Wert
            preg_match('/^(\d+)/', $subimageSift['value'], $matches);
            $subimageFeatCount = $matches[1];

            //SIFTMATCH ausfuehren und den Rueckgabewert im Arry $returnValues speichern
            $returnValues = array();
            exec($this->_siftmatch . ' ' . $tmpSubimageFile . ' ' . $tmpDocumentFile , $returnValues);

            //Das temporaere Subimage SIFT-File wird geloescht
            unlink($tmpSubimageFile);

            //Der Faktor der Übereinstimmung wird berechnet. Dabei wird angenommen, dass
            //100% Übereinstimmung herrscht, wenn 100% der möglichen SIFT-Features
            //in einem Subimage in dem Dokument gefunden werden.
            //
            //dies ergibt folgende Formel:
            //100 / Max mögliche SIFT-Features * gefundene SIFT-Features
            $subimageMatch = number_format(100/$subimageFeatCount*$returnValues[0],2);

            //Bei einfachen Logoformen werden diese in einem Dokument überproportional
            //oft wiederegefunden. Dies bedeutet, dass es keine Übereinstimmung gibt sobald
            //mehr als 2fache der möglichen Features gefunden werden. Dieser Wert hat sich
            //in Praxistests als vernünftig erwiesen. Falls weniger als 10% der möglichen
            //Features gefunden werden, hat sich in der Praxis auch bewährt, den Matchwert
            //zu ignorieren.
            if ($subimageMatch > 200 || $subimageMatch < 10) {
                $subimageMatch = 0;
            }

            $this->_documentStats['subimage'][$subimageSift['name']] = $subimageMatch;

            //Falls das Debugging eingeschaltet ist, weren die Schluesselinformationen ausgegeben.
            if (DEBUG_LEVEL == 1) {
                echo "PROCESSING SUBIMAGE: " . $subimageSift['key']  . ' : ' .
                                               $subimageSift['name'] . "\n";
                echo "MAX SIFT FEATURES: " .$subimageFeatCount . "\n";
                echo "SIFTMATCH: $subimageMatch%\n\n";
            }

            //Die Treffer der SIFT-Features werden hier in das Verhaeltnis zu den
            //maximal moeglichen Treffern pro Subimage gestellt.
            $this->_groupResults[$subimageSift['key']] = $subimageMatch;

        }
        //Das temporaere Document-SIFT-File wird geloescht
        unlink($tmpDocumentFile);

        //Trefferwahrscheinlichkeit berechnen (Details siehe die Methode calcAccuracy)
        // sind es weniger 5, gehen wir weiter wenn es etwas >60 gibt
        $accuracy = $this->calcAccuracy(array_values($this->_groupResults), 'groupstats');

        //Setzen des Gewinners sowie der Uebereinstimmungsquote. Es wird
        //immer der hoechste Trefferwert als Gewinner angenommen
        if ($accuracy == 0) {
            $this->_groupWinner = NULL;
        } else {
            asort($this->_groupResults, SORT_NUMERIC);
            end($this->_groupResults);
            $this->_groupWinner = array(key($this->_groupResults) => $accuracy);
        }

        //Debuginfos ausgeben falls Debuggin eingeschaltet
        if(DEBUG_LEVEL > 0) {
            echo "ACCURACY: " . $accuracy . "%\n";
            if (!is_array($this->_groupWinner))
                echo "NO WINNER\n\n";
            else
                echo "WINNER: " . key($this->_groupWinner) ."\n\n";
        }

        return $this;
    }


   /**
    * Diese Funktion berechnet die Trefferwahrscheinlichkeit aufgrund des
    * Interquantilabstandes. Es wird der Interquantileabstand mit dem Abstand
    * zwischen dem Maximalwert (wahrscheinlicher Treffer) und dem upperQuantile
    * prozentual verglichen
    *
    * @param array $matches Array mit allen Matchzaehlern
    * @return float Quotient der uebereinstimmung
    */
    private function calcAccuracy($matches, $groupSpec=NULL)
    {
        // für den boxplot werden mindestens 5 werte benötigt..
        // haben wir weniger als 5 geben wir trotzdem "ok" zurück, falls wir
        // einen treffer >60 haben!
        if (count($matches) < 5) {
            $ret = 0;
            foreach ($matches as $match) {
                if ($match > 60) $ret = 100;
            }

            return $ret;
        }

        //Das R-Script outliers.r gibt die statistischen Informationen eines
        //Boxplot aus. Diese weisen alle Ausreisser in einem Array aus. Details
        //siehe R-Script outliers.r
        $returnValues = array();
        $outliers = array();
        exec(APPLICATION_PATH . '/../bin/outliers.r ' . implode(' ', $matches), $returnValues);
        if (count($returnValues) > 0)
            $outliers = explode(',', $returnValues[0]);

        foreach ($outliers as $key=>$value) {
            //Ausreisser unter dem Durchschnitt eliminieren (negative Ausreisser)
            if ($value < array_sum($matches)/count($matches))
                unset($outliers[$key]);
        }

        //Falls der optionale Parameter $groupSpec gesetzt ist, wird mit R
        //ein Boxplot als Grafik genieriert und den documentStats uebergeben
        if($groupSpec) {
            $tempfile = tempnam('/tmp', 'deedbox_graph');
            exec(APPLICATION_PATH .
                 '/../bin/outliers_graph.r ' .
                 $tempfile . ' ' .
                 $groupSpec . ' ' .
                 implode(' ', $matches), $returnValues);
            $this->_documentStats['boxplot'][$groupSpec] = base64_encode(file_get_contents($tempfile));
            unlink($tempfile);
        }

        //Wenn das Debugging eingeschaltet ist, werden die Werte ausgegeben.
        if (DEBUG_LEVEL > 0) {
            if (is_array($outliers))
                echo "OUTLIERS: " . implode(',', $outliers) ."\n";
            else
                echo "NO OUTLIERS\n";
            echo "R-OUTPUT: \n" . implode(',', $matches) . "\n";
        }

        //Die Summe der Ausreisser (Summe der Prozentpunkte) geteilt durch
        //die Anzahl ergibt einen Indikator wie zuverlaessig der Treffer ist.
        //Gibt es keinen Ausresisser wird 0 zurueckgegeben.
        //Somit laesst sich das Dokument keinem der gegebenen
        //Gruppen-/Klassenmerkmale zuweisen.
        if (count($outliers) == 0) {
            return 0;
        } elseif (count($outliers) == 1) {
            return 100;
        } else {
            return array_sum($outliers)/count($outliers);
        }
    }


    /**
     * Analysiert den Dokumententext anhand von gegebenen regulaeren Ausdruecken
     * und weist das Dokument aufgrund der Resultate einer Dokumentenklasse
     * zu
     *
     * @return \DeedBox_DocumentAnalyzer
     */
    public function alanyzeDocumentSpec()
    {
        if (DEBUG_LEVEL > 0) {
            echo "
*******************************\n
PROCESSING DOCUMENT CLASSIFYING\n
*******************************\n\n";
        }

        //Jede Dokumentenklasse kann aus 1 oder mehreren Mustern (RegEx) bestehen
        //gegen die der Dokumententext geprueft wird. Diese Muster werden
        //hier durchlaufen und die Anzahl der Treffer festgehalten
        $matches = array();
        foreach ($this->_documentSpecs as $docSpec) {
            if (count($this->_docspecSamples) > 10) {
                $type = 'specstats_sim';
                //Auswertungszähler initialisieren
                $percent = 0;
                $percents = 0;
                foreach ($this->_docspecSamples as $sample) {
                    if (key($sample) == $docSpec['key']) {
                        //entfernen aller mehrfachen whitespaces, tabulatoren und newlines
                        $sampleString = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', end($sample));
                        $docContent = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $this->_documentText);
                        //Ähnlichkeit der ersten 5000 Zeichen der Texte mittels der PHP Funktion similar_text ermitteln
                        similar_text(substr($sampleString, 0, 5000), substr($docContent, 0, 5000), $percent);
                        $percents += $percent;
                    }
                }
                if ($percents > 0)
                    //Es wird der Durchschnittswert aller Textsamples zu einer Dokumentkategorie ermittelt
                    $matches[$docSpec['key']] = number_format($percents/$this->_docspecSampleCount[$docSpec['key']], 2);
                else
                    $matches[$docSpec['key']] = number_format(0, 2);
            } else {
                $type = 'specstats_rgx';
                if (count($docSpec['recogFeatures']) > 0) {
                    $specCount  = 0;
                    $matchCount = 0;
                    foreach ($docSpec['recogFeatures'] as $feat) {
                        if (DEBUG_LEVEL > 0) {
                            echo "Query: '" . $feat->query . "' --> ";
                            echo preg_match($feat->query, $this->_documentText) . "\n";
                        }
                        $specCount++;
                        $matchCount += preg_match($feat->query, $this->_documentText);
                    }

                    $matches[$docSpec['key']] = number_format(100/$specCount*$matchCount, 2);
                } else {
                    //Die Anzahl Treffer wird in einem Array festgehalten
                    $matches[$docSpec['key']] = number_format(0, 2);
                }
            }

            if (DEBUG_LEVEL > 0) {
                echo "PROCESSING DOCSPEC: " . $docSpec['key'] . ' : '
                                            . $docSpec['name'] . "\n";
                echo "CALCULATED WITH ";
                echo $type=='specstats_rgx'?$specCount:$this->_docspecSampleCount[$docSpec['key']] . " SAMPLES\n";
                echo "MATCH: " . $matches[$docSpec['key']] . "%\n\n";
            }

            //statistische Daten zum Nachvollziehen speichern
            $this->_documentStats['specs'][$docSpec['name']] = $matches[$docSpec['key']];
        }

        //Trefferwahrscheinlichkeit berechnen
        $accuracy = $this->calcAccuracy(array_values($matches), $type);

        //Setzen des Gewinners sowie der Uebereinstimmungsquote. Es wird
        //immer der hoechste Trefferwert als Gewinner angenommen
        if ($accuracy == 0) {
            $this->_specWinner = NULL;
        } else {
            asort($matches, SORT_NUMERIC);
            end($matches);
            $this->_specWinner = array(key($matches) => $accuracy);
        }

        //Debuginfos ausgeben
        if(DEBUG_LEVEL > 0) {
            echo "ACCURACY: " . $accuracy . "%\n";
            if (!is_array($this->_specWinner))
                echo "NO WINNER\n\n";
            else
                echo "WINNER: " . key($this->_specWinner) ."\n\n";
        }
        return $this;
    }


    /**
     * Ermittelt anhand von diversen Mustern das Datum des Dokumentes. Die Muster
     * werden in der untenstehenden Reihenfolge abgearbeitet. Der erste Treffer
     * wird als Dokumentdatum angenommen. Wird kein Datum im Dokument gefunden,
     * wird das aktuelle Tagesdatum gespeichert. Das Datum wird als UNIX Timestamp
     * gespeichert.
     *
     * @return \DeedBox_DocumentAnalyzer
     */
    public function findDate()
    {
        $matches = array();
        $translate = array('Januar'=>'01.', 'Februar'=>'02.','März'=>'03.','April'=>'04.','Mai'=>'05.','Juni'=>'06.','Juli'=>'07.','August'=>'08.','September'=>'09.','Oktober'=>'10.','November'=>'11.','Dezember'=>'12.');

        //Findet Muster wie Bern, 20.12.2012
        if (preg_match("/([a-z]*,)([\\n ])([0-3][0-9][\\.-][01][0-9][\\.-][12]?[0-9]?[0-9]{2})([ \\n])/uis", $this->_documentText, $matches) > 0) {
            if (DEBUG_LEVEL == 1)
                echo "DATUM: $matches[3]\n";
            $this->_documentDate = date('Y-m-d H:i:s', strtotime($matches[3]));
            return $this;
        }

        //Findet Muster wie Luzern, 30. Juli 2012
        if (preg_match("/([a-z]*, )([0-3][0-9][\\.,-] (Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember) [12]?[0-9]?[0-9]{2})/uis", $this->_documentText, $matches) > 0) {
            $matches[2] = strtr($matches[2],',','.');
            $matches[2] = strtr($matches[2], $translate);
            $matches[2] = str_replace(' ', '', $matches[2]);
            if (DEBUG_LEVEL == 1)
                echo "DATUM: $matches[2]\n";
            $this->_documentDate = date('Y-m-d H:i:s', strtotime($matches[2]));
            return $this;
        }

        //Findet Muster wie ***datum: 01.01.2012
        if (preg_match("/(datum: *)([\\n ])([0-3][0-9][\\.,-] ?[01][0-9][\\.,-]([12][0-9][0-9]{2}|[1-9][0-9]))([ \\n])/uis", $this->_documentText, $matches) >0) {
            $matches[3] = strtr($matches[3],',','.');
            if (DEBUG_LEVEL == 1)
                echo "DATUM: $matches[3]\n";
            $this->_documentDate = date('Y-m-d H:i:s', strtotime($matches[3]));
            return $this;
        }

                //Findet Muster wie ***datum: 30. Juli 2012
        if (preg_match("/(datum: *)([\\n ])([0-3][0-9][\\.,-] (Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember) [12]?[0-9]?[0-9]{2})/uis", $this->_documentText, $matches) > 0) {
            $matches[3] = strtr($matches[3],',','.');
            $matches[3] = strtr($matches[3], $translate);
            $matches[3] = str_replace(' ', '', $matches[3]);
            if (DEBUG_LEVEL == 1)
                echo "DATUM: $matches[3]\n";
            $this->_documentDate = date('Y-m-d H:i:s', strtotime($matches[3]));
            return $this;
        }


        //Findet generelles Datumsmuster
        if (preg_match("/([ \\n])([0-3][0-9][\\.,-][0-1][0-9][\\.,-][12]?[0-9]?[0-9]{2})([ \\n])/ui", $this->_documentText, $matches) > 0) {
            $matches[2] = strtr($matches[2],',','.');
            if (DEBUG_LEVEL == 1)
                echo "DATUM: $matches[2]\n";
            $this->_documentDate = date('Y-m-d H:i:s', strtotime(trim($matches[2])));
            return $this;
        }

        //Falls kein Datum gefunden wurde, wird das aktuelle Datum gesetzt
        $this->_documentDate = date('Y-m-d H:i:s', time());
        if (DEBUG_LEVEL == 1)
            echo "DATUM: " . date('d.m.Y') . "\n";
        return $this;
    }


    /**
     * Findet eine IBAN-Nummer in einem Dokument und legt diese in der Klassenvariablen
     * _iban ab.
     */
    public function findIBAN()
    {
        $matches = array();
        if(preg_match("/[a-zA-Z]{2}[0-9]{2} ?[a-zA-Z0-9]{4} ?[0-9]{4} ?[0-9]{4} ?[0-9]{4} ?[0-9]/ui", $this->_documentText, $matches) > 0) {
            $this->_iban = $matches[0];
        } else {
            $this->_iban = NULL;
        }
    }
}
