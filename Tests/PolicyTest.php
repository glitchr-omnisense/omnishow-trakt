<?php

namespace Omnishow\Trakt\Tests;

use Omnisense\Contracts\Policy\InvalidPolicyException;
use Omnisense\Contracts\Policy\Permission;
use Omnishow\Exception\OmnishowException;
use Omnishow\Exception\UsageLimitReachedException;
use Omnishow\Model\WorkRef;
use Omnishow\Registry;
use Omnishow\Testing\RecordedHttpClient;
use Omnishow\Trakt\TraktFactory;
use Omnishow\Trakt\TraktProvider;
use PHPUnit\Framework\TestCase;

/**
 * TraktProvider's usage policy (policy.json) through omnishow's registry:
 * as declared, a site's limit counted with the call over it never made,
 * a 429 and its Retry-After holding every next call, a looser setting
 * refused. No network: recorded answers only.
 */
final class PolicyTest extends TestCase
{
    private const OPTIONS = ['client_id' => 'not-a-real-trakt-client', 'user_agent' => 'omnishow-tests/1.0'];

    public function testThePolicyIsTheOneDeclared(): void
    {
        $policy = (new Registry([new TraktFactory(RecordedHttpClient::routes([]))], ['films' => ['factory' => 'trakt', 'options' => self::OPTIONS]]))->policy('films');

        self::assertSame('trakt', $policy->provider);
        self::assertSame('2026-11-10', $policy->provenance->verifiedAt);
        self::assertFileExists(TraktProvider::POLICY);
        self::assertSame(['100/minute/application/*'], array_map(static fn ($l) => $l->id(), $policy->rateLimits ?? []));
        self::assertSame(Permission::Allowed, $policy->rights->commercial);
        self::assertSame(Permission::Forbidden, $policy->rights->redistribution);
        self::assertTrue($policy->requirements->acceptsUserAgent('omnishow-tests/1.0'));
    }

    public function testASitesLimitIsCountedAndTheCallOverItIsNeverMade(): void
    {
        $http = RecordedHttpClient::routes([]);
        $registry = new Registry([new TraktFactory($http)], ['films' => ['factory' => 'trakt', 'options' => self::OPTIONS, 'policy' => ['rateLimits' => [['max' => 1, 'window' => 'day', 'operations' => ['work']]]]]]);

        try {
            $registry->lookup('films')->work(WorkRef::trakt(1));
        } catch (UsageLimitReachedException $e) {
            throw $e;
        } catch (OmnishowException) {
            // Nothing recorded: the provider's own answer to that, counted all the same.
        }
        $made = \count($http->requests);
        self::assertGreaterThan(0, $made);
        try {
            $registry->lookup('films')->work(WorkRef::trakt(1));
            self::fail('The call over the site\'s limit was made.');
        } catch (UsageLimitReachedException $e) {
            self::assertInstanceOf(OmnishowException::class, $e, 'the family\'s catch holds it');
            self::assertSame('1/day/application/work', $e->limit?->id());
        }
        self::assertCount($made, $http->requests, 'the refused call reached nothing');
    }

    public function testA429AndItsRetryAfterHoldEveryCallUntilTheWaitIsOver(): void
    {
        $http = RecordedHttpClient::routes(['~.~' => [429, '{"error":"too many requests"}', ['retry-after' => ['120']]]]);
        $registry = new Registry([new TraktFactory($http)], ['films' => ['factory' => 'trakt', 'options' => self::OPTIONS]]);

        try {
            $registry->lookup('films')->work(WorkRef::trakt(1));
            self::fail('A 429 was read as an answer.');
        } catch (UsageLimitReachedException $e) {
            self::assertSame(120, $e->retryAfter);
            self::assertSame('trakt answered 429 Too Many Requests: try again in 120 s.', $e->getMessage());
        }
        try {
            $registry->lookup('films')->work(WorkRef::trakt(1));
            self::fail('A call went out while the provider asked to wait.');
        } catch (UsageLimitReachedException $e) {
            self::assertGreaterThanOrEqual(119, $e->retryAfter);
        }
        self::assertCount(1, $http->requests, 'the wait held the next request back');
    }

    public function testASiteCannotLoosenThePolicy(): void
    {
        $registry = new Registry([new TraktFactory(RecordedHttpClient::routes([]))], ['films' => ['factory' => 'trakt', 'options' => self::OPTIONS, 'policy' => ['rateLimits' => [['max' => 500, 'window' => 'minute']]]]]);

        $this->expectException(InvalidPolicyException::class);
        $this->expectExceptionMessage('100 a minute');
        $registry->lookup('films');
    }
}
