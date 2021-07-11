<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Parser;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\IssueHandler\FailOnIssueHandler;
use Navarr\Depends\Parser\LegacyParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LegacyParserTest extends TestCase
{
    private const LEGACY_FILE = '../_data/legacyParserUsage.php';
    private const EMPTY_FILE = '../_data/emptyFile.php';

    private function getStandardResults(): array
    {
        $parser = new LegacyParser();
        $contents = file_get_contents(__DIR__ . '/' . self::LEGACY_FILE);
        return $parser->parse($contents);
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

    public function testEmptyFileReturnsEmptyResults()
    {
        $file = __DIR__ . '/' . self::EMPTY_FILE;

        $parser = new LegacyParser();
        $result = $parser->parse(file_get_contents($file));

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testInvalidUnicodeContentGracefullyFails()
    {
        $content = "a\xff";

        $parser = new LegacyParser();
        $result = $parser->parse($content);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testIssueHandlerIsUtilized()
    {
        // I don't like doing this, but I can't reliably make this parser error :|
        $handler = new FailOnIssueHandler();
        $parser = new LegacyParser($handler);

        $exceptionMessage = uniqid();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $reflectionClass = new \ReflectionClass($parser);
        $method = $reflectionClass->getMethod('handleIssue');
        $method->setAccessible(true);
        $method->invoke($parser, $exceptionMessage);
    }
}
