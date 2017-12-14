<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace bomberman\io;

use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use bomberman\components\Room;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class FieldCollection
 * @package bomberman\io
 */
class FieldCollection extends ArrayCollection
{

    /**
     * @param int $resourceId
     * @return null|Player
     */
    public function findPlayerBySender($resourceId)
    {
        $fieldCells = $this->filter(function (FieldCell $fieldCell) use ($resourceId) {
            return null !== $fieldCell->getPlayer($resourceId);
        });
        if ($fieldCells->count() > 0) {
            return $fieldCells->first()->getPlayer($resourceId);
        }
        return null;
    }

    /**
     * @return array
     */
    public function findBombs()
    {
        $bombs = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $bombs = array_merge($bombs, $fieldCell->getAllBombs());
        }
        return $bombs;
    }

    public function findExplosions()
    {
        $explosions = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $explosions = array_merge($explosions, $fieldCell->getAllExplosions());
        }
        return $explosions;
    }

}
