<?php

namespace UnknowL\games\types;

use UnknowL\games\BaseGame;
use UnknowL\player\LinesiaPlayer;

class BossGame extends BaseGame
{

    public function start(): BaseGame
    {
        parent::start();
        return $this;
    }

    public function join(LinesiaPlayer $player): void
    {
        // TODO: Implement join() method.
    }

    public function leave(LinesiaPlayer $player): void
    {
        // TODO: Implement leave() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function onTick(): void
    {
        // TODO: Implement onTick() method.
    }
}