<?php
/** Traitement du formulaire de contact : validation, anti-spam, limitation de débit. */
declare(strict_types=1);

function handle_contact(array $in): array
{
    if (!empty($in['website'])) { // pot de miel anti-robots
        return [true, 'Message transmis.', []];
    }
    $data = array_map(static fn ($v) => is_string($v) ? trim($v) : '', $in);
    $errors = validate($data, [
        'full_name' => 'required|min:2|max:160', 'email' => 'required|email|max:190',
        'subject' => 'required|max:190', 'message' => 'required|min:20|max:4000',
    ], ['full_name' => 'Nom complet', 'email' => 'E-mail', 'subject' => 'Objet', 'message' => 'Message']);
    if (empty($in['consent'])) {
        $errors['consent'] = 'Votre consentement est requis pour traiter la demande.';
    }
    if ($errors) {
        return [false, 'Veuillez corriger les champs signalés.', $errors];
    }
    $recent = (int) q_val('SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)', [client_ip()]);
    if ($recent >= 5) {
        return [false, 'Trop de messages envoyés depuis votre connexion. Réessayez dans une heure.', []];
    }
    q('INSERT INTO contact_messages (full_name, email, subject, message, ip_address, created_at) VALUES (?,?,?,?,?,UTC_TIMESTAMP())',
        [$data['full_name'], mb_strtolower($data['email']), $data['subject'], $data['message'], client_ip()]);
    if ($to = setting('contact_email')) {
        send_mail($to, "[AFRICA'SPY] " . $data['subject'], "De : {$data['full_name']} <{$data['email']}>\n\n{$data['message']}");
    }
    return [true, 'Message transmis. Notre équipe vous répondra par e-mail.', []];
}
