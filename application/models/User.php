<?php

/**
 * User Model
 *
 * Hat session-bezogene Funktionen für den User - aber auch generelle Funktionen
 * welche mit Usern zu tun hat - so halten wir alles bisschen einfacher.
 */
class Application_Model_User
{

    private static $_storage = null;

    /**
     * Eröffnet den Security-Kontext durch username/password -
     * Daten aus dem Loginform.
     *
     * @param string $username Username
     * @param string $password Passwort
     *
     * @return bool true wenn alles ok, sonst false
     */
    public static function initByUserCredentials($username, $password)
    {
        $authAdapter = new DeedBox_Auth_Adapter_DbCrypt(
            $username,
            $password
        );

        return self::validateAdapter($authAdapter);
    }

    /**
     * Hier kann man - durch Umgehung aller Security - den Session-Kontext
     * eröffnen ohne Passwort für einen bestimmten User.
     *
     * Benötigt man für CLI-Scripts..
     *
     * @param int $userId User-ID
     *
     * @return void
     */
    public static function initUserById($userId)
    {
        $authAdapter = new DeedBox_Auth_Adapter_Dummy(
            $userId
        );

        return self::validateAdapter($authAdapter);
    }

    /**
     * Kleine Generalisierung für den Umgang mit diesen Adaptern..
     *
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return boolean true wenn OK; sonst false
     */
    private static function validateAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        if (self::isLoggedIn()) self::logout();

        $ret = false;
        $result = Zend_Auth::getInstance()->authenticate($adapter);

        if($result->isValid()){
            $ret = true;
            self::setUserdata($adapter->getResultRowObject());
        }

        return $ret;

    }

	/**
	* Initialisiert den User
	*
	* @param array Userdaten (Row aus Table)
	*
	* @return void
	*/
	public static function setUserdata($userdata)
	{
		$_SESSION['Userdata'] = (array)$userdata;
	}

    /**
     * Gibt die Userdaten aus der Session zurück
     *
     * @return array Userdaten
     */
	public static function getUserData()
	{
		return $_SESSION['Userdata'];
	}

	/**
	* Boolean Toggle, ob jemand eingeloggt ist oder nicht
	*
	* @return boolean true wenn ja, sonst false
	*/
	public static function isLoggedIn()
	{
		$ret = false;
		if (Zend_Auth::getInstance()->hasIdentity()) $ret = true;

		return $ret;
	}

	/**
	* Logout
	*
	* @return void
	*/
	public static function logout()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$_SESSION['Userdata'] = null;
        self::$_storage = null;

        if (php_sapi_name() != 'cli') {
            Zend_Session::destroy();
        } else {
            // bei cli destroyen wir die session nicht richtig,
            // sondern löschen einfach das superglobal..
            foreach ($_SESSION as $key => $val) {
                unset($_SESSION[$key]);
            }
        }
	}

    /**
     * Gibt die IndexHandler-Instanz zu diesem User zurück
     *
     * @return \DeedBox_IndexHandler IndexHandler
     */
    public static function getIndexHandler()
    {
        return new DeedBox_IndexHandler(self::getIndexPath());
    }

    /**
     * Gibt den Pfad zum Index dieses Users zurück
     *
     * @return string Indexpfad
     */
    public static function getIndexPath()
    {
        $config = Zend_Registry::get('config');
        $path = realpath($config->indexdir).'/'.self::getHashId();

        return $path;
    }

    public static function getHashId()
    {
        $config = Zend_Registry::get('config');

        if (!isset($config->authcrypt->salt)) {
            throw new Exception('No crypt salt found in application.ini! Exiting');
        }

        return md5($config->authcrypt->salt.self::getIdentity());
    }

    /**
     * Gibt die Identity dieses Users zurück
     *
     * @return string Identity
     */
	public static function getIdentity()
	{
		return Zend_Auth::getInstance()->getIdentity();
	}

    /**
     * Gibt die User-ID dieses Users zurück
     *
     * @return int ID
     */
    public static function getId()
    {
        $ret = null;
        $userData = self::getUserData();
        if (isset($userData['id'])) $ret = $userData['id'];

        return $ret;
    }

    public static function getLocalStorage()
    {
        $config = Zend_Registry::get('config');

        $options = array(
            'baseDir' => realpath($config->localstoragedir)
        );

        return new Application_Model_Storage_UserLocal($options);
    }

    /**
     * Gibt die Storage Instanz zurück
     *
     * @return Application_Model_Storage_Abstract
     * @throws Exception
     */
    public static function getStorage()
    {
        if (is_null(self::$_storage)) {
            // welches backend?
            // TODO: das könnte/sollte man noch VIIIIEEEL schöner machen (factory)!
            $storage = false;
            $userdata = self::getUserData();
            $config = Zend_Registry::get('config');

            switch ($userdata['storage_backend']) {
                case 'dropbox':
                    $options = $config->storage->dropbox->toArray();
                    $options['db'] = $config->database->params->toArray();

                    $storage = new Application_Model_Storage_Dropbox($options);
                    break;
                default:
                    break;
            }

            if (!$storage instanceof Application_Model_Storage_Abstract) {
                throw new Exception('Could not instantiate user storage!');
            }

            self::$_storage = $storage;
        }

        return self::$_storage;

        //TMPLOCAL
        /*
        $options = array(
            'baseDir' => '/var/www/dev.deedbox.ch/data/tmplocal'
        );

        return new Application_Model_Storage_Local($options);
         *
         */
    }

}

