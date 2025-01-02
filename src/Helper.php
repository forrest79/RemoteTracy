<?php declare(strict_types=1);

namespace Forrest79\TracyRemoteBar;

class Helper
{

	/**
	 * @param class-string $class
	 */
	public static function classDir(string $class): string
	{
		$filename = (new \ReflectionClass($class))->getFileName();
		if ($filename === FALSE) {
			throw new \RuntimeException(sprintf('File name from class "%s" can\'t be determined.', $class));
		}

		return dirname($filename);
	}


	public static function createTempDir(string $dir): string
	{
		$dir = sys_get_temp_dir() . '/' . $dir;
		if (!is_dir($dir) && !mkdir($dir, recursive: TRUE) && !is_dir($dir)) {
			throw new \RuntimeException(sprintf('Directory "%s" not exists.', $dir));
		}

		return $dir;
	}

}