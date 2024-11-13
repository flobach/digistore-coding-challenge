<?php

namespace App\Dto\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class MessagesListRequest
{
    public const STATUS_SENT = 'sent';
    public const STATUS_READ = 'read';

    #[Assert\Choice(choices: [self::STATUS_READ, self::STATUS_SENT])]
    private ?string $status = null;

    public function __construct(Request $request) {
        $status = $request->query->get('status');
        if (null !== $status) {
            $this->status = (string) $status;
        }
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
}
