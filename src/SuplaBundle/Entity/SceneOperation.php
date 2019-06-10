<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="supla_scene_operation")
 */
class SceneOperation {
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"basic"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Scene", inversedBy="operations")
     * @ORM\JoinColumn(name="scene_id", referencedColumnName="id", nullable=false)
     */
    private $scene;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannel", inversedBy="directLinks")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channel;

    /**
     * @ORM\ManyToOne(targetEntity="IODeviceChannelGroup", inversedBy="directLinks")
     * @ORM\JoinColumn(name="channel_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $channelGroup;

    /**
     * @ORM\Column(name="action", type="integer", nullable=false)
     * @Groups({"basic"})
     */
    private $action;

    /**
     * @ORM\Column(name="action_param", type="string", nullable=true, length=255)
     * @Groups({"basic"})
     */
    private $actionParam;

    /**
     * @ORM\Column(name="delay_ms", type="integer", nullable=false, options={"default" : 0})
     * @Groups({"basic"})
     */
    private $delayMs;

    public function __construct(Scene $scene, HasFunction $subject, array $actionParam = []) {
        $this->scene = $scene;
        if ($subject instanceof IODeviceChannel) {
            $this->channel = $subject;
        } elseif ($subject instanceof IODeviceChannelGroup) {
            $this->channelGroup = $subject;
        } else {
            throw new \InvalidArgumentException('Invalid scene operation subject given: ' . get_class($subject));
        }
        $this->actionParam = $actionParam;
    }

    public function getId(): int {
        return $this->id;
    }

    /** @Groups({"subject"}) */
    public function getSubject(): HasFunction {
        return $this->channel ?: $this->channelGroup;
    }

    public function getSubjectType(): ActionableSubjectType {
        return ActionableSubjectType::forEntity($this->getSubject());
    }

    /** @return array|null */
    public function getActionParam() {
        return $this->actionParam ? json_decode($this->actionParam, true) : $this->actionParam;
    }

    public function getAction(): ChannelFunctionAction {
        return new ChannelFunctionAction($this->action);
    }

    public function getDelayMs(): int {
        return $this->delayMs ?? 0;
    }
}
