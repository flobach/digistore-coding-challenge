<?php

namespace App\Services\Mapper;

use App\Dto\Message as MessageDto;
use App\Entity\Message;

class MessageMapper
{
    public static function mapEntityToDto(Message $message): MessageDto
    {
        return (new MessageDto())
            ->setText($message->getText())
            ->setUuid($message->getUuid())
            ->setStatus($message->getStatus());
    }
}