<?php

namespace UnknowL\handlers;

use pocketmine\math\AxisAlignedBB;
use pocketmine\world\World;

final class ChunkHandler extends Handler
{

    protected function loadData(): void
    {
		//NOOP
	}

    protected function saveData(): void
    {
		//Im dylan so NOOP
	}

	final public function canPlace(int $chunkX, int $chunkZ, World $world): bool
	{
		$chunk = $world->getChunk($chunkX, $chunkZ);
		$x = $chunkX << 4;
		$y = $chunk->getHeightMapArray();
		$z = $chunkZ << 4;

		/*$area = new AxisAlignedBB($x, $y, $z, $x + 16, $y + 16,);
		$blocks = $chunk->get*/
		return true;
	}

    public function getName(): string
    {
		return "Chunk";
	}
}