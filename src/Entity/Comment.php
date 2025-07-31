<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $publication_date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        min: 20,
        max: 500,
        minMessage: 'Le commentaire doit contenir au moins 20 caractéres',
        maxMessage: 'Le commentaire doit contenir au maximun 500 caractéres',
    )]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?user $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicationDate(): ?\DateTime
    {
        return $this->publication_date;
    }

    public function setPublicationDate(\DateTime $publication_date): static
    {
        $this->publication_date = $publication_date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): static
    {
        $this->user = $user;

        return $this;
    }
}
