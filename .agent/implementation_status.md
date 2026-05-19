# Implementation Status

## Done

- Active visitors query fixed.
- Segment preview endpoint implemented.
- Controllers, services, repositories, validators, and DTOs separated.
- Route registration extracted to `Router`.
- Segment preview repository split from active visitors repository.
- PSR-3 logger added.
- PSR-6 in-memory cache added.
- PSR-7 request/response interfaces used.
- PSR-17 HTTP factories wired.
- PSR-20 clock added.
- Unit and integration tests passing.

## Current Validation

```bash
docker compose run --rm app composer test
```

Expected:

```text
OK (13 tests, 73 assertions)
```

## Known Gaps

- No PHPStan yet.
- No PHPCS/PHP-CS-Fixer yet.
- Cache is not shared across processes.
- Validator unit tests can still be expanded.
