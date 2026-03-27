<?php

declare(strict_types=1);

namespace App\Entity;

use PhpCollective\SymfonyDjot\Validator\Constraints\ValidDjot;
use Symfony\Component\Validator\Constraints as Assert;

class Article
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $title = '';

    #[Assert\NotBlank]
    #[ValidDjot]
    private string $body = '';

    #[ValidDjot(strict: true, message: 'Comment must be valid Djot (strict mode): {{ error }}')]
    private ?string $comment = null;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
