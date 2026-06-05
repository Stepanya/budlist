<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time importer that maps the legacy two-table structure
 * (user_lists / list_items) from the phpMyAdmin export into the new
 * lists / tasks schema, preserving original IDs.
 *
 * It is idempotent (upsert by primary key) and supports --dry-run, which
 * parses + reports counts without touching the database.
 */
class ImportBudlist extends Command
{
    protected $signature = 'budlist:import
                            {--file= : Path to the phpMyAdmin .sql export (defaults to current build/webchat.sql)}
                            {--dry-run : Parse and report counts without writing to the database}';

    protected $description = 'Import legacy Budlist data (user_lists/list_items) into the new lists/tasks schema';

    public function handle(): int
    {
        $file = $this->option('file') ?: base_path('current build/webchat.sql');

        if (! is_file($file)) {
            $this->error("SQL export not found: {$file}");
            return self::FAILURE;
        }

        $this->info('Reading ' . $file);
        $sql = file_get_contents($file);

        // ---- parse the two legacy tables --------------------------------------
        $oldLists = $this->parseInsert($sql, 'user_lists');
        $oldItems = $this->parseInsert($sql, 'list_items');

        if (empty($oldLists) || empty($oldItems)) {
            $this->error('Could not parse the expected user_lists / list_items rows from the export.');
            return self::FAILURE;
        }

        // ---- map → new rows ----------------------------------------------------
        $listIds = [];
        $lists = [];
        foreach ($oldLists as $r) {
            $listIds[(int) $r['id']] = true;
            $lists[] = [
                'id' => (int) $r['id'],
                'list_type' => strtolower($r['type']),
                'title' => $r['title'],
                'budget' => $this->numOrNull($r['budget']),
                'position' => 0, // assigned below
                'created_at' => $this->ts($r['created_at']),
                'updated_at' => $this->ts($r['updated_at']),
            ];
        }

        // Seed list order newest-first within each type (matches the old app's
        // created_at DESC). Manual drag-reorder later overwrites these.
        $byType = [];
        foreach ($lists as $i => $l) {
            $byType[$l['list_type']][] = $i;
        }
        foreach ($byType as $indexes) {
            usort($indexes, function ($a, $b) use ($lists) {
                $cmp = strcmp((string) $lists[$b]['created_at'], (string) $lists[$a]['created_at']);
                return $cmp !== 0 ? $cmp : ($lists[$b]['id'] <=> $lists[$a]['id']);
            });
            foreach ($indexes as $pos => $i) {
                $lists[$i]['position'] = $pos;
            }
        }

        $taskPosByList = [];
        $tasks = [];
        $orphans = [];
        $doneCount = 0;
        foreach ($oldItems as $r) {
            $listId = (int) $r['list_id'];
            if (! isset($listIds[$listId])) {           // item whose parent list is missing
                $orphans[] = (int) $r['id'];
                continue;
            }
            $taskPosByList[$listId] = ($taskPosByList[$listId] ?? -1) + 1;
            $done = (int) $r['checked'] === 1;
            if ($done) {
                $doneCount++;
            }
            $tasks[] = [
                'id' => (int) $r['id'],
                'list_id' => $listId,
                'text' => $r['item_name'],
                'amount' => $this->numOrNull($r['price']) ?? 0,
                'quantity' => $r['quantity'] === null ? 1 : (int) $r['quantity'],
                'note' => ($r['note'] === null || $r['note'] === '') ? null : $r['note'],
                'due_date' => $r['date'] === null ? null : substr($r['date'], 0, 10), // date only
                'done' => $done,
                'position' => $taskPosByList[$listId],
                'created_at' => $this->ts($r['created_at']),
                'updated_at' => $this->ts($r['updated_at']),
            ];
        }

        // ---- report ------------------------------------------------------------
        $byType = ['budget' => 0, 'loan' => 0, 'shopping' => 0];
        foreach ($lists as $l) {
            $byType[$l['list_type']] = ($byType[$l['list_type']] ?? 0) + 1;
        }

        $this->newLine();
        $this->line('<options=bold>Parsed from export (source of truth):</>');
        $this->table(['Metric', 'Count'], [
            ['Lists (user_lists)', count($oldLists)],
            ['  ├ budget', $byType['budget']],
            ['  ├ loan', $byType['loan']],
            ['  └ shopping', $byType['shopping']],
            ['Items (list_items)', count($oldItems)],
            ['  ├ mapped to tasks', count($tasks)],
            ['  ├ done', $doneCount],
            ['  ├ open', count($tasks) - $doneCount],
            ['  └ orphaned (no parent list)', count($orphans)],
        ]);

        if ($orphans) {
            $this->warn('Orphaned item IDs (parent list missing in export): ' . implode(', ', $orphans));
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('DRY RUN — nothing was written. Re-run without --dry-run to import.');
            return self::SUCCESS;
        }

        // ---- write (idempotent upsert, lists before tasks for FK) --------------
        $this->newLine();
        $this->info('Importing into the database…');

        DB::transaction(function () use ($lists, $tasks) {
            foreach (array_chunk($lists, 200) as $chunk) {
                DB::table('lists')->upsert(
                    $chunk,
                    ['id'],
                    ['list_type', 'title', 'budget', 'position', 'created_at', 'updated_at']
                );
            }
            foreach (array_chunk($tasks, 300) as $chunk) {
                DB::table('tasks')->upsert(
                    $chunk,
                    ['id'],
                    ['list_id', 'text', 'amount', 'quantity', 'note', 'due_date', 'done', 'position', 'created_at', 'updated_at']
                );
            }
        });

        // ---- verify ------------------------------------------------------------
        $dbLists = DB::table('lists')->count();
        $dbTasks = DB::table('tasks')->count();
        $dbDone = DB::table('tasks')->where('done', true)->count();

        $this->newLine();
        $this->line('<options=bold>Verification (old export vs new DB):</>');
        $this->table(['Metric', 'Old (export)', 'New (DB)', 'Match'], [
            ['Lists', count($lists), $dbLists, $this->mark(count($lists) === $dbLists)],
            ['Tasks', count($tasks), $dbTasks, $this->mark(count($tasks) === $dbTasks)],
            ['Done tasks', $doneCount, $dbDone, $this->mark($doneCount === $dbDone)],
        ]);

        $ok = count($lists) === $dbLists && count($tasks) === $dbTasks && $doneCount === $dbDone;
        if (! $ok) {
            $this->error('Count mismatch — review the data above.');
            return self::FAILURE;
        }

        $this->info('Import complete — all counts match.');
        return self::SUCCESS;
    }

    // =======================================================================
    // SQL export parsing
    // =======================================================================

    /**
     * Extract every row of `INSERT INTO `$table` (cols) VALUES (...),(...);`
     * from the dump as associative arrays keyed by column name. There may be
     * several INSERT statements for the same table (phpMyAdmin splits them).
     *
     * @return array<int, array<string, string|null>>
     */
    private function parseInsert(string $sql, string $table): array
    {
        $rows = [];
        $needle = 'INSERT INTO `' . $table . '`';
        $offset = 0;

        while (($pos = strpos($sql, $needle, $offset)) !== false) {
            // column list: between the first "(" after the table name and its ")"
            $colStart = strpos($sql, '(', $pos);
            $colEnd = strpos($sql, ')', $colStart);
            $colSegment = substr($sql, $colStart + 1, $colEnd - $colStart - 1);
            $columns = array_map(
                fn ($c) => trim(trim($c), '`'),
                explode(',', $colSegment)
            );

            // values start after the VALUES keyword
            $valuesPos = stripos($sql, 'VALUES', $colEnd);
            $i = $valuesPos + 6;
            [$tuples, $next] = $this->parseTuples($sql, $i);

            foreach ($tuples as $tuple) {
                if (count($tuple) !== count($columns)) {
                    continue; // skip anything that doesn't line up
                }
                $rows[] = array_combine($columns, $tuple);
            }

            $offset = $next;
        }

        return $rows;
    }

    /**
     * Parse a comma-separated list of `(v1, v2, ...)` tuples starting at $i,
     * stopping at the top-level statement terminator `;`. Understands
     * single-quoted strings with backslash and doubled-quote escaping, and the
     * bare NULL literal.
     *
     * @return array{0: array<int, array<int, string|null>>, 1: int}
     */
    private function parseTuples(string $s, int $i): array
    {
        $len = strlen($s);
        $tuples = [];

        while ($i < $len) {
            $c = $s[$i];
            if ($c === ';') {                 // end of this INSERT statement
                $i++;
                break;
            }
            if ($c === '(') {                 // a tuple begins
                $i++;
                $fields = [];
                while ($i < $len) {
                    // skip whitespace between fields
                    while ($i < $len && ($s[$i] === ' ' || $s[$i] === "\n" || $s[$i] === "\r" || $s[$i] === "\t")) {
                        $i++;
                    }
                    if ($s[$i] === "'") {     // quoted string
                        $i++;
                        $val = '';
                        while ($i < $len) {
                            $ch = $s[$i];
                            if ($ch === '\\') {            // backslash escape
                                $next = $s[$i + 1] ?? '';
                                $val .= match ($next) {
                                    'n' => "\n", 'r' => "\r", 't' => "\t",
                                    '0' => "\0", default => $next,
                                };
                                $i += 2;
                                continue;
                            }
                            if ($ch === "'") {
                                if (($s[$i + 1] ?? '') === "'") { // doubled '' => literal '
                                    $val .= "'";
                                    $i += 2;
                                    continue;
                                }
                                $i++;          // closing quote
                                break;
                            }
                            $val .= $ch;
                            $i++;
                        }
                        $fields[] = $val;
                    } else {                  // bare token: NULL or number
                        $tok = '';
                        while ($i < $len && $s[$i] !== ',' && $s[$i] !== ')') {
                            $tok .= $s[$i];
                            $i++;
                        }
                        $tok = trim($tok);
                        $fields[] = (strtoupper($tok) === 'NULL' || $tok === '') ? null : $tok;
                    }

                    // after a field: comma → next field, ) → end of tuple
                    while ($i < $len && ($s[$i] === ' ' || $s[$i] === "\n" || $s[$i] === "\r" || $s[$i] === "\t")) {
                        $i++;
                    }
                    if ($s[$i] === ',') {
                        $i++;
                        continue;
                    }
                    if ($s[$i] === ')') {
                        $i++;
                        break;
                    }
                }
                $tuples[] = $fields;
            } else {
                $i++; // skip commas/whitespace between tuples
            }
        }

        return [$tuples, $i];
    }

    private function numOrNull(?string $v): ?string
    {
        return ($v === null || $v === '') ? null : $v;
    }

    private function ts(?string $v): ?string
    {
        return ($v === null || $v === '' || str_starts_with($v, '0000')) ? null : $v;
    }

    private function mark(bool $ok): string
    {
        return $ok ? '<info>✓</info>' : '<error>✗</error>';
    }
}
