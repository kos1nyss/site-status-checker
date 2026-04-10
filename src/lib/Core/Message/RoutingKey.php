<?php

namespace Kondrashov\DownDetector\Core\Message;

enum RoutingKey: string
{
	case SEND_MAIL = 'sendMail';
}