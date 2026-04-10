<?php

namespace Kondrashov\DownDetector\Core\Http;

class HttpResponse
{
	public function __construct(
		private readonly string $body,
		private readonly int $statusCode,
	)
	{
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}
}