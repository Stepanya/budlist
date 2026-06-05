# Budlist

A personal budgeting / to-do app with three list types — **Budget**, **Loan**, and **Shopping**.
Each type holds named lists; each list holds items with a price, quantity, note, and (for loans)
a due date. Built as a single-page app: every action (switch tab, add, edit, toggle, rename,
delete, drag-reorder, paginate, search) goes through jQuery `$.ajax` to small JSON endpoints —
no full-page reloads.

The app is gated behind a login. Accounts are confirmed with a one-time code emailed at
registration, and every list/item is scoped to its owner.

## Stack

- **Laravel 12** (PHP 8.2)
- **MySQL**
- **Bootstrap 5.3.3**, **jQuery 3.7.1**, **SortableJS**, **paginationjs** — all **self-hosted**
  in `public/vendor/` (no CDN, no npm/Vite build step). Fonts (Fraunces + Outfit) are self-hosted too.
- Front-end code lives in plain static files: [public/js/app.js](public/js/app.js) and
  [public/css/app.css](public/css/app.css).

## Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- Composer 2
- MySQL (local dev was tested with XAMPP MySQL on `127.0.0.1:3306`)

## Setup & run (local)

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the .env (already MySQL-configured in .env.example) and an app key
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# 3. Create the empty database, then point .env at it
#    Defaults expected by .env: DB_DATABASE=budlist, DB_USERNAME=root, DB_PASSWORD= (empty), 127.0.0.1:3306
#    e.g. via the mysql client:
#        mysql -u root -e "CREATE DATABASE budlist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Run migrations
php artisan migrate

# 5. (Optional) Import the legacy data from the phpMyAdmin export
php artisan budlist:import --dry-run   # parse + show counts, writes nothing
php artisan budlist:import             # import for real (idempotent, preserves IDs)

# 6. Serve
php artisan serve
```

Then open **http://127.0.0.1:8000**. You'll be sent to **/login** — use **Create an account** to
register. The **first** account to verify its OTP inherits all the imported (ownerless) lists;
anyone who registers after that starts with an empty workspace.

> No front-end build is required — there is no `npm install` / Vite step. The libraries and
> fonts are committed under `public/vendor/` and served directly.

## Accounts, email OTP & "keep me signed in"

- **Register** (name / email / password) → the app emails a **6-digit code** (valid 10 minutes) →
  enter it on the verify screen to finish. Unverified accounts can't sign in until they verify.
- **Email delivery** uses Laravel's mailer. Out of the box `MAIL_MAILER=log`, so the code is
  written to `storage/logs/laravel.log` (no real email is sent — fine for local testing). To send
  real OTPs from a personal Gmail, set the `MAIL_*` SMTP values shown in `.env.example` (Gmail
  needs a **App password**, not your normal password). **Do not commit real mail credentials.**
- **Keep me signed in** on the login form uses Laravel's remember-me cookie, so you stay logged in
  across browser restarts for a long time (until you sign out). Leave it unticked for a session
  that ends when the browser session expires.
- Sign out with the door icon in the header.

## Data import

`php artisan budlist:import` maps the legacy two-table structure (`user_lists` / `list_items`)
from the phpMyAdmin export at `current build/webchat.sql` into the new `lists` / `tasks` schema,
**preserving original IDs**. It is **idempotent** (upsert by primary key) and verifies that the
old export counts match the new database (79 lists / 798 tasks / 738 done).

- `--dry-run` — parse and print counts without touching the database (no DB connection needed).
- `--file=PATH` — use a different `.sql` export.

## Schema

| `lists` | `tasks` |
|---|---|
| `id`, `user_id` → users (nullable), `list_type` (budget/loan/shopping), `title`, `budget`, `position`, timestamps | `id`, `list_id` → lists (cascade), `text`, `amount`, `quantity`, `note`, `due_date`, `done`, `position`, timestamps |

`users` also carries `otp_code` + `otp_expires_at` (alongside the stock `email_verified_at`).

Models: `App\Models\TaskList` (→ `lists` table) and `App\Models\Task`. Both apply a global scope so
every web query is automatically restricted to the signed-in user; new lists are stamped with their
owner on create. The CLI importer uses the query builder directly, so it is unaffected by the scope.

## API

Auth routes (full-page Blade forms, CSRF-protected):

| Method | Path | Purpose |
|---|---|---|
| GET / POST | `/login`          | Sign in (`remember` checkbox = persistent login) |
| GET / POST | `/register`       | Create an account → issues an email OTP |
| GET / POST | `/verify`         | Enter the 6-digit OTP to verify + sign in |
| POST       | `/verify/resend`  | Email a fresh OTP |
| POST       | `/logout`         | Sign out |

The app shell (`/`) and all `/api/*` routes below are behind the `auth` middleware (session + CSRF
via the `<meta name="csrf-token">` tag + `$.ajaxSetup`). An AJAX call that hits an expired session
gets a 401/419 and the front-end redirects to `/login`.

| Method | Path | Purpose |
|---|---|---|
| GET    | `/api/lists/{type}`                   | Lists of a type (with item counts + totals) |
| POST   | `/api/lists`                          | Create a list (at the top) |
| POST   | `/api/lists/{list}/duplicate`         | Duplicate a list + its items |
| PATCH  | `/api/lists/{list}`                   | Rename / set budget |
| DELETE | `/api/lists/{list}`                   | Delete a list (cascades to its tasks) |
| PATCH  | `/api/lists/reorder`                  | Persist list order (`{ ids: [...] }`) |
| GET    | `/api/lists/{list}/tasks`             | Tasks of a list |
| PATCH  | `/api/lists/{list}/tasks/reorder`     | Persist task order within a list |
| POST   | `/api/tasks`                          | Add an item |
| PATCH  | `/api/tasks/{task}`                   | Update an item (toggle done, price, etc.) |
| DELETE | `/api/tasks/{task}`                   | Delete an item |

## Features

- **Accounts with email OTP** — register, verify a 6-digit emailed code, then sign in. Optional
  persistent "keep me signed in". Data is scoped per user.
- **Optimistic UI** with rollback on failure for every action.
- **Light / dark themes** driven entirely by CSS variables, persisted to `localStorage` and
  applied before first paint (no theme flash). Toggle in the header.
- **Drag-to-reorder** lists and items (SortableJS), persisted via the reorder endpoints.
- **Pagination** — 20 lists per page (paginationjs).
- **Search** — filter the current type's lists by title.
- **Inline price edit** — click an item's price to edit just the price.

## Notes

- `.env` is gitignored; production credentials are not committed. Configure the production `.env`
  on the server.
- `current build/` is the previous version (kept as reference + the source of the data import);
  it is not part of the running app.
