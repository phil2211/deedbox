<?php

/**
 * Extrahiert den Text aus einem PDF File unter Verwendung von XPDF
 *
 * @category   DeedBox Library
 * @package    DeedBox
 * @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
 * @license    GPL
 * @since      Klasse vorhanden seit Release 0.1
 */

class DeedBox_Pdf2Text {

    /**
     * Enthält Pfad und Dateiname des zu extrahierenden PDF-Files
     *
     * @var string
     */
    private $_pdfFile = NULL;

    /**
     * Enthält Pfad und Dateiname zu dem ausführbaren pdftotext tools
     *
     * @var string
     */
    private $_converterTool = '/opt/local/bin/xpdf-pdftotext';

    /**
     * Klassenkonstruktor
     *
     * Nimmt den Dateinamen des zu extrahierenden PDF-Files entgegen
     *
     * @param type $pdfFile
     */
    public function __construct($pdfFile)
    {
        $this->_pdfFile = $pdfFile;
    }

    /**
     * Setzt Pfad und Dateiname zu dem Konvertierungstool pdftotext
     *
     * @param type $toolName
     */
    public function setConverterTool($toolName)
    {
        $this->_converterTool = $toolName;
    }

    /**
     * Extrahiert den Text aus dem PDF und gibt diesen zurück
     *
     * @param boolean $layout bestimmt, ob das Layout aus dem PDF-Dokument erhalten bleiben soll oder nicht
     * @return string Den extrahierten Text
     */
    public function getPdfText($layout = FALSE)
    {
        $returnValue = NULL;
        $ret = '';

        //Setzt den Kommandozeilenbefehl für pdftotext zusammen
        $execString = $this->_converterTool;
        if ($layout)
            $execString .= ' -layout';
        $execString .= ' -enc UTF-8 ' . escapeshellarg($this->_pdfFile);

        //Führt pdftotext aus. Dieses schreibt den extrahierten Text in eine Datei welche mit .txt endet
        //ansonsten aber gleich heisst wie die ursprüngliche PDF-Datei
        exec($execString, $output, $returnValue);

        //Prüft, ob die Konvertierung funktioniert hat und liest die generierte Textdatei wieder ein und gibt diese zurück
        $tmpFile = $this->_pdfFile . '.txt';
        if($returnValue == 0 && file_exists($tmpFile)) {
            $ret = file_get_contents($tmpFile);
            unlink($tmpFile);
        }

        return $ret;
    }
}
