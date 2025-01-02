<?php declare(strict_types=1);

namespace Forrest79\TracyRemoteBar;

use Tracy\Debugger;
use Tracy\Helpers;

class Remote
{
	private static string|NULL $serverUrl = NULL;


	public static function setServerUrl(string $serverUrl): void
	{
		self::$serverUrl = $serverUrl;
	}


	public static function isActive(): bool
	{
		return self::$serverUrl !== NULL;
	}


	public static function addBar(string $html): void
	{
		if (!self::isActive()) {
			return;
		}

		assert(self::$serverUrl !== NULL);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, rtrim(self::$serverUrl, '/') . '/api/');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $html);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: text/plain',
			'Content-Length: ' . strlen($html),
		]);

		curl_exec($ch);

		if (curl_errno($ch) !== CURLE_OK) {
			Debugger::log('#' . curl_errno($ch) . ': ' . curl_error($ch), 'tracy-remote-bar');
		} else {
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($httpCode !== 200) {
				Debugger::log('# HTTP code ' . $httpCode . ' was returned.', 'tracy-remote-bar');
			}
		}
	}


	public static function dispatchBars(): void
	{
		if (self::isActive() && !(bool) Debugger::$productionMode) {
			Debugger::removeOutputBuffers(FALSE);
			try {
				self::addBar(Helpers::capture(static function (): void {
					Debugger::getStrategy()->renderBar();
				}));
			} catch (\Throwable $e) {
				Debugger::exceptionHandler($e);
			}
		}
	}


	public static function isHttpAjax(): bool
	{
		return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
	}

}