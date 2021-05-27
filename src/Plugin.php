<?php
/**
 * @copyright 2020 Navarr Barnier. All Rights Reserved.
 */

declare(strict_types=1);

namespace Navarr\Depends;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Navarr\Depends\Command\WhyBlockCommand;

/**
 * In charge of registering everything our plugin needs
 *
 * @dependency composer-plugin-api:^1|^2 Reliant Interfaces
 * @dependency composer/composer:^1|^2 Existence of IOInterface and Composer class
 */
class Plugin implements PluginInterface, Capable, CommandProvider
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        /* No-op */
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        /* No-op */
    }

    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => static::class,
        ];
    }

    public function getCommands(): array
    {
        return [
            new WhyBlockCommand(),
        ];
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        /* No-op */
    }
}
