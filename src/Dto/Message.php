<?php

namespace App\Dto;

class Message implements \JsonSerializable
{
    private ?string $uuid = null;
    private ?string $text = null;
    private ?string $status = null;

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'uuid' => $this->uuid,
            'text' => $this->text,
            'status' => $this->status,
        ];
    }
}