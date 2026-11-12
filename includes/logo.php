<?php
/**
 * AFRICA'SPY — emblème institutionnel (SVG vectoriel, généré côté serveur).
 *
 * Concept : un cadran d'observation circulaire (graduations, réticule) enserrant
 * une Afrique géométrique facettée, traversée de lignes électroniques qui
 * convergent vers un noyau lumineux — « l'œil » analytique de l'Académie.
 * Variantes : holo (holographique), gold (or), silver (argent), mono (currentColor).
 */
declare(strict_types=1);

const AFSPY_AFRICA_PATH = 'M74.4,102.4L76.6,90.5L85.0,82.4L85.4,78.9L89.9,74.6L94.8,75.4L110.2,72.5L112.3,73.1L111.3,77.2L122.9,81.9L126.1,78.5L135.8,81.1L142.9,80.6L142.8,85.1L140.4,82.5L140.9,84.0L147.2,97.3L154.2,106.4L156.6,108.1L165.2,106.3L161.8,114.8L149.4,128.0L151.5,141.2L143.6,148.0L144.5,153.7L140.7,155.8L140.2,159.8L134.9,165.1L123.6,167.8L113.3,145.7L115.8,136.0L113.4,128.5L109.3,123.3L110.1,116.9L105.5,116.2L103.4,113.6L87.2,116.1L80.6,111.6ZM154.8,151.0L156.4,143.3L160.7,141.1L162.6,137.7L164.3,141.9L159.9,154.8L155.8,154.8Z';
const AFSPY_NODE = [133.3, 123.2];
const AFSPY_FACETS = [[74.4, 102.4], [89.9, 74.6], [111.3, 77.2], [142.9, 80.6], [147.2, 97.3], [161.8, 114.8], [144.5, 153.7], [123.6, 167.8], [109.3, 123.3], [87.2, 116.1]];

function logo_svg(string $variant = 'holo', string $class = 'logo-mark', bool $decorative = false): string
{
    static $n = 0;
    $id = 'afl' . (++$n);
    $palettes = [
        'holo'   => ['ring' => "url(#{$id}r)", 'arc' => '#D5B36A', 'fill' => "url(#{$id}f)", 'line' => '#00F0D0', 'node' => '#E8F2FA', 'glow' => '#00C8FF', 'stroke' => '#00C8FF'],
        'gold'   => ['ring' => '#D5B36A', 'arc' => '#F1DFA8', 'fill' => 'rgba(213,179,106,.14)', 'line' => '#D5B36A', 'node' => '#F7EBC8', 'glow' => '#D5B36A', 'stroke' => '#D5B36A'],
        'silver' => ['ring' => '#AEBBC9', 'arc' => '#E8F2FA', 'fill' => 'rgba(174,187,201,.12)', 'line' => '#AEBBC9', 'node' => '#FFFFFF', 'glow' => '#AEBBC9', 'stroke' => '#AEBBC9'],
        'mono'   => ['ring' => 'currentColor', 'arc' => 'currentColor', 'fill' => 'none', 'line' => 'currentColor', 'node' => 'currentColor', 'glow' => 'currentColor', 'stroke' => 'currentColor'],
    ];
    $p = $palettes[$variant] ?? $palettes['holo'];
    $a11y = $decorative ? 'aria-hidden="true" focusable="false"' : 'role="img" aria-label="Emblème AFRICA\'SPY"';

    // Graduations du cadran
    $ticks = '';
    for ($i = 0; $i < 72; $i++) {
        $ang = deg2rad($i * 5);
        $major = $i % 18 === 0;
        $mid = $i % 6 === 0;
        $r1 = 108;
        $r2 = $major ? 96 : ($mid ? 101 : 104);
        $ticks .= sprintf('M%.1f,%.1fL%.1f,%.1f', 120 + $r1 * sin($ang), 120 - $r1 * cos($ang), 120 + $r2 * sin($ang), 120 - $r2 * cos($ang));
    }
    // Lignes électroniques (facettes) convergeant vers le noyau
    [$nx, $ny] = AFSPY_NODE;
    $facets = '';
    $dots = '';
    foreach (AFSPY_FACETS as $k => [$x, $y]) {
        // tracé en coude façon circuit imprimé
        $mx = $k % 2 ? $x : $nx;
        $my = $k % 2 ? $ny : $y;
        $facets .= sprintf('M%.1f,%.1fL%.1f,%.1fL%.1f,%.1f', $nx, $ny, ($nx + $mx) / 2, ($ny + $my) / 2, $x, $y);
        $dots .= sprintf('<circle cx="%.1f" cy="%.1f" r="1.3"/>', $x, $y);
    }
    // Arcs dorés (quadrants)
    $arc = static function (float $a0, float $a1, float $r): string {
        $p0 = [120 + $r * sin(deg2rad($a0)), 120 - $r * cos(deg2rad($a0))];
        $p1 = [120 + $r * sin(deg2rad($a1)), 120 - $r * cos(deg2rad($a1))];
        return sprintf('M%.1f,%.1fA%d,%d 0 0 1 %.1f,%.1f', $p0[0], $p0[1], $r, $r, $p1[0], $p1[1]);
    };
    $arcs = $arc(20, 70, 114) . $arc(110, 160, 114) . $arc(200, 250, 114) . $arc(290, 340, 114);

    $defs = $variant === 'holo' ? <<<SVG
<defs>
  <linearGradient id="{$id}r" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#00C8FF"/><stop offset=".55" stop-color="#00F0D0"/><stop offset="1" stop-color="#865DFF"/></linearGradient>
  <linearGradient id="{$id}f" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#087CFF" stop-opacity=".38"/><stop offset="1" stop-color="#00F0D0" stop-opacity=".08"/></linearGradient>
  <radialGradient id="{$id}g"><stop offset="0" stop-color="#E8F2FA"/><stop offset=".35" stop-color="#00C8FF" stop-opacity=".9"/><stop offset="1" stop-color="#00C8FF" stop-opacity="0"/></radialGradient>
</defs>
SVG : '';
    $glow = $variant === 'holo' ? sprintf('<circle class="lm-glow" cx="%.1f" cy="%.1f" r="13" fill="url(#%sg)"/>', $nx, $ny, $id) : '';

    $africa = AFSPY_AFRICA_PATH;
    return <<<SVG
<svg class="{$class}" viewBox="0 0 240 240" xmlns="http://www.w3.org/2000/svg" {$a11y}>{$defs}
  <g class="lm-dial" fill="none" stroke="{$p['ring']}">
    <circle cx="120" cy="120" r="108" stroke-width="1.4" opacity=".55"/>
    <path d="{$ticks}" stroke-width="1.4"/>
    <circle class="lm-inner" cx="120" cy="120" r="90" stroke-width=".8" stroke-dasharray="2 5" opacity=".6"/>
  </g>
  <path class="lm-arcs" d="{$arcs}" fill="none" stroke="{$p['arc']}" stroke-width="3" stroke-linecap="round"/>
  <path class="lm-cross" d="M120,4V22M120,218V236M4,120H22M218,120H236" stroke="{$p['arc']}" stroke-width="2"/>
  <g class="lm-core" transform="translate(120 120) scale(1.5) translate(-120 -120)">
  <path class="lm-africa" d="{$africa}" fill="{$p['fill']}" stroke="{$p['stroke']}" stroke-width="1.5" stroke-linejoin="round"/>
  <path class="lm-lines" d="{$facets}" fill="none" stroke="{$p['line']}" stroke-width=".6" opacity=".7"/>
  <g class="lm-dots" fill="{$p['line']}">{$dots}</g>
  {$glow}
  <circle class="lm-iris" cx="{$nx}" cy="{$ny}" r="7" fill="none" stroke="{$p['glow']}" stroke-width="1"/>
  <circle class="lm-node" cx="{$nx}" cy="{$ny}" r="3" fill="{$p['node']}"/>
  </g>
</svg>
SVG;
}

/** Bloc logo complet (emblème + logotype) pour l'en-tête et le pied de page. */
function logo_lockup(string $variant = 'holo', string $extraClass = ''): string
{
    return '<span class="brand ' . e($extraClass) . '">' . logo_svg($variant, 'brand__mark', true)
        . '<span class="brand__type"><span class="brand__name">AFRICA<span class="brand__apos">\'</span>SPY</span>'
        . '<span class="brand__sub">Intelligence Academy</span></span></span>';
}
