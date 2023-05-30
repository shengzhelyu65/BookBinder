<?php

namespace App\Entity;

use App\Repository\MeetupListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MeetupListRepository::class)]
class MeetupList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $meetup_list_ID = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'meetup_ID', referencedColumnName: 'meetup_id', nullable: false)]
    private ?MeetupRequests $meetup_ID = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_ID', referencedColumnName: 'id', nullable: false)]
    private ?User $user_ID = null;

    public function getMeetupListID(): ?int
    {
        return $this->meetup_list_ID;
    }

    public function getMeetupID(): ?MeetupRequests
    {
        return $this->meetup_ID;
    }

    public function setMeetupID(?MeetupRequests $meetup_ID): self
    {
        $this->meetup_ID = $meetup_ID;

        return $this;
    }

    public function getUserID(): ?User
    {
        return $this->user_ID;
    }

    public function setUserID(?User $user_ID): self
    {
        $this->user_ID = $user_ID;

        return $this;
    }
}
