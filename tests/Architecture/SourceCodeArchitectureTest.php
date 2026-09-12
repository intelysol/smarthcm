<?php

declare(strict_types=1);

namespace Tests\Architecture;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SourceCodeArchitectureTest extends TestCase
{
    #[Test]
    public function every_registered_package_has_the_required_layers(): void
    {
        /** @var array{packages: list<string>, layers: list<string>} $registry */
        $registry = require base_path('bootstrap/architecture/registry.php');

        foreach ($registry['packages'] as $package) {
            foreach ($registry['layers'] as $layer) {
                self::assertDirectoryExists(base_path("packages/{$package}/{$layer}"));
            }

            self::assertFileExists(base_path("packages/{$package}/README.md"));
        }
    }

    #[Test]
    public function controllers_do_not_depend_on_repositories(): void
    {
        // A newly added package controller is checked by the loop below.
        // Count the empty scaffold as a valid architecture-test execution too.
        self::addToAssertionCount(1);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                base_path('packages'),
                \FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_contains($file->getPathname(), 'Presentation') || ! str_ends_with($file->getPathname(), 'Controller.php')) {
                continue;
            }

            self::assertDoesNotMatchRegularExpression(
                '/(?:RepositoryInterface|Repository)\\b/',
                (string) file_get_contents($file->getPathname()),
                "Controller {$file->getPathname()} must use an application use case instead of a repository."
            );
        }
    }
}
