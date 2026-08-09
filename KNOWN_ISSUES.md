# Known Issues — laravel-settings

_Last checked: 2026-08-02_

## Failing tests

No failing tests. `composer test:unit` (Pest) reports **5 passed** (17 assertions).

Note: the chained `composer test` script stops early because `test:refacto` (rector `--dry-run`) exits non-zero — so `test:lint`, `test:types`, and `test:unit` must be run individually to see their results (as done for this audit).

## Style / static-analysis debt

- `vendor/bin/rector --dry-run` reports **5 files** with pending refactors (`src/Facades/Settings.php`, `src/LaravelSettingsServiceProvider.php`, `src/Models/Setting.php`, `src/Settings.php`, `tests/TestCase.php`) — mostly `AddOverrideAttributeToOverriddenMethodsRector`, plus a couple of `FlipTypeControlToUseExclusiveTypeRector`/`PrivatizeFinalClassMethodRector`/`AddTypeToConstRector` changes in `Setting.php`/`Settings.php`. Run `composer refacto` to apply.
- `vendor/bin/pint --test` reports **3 files** with unapplied fixers: `src/Models/Setting.php` (`binary_operator_spaces`), `src/Commands/SettingsSetCommand.php` (`binary_operator_spaces`), `src/LaravelSettingsServiceProvider.php` (`new_with_parentheses`, `new_with_braces`, `no_whitespace_in_blank_line`). Run `composer lint` to apply.
- PHPStan (`level: max`) reports **71 errors**, and `phpstan-baseline.neon` is empty (0 bytes) — so all 71 are live, unbaselined findings, not pre-accepted debt. The bulk are in `src/Settings.php`: repeated "Access to an undefined property `Centrex\Settings\Models\Setting::$key`/`$value`" (the model likely relies on dynamic/magic properties phpstan can't see), several "mixed given" / missing generic type errors on `Collection`/`array` returns (`all()`, `autoloaded()`, `identity()`, `settingFromAttributes()`), a few "Cannot cast mixed to int/string" errors, and one `Schema::connection()` call passed a `mixed` argument.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/` — no matches).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
