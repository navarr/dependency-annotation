<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Parser;

use DI\Container;
use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Parser\AstParser;
use Navarr\Depends\IssueHandler\FailOnIssueHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AstParserTest extends TestCase
{
    private const EMPTY_FILE = '../data/emptyFile.php';
    private const FILE_ATTRIBUTE_USAGE = '../data/attributeUsage.php';
    private const FILE_INVALID = '../data/invalidAttributeUsage.php';
    private const ATTRIBUTE_USAGE_ATTRIBUTE_COUNT = 10;

    private function buildAstParser(array $args = []): AstParser
    {
        $container = new Container();
        return $container->make(AstParser::class, $args);
    }

    /**
     * @return DeclaredDependency[]
     */
    private function getStandardResults(): array
    {
        $parser = $this->buildAstParser();
        $contents = file_get_contents(__DIR__ . '/' . self::FILE_ATTRIBUTE_USAGE);
        return $parser->parse($contents);
    }

    /**
     * @return DeclaredDependency[]
     */
    public function testParserFindsAllAttributes(): array
    {
        $results = $this->getStandardResults();

        $this->assertIsArray($results);
        $this->assertCount(self::ATTRIBUTE_USAGE_ATTRIBUTE_COUNT, $results);
        foreach ($results as $result) {
            $this->assertInstanceOf(DeclaredDependency::class, $result);
        }

        return $results;
    }

    /**
     * @depends testParserFindsAllAttributes
     *
     * @param DeclaredDependency[]
     */
    public function testParserFindsAllReasons(array $results)
    {
        /** A complete list of strings that should be found in the attributes gathered */
        $searchReasons = [
            'Class Attribute',
            'Constant Attribute',
            'Property Attribute',
            'Method Attribute',
            'Method Parameter Attribute',
            'Function Attribute',
            'Function Parameter Attribute',
            'Mixed Parameter Order',
        ];

        foreach ($searchReasons as $searchReason) {
            $this->assertContains(
                $searchReason,
                array_map(
                    static function (DeclaredDependency $result) {
                        return $result->getReason();
                    },
                    $results
                )
            );
        }
    }

    /**
     * @depends testParserFindsAllAttributes
     *
     * @param DeclaredDependency[]
     */
    public function testParserFindsAllVersions(array $results)
    {
        /** A complete list of version strings that should be found in the attributes gathered */
        $searchVersions = [
            '^1',
            '^2',
            '^3',
            '^4',
            '^5',
            '^6',
            '^7',
            '^8',
            '^9',
        ];

        foreach ($searchVersions as $searchVersion) {
            $this->assertContains(
                $searchVersion,
                array_map(
                    static function (DeclaredDependency $result) {
                        return $result->getConstraint();
                    },
                    $results
                )
            );
        }
    }

    public function testParserFailsOnInvalidFile()
    {
        $file = __DIR__ . '/' . self::FILE_INVALID;
        $contents = file_get_contents($file);

        $parser = $this->buildAstParser(['issueHandler' => new FailOnIssueHandler()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches("#^Could not parse contents#");

        $parser->parse($contents);
    }

    public function testParserReturnsEmptyResultsOnInvalidFile()
    {
        $file = __DIR__ . '/' . self::FILE_INVALID;
        $contents = file_get_contents($file);

        $parser = $this->buildAstParser();
        $result = $parser->parse($contents);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testParserGracefullyHandlesBadAttributeSyntax()
    {
        $file = __DIR__ . '/' . '../data/incorrectAttributeUsage.php';
        $contents = file_get_contents($file);

        $parser = $this->buildAstParser();
        $result = $parser->parse($contents);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testEmptyFileReturnsEmptyResults()
    {
        $file = __DIR__ . '/' . self::EMPTY_FILE;
        $contents = file_get_contents($file);

        $parser = $this->buildAstParser();
        $result = $parser->parse($contents);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
