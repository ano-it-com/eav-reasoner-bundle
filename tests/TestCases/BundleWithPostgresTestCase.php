<?php

namespace ANOITCOM\EAVReasonerBundle\Tests\TestCases;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Statement;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class BundleWithPostgresTestCase extends BundleTestCase
{

    protected static bool $debug = false;


    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::createDbAndMigrations();

    }


    protected static function createDbAndMigrations(): void
    {
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        self::createDB($application);
        self::dropSchema($application);
        $pathToMigration = self::createMigrationFile();
        self::migrate($application, $pathToMigration);
    }


    protected static function dropSchema(Application $application): void
    {
        if (self::$debug) {
            echo "Dropping tables..." . PHP_EOL;
        }
        $connection = self::$container->get('doctrine.dbal.default_connection');
        /** @var Statement $stmt */
        $stmt = $connection->executeQuery('select \'drop table "\' || tablename || \'" cascade;\' as stmt from pg_tables where schemaname = \'public\'');

        $dropQueries = $stmt->fetchAll(FetchMode::ASSOCIATIVE);
        $dropQueries = array_column($dropQueries, 'stmt');

        foreach ($dropQueries as $dropQuery) {
            $connection->executeQuery($dropQuery);
        }
    }


    protected static function createDB(Application $application): void
    {
        $input = new ArrayInput([
            'command'         => 'doctrine:database:create',
            '--if-not-exists' => true
        ]);

        $output = self::$debug ? new BufferedOutput() : new NullOutput();
        $application->run($input, $output);

        if (self::$debug) {
            echo $output->fetch();
        }

    }


    protected static function migrate(Application $application, string $fqdn): void
    {
        $input = new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            $fqdn,
            '--no-interaction',
        ]);

        $output = self::$debug ? new BufferedOutput() : new NullOutput();
        $application->run($input, $output);

        if (self::$debug) {
            echo $output->fetch();
        }

    }


    protected static function createMigrationFile(): string
    {
        $templatePath = __DIR__ . '/../App/Migrations/Migration.tpl.php';
        $className    = 'VersionTest';

        ob_start();

        include $templatePath;

        $content = ob_get_clean();

        $pathToSave = self::$kernel->getCacheDir() . '/migrations/' . $className . '.php';

        @mkdir(dirname($pathToSave), 0755, true);

        $fp = fopen($pathToSave, 'wb+');
        fwrite($fp, $content);
        fclose($fp);

        return 'DoctrineMigrations\\' . $className;


    }
}