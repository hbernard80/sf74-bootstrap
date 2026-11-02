<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Fixture\TimestampedItem;
use DateTimeImmutable;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class TimestampableTraitTest extends TestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $configuration = ORMSetup::createAttributeMetadataConfig(
            [dirname(__DIR__).'/Fixture'],
            isDevMode: true,
        );
        $configuration->enableNativeLazyObjects(true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
        $this->entityManager = new EntityManager($connection, $configuration);

        (new SchemaTool($this->entityManager))->createSchema([
            $this->entityManager->getClassMetadata(TimestampedItem::class),
        ]);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager->getConnection()->close();
    }

    public function testInsertInitializesAndPersistsBothDates(): void
    {
        $item = new TimestampedItem(1, 'Initial');
        self::assertNull($item->getCreatedAt());
        self::assertNull($item->getUpdatedAt());

        $this->entityManager->persist($item);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $storedItem = $this->entityManager->find(TimestampedItem::class, 1);
        self::assertNotNull($storedItem);
        self::assertInstanceOf(DateTimeImmutable::class, $storedItem->getCreatedAt());
        self::assertEquals($storedItem->getCreatedAt(), $storedItem->getUpdatedAt());
    }

    public function testInsertPreservesExplicitDates(): void
    {
        $createdAt = new DateTimeImmutable('2000-01-01 10:00:00');
        $updatedAt = new DateTimeImmutable('2000-01-02 10:00:00');
        $item = (new TimestampedItem(1, 'Imported'))
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $storedItem = $this->entityManager->find(TimestampedItem::class, 1);
        self::assertNotNull($storedItem);
        self::assertEquals($createdAt, $storedItem->getCreatedAt());
        self::assertEquals($updatedAt, $storedItem->getUpdatedAt());
    }

    public function testUpdateRefreshesUpdatedAtAndPreservesCreatedAt(): void
    {
        $originalDate = new DateTimeImmutable('2000-01-01 10:00:00');
        $item = (new TimestampedItem(1, 'Initial'))
            ->setCreatedAt($originalDate)
            ->setUpdatedAt($originalDate);

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        $item->name = 'Modified';
        $this->entityManager->flush();
        $this->entityManager->clear();

        $storedItem = $this->entityManager->find(TimestampedItem::class, 1);
        self::assertNotNull($storedItem);
        self::assertSame('Modified', $storedItem->name);
        self::assertEquals($originalDate, $storedItem->getCreatedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $storedItem->getUpdatedAt());
        self::assertGreaterThan($originalDate, $storedItem->getUpdatedAt());
    }
}
