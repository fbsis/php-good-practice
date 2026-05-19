# PR Summary

## Summary

Implemented the visitor analytics challenge and refactored the app into clearer layers. The API now fixes active visitor analytics, implements segment preview, and adds lightweight PSR-based infrastructure.

## Main Changes

- Fixed active visitor query bugs.
- Implemented segment preview endpoint.
- Split controllers, services, repositories, validators, and DTOs.
- Extracted route registration to `Router`.
- Added PSR-3 logging.
- Added PSR-6 in-memory cache.
- Added PSR-17 HTTP factories.
- Added PSR-20 clock.
- Added integration and unit tests.

## Tests

```bash
docker compose run --rm app composer test
```

Result:

```text
OK (13 tests, 73 assertions)
```

## Notes

- Cache is intentionally in-memory for this small project.
- Public HTTP contracts were preserved.
- Future improvements: PHPStan, PSR-12 tooling, validator unit tests.
