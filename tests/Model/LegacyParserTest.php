<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Model;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Model\FailOnIssueHandler;
use Navarr\Depends\Model\LegacyParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LegacyParserTest extends TestCase
{
    private const LEGACY_FILE = '../data/legacyParserUsage.php';
    private const EMPTY_FILE = '../data/emptyFile.php';

    private function getStandardResults(): array
    {
        $parser = new LegacyParser();
        return $parser->parse(__DIR__ . '/' . self::LEGACY_FILE);
    }

    public function testParserFindsAllAttributes(): array
    {
        $results = $this->getStandardResults();

        $this->assertIsArray($results);
        $this->assertCount(18, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(DeclaredDependency::class, $result);
        }

        return $results;
    }

    /**
     * @param DeclaredDependency[] $results
     * @depends testParserFindsAllAttributes
     */
    public function testParserTrimsReasons(array $results)
    {
        foreach ($results as $result) {
            /** @var DeclaredDependency $result */

            // No whitespace
            $this->assertFalse(substr($result->getReason(), -1) == ' ');

            // No end-comment symbols
            $this->assertFalse(substr($result->getReason(), -2) == '*/');
        }
    }

    /**
     * @param DeclaredDependency[] $results
     * @depends testParserFindsAllAttributes
     */
    public function testParserFindsAllRecordedReasons(array $results)
    {
        $reasons = [
            'composerDependency with version in big doc',
            'composerDependency without version in big doc',
            'dependency with version in big doc',
            'dependency without version in big doc',
            'dependency with version in small doc',
            'dependency without version in small doc',
            'composerDependency with version in small doc',
            'composerDependency without version in small doc',
            'dependency with version in slash doc after other content',
            'this is a test with the comment ending immediately after the reason',
        ];

        $resultReasons = array_map(
            static function (DeclaredDependency $dependency) {
                return $dependency->getReason();
            },
            $results
        );

        foreach ($reasons as $reason) {
            $this->assertContains(
                $reason,
                $resultReasons
            );
        }
    }

    public function testParserFailsOnFileNotFound()
    {
        $file = __DIR__ . '/' . self::LEGACY_FILE . '-not-found';

        $parser = new LegacyParser();
        $parser->setIssueHandler(new FailOnIssueHandler);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not read contents of file '{$file}'");

        $parser->parse($file);
    }

    public function testParserReturnsEmptyResultsOnNonExistentFile()
    {
        $file = __DIR__ . '/' . self::LEGACY_FILE . '-not-found';

        $parser = new LegacyParser();
        $result = $parser->parse($file);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testEmptyFileReturnsEmptyResults()
    {
        $file = __DIR__ . '/' . self::EMPTY_FILE;

        $parser = new LegacyParser();
        $result = $parser->parse($file);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}