<?php

declare(strict_types=1);

namespace Softline\Core\Http;

class HttpClient
{
	public function get(string $url)
	{
		return $this->request('GET', $url);
	}

	public function request(
		string $method,
		string $url,
	): ?HttpResponse
	{
		$ch = \curl_init($url);

		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_exec($ch);

		if (curl_errno($ch))
		{
			return null;
		}

		$response = new HttpResponse(
			'',
			(int)curl_getinfo($ch, CURLINFO_HTTP_CODE),
		);

		curl_close($ch);

		return $response;
	}
}