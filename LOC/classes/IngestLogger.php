<?php
class IngestLogger
{
    protected static $_logDir = null;
    protected static $_filePointers = array();

    public function __destruct()
    {
        foreach(self::$_filePointers AS $fp) {
            fwrite($fp, 'closing file' . PHP_EOL);
            fclose($fp);
        }
    }


    protected static function getLogDir()
    {
        if (null === self::$_logDir) {
            $base_dir = realpath(dirname(__FILE__));
		
		//$base_dir = preg_replace('/classes', '', $base_dir);
		$base_dir .= "/../";
            if (!is_dir($base_dir . '/data/')) {
                mkdir($base_dir . '/data/');
            }
            self::$_logDir = realpath($base_dir . '/data/');
        }
        return self::$_logDir;
    }

    protected static function getFilePointer($name) {
        if (!array_key_exists($name, self::$_filePointers)) {
            self::$_filePointers[$name] = fopen(self::getLogDir() . '/' . $name, 'a');
        }
        return self::$_filePointers[$name];
    }

     /**
     *
     */
    public static function writeEntry($string, $type)
    {

	    $currtime = microtime(true);
		switch ($type) {
			case "Data":
                fwrite(self::getFilePointer('data_error_log'. date('Ymd').'.log'), date('c') . ',' . $string . PHP_EOL);
				break;
                        case "Info" :
                            fwrite(self::getFilePointer('RsIngestLog'. date('Ymd').'.log'), date('c') .',' .$string. PHP_EOL);
                            break;
			default:
				fwrite(self::getFilePointer('RsIngestLog'.date('Ymd') . '.log'), date('c') . ',' . $string . PHP_EOL);
		}
    }
}
