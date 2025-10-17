<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotNull]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 30, unique: true)]
    #[Assert\NotNull]
    #[Assert\Length(min: 3, max: 30, minMessage: 'Le pseudo doit faire au moins 3 caractères', maxMessage: 'Le pseudo ne peut pas excéder 30 caractères')]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotNull]
    private ?string $password = null;

    #[Vich\UploadableField(mapping: 'profilePicture', fileNameProperty: 'profilePictureLink', size: 'profilePictureSize')]
    #[Assert\Image(mimeTypes: ["image/jpeg", "image/png", "image/gif"], mimeTypesMessage: "Veuillez télécharger une image valide (jpeg, png, gif).")]
    private ?File $profilePicture = null;

    #[ORM\Column(nullable: true)]
    private ?string $profilePictureLink = null;

    #[ORM\Column(nullable: true)]
    private ?int $profilePictureSize = null;

    #[Vich\UploadableField(mapping: 'profileBackgroundImage', fileNameProperty: 'profileBackgroundLink', size: 'profileBackgroundSize')]
    #[Assert\Image(mimeTypes: ["image/jpeg", "image/png", "image/gif"], mimeTypesMessage: "Veuillez télécharger une image valide (jpeg, png, gif).")]
    private ?File $profileBackground = null;

    #[ORM\Column(nullable: true)]
    private ?string $profileBackgroundLink = null;

    #[ORM\Column(nullable: true)]
    private ?int $profileBackgroundSize = null;

    #[ORM\Column(length: 7, options: ["default" => '#ffffff'])]
    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'La couleur doit être au format HEX (e.g., #ffffff).')]
    private ?string $profileBorderColor = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas excéder 255 caractères.')]
    private ?string $description = null;

    #[ORM\Column(nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $updatedAt = null;
    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class)]
    private Collection $posts;

    #[ORM\ManyToMany(mappedBy: 'likedBy', targetEntity: Post::class)]
    private Collection $likedPosts;

    #[ORM\ManyToMany(mappedBy: 'likedBy', targetEntity: Comment::class)]
    private Collection $likedComments;

    public function getId(): ?int {
        return $this->id;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): static {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string {
        return (string) $this->email;
    }

    public function getRoles(): array {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static {
        $this->roles = $roles;

        return $this;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $username): static {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): static {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getProfilePicture(): ?File {
        return $this->profilePicture;
    }

    public function setProfilePicture(?File $profilePicture = null): void {
        $this->profilePicture = $profilePicture;

        if (null !== $profilePicture) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getProfilePictureLink(): ?string {
        return $this->profilePictureLink;
    }

    public function setProfilePictureLink(?string $profilePictureLink): void {
        $this->profilePictureLink = $profilePictureLink;
    }

    public function getProfilePictureSize(): ?int {
        return $this->profilePictureSize;
    }

    public function setProfilePictureSize(?int $profilePictureSize): void {
        $this->profilePictureSize = $profilePictureSize;
    }

    public function getProfileBackground(): ?File {
        return $this->profileBackground;
    }

    public function setProfileBackground(?File $profileBackground = null): void {
        $this->profileBackground = $profileBackground;

        if (null !== $profileBackground) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getProfileBackgroundLink(): ?string {
        return $this->profileBackgroundLink;
    }

    public function setProfileBackgroundLink(?string $profileBackgroundLink): void {
        $this->profileBackgroundLink = $profileBackgroundLink;
    }

    public function getProfileBackgroundSize(): ?int {
        return $this->profileBackgroundSize;
    }

    public function setProfileBackgroundSize(?int $profileBackgroundSize): void {
        $this->profileBackgroundSize = $profileBackgroundSize;
    }

    public function getProfileBorderColor(): ?string {
        return $this->profileBorderColor;
    }

    public function setProfileBorderColor(string $profileBorderColor): static {
        $this->profileBorderColor = $profileBorderColor;

        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): static {
        $this->description = $description ?? '';

        return $this;
    }

    public function getUpdatedAt() : ?\DateTimeImmutable {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt) : void {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt() : ?\DateTimeImmutable {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt) : void {
        $this->createdAt = $createdAt;
    }

    public function getPosts(): Collection {
        return $this->posts;
    }

    public function addPost(Post $post): self {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self {
        if ($this->posts->removeElement($post)) {
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getLikedPosts(): Collection {
        return $this->likedPosts;
    }

    public function addLikedPost(Post $post): self {
        if (!$this->likedPosts->contains($post)) {
            $this->likedPosts->add($post);
        }

        return $this;
    }

    public function removeLikedPost(Post $post): self {
        if ($this->likedPosts->contains($post)) {
            $this->likedPosts->removeElement($post);
            $post->removeLikedBy($this); // Met à jour l'association côté Post
        }
        return $this;
    }

    public function addLikedComment(Comment $comment): self {
        if (!$this->likedComments->contains($comment)) {
            $this->likedComments->add($comment);
        }

        return $this;
    }

    public function removeLikedComment(Comment $comment): self {
        if ($this->likedComments->contains($comment)) {
            $this->likedComments->removeElement($comment);
            $comment->removeLikedBy($this);
        }
        return $this;
    }

    public function serialize(): string {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            $this->username,
        ]);
    }

    public function unserialize($serialized): void {
        list(
            $this->id,
            $this->email,
            $this->password,
            $this->username,
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }
}
