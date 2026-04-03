<?php

namespace Softline\Core\Message;

enum RoutingKey: string
{
	case SEND_MAIL = 'sendMail';
}