<?php

declare(strict_types=1);

namespace SmartHome\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use SmartHome\Enum\ModuleType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210606193451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE modules SET type="'.ModuleType::LIGHT.'"');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE modules SET type=""');
    }
}
