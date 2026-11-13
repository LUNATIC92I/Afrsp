<?php
/**
 * Typographie sobre : remplace les tirets longs (—, –), points médians (·), flèches,
 * puces et pictogrammes décoratifs par la ponctuation française usuelle.
 *
 * Usage (installation existante, contenus déjà en base) :
 *   php database/typography-cleanup.php           # aperçu du nombre de champs concernés
 *   php database/typography-cleanup.php --apply   # applique les remplacements
 *
 * Les factures et avoirs déjà émis ne sont jamais modifiés (pièces comptables figées).
 */
declare(strict_types=1);

function plain_typography(string $s): string
{
    $map = [
        "AFRICA'SPY — Intelligence Academy" => "AFRICA'SPY Intelligence Academy",
        "AFRICA\\'SPY — Intelligence Academy" => "AFRICA\\'SPY Intelligence Academy",
        '&nbsp;·&nbsp;' => '&nbsp;|&nbsp;',
        '&mdash;' => '-', '&ndash;' => '-', '&hellip;' => '...', '&rarr;' => '-&gt;', '&larr;' => '&lt;-',
        '(↗ → ↘)' => '(hausse, stable, baisse)', '↗' => '', '↘' => '', '↑' => '', '↓' => '',
        '…' => '...', '−' => '-', '≈ ' => 'environ ', '─►' => '->', '►' => '>', '─' => '-',
    ];
    $s = strtr($s, $map);

    // Encadrement d'un libellé d'option : « — Aucun … — » → « Aucun … »
    $s = preg_replace('/([\'">])— ([^—\'"<\n]+?) —([\'"<])/u', '$1$2$3', $s);
    // Incise encadrée par deux tirets → parenthèses
    $s = preg_replace('/ — ((?:[^—.!?;\n\'"<>]|(?<=\p{L})\'(?=\p{L})){2,220}?) —(,?) /u', ' ($1)$2 ', $s);
    // Tiret isolé servant de valeur vide : '—', >—<
    $s = preg_replace('/([\'">])—([\'"<])/u', '$1-$2', $s);
    // Tiret en début de chaîne : '— Sans réponse' → 'Sans réponse'
    $s = preg_replace('/([\'">])— /u', '$1', $s);
    // Tiret simple entre deux espaces : deux-points après un intitulé court, virgule sinon
    $out = '';
    $parts = preg_split('/ — /u', $s);
    foreach ($parts as $i => $part) {
        if ($i === 0) { $out = $part; continue; }
        // Texte qui précède, depuis la dernière limite de phrase ou de chaîne
        $tail = preg_split('/[.!?;:(\[>\n"]|(?<![\p{L}])\'|\'(?![\p{L}])/u', $out);
        $before = trim((string) end($tail));
        $words = $before === '' ? 0 : count(preg_split('/\s+/u', $before));
        $out .= ($words <= 5 ? ' : ' : ', ') . $part;
    }
    $s = $out;
    $s = str_replace(['—'], ['-'], $s);

    // Tiret demi-cadratin
    $s = preg_replace('/(\d+)\s?–\s?(\d+)/u', '$1 à $2', $s);
    $s = preg_replace('/\b([A-Z])–([A-Z])\b/u', '$1 à $2', $s);
    $s = str_replace([' – ', '–'], [', ', '-'], $s);

    // Point médian
    $s = preg_replace('/(?<!\d)(\d{2}) · /u', '$1. ', $s);
    $s = preg_replace('/(PHASE \d) · /u', '$1 : ', $s);
    $s = str_replace([' · ', '·'], [' | ', ' | '], $s);

    // Flèches et pictogrammes
    $s = preg_replace('/\s→\s*(?=[<\'"]|$)/mu', '', $s);   // « Suivant → »
    $s = preg_replace('/(?<=[>\'"])\s*←\s/u', '', $s);      // « ← Précédent »
    $s = str_replace([' → ', '→', '←', ' › ', '›'], [' puis ', '->', '<-', ' / ', '/'], $s);
    $s = str_replace(['● ', '●', '✓ ', '✓', '✗ ', '✗', '• ', '•'], '', $s);
    return $s;
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    require dirname(__DIR__) . '/includes/bootstrap.php';
    $apply = in_array('--apply', $argv, true);
    $targets = [
        'categories' => ['name', 'description'], 'courses' => ['title', 'subtitle', 'summary', 'description', 'objectives', 'prerequisites', 'audience'],
        'chapters' => ['title', 'summary'], 'lessons' => ['title', 'content'], 'questions' => ['question', 'explanation'], 'answers' => ['answer'],
        'exams' => ['title', 'description'], 'media' => ['title', 'attribution'], 'announcements' => ['title', 'body'], 'settings' => ['setting_value'],
    ];
    $total = 0;
    foreach ($targets as $table => $cols) {
        $have = array_column(q_all("SHOW COLUMNS FROM `$table`"), 'Field');
        $cols = array_values(array_intersect($cols, $have));
        $pk = $table === 'settings' ? 'setting_key' : 'id';
        if (!$cols) continue;
        foreach (q_all('SELECT `' . $pk . '`, `' . implode('`, `', $cols) . "` FROM `$table`") as $row) {
            $set = [];
            foreach ($cols as $c) {
                if ($row[$c] === null) continue;
                $new = plain_typography((string) $row[$c]);
                if ($new !== $row[$c]) $set[$c] = $new;
            }
            if (!$set) continue;
            $total += count($set);
            if ($apply) {
                q("UPDATE `$table` SET " . implode(', ', array_map(static fn ($c) => "`$c` = ?", array_keys($set))) . " WHERE `$pk` = ?", [...array_values($set), $row[$pk]]);
            }
        }
    }
    echo ($apply ? 'Champs mis à jour : ' : 'Champs à mettre à jour (aperçu, ajoutez --apply) : ') . $total . "\n";
}
