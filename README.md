# Budlist

A personal budgeting / to-do app with three list types — **Budget**, **Loan**, and **Shopping**.
Each type holds named lists; each list holds items with a price, quantity, note, and (for loans)
a due date. Built as a single-page app: every action (switch tab, add, edit, toggle, rename,
delete, drag-reorder, paginate, search) goes through jQuery `$.ajax` to small JSON endpoints —
no full-page reloads.

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

Then open **http://127.0.0.1:8000**.

> No front-end build is required — there is no `npm install` / Vite step. The libraries and
> fonts are committed under `public/vendor/` and served directly.

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
| `id`, `list_type` (budget/loan/shopping), `title`, `budget`, `position`, timestamps | `id`, `list_id` → lists (cascade), `text`, `amount`, `quantity`, `note`, `due_date`, `done`, `position`, timestamps |

Models: `App\Models\TaskList` (→ `lists` table) and `App\Models\Task`.

## API

All routes are under `/api` and use the web middleware group (session + CSRF via the
`<meta name="csrf-token">` tag + `$.ajaxSetup`).

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
