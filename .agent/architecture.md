# Architecture

Small Slim PHP API with explicit layers.

## Flow

```text
public/index.php
  -> AppFactory
  -> Router
  -> Controller
  -> Service
  -> Repository
  -> MySQL
```

## Layers

- `src/Controllers`: HTTP request/response handling.
- `src/Services`: application use cases and cache orchestration.
- `src/Repositories`: SQL and persistence.
- `src/Validation`: request validation.
- `src/Dto`: typed input objects.
- `src/Cache`: PSR-6 cache adapter.
- `src/Clock`: PSR-20 clock.
- `src/Logging`: PSR-3 logger.
- `src/Http`: JSON response helper and PSR-17 factories.

## PSRs

- PSR-3: logging.
- PSR-4: autoloading.
- PSR-6: cache.
- PSR-7: HTTP messages.
- PSR-17: HTTP factories.
- PSR-20: clock.

## Tradeoffs

- Cache is in-memory and process-local.
- No dependency injection container.
- Environment reads are still done in factories/AppFactory.
