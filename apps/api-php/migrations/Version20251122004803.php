<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122004803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unique constraint and add partial unique index for confirmed bookings only';
    }

    public function up(Schema $schema): void
    {
        // Drop the old unique constraint that applies to all bookings
        $this->addSql('DROP INDEX uniq_booking_provider_datetime');
        $this->addSql('ALTER TABLE booking ALTER status DROP DEFAULT');
        
        // Create a partial unique index that only applies to confirmed bookings
        // This allows cancelled bookings to occupy the same provider/datetime slot
        $this->addSql('CREATE UNIQUE INDEX uniq_booking_provider_datetime_confirmed ON booking (provider_id, datetime) WHERE status = \'confirmed\'');
    }

    public function down(Schema $schema): void
    {
        // Revert back to the original unique constraint
        $this->addSql('DROP INDEX uniq_booking_provider_datetime_confirmed');
        $this->addSql('ALTER TABLE booking ALTER status SET DEFAULT \'confirmed\'');
        $this->addSql('CREATE UNIQUE INDEX uniq_booking_provider_datetime ON booking (provider_id, datetime)');
    }
}
