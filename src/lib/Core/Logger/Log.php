<?php

namespace Softline\Core\Logger;

enum Log: string
{
	case GENERAL = 'general';
	case INFO = 'info';
	case ERROR = 'error';
}