<?php

/**
 * @copyright 2021 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends\Controller\Common\WhyBlockUtility;

use Navarr\Depends\Controller\Common\WhyBlockUtility;
use Symfony\Component\Console\Input\InputInterface;

class OptionValuesBuilder
{
    public function buildFromInput(InputInterface $input): OptionValues
    {
        $packageToSearchFor = (string)$input->getArgument(WhyBlockUtility::ARGUMENT_PACKAGE);
        $versionToCompareTo = (string)$input->getArgument(WhyBlockUtility::ARGUMENT_VERSION);
        $outputFormat = (string)$input->getOption(WhyBlockUtility::OPTION_OUTPUT_FORMAT);

        if (!in_array($outputFormat, WhyBlockUtility::ACCEPTABLE_FORMATS)) {
            $outputFormat = 'text';
        }

        $failOnError = (bool)$input->getOption(WhyBlockUtility::OPTION_FAIL_ON_ERROR);
        $includeAnnotations = (bool)$input->getOption(WhyBlockUtility::OPTION_INCLUDE_ANNOTATIONS);

        return new OptionValues(
            $packageToSearchFor,
            $versionToCompareTo,
            $outputFormat,
            $includeAnnotations,
            $failOnError
        );
    }
}
