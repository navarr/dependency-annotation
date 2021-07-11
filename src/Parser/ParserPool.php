<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Parser;

use Navarr\Attribute\Dependency;
use TypeError;

class ParserPool implements ParserInterface
{
    /** @var ParserInterface[] */
    private $parsers;

    /**
     * @param ParserInterface[] $parsers
     */
    #[Dependency('php', '>=7', 'Throwing TypeError')]
    public function __construct(
        array $parsers = []
    ) {
        array_walk(
            $parsers,
            static function ($parser) {
                if (!$parser instanceof ParserInterface) {
                    throw new TypeError('All parsers must implement ' . ParserInterface::class);
                }
            }
        );
        $this->parsers = $parsers;
    }

    public function parse(string $contents): array
    {
        $result = [[]];
        foreach ($this->parsers as $parser) {
            $result[] = $parser->parse($contents);
        }

        return array_merge(...$result);
    }
}
