<?php

namespace App\Entity;

use App\Repository\ChatRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatRoomRepository::class)]
class ChatRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $chatName = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'chatRoom')]
    private Collection $messages;

    #[ORM\Column]
    private ?int $state = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'chat')]
    private Collection $usersch;



    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->usersch = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatName(): ?string
    {
        return $this->chatName;
    }

    public function setChatName(string $chatName): static
    {
        $this->chatName = $chatName;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setChatRoom($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChatRoom() === $this) {
                $message->setChatRoom(null);
            }
        }

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): static
    {
        $this->state = $state;

        return $this;
    }


    public function updateState(): void
    {
        // Si el chat tiene usuarios asociados, el estado será 0 (activo).
        // Si no tiene usuarios, el estado será 1 (inactivo).
        $this->state = $this->usersch->isEmpty() ? 1 : 0;
    }

   

/**
 * @return Collection<int, User>
 */
public function getUsersch(): Collection
{
    return $this->usersch;
}

public function addUsersch(User $usersch): static
{
    if (!$this->usersch->contains($usersch)) {
        $this->usersch->add($usersch);
        $usersch->setChat($this);
    }

    return $this;
}

public function removeUsersch(User $usersch): static
{
    if ($this->usersch->removeElement($usersch)) {
        // set the owning side to null (unless already changed)
        if ($usersch->getChat() === $this) {
            $usersch->setChat(null);
        }
    }

    return $this;
}
 }
