# Changelog

## Unreleased (1.x)

### Changed

- Trakt's limit declared as its guides state it: 500 GET calls every 5 minutes
  (`{"max": 500, "window": "minute", "every": 5}`), where it was written as 100 a minute for want
  of a 5-minute window. Needs omnisense/contracts at 5bac28b or later (`every`; still
  `^1.0@dev`, untagged).
