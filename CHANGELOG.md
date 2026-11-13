# Changelog

## [Unreleased]

## [1.0.0] - 2026-11-13

The first stable release.

### Release

- Requires `omnisense/contracts: ^1.0`, `omnisense/omnishow: ^1.0`: stable versions, so `composer
  require omnishow/trakt` installs in a project at Composer's default minimum-stability.
  `extra.branch-alias` names 1.x (`1.x-dev`); the CI (`.github/workflows/ci.yml`, glitchr-studio/ci's
  php-package.yml) is callable, and `release.yml` runs it on a tag before glitchr-studio/ci's
  php-release.yml.

### Changed

- Trakt's limit declared as its guides state it: 500 GET calls every 5 minutes
  (`{"max": 500, "window": "minute", "every": 5}`), where it was written as 100 a minute for want
  of a 5-minute window. Needs omnisense/contracts at 5bac28b or later (`every`; still
  `^1.0@dev`, untagged).
