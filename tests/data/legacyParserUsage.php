<?php

/**
 * @composerDependency example:^6 composerDependency with version in big doc
 * @composerDependency example:^5
 * @composerDependency example
 * @composerDependency example composerDependency without version in big doc
 *
 * @dependency example:^7 dependency with version in big doc
 * @dependency example:^8
 * @dependency example
 * @dependency example dependency without version in big doc
 */
class TestClassD { // @dependency example:^13 dependency with version in slash doc after other content
    /** @dependency example:^9 dependency with version in small doc */
    /** @dependency example:^11 */
    /** @dependency example dependency without version in small doc */
    /** @dependency example */
    /** @composerDependency example:^10 composerDependency with version in small doc */
    /** @composerDependency example composerDependency without version in small doc */
    /** @composerDependency example:^12 */
    /** @composerDependency example */
}
