# Agent Notes

## Rules

- Keep changes small and scoped.
- Do not change public API responses unless requested.
- Keep SQL inside repositories.
- Keep HTTP request/response code inside controllers or HTTP helpers.
- Keep route registration in `src/Router.php`.
- Keep dependency wiring in `src/AppFactory.php`.
- Prefer typed DTOs over associative arrays between layers.
- Preserve PSR usage already present in the project.

## Commands

Run tests:

```bash
docker compose run --rm app composer test
```

Start app:

```bash
docker compose up --build
```

## Expected Test Result

```text
OK (13 tests, 73 assertions)
```
