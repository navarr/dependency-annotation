<?php

namespace Navarr\Depends\Model;

use Navarr\Depends\Data\DeclaredDependency;

class LegacyParser implements ParserInterface
{
    private const INLINE_MATCH_PACKAGE = 2;
    private const INLINE_MATCH_VERSION = 3;
    private const INLINE_MATCH_REASON = 4;

    public function parse(string $file): array
    {
        $contents = file_get_contents($file);
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
     * @param array $matches
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

            $line = substr_count(mb_substr($contents, 0, $matches[0][$match][1]), "\n") + 1;

            $reason = trim($matches[static::INLINE_MATCH_REASON][$match][0]) ?? 'No reason provided';
            if (substr($reason, -2) === '*/') {
                $reason = trim(substr($reason, 0, -2));
            }

            $results[] = new DeclaredDependency(
                $file,
                $line,
                "{$file}:{$line}",
                $package,
                $version,
                $reason
            );
        }

        return $results;
    }
}
