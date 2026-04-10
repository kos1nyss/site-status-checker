<?php

namespace Kondrashov\DownDetector\Core\Logger;

enum Log: string
{
	case GENERAL = 'general';
	case INFO = 'info';
	case ERROR = 'error';
}