<?php
/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

namespace Navarr\Depends\Test\Model;

use Navarr\Depends\Data\DeclaredDependency;
use Navarr\Depends\Model\AstParser;
use Navarr\Depends\Model\FailOnIssueHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AstParserTest extends TestCase
{
    const FILE_ATTRIBUTE_USAGE = '../data/attributeUsage.php';
    const FILE_INVALID = '../data/invalidAttributeUsage.php';

    const ATTRIBUTE_USAGE_ATTRIBUTE_COUNT = 10;

    /**
     * @return DeclaredDependency[]
     */
    private function getStandardResults(): array
    {
        $parser = new AstParser();
        return $parser->parse(__DIR__ . '/' . self::FILE_ATTRIBUTE_USAGE);
    }

    /**
     * @return DeclaredDependency[]
     */
    public function testParserFindsAllAttributes(): array
    {
        $results = $this->getStandardResults();

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
                        return $result->getVersion();
                    },
                    $results
                )
            );
        }
    }

    public function testParserFailsOnFileNotFound()
    {
        $file = __DIR__ . '/' . self::FILE_ATTRIBUTE_USAGE . '-not-found';

        $parser = new AstParser();
        $parser->setIssueHandler(new FailOnIssueHandler);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not read contents of file '{$file}'");

        $parser->parse($file);
    }

    public function testParserFailsOnInvalidFile()
    {
        $file = __DIR__ . '/' . self::FILE_INVALID;

        $parser = new AstParser();
        $parser->setIssueHandler(new FailOnIssueHandler);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches("#^Could not parse contents of file#");

        $parser->parse($file);
    }

    public function testParserReturnsEmptyResultsOnNonExistentFile()
    {
        $file = __DIR__ . '/' . self::FILE_ATTRIBUTE_USAGE . '-not-found';

        $parser = new AstParser();
        $result = $parser->parse($file);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testParserReturnsEmptyResultsOnInvalidFile()
    {
        $file = __DIR__ . '/' . self::FILE_INVALID;

        $parser = new AstParser();
        $result = $parser->parse($file);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
