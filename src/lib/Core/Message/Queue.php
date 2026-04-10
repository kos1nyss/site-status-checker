<?php

namespace Kondrashov\DownDetector\Core\Message;

enum Queue: string
{
	case SEND_MAIL = 'sendMail';
}