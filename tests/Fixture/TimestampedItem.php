<?php

declare(strict_types=1);

namespace App\Tests\Fixture;

use App\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class TimestampedItem
{
    use TimestampableTrait;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id,
        #[ORM\Column]
        public string $name,
    ) {
    }
}
