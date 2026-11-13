<?php
/** AFRICA'SPY — utilitaires de l'espace d'administration. */
declare(strict_types=1);
require_once APP_ROOT . '/includes/catalog.php';

/** Champ de formulaire standard. */
function fld(string $name, string $label, $value = '', string $type = 'text', array $attrs = [], ?string $hint = null): string
{
    $id = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $a = '';
    foreach ($attrs as $k => $v) {
        $a .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    $input = $type === 'textarea'
        ? '<textarea class="textarea' . (isset($attrs['data-code']) ? ' textarea--code' : '') . '" id="' . $id . '" name="' . e($name) . '"' . $a . '>' . e($value) . '</textarea>'
        : '<input class="input" type="' . e($type) . '" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . $a . '>';
    return '<div class="field' . (isset($attrs['data-full']) ? ' full' : '') . '"><label for="' . $id . '">' . e($label) . '</label>' . $input . ($hint ? '<span class="hint">' . e($hint) . '</span>' : '') . '</div>';
}

function sel(string $name, string $label, array $options, $current, array $attrs = []): string
{
    $id = 'f_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
    $a = '';
    foreach ($attrs as $k => $v) {
        $a .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    $o = '';
    foreach ($options as $val => $txt) {
        $o .= '<option value="' . e($val) . '"' . ((string) $val === (string) $current ? ' selected' : '') . '>' . e($txt) . '</option>';
    }
    return '<div class="field"><label for="' . $id . '">' . e($label) . '</label><select class="select" id="' . $id . '" name="' . e($name) . '"' . $a . '>' . $o . '</select></div>';
}

/** Petit formulaire POST d'action (supprimer, basculer…) protégé CSRF. */
function action_btn(string $action, array $hidden, string $label, string $cls = 'btn--sm', ?string $confirm = null, string $target = ''): string
{
    $h = csrf_field() . '<input type="hidden" name="action" value="' . e($action) . '">';
    foreach ($hidden as $k => $v) {
        $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return '<form method="post"' . ($target ? ' action="' . e($target) . '"' : '') . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . $h . '<button class="btn ' . e($cls) . '" type="submit">' . e($label) . '</button></form>';
}

function admin_courses_options(bool $withAll = false): array
{
    $o = $withAll ? ['' => 'Toutes les formations'] : [];
    foreach (q_all('SELECT id, code, title FROM courses ORDER BY sort_order, id') as $c) {
        $o[$c['id']] = $c['code'] . ' — ' . $c['title'];
    }
    return $o;
}

/** Redirige vers la même page (motif Post/Redirect/Get). */
function back(array $query = []): never
{
    $path = ltrim(substr((string) strtok($_SERVER['SCRIPT_NAME'], '?'), strlen(base_path())), '/');
    redirect($path, $query);
}
