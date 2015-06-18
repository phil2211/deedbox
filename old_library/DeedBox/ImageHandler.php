<?php

/**
 * Kurzbeschreibung
 *
 * Langbeschreibung (wenn vorhanden)
 *
 * @category
 * @package
 * @copyright  Copyright (c) 2012 Philip Eschenbacher <philip@eschenbacher.ch>
 * @license    GPL
 * @version    Release: @package_version@
 * @since      Klasse vorhanden seit Release 0.1
 * @deprecated
 */
class DeedBox_ImageHandler {


    /**
     * Enthält den Dateinamen der zu bearbeitenden Bilddatei
     *
     * @var string
     */
    private $_fileName = NULL;

    /**
     * Enthält einen BLOB mit einem Bild das zu bearbeiten ist
     *
     * @var BLOB
     */
    private $_image = NULL;

    /**
     * Enthält das ImageMagick Objekt zum Bearbeiten von Bildern
     *
     * @var Object
     */
    private $_imagick = NULL;

    /**
     * Enthält die SIFT-Features eines Bildes
     *
     * @var CLOB
     */
    private $_siftFeat = NULL;

    /**
     * Enthält Pfad und Dateiname des SIFTFEAT Executables
     *
     * @var string
     */
    private $_siftTool = NULL;

    /**
     * Der Klassenkonstruktor
     *
     * Instanziert ein neues ImageMagick Objekt und setzt die Auflösung
     * auf 300dpi
     *
     */
    public function __construct() {
        $this->_imagick = new Imagick();
        $this->_imagick->setresolution(300, 300);
    }

    /**
     * Setzt den Pfad und Dateinamen für das siftfeat Executable
     *
     * @param string $siftTool
     * @return \DeedBox_ImageHandler (liquid Interface)
     */
    public function setSiftTool($siftTool)
    {
        $this->_siftTool = $siftTool;
        return $this;
    }

    /**
     * Gibt ein ermitteltes SIFT-Feature CLOB zurück
     *
     * @return CLOB
     */
    public function getSiftFeat()
    {
        return $this->_siftFeat;
    }

    /**
     * Setzt den Dateinamen der zu bearbeitenden Bilddatei
     *
     * @param string $fileName
     * @return \DeedBox_ImageHandler (liquid Interface)
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
        $this->loadImage();
        return $this;
    }

    /**
     * Setzt einen BLOB mit einem Bild das zu bearbeiten ist. Dies spart
     * den Umweg über ein File
     *
     * @param BLOB $image (PNG, JPEG, BMP)
     * @return \DeedBox_ImageHandler (liquid Interface)
     * @throws Exception Wenn das Bild ungültig ist
     */
    public function setImage($image)
    {
        $this->_image = $image;
        try {
            $this->loadImage();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            throw new Exception('Image Invalid');
        }
        return $this;
    }

    /**
     * Gibt eine Instanz des ImageMagick Objektes zurück
     *
     * @return Object
     */
    public function getImage()
    {
        return $this->_imagick;
    }

    /**
     * Generiert ein Thumbnail mit 100px Breite
     *
     * @return \DeedBox_ImageHandler (liquid Interface)
     */
    public function createThumbnail()
    {
        $this->convertToPng(100);
        return $this;
    }

    /**
     * Generiert ein Vorschaubild mit 750px Breite
     *
     * @return \DeedBox_ImageHandler (liquid Interface)
     */
    public function createPreview()
    {
        $this->convertToPng(750);
        return $this;
    }

    /**
     * Schneidet ein Bild anhand von xy Koordinaten der linken oberen Ecke
     * sowie von Breiten- und Höhenangaben aus
     *
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @return \DeedBox_ImageHandler (liquied Interface)
     */
    public function cropImage($width, $height, $x, $y)
    {
        $this->_imagick->cropImage($width, $height, $x, $y);
        return $this;
    }

    /**
     * Konvertiert ein Bild in das PNG Format
     *
     * @param int $width
     * @param int $height
     * @return \DeedBox_ImageHandler (liquied Interface)
     * @throws Exception Falls keine Datei zum Konvertieren angegeben wurde
     */
    public function convertToPng($width = 0, $height = 0)
    {
        $this->_imagick->resetiterator();

        if ($width > 0 || $height > 0)
            $this->_imagick->scaleImage($width, $height);

        //dies loest das Problem von transparenten PNGs aus PDFS
        $this->_imagick->borderimage('white', 0, 0);
        $this->_imagick->setImageFormat('png32');
        return $this;
    }

    /**
     * Lädt ein Bild aus einer Datei oder aus einem BLOB in das ImageMagick Bildobjekt
     *
     * @return void
     * @throws Exception
     */
    private function loadImage()
    {
        if ($this->_fileName === NULL && $this->_image === NULL) {
            throw new Exception('Kein Bild zum Konvertieren angegeben');
        } elseif ($this->_fileName === NULL) {
            try {
                $this->_imagick->readimageblob($this->_image);
            } catch (Exception $exc) {
                return false;
            }
        } else {
            $this->_imagick->readimage($this->_fileName);
        }
    }

    /**
     * Extrahiert aus einem gegebenen Bild einen SIFT-Index
     *
     * @return \DeedBox_ImageHandler
     */
    public function createSiftIndex()
    {
        $tmpfile_in = tempnam('/tmp', 'deedbox_in');
        $tmpfile_out = tempnam('/tmp', 'deedbox');

        file_put_contents($tmpfile_in, $this->_imagick);

        $cmd = $this->_siftTool;
        $cmd .= ' -o ' . escapeshellarg($tmpfile_out);
        $cmd .= ' -x ' . escapeshellarg($tmpfile_in);

        exec($cmd);

        unset($this->_siftFeat);

        $this->_siftFeat = file_get_contents($tmpfile_out);

        unlink($tmpfile_in);
        unlink($tmpfile_out);

        return $this;
    }
}
