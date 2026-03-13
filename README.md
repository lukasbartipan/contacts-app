# Contacts Import App

## Goal
Build a small Laravel 12 application for contact management with CRUD,
full-text search, and an efficient XML import (~100k records),
including import result reporting.

## Packages Used and Why
- `laravel/framework` (Laravel 12): stable base, migrations, queue, Eloquent.
- `filament/filament` (v5): fast admin UI for CRUD, tables, and uploads.
- `pestphp/pest`: fast, readable tests.
- `laravel/boost`, `laravel/pint`, `laravel/pail`: local DX and code style.
- `Egulias EmailValidator` (via Laravel): RFC email validation.

## Solution Architecture
- **Contacts**: `contacts` table with `email`, `first_name`, `last_name`, unique email.
- **Full-text**: MySQL full-text index on `email/first_name/last_name`.
- **Import**: UI upload → `ImportRun` → queued job → XMLReader stream parsing
  + batch `insertOrIgnore`.
- **Reporting**: `ImportRun` stores totals, duplicates, invalids, duration, and status.
- **Admin UI**: Filament resources for contacts and imports + dashboard widgets.

## Run
Required versions:
- PHP 8.4
- Node.js 24

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan queue:work
```

Login:
- email: `admin@example.com`
- password: `admin`

Admin:
- login: `/`
- dashboard: `/dashboard`

## Technical Challenges
- **Import performance**: 100k records without memory overflow (stream parsing, batching).
- **Validation**: RFC email + deduplication (unique index, `insertOrIgnore`).
- **UX**: import runs in the background (queue) and a report is available when finished.
- **Livewire upload limit**: limits set for larger XML files.

## RFC vs. TLD
- **RFC validation** allows emails without a TLD (e.g. `nsmith@yahoo`).
- **TLD requirement** is more practical for real addresses but stricter than RFC.

In this implementation, the **TLD requirement is enabled**
(we check that the domain contains a dot).
If validation should be strictly RFC-only, this check can be disabled
in `Contact::rules()` and in the import job.

## Conclusion
The solution meets the requirements: CRUD, full-text search, efficient XML import,
reporting, and a scalable architecture. A key decision is the choice between
strict RFC validation and the more practical TLD validation, which is currently enabled.

## Limitations
- Full-text search is optimized for MySQL.
- Import expects XML in the `data/item/email|first_name|last_name` format.
- TLD validation is stricter than pure RFC.

## Possible Extensions
- Detailed error report per row (CSV export of invalid records).
- Fuzzy search (typo tolerance and word order independence), e.g. via Laravel Scout + Meilisearch.
