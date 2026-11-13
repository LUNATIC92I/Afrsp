<?php
/** /.well-known/security.txt (RFC 9116) : comment signaler une vulnérabilité. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: public, max-age=86400');
$contact = setting('security_contact', setting('contact_email', ''));
$expires = gmdate('Y-m-d\T00:00:00\Z', strtotime('+1 year'));
echo "# Signalement de vulnérabilités — AFRICA'SPY Intelligence Academy\n";
echo "# Merci de nous laisser un délai raisonnable de correction avant toute divulgation publique.\n";
if ($contact !== '') echo "Contact: mailto:$contact\n";
echo "Contact: " . absolute_url('contact.php') . "\n";
echo "Expires: $expires\n";
echo "Preferred-Languages: fr, en\n";
echo "Canonical: " . absolute_url('.well-known/security.txt') . "\n";
echo "Policy: " . absolute_url('legal.php#securite') . "\n";
