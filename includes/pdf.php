<?php
/**
 * AFRICA'SPY — génération PDF (Dompdf) : supports de cours et certificats.
 */
declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

function pdf_engine(string $orientation = 'portrait'): Dompdf
{
    $opt = new Options();
    $opt->setChroot([APP_ROOT]);
    $opt->setIsRemoteEnabled(false);
    $opt->setIsPhpEnabled(false);
    $opt->setIsJavascriptEnabled(false);
    $opt->setFontDir(APP_ROOT . '/storage/fonts');
    $opt->setFontCache(APP_ROOT . '/storage/fonts');
    $opt->setTempDir(APP_ROOT . '/storage/cache');
    $opt->setDefaultFont('Inter');
    $opt->setDpi(150);
    $pdf = new Dompdf($opt);
    $pdf->setPaper('A4', $orientation);
    return $pdf;
}

/** Déclarations @font-face pointant vers les TTF locaux (embarqués dans le PDF). */
function pdf_fonts_css(): string
{
    $dir = 'file://' . APP_ROOT . '/resources/pdf-fonts/';
    $faces = [
        ['Orbitron', 500, 'normal', 'Orbitron-500.ttf'], ['Orbitron', 700, 'normal', 'Orbitron-700.ttf'], ['Orbitron', 900, 'normal', 'Orbitron-900.ttf'],
        ['Rajdhani', 600, 'normal', 'Rajdhani-600.ttf'], ['Inter', 400, 'normal', 'Inter-400.ttf'], ['Inter', 600, 'normal', 'Inter-600.ttf'],
        ['Inter', 700, 'normal', 'Inter-600.ttf'], ['ShareTechMono', 400, 'normal', 'ShareTechMono-400.ttf'],
        ['Cormorant', 600, 'normal', 'CormorantGaramond-600.ttf'], ['Cormorant', 500, 'italic', 'CormorantGaramond-500i.ttf'],
        ['GreatVibes', 400, 'normal', 'GreatVibes-400.ttf'],
    ];
    $css = '';
    foreach ($faces as [$fam, $w, $st, $file]) {
        $css .= "@font-face{font-family:'$fam';font-weight:$w;font-style:$st;src:url('{$dir}{$file}') format('truetype');}";
    }
    return $css;
}

function svg_data_uri(string $svg): string
{
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Support de cours complet (PDF portrait).
 * $course : clés title, subtitle, code, summary, description, objectives, skills, prerequisites, level, duration_hours, category_name
 * $chapters : [ ['title','summary','lessons'=>[['title','type','content']]] ]
 */
function course_support_html(array $course, array $chapters): string
{
    require_once APP_ROOT . '/includes/logo.php';
    $logo = svg_data_uri(str_replace('class="pdf-logo" ', '', logo_svg('gold', 'pdf-logo', true)));
    $list = static fn (?string $t) => implode('', array_map(static fn ($l) => '<li>' . e($l) . '</li>', lines($t)));
    $toc = '';
    $body = '';
    foreach ($chapters as $i => $ch) {
        $n = sprintf('%02d', $i + 1);
        $toc .= '<tr><td class="n">' . $n . '</td><td>' . e($ch['title']) . '</td></tr>';
        $body .= '<div class="chapter"><p class="kick">CHAPITRE ' . $n . '</p><h2>' . e($ch['title']) . '</h2>';
        if (!empty($ch['summary'])) {
            $body .= '<p class="sum">' . e($ch['summary']) . '</p>';
        }
        foreach ($ch['lessons'] as $l) {
            if (in_array($l['type'], ['video', 'document'], true)) {
                continue;
            }
            $body .= '<h3><span>' . e(lesson_type_label($l['type'])) . '</span> ' . e($l['title']) . '</h3>' . sanitize_html($l['content'] ?? '');
        }
        $body .= '</div>';
    }
    $fonts = pdf_fonts_css();
    $date = fmt_date(gmdate('Y-m-d H:i:s'));
    return <<<HTML
<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>
{$fonts}
@page { margin: 22mm 18mm 20mm; }
body { font-family: Inter; font-size: 10.2pt; line-height: 1.55; color: #1b2430; }
.cover { position: relative; height: 245mm; page-break-after: always; }
.cover .band { position: absolute; left: -18mm; right: -18mm; top: -22mm; height: 120mm; background: #05070B; }
.cover img { position: absolute; left: 0; top: 8mm; width: 34mm; }
.cover .brand { position: absolute; left: 40mm; top: 16mm; font-family: Orbitron; font-weight: 900; font-size: 17pt; letter-spacing: 3pt; color: #E8F2FA; }
.cover .brand small { display: block; font-family: ShareTechMono; font-size: 8pt; letter-spacing: 3pt; color: #AEBBC9; font-weight: 400; margin-top: 2mm; }
.cover .code { position: absolute; left: 0; top: 60mm; font-family: ShareTechMono; color: #D5B36A; font-size: 10pt; letter-spacing: 2pt; }
.cover h1 { position: absolute; left: 0; right: 0; top: 68mm; font-family: Orbitron; font-weight: 700; font-size: 22pt; line-height: 1.2; color: #FFFFFF; margin: 0; }
.cover .sub { position: absolute; left: 0; right: 10mm; top: 112mm; font-size: 12pt; color: #2d3a4a; }
.cover table.meta { position: absolute; left: 0; top: 140mm; border-collapse: collapse; }
.cover table.meta td { padding: 2mm 8mm 2mm 0; font-size: 9.5pt; border: 0; border-bottom: 1px solid #e3e7ec; }
.cover table.meta td.k { font-family: ShareTechMono; color: #6b7686; letter-spacing: 1pt; font-size: 8pt; }
.cover .foot { position: absolute; left: 0; right: 0; bottom: 0; font-size: 8pt; color: #6b7686; border-top: 1px solid #c9ced6; padding-top: 3mm; }
h2 { font-family: Orbitron; font-weight: 700; font-size: 15pt; color: #05070B; margin: 0 0 3mm; }
h3 { font-family: Rajdhani; font-weight: 600; font-size: 13pt; color: #0B1018; margin: 7mm 0 2mm; border-bottom: 1px solid #d9dee5; padding-bottom: 1mm; }
h3 span { font-family: ShareTechMono; font-size: 8pt; color: #087CFF; letter-spacing: 1pt; text-transform: uppercase; }
.kick { font-family: ShareTechMono; color: #B08A3E; letter-spacing: 2pt; font-size: 8.5pt; margin: 0 0 1mm; }
.sum { color: #4a5666; font-style: italic; }
.chapter { page-break-before: always; }
.intro h2 { margin-top: 0; }
ul, ol { margin: 0 0 3mm; padding-left: 6mm; }
li { margin-bottom: 1mm; }
table { width: 100%; border-collapse: collapse; margin: 3mm 0 4mm; font-size: 9pt; }
th, td { border: 1px solid #c9ced6; padding: 1.6mm 2.2mm; text-align: left; vertical-align: top; }
th { background: #EEF3F8; font-family: Rajdhani; font-weight: 600; font-size: 9.5pt; }
blockquote { margin: 3mm 0; padding: 3mm 4mm; border-left: 3px solid #D5B36A; background: #FBF6EA; }
.callout, .note { padding: 3mm 4mm; border: 1px solid #9fd8ef; background: #EFF9FD; margin: 3mm 0; }
.warning { padding: 3mm 4mm; border: 1px solid #f1a3ad; background: #FFF2F4; margin: 3mm 0; }
pre { font-family: ShareTechMono; font-size: 8pt; background: #0B1018; color: #CFE9F5; padding: 3mm; white-space: pre-wrap; }
code { font-family: ShareTechMono; font-size: 9pt; color: #087CFF; }
pre code { color: #CFE9F5; }
table.toc td { border: 0; border-bottom: 1px dotted #c9ced6; padding: 2mm 0; font-size: 10.5pt; }
table.toc td.n { width: 12mm; font-family: ShareTechMono; color: #B08A3E; }
.cols td { width: 50%; }
</style></head><body>
<div class="cover"><div class="band"></div>
<img src="{$logo}" alt="">
<div class="brand">AFRICA'SPY<small>INTELLIGENCE ACADEMY</small></div>
<div class="code">{$course['code']} · SUPPORT DE COURS ET FICHE DE SYNTHÈSE</div>
<h1>{$course['title_e']}</h1>
<div class="sub">{$course['subtitle_e']}</div>
<table class="meta"><tr><td class="k">DOMAINE</td><td>{$course['category_e']}</td></tr><tr><td class="k">NIVEAU</td><td>{$course['level_e']}</td></tr><tr><td class="k">DURÉE INDICATIVE</td><td>{$course['duration_hours']} heures</td></tr><tr><td class="k">ÉDITION</td><td>{$date}</td></tr></table>
<div class="foot">Document pédagogique réservé aux apprenants inscrits. Les scénarios, organisations et données cités dans les exercices et études de cas sont fictifs. AFRICA'SPY est un organisme de formation privé ; ce support ne vaut pas diplôme.</div>
</div>
<div class="intro"><h2>Présentation</h2>{$course['description_h']}
<table class="cols"><tr><th>Objectifs pédagogiques</th><th>Compétences visées</th></tr><tr><td><ul>{$list($course['objectives'])}</ul></td><td><ul>{$list($course['skills'])}</ul></td></tr></table>
<h3><span>Prérequis</span></h3><ul>{$list($course['prerequisites'])}</ul>
<h3><span>Sommaire</span></h3><table class="toc">{$toc}</table></div>
{$body}
</body></html>
HTML;
}

function render_course_support_pdf(array $course, array $chapters): string
{
    $course['title_e'] = e($course['title']);
    $course['subtitle_e'] = e($course['subtitle'] ?? '');
    $course['category_e'] = e($course['category_name'] ?? '');
    $course['level_e'] = e(level_label($course['level']));
    $course['description_h'] = sanitize_html($course['description'] ?? '');
    $pdf = pdf_engine();
    $pdf->loadHtml(course_support_html($course, $chapters), 'UTF-8');
    $pdf->render();
    $canvas = $pdf->getCanvas();
    $canvas->page_text(520, 810, '{PAGE_NUM} / {PAGE_COUNT}', null, 7, [0.45, 0.5, 0.56]);
    $canvas->page_text(50, 810, "AFRICA'SPY · " . $course['code'], null, 7, [0.45, 0.5, 0.56]);
    return $pdf->output();
}

/** QR Code (PNG data URI) pointant vers l'URL de vérification. */
function qr_data_uri(string $data): string
{
    $opts = new \chillerlan\QRCode\QROptions([
        'outputType'   => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
        'eccLevel'     => \chillerlan\QRCode\Common\EccLevel::M,
        'scale'        => 8,
        'quietzoneSize' => 1,
        'outputBase64' => true,
        'bgColor'      => [255, 255, 255],
        'moduleValues' => [
            \chillerlan\QRCode\Data\QRMatrix::M_DATA_DARK => [5, 7, 11], \chillerlan\QRCode\Data\QRMatrix::M_FINDER_DARK => [5, 7, 11],
            \chillerlan\QRCode\Data\QRMatrix::M_ALIGNMENT_DARK => [5, 7, 11], \chillerlan\QRCode\Data\QRMatrix::M_TIMING_DARK => [5, 7, 11],
            \chillerlan\QRCode\Data\QRMatrix::M_FORMAT_DARK => [5, 7, 11], \chillerlan\QRCode\Data\QRMatrix::M_VERSION_DARK => [5, 7, 11],
            \chillerlan\QRCode\Data\QRMatrix::M_FINDER_DOT => [5, 7, 11],
        ],
    ]);
    return (new \chillerlan\QRCode\QRCode($opts))->render($data);
}

/**
 * Certificat (A4 paysage, fond sombre, finitions dorées).
 * $c : certificate_no, holder_name, course_title, course_code, score, issued_at
 */
function certificate_html(array $c): string
{
    require_once APP_ROOT . '/includes/logo.php';
    $logo = svg_data_uri(logo_svg('gold', 'x', true));
    $verifyUrl = absolute_url('verify-certificate.php', ['code' => $c['certificate_no']]);
    $qr = qr_data_uri($verifyUrl);
    $fonts = pdf_fonts_css();
    $name = e($c['holder_name']);
    $course = e($c['course_title']);
    $code = e($c['course_code'] ?? '');
    $no = e($c['certificate_no']);
    $date = e(fmt_date($c['issued_at']));
    $score = e(number_format((float) $c['score'], 0)) . ' %';
    $signer = e(setting('certificate_signatory_name', 'Direction académique'));
    $signerTitle = e(setting('certificate_signatory_title', "Direction académique AFRICA'SPY"));
    $fictitious = setting('signature_is_fictitious', '1') === '1';
    $sigNote = $fictitious ? '<div class="signote">Signature institutionnelle fictive — aucun signataire nominatif désigné</div>' : '';
    $verifyShort = e(preg_replace('#^https?://#', '', $verifyUrl));
    // Motif électronique discret (lignes de circuit) en SVG
    $traces = '';
    for ($i = 0; $i < 9; $i++) {
        $y = 30 + $i * 22;
        $traces .= "<path d='M0,{$y}H" . (60 + $i * 9) . 'l14,14H' . (150 + $i * 7) . "' fill='none' stroke='#D5B36A' stroke-width='0.7'/><circle cx='" . (150 + $i * 7) . "' cy='" . ($y + 14) . "' r='2' fill='#D5B36A'/>";
    }
    $circuit = svg_data_uri("<svg xmlns='http://www.w3.org/2000/svg' width='240' height='240' viewBox='0 0 240 240'>{$traces}</svg>");
    return <<<HTML
<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>
{$fonts}
@page { margin: 0; size: A4 landscape; }
body { margin: 0; font-family: Inter; color: #E8F2FA; background: #070A10; }
.page { position: relative; width: 297mm; height: 209mm; background: #070A10; overflow: hidden; }
.f1 { position: absolute; left: 7mm; top: 7mm; right: 7mm; bottom: 7mm; border: 1.2pt solid #D5B36A; }
.f2 { position: absolute; left: 10mm; top: 10mm; right: 10mm; bottom: 10mm; border: 0.4pt solid #8A7345; }
.corner { position: absolute; width: 16mm; height: 16mm; border-color: #F1DFA8; border-style: solid; }
.c1 { left: 5mm; top: 5mm; border-width: 2.4pt 0 0 2.4pt; } .c2 { right: 5mm; top: 5mm; border-width: 2.4pt 2.4pt 0 0; }
.c3 { left: 5mm; bottom: 5mm; border-width: 0 0 2.4pt 2.4pt; } .c4 { right: 5mm; bottom: 5mm; border-width: 0 2.4pt 2.4pt 0; }
.circ1 { position: absolute; left: 10mm; top: 10mm; width: 70mm; opacity: .35; }
.circ2 { position: absolute; right: 10mm; bottom: 10mm; width: 70mm; opacity: .35; }
.mark { position: absolute; left: 128.5mm; top: 15mm; width: 40mm; }
.brand { position: absolute; left: 0; right: 0; top: 57mm; text-align: center; font-family: Orbitron; font-weight: 900; font-size: 14pt; letter-spacing: 6pt; color: #F1DFA8; }
.brand small { display: block; font-family: ShareTechMono; font-weight: 400; font-size: 7.5pt; letter-spacing: 4pt; color: #AEBBC9; margin-top: 1.5mm; }
.title { position: absolute; left: 0; right: 0; top: 76mm; text-align: center; font-family: Orbitron; font-weight: 700; font-size: 21pt; letter-spacing: 7pt; color: #FFFFFF; }
.lead { position: absolute; left: 0; right: 0; top: 91mm; text-align: center; font-family: Cormorant; font-style: italic; font-weight: 500; font-size: 14pt; color: #C9D5E1; }
.name { position: absolute; left: 30mm; right: 30mm; top: 99mm; text-align: center; font-family: Cormorant; font-weight: 600; font-size: 31pt; color: #F1DFA8; border-bottom: 0.6pt solid #8A7345; padding-bottom: 2mm; }
.for { position: absolute; left: 0; right: 0; top: 122mm; text-align: center; font-size: 10pt; color: #AEBBC9; }
.course { position: absolute; left: 35mm; right: 35mm; top: 128mm; text-align: center; font-family: Rajdhani; font-weight: 600; font-size: 17pt; color: #FFFFFF; letter-spacing: 0.5pt; }
.meta { position: absolute; left: 0; right: 0; top: 146mm; text-align: center; font-family: ShareTechMono; font-size: 8.5pt; letter-spacing: 1.5pt; color: #00C8FF; }
.sig { position: absolute; left: 28mm; bottom: 24mm; width: 80mm; text-align: center; }
.sig .script { font-family: GreatVibes; font-size: 24pt; color: #D5B36A; height: 12mm; }
.sig .line { border-top: 0.6pt solid #8A7345; margin-top: 2mm; padding-top: 1.5mm; font-size: 8.5pt; color: #E8F2FA; }
.sig .line small { display: block; color: #AEBBC9; font-size: 7.5pt; }
.signote { font-family: ShareTechMono; font-size: 6.2pt; color: #FF8D9B; margin-top: 1mm; letter-spacing: .5pt; }
.qr { position: absolute; right: 28mm; bottom: 20mm; width: 70mm; text-align: center; }
.qr img { width: 27mm; height: 27mm; border: 1.5mm solid #FFFFFF; }
.qr p { margin: 1.5mm 0 0; font-family: ShareTechMono; font-size: 7pt; color: #AEBBC9; letter-spacing: .6pt; }
.no { position: absolute; left: 0; right: 0; bottom: 30mm; text-align: center; font-family: ShareTechMono; font-size: 10pt; letter-spacing: 3pt; color: #F1DFA8; }
.no small { display: block; font-size: 6.5pt; color: #8592A3; letter-spacing: 2pt; }
.legal { position: absolute; left: 70mm; right: 70mm; bottom: 13.5mm; text-align: center; font-size: 6.2pt; color: #6B7686; line-height: 1.4; }
</style></head><body><div class="page">
<img class="circ1" src="{$circuit}" alt=""><img class="circ2" src="{$circuit}" alt="" style="transform: rotate(180deg)">
<div class="f1"></div><div class="f2"></div>
<div class="corner c1"></div><div class="corner c2"></div><div class="corner c3"></div><div class="corner c4"></div>
<img class="mark" src="{$logo}" alt="">
<div class="brand">AFRICA'SPY<small>INTELLIGENCE ACADEMY</small></div>
<div class="title">CERTIFICAT DE RÉUSSITE</div>
<div class="lead">Le présent certificat atteste que</div>
<div class="name">{$name}</div>
<div class="for">a suivi avec succès le parcours pédagogique et réussi l'examen final de la formation</div>
<div class="course">{$course}</div>
<div class="meta">{$code} &nbsp;·&nbsp; SCORE {$score} &nbsp;·&nbsp; DÉLIVRÉ LE {$date}</div>
<div class="sig"><div class="script">{$signer}</div><div class="line">{$signerTitle}<small>Pour l'Académie</small></div>{$sigNote}</div>
<div class="no"><small>N° DE CERTIFICAT</small>{$no}</div>
<div class="qr"><img src="{$qr}" alt="QR"><p>VÉRIFICATION : {$verifyShort}</p></div>
<div class="legal">Certificat d'organisme de formation privé attestant la réussite d'un parcours interne. Il ne constitue ni un diplôme d'État ni une qualification officiellement reconnue. Authenticité vérifiable en ligne par numéro ou QR Code.</div>
</div></body></html>
HTML;
}

function render_certificate_pdf(array $c): string
{
    $pdf = pdf_engine('landscape');
    $pdf->loadHtml(certificate_html($c), 'UTF-8');
    $pdf->render();
    return $pdf->output();
}
