# omnishow/trakt

[omnisense/omnishow](https://github.com/glitchr-omnisense/omnishow)'s Trakt, under any PHP host,
through Trakt's public API with an application's client id (no user signed in): search, a film's or
a show's record (`extended=full`) and its people, a work or an episode by its Trakt, IMDb, TMDB or
TheTVDB number (`/search/{id_type}/{id}`), a season's episodes. Trakt serves no pictures: its
records carry the TMDB and TheTVDB numbers that lead to them (`Registry::work()` with
omnishow/tmdb).

```php
$trakt = (new TraktFactory($httpClient))->create([
    'client_id' => getenv('TRAKT_CLIENT_ID'),
    'user_agent' => 'MySite/1.0 (+https://example.org)',
]);
```

Options: `client_id` and `user_agent` (required), `api` ("https://api.trakt.tv"), `cast` (20).
Every request sends `trakt-api-version: 2`, `trakt-api-key` and the User-Agent.

Written on Symfony's HTTP client contracts, like the family: no client known that takes the
application's client without a framework.

## Trakt's terms

Trakt's [API reference](https://trakt.docs.apiary.io) documented a limit of 1,000 GET calls every 5
minutes for an application, and asks every application to send a User-Agent naming it; its
[terms](https://trakt.tv/terms) govern the data. That reference is now deprecated: Trakt's
developer guides (developer.trakt.tv, read on 2026-11-10) say 500 GET calls every 5 minutes, which
the usage policy below declares.

## Verified, and not

- Verified (the core's harness, PHP 8.4): search with the application's headers, a film by its
  IMDb number with its people, an episode, a season, not found, a refusal; `BareTest`; PHPStan
  max. The answers are **shaped after Trakt's API reference and written by hand**: no client id was
  used.
- Not verified live: no client id.

## Usage policy

The package declares Trakt's usage policy as data, in [policy.json](policy.json)
(omnisense/contracts' schema, read at its sources on 2026-11-10): rate limits 500 GET calls every 5
minutes per client ID and IP, declared as 100 a minute (the schema has no 5-minute window);
commercial and non-commercial use free under the API use policy, no fixed cache duration, no
redistribution as a feed or dataset, the credit "[App name] uses Trakt but is not endorsed or
certified by Trakt" on an About or credits page, a User-Agent naming the application and its
version. Through omnishow's registry every call is held to it: the 101st call of a minute is refused
before it reaches Trakt, a 429 and its `Retry-After` hold every next call until the wait is over
(`UsageLimitReachedException`), and a site may tighten the policy in its configuration (`'policy' =>
[...]`), never loosen it. What the terms do not say is declared unknown; the sources and the details
are in the file's `provenance`.

## Responsibility

An independent, unofficial client, affiliated with, endorsed by or sponsored by none of the services
it reaches. The MIT licence covers this code only and grants no right to the service or its data:
whoever runs it complies with the terms above and obtains what they require. The authors publish
it free of charge, make no commercial use of it and accept no liability for its use.

## Licence

MIT.
