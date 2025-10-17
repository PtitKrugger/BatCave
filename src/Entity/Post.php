<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Assert\NotNull]
    private ?User $author = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le contenu du post doit faire au moins 3 caractères', maxMessage: 'Le contenu du post ne doit pas excéder 255 caractères')]
    private ?string $content = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(inversedBy: 'likedPosts', targetEntity: User::class)]
    #[ORM\JoinTable(name: 'likes_posts')]
    private Collection $likedBy;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self 
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt() : ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt) : void
    {
        $this->createdAt = $createdAt;
    }

    public function getLikedBy(): Collection
    {
        return $this->likedBy;
    }

    public function addLikedBy(User $user): self
    {
        if (!$this->likedBy->contains($user)) {
            $this->likedBy->add($user);
        }
        return $this;
    }

    public function removeLikedBy(User $user): self
    {
        $this->likedBy->removeElement($user);
        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }
}
