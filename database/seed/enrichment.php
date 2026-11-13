<?php
/**
 * Assemble les enrichissements pédagogiques : approfondissements et QCM par chapitre,
 * glossaire et bibliographie ajoutés au dernier chapitre de chaque formation.
 * @return array<string, array{chapters: array<int, array{lessons: array, quiz: array}>, final: array}>
 */
$parts = array_merge(require __DIR__ . '/enrich_AB.php', require __DIR__ . '/enrich_CD.php', require __DIR__ . '/enrich_EF.php');
$gloss = require __DIR__ . '/glossaries.php';
$out = [];
foreach ($parts as $code => $def) {
    $final = [];
    if (isset($gloss[$code])) {
        $g = $gloss[$code];
        usort($g['g'], static fn ($a, $b) => strcmp(iconv('UTF-8', 'ASCII//TRANSLIT', $a[0]), iconv('UTF-8', 'ASCII//TRANSLIT', $b[0])));
        $dl = '';
        foreach ($g['g'] as [$t, $d]) {
            $dl .= '<dt>' . htmlspecialchars($t, ENT_QUOTES) . '</dt><dd>' . htmlspecialchars($d, ENT_QUOTES) . '</dd>';
        }
        $final[] = ['title' => 'Glossaire de la formation', 'type' => 'text', 'minutes' => 10,
            'content' => '<p>Les notions essentielles de la formation, à maîtriser pour l\'examen final.</p><dl class="glossary">' . $dl . '</dl>'];
        $li = '';
        foreach ($g['b'] as [$ref, $why]) {
            $li .= '<li>' . $ref . '<small>' . htmlspecialchars($why, ENT_QUOTES) . '</small></li>';
        }
        $final[] = ['title' => 'Pour aller plus loin : bibliographie et ressources', 'type' => 'text', 'minutes' => 10,
            'content' => '<p>Sélection d\'ouvrages, de normes et de ressources reconnues pour prolonger la formation. Les ressources en accès libre sont signalées.</p><ul class="biblio">' . $li . '</ul>'
                . '<div class="note">Ces références sont indiquées à titre pédagogique ; AFRICA\'SPY n\'a aucun lien d\'affiliation avec leurs auteurs ou éditeurs.</div>'];
    }
    $out[$code] = ['chapters' => $def['chapters'], 'final' => $final];
}
return $out;
