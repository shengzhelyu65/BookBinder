<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Throwable;

class ResetAutoincrementFixture extends Fixture
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function load(ObjectManager $manager): void
    {
        // List all the table names in your database
        $tableNames = [
            'book',
            'book_reviews',
            'library',
            'meetup_requests',
            'meetup_request_list',
            'meetup_list',
            'user',
            'user_personal_info',
            'user_reading_interest',
            'user_reading_list',
        ];

        $this->connection->beginTransaction();

        try {
            foreach ($tableNames as $tableName) {
                $this->resetAutoincrement($tableName);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function resetAutoincrement(string $tableName): void
    {
        $this->connection->executeStatement("ALTER TABLE $tableName AUTO_INCREMENT = 1");
    }
}
