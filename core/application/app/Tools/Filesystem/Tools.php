<?php

/**
 * Description of Tools
 *
 * @author iamne
 */
class Tools_Filesystem_Tools {

	private static $_excludedFiles = array('.svn', '.', '..', '.htaccess', 'en.lng', 'fr.lng');

	/**
	 * Scan directory and get all files from it.
	 * Exlude files wich are in the $_excludedFiles from the result
	 *
	 * @param string $path Path to the directory
	 * @return array
	 */
	public static function scanDirectory($path) {
		$foundFiles = array();
		$path       = (string)trim($path);
		if(!$path) {
			throw new Exceptions_SeotoasterException('Scaning directory: path to the directrory is empty.');
		}
		if(!is_dir($path)) {
			throw new Exceptions_SeotoasterException('Scaning directory: path is not a directrory.');
		}
		$foundFiles = @scandir($path);
		if(!empty ($foundFiles)) {
			foreach ($foundFiles as $key => $file) {
				if(in_array($file, self::$_excludedFiles)) {
					unset ($foundFiles[$key]);
				}
			}
		}
		return $foundFiles;
	}

	public static function scanDirectoryForDirs($path) {
		$foundDirs = array();
		$path      = (string)trim($path);
		if(!$path) {
			throw new Exceptions_SeotoasterException('Scaning directory: path to the directrory is empty.');
		}
		if(!is_dir($path)) {
			throw new Exceptions_SeotoasterException('Scaning directory: path is not a directrory.');
		}
		$foundDirs = @scandir($path);
		if(!empty ($foundDirs)) {
			foreach ($foundDirs as $key => $directory) {
				if(!is_dir($path . $directory) || in_array($directory, self::$_excludedFiles)) {
					unset ($foundDirs[$key]);
				}
			}
		}
		return $foundDirs;
	}

	/**
	 * Scan given directory for files with given extension
	 *
	 * @param string $directory Directory to scan
	 * @param string $extension Files extension
	 * @return array
	 */
	public static function findFilesByExtension($directory, $extension) {
		$foundFiles = array();
		$files      = array();
		$files      = self::scanDirectory($directory);
		if(!empty($files)) {
			foreach ($files as $file) {
				if(preg_match('~^[a-zA-Z0-9-_\s]+\.' . $extension . '$~U', $file)) {
					$foundFiles[] = $file;
				}
			}
		}
		return $foundFiles;
	}

	public static function saveFile($path, $content) {
		if(false === @file_put_contents($path, $content)) {
			throw new Exceptions_SeotoasterException('Unable to save file: ' . $path . ' check permissions.');
		}
	}

	
}
