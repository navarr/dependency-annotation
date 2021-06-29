<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Model;

use Navarr\Depends\Data\DeclaredDependency;

class LegacyParser implements ParserInterface
{
    private const INLINE_MATCH_PACKAGE = 2;
    private const INLINE_MATCH_VERSION = 3;
    private const INLINE_MATCH_REASON = 4;

    /** @var IssueHandlerInterface */
    private $issueHandler;

    public function parse(string $file): array
    {
        $contents = file_get_contents($file);
        if ($contents === false) {
            $this->handleIssue("Could not read contents of file '{$file}'");
            return [];
        }

        $results = [[]];

        // Double slash comments
        preg_match_all(
            '#//\s+@(dependency|composerDependency)\s+([^:\s]+):(\S+)\s(.*)?(?=$)#im',
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        $results[] = $this->processMatches($matches, $contents, $file);

        // Slash asterisk comments.  We're cheating here and only using an asterisk as indicator.  False
        // positives possible.
        preg_match_all(
            '#\*\s+@(dependency|composerDependency)\s+([^:]+):(\S+) ?(.*)$#im',
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        $results[] = $this->processMatches($matches, $contents, $file);

        return array_merge(...$results);
    }

    /**
     * @param string[][] $matches
     * @param string $contents
     * @param string $file
     * @return DeclaredDependency[]
     */
    private function processMatches(array $matches, string $contents, string $file): array
    {
        $results = [];

        $matchCount = count($matches[0]) ?? 0;
        for ($match = 0; $match < $matchCount; ++$match) {
            $package = strtolower($matches[static::INLINE_MATCH_PACKAGE][$match][0]);
            $version = $matches[static::INLINE_MATCH_VERSION][$match][0];

            $line = substr_count(mb_substr($contents, 0, (int)$matches[0][$match][1]), "\n") + 1;

            $reason = trim($matches[static::INLINE_MATCH_REASON][$match][0]) ?? 'No reason provided';
            if (substr($reason, -2) === '*/') {
                $reason = trim(substr($reason, 0, -2));
            }

            $results[] = new DeclaredDependency(
                $file,
                (string)$line,
                "{$file}:{$line}",
                $package,
                $version,
                $reason
            );
        }

        return $results;
    }

    public function setIssueHandler(IssueHandlerInterface $handler): void
    {
        $this->issueHandler = $handler;
    }

    private function handleIssue(string $description): void
    {
        if ($this->issueHandler !== null) {
            $this->issueHandler->execute($description);
        }
    }
}
