<?php

namespace Omnishow\Trakt\Tests;

use Omnishow\Testing\BareTestCase;
use Omnishow\Trakt\TraktProvider;

/** omnishow/trakt's classes load with omnisense/omnishow and Symfony's HTTP client contracts alone. */
final class BareTest extends BareTestCase
{
    protected static function entryClass(): string
    {
        return TraktProvider::class;
    }

    protected static function requiredPackages(): array
    {
        return ['symfony/http-client-contracts'];
    }
}
