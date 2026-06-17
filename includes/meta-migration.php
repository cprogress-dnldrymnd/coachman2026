<?php

/**
 * One-time migration: Carbon Fields complex / association values -> native meta.
 *
 * Simple fields (text, select, oembed, date, image/file IDs, theme options) are
 * already stored by Carbon Fields at the native `_{name}` key, so they need NO
 * migration — the theme already reads them with get__post_meta() etc.
 *
 * Only four fields use Carbon's special storage and are reshaped here:
 *
 *   | field             | object            | kind         | written to            |
 *   |-------------------|-------------------|--------------|-----------------------|
 *   | technical_details | model terms       | complex      | _technical_details    |
 *   | page              | model terms       | association  | _page  (array of IDs) |
 *   | stocks            | wpsl_stores posts | complex(nest)| _stocks               |
 *   | display_on        | template posts    | association  | _display_on (IDs)     |
 *
 * Strategy: read each value through the Carbon Fields API (which decodes its own
 * flattened storage) **while Carbon Fields is still installed**, then write a
 * clean native value. The prior native value is backed up first so the whole
 * object can be reverted. Run this once, right after deploying the standalone
 * field framework, then verify and remove the Carbon Fields plugin.
 *
 * Tools -> Migrate Carbon Meta.
 *
 * @package Coachman
 */

if (! defined('ABSPATH')) {
    exit;
}

const CM_META_BACKUP_META = '_cm_meta_premigrate';
const CM_META_ABSENT      = '__CM_ABSENT__';

/* -------------------------------------------------------------------------- */
/* Specs                                                                      */
/* -------------------------------------------------------------------------- */

/** Taxonomies whose terms carry migratable meta. */
function cm_meta_migrate_taxonomies()
{
    return array('caravan_model', 'motorhome_model', 'campervan_model');
}

/** Field => kind map for a given object context/type. */
function cm_meta_migrate_fields_for($context, $type = '')
{
    if ($context === 'term') {
        return array(
            'technical_details' => 'complex',
            'page'              => 'association',
        );
    }
    if ($context === 'post' && $type === 'wpsl_stores') {
        return array('stocks' => 'complex');
    }
    if ($context === 'post' && $type === 'template') {
        return array('display_on' => 'association');
    }
    return array();
}

/* -------------------------------------------------------------------------- */
/* Backend read / write helpers                                               */
/* -------------------------------------------------------------------------- */

/** Read a value through the Carbon Fields API (null if CF is unavailable). */
function cm_meta_migrate_carbon_read($context, $id, $field)
{
    if ($context === 'term') {
        return function_exists('carbon_get_term_meta') ? carbon_get_term_meta($id, $field) : null;
    }
    return function_exists('carbon_get_post_meta') ? carbon_get_post_meta($id, $field) : null;
}

/** Reshape a Carbon value into its native form for the given kind. */
function cm_meta_migrate_transform($kind, $value)
{
    if ($kind === 'association') {
        $ids = array();
        if (is_array($value)) {
            foreach ($value as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $ids[] = (int) $item['id'];
                } elseif (is_numeric($item)) {
                    $ids[] = (int) $item;
                }
            }
        }
        return array_values(array_filter($ids));
    }
    // complex (incl. nested) — Carbon already returns the right array shape.
    return is_array($value) ? $value : array();
}

function cm_meta_migrate_get_native($context, $id, $key)
{
    return $context === 'term'
        ? get_term_meta($id, $key, true)
        : get_post_meta($id, $key, true);
}

function cm_meta_migrate_native_exists($context, $id, $key)
{
    return $context === 'term'
        ? metadata_exists('term', $id, $key)
        : metadata_exists('post', $id, $key);
}

function cm_meta_migrate_set_native($context, $id, $key, $value)
{
    if ($context === 'term') {
        update_term_meta($id, $key, $value);
    } else {
        update_post_meta($id, $key, $value);
    }
}

function cm_meta_migrate_delete_native($context, $id, $key)
{
    if ($context === 'term') {
        delete_term_meta($id, $key);
    } else {
        delete_post_meta($id, $key);
    }
}

function cm_meta_migrate_backup_exists($context, $id)
{
    return cm_meta_migrate_native_exists($context, $id, CM_META_BACKUP_META);
}

/* -------------------------------------------------------------------------- */
/* Enumerate objects                                                          */
/* -------------------------------------------------------------------------- */

/**
 * @return array List of ['context','type','id','label'] for every object that
 *               could hold migratable meta.
 */
function cm_meta_migrate_objects()
{
    $objects = array();

    $terms = get_terms(array(
        'taxonomy'   => cm_meta_migrate_taxonomies(),
        'hide_empty' => false,
    ));
    if (! is_wp_error($terms)) {
        foreach ($terms as $term) {
            $objects[] = array(
                'context' => 'term',
                'type'    => $term->taxonomy,
                'id'      => (int) $term->term_id,
                'label'   => $term->name . ' (' . $term->taxonomy . ')',
            );
        }
    }

    foreach (array('wpsl_stores', 'template') as $pt) {
        $ids = get_posts(array(
            'post_type'      => $pt,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        foreach ($ids as $pid) {
            $objects[] = array(
                'context' => 'post',
                'type'    => $pt,
                'id'      => (int) $pid,
                'label'   => (get_the_title($pid) ?: '(no title)') . ' (' . $pt . ' #' . $pid . ')',
            );
        }
    }

    return $objects;
}

/**
 * Inspect one object: read each Carbon value + the native value we'd write.
 *
 * @return array ['has_legacy'=>bool,'migrated'=>bool,'fields'=>[field=>[kind,carbon,native]]]
 */
function cm_meta_migrate_inspect($obj)
{
    $fields  = cm_meta_migrate_fields_for($obj['context'], $obj['type']);
    $out     = array('has_legacy' => false, 'migrated' => cm_meta_migrate_backup_exists($obj['context'], $obj['id']), 'fields' => array());

    foreach ($fields as $field => $kind) {
        $carbon = cm_meta_migrate_carbon_read($obj['context'], $obj['id'], $field);
        $native = cm_meta_migrate_transform($kind, $carbon);
        if (! empty($carbon)) {
            $out['has_legacy'] = true;
        }
        $out['fields'][$field] = array('kind' => $kind, 'carbon' => $carbon, 'native' => $native);
    }
    return $out;
}

/* -------------------------------------------------------------------------- */
/* Run / revert                                                               */
/* -------------------------------------------------------------------------- */

/**
 * Migrate one object. Skips when already migrated (backup present) or when there
 * is nothing to migrate. Backs up prior native values before writing.
 *
 * @return array ['changed'=>bool,'skipped'=>bool,'error'=>string]
 */
function cm_meta_migrate_run_object($obj)
{
    $context = $obj['context'];
    $id      = $obj['id'];
    $fields  = cm_meta_migrate_fields_for($context, $obj['type']);
    if (empty($fields)) {
        return array('changed' => false, 'skipped' => false, 'error' => '');
    }

    // Read everything via Carbon FIRST (associations live at the same native key
    // we are about to overwrite, so this must happen before any write).
    $reads      = array();
    $has_legacy = false;
    foreach ($fields as $field => $kind) {
        $val = cm_meta_migrate_carbon_read($context, $id, $field);
        $reads[$field] = $val;
        if (! empty($val)) {
            $has_legacy = true;
        }
    }

    if (! $has_legacy) {
        return array('changed' => false, 'skipped' => false, 'error' => '');
    }
    if (cm_meta_migrate_backup_exists($context, $id)) {
        return array('changed' => false, 'skipped' => true, 'error' => '');
    }

    // Snapshot current native values so the object can be reverted.
    $backup = array();
    foreach ($fields as $field => $kind) {
        $key = '_' . $field;
        $backup[$field] = cm_meta_migrate_native_exists($context, $id, $key)
            ? cm_meta_migrate_get_native($context, $id, $key)
            : CM_META_ABSENT;
    }
    cm_meta_migrate_set_native($context, $id, CM_META_BACKUP_META, $backup);

    // Write the native values.
    foreach ($fields as $field => $kind) {
        $key    = '_' . $field;
        $native = cm_meta_migrate_transform($kind, $reads[$field]);
        if (empty($native)) {
            cm_meta_migrate_delete_native($context, $id, $key);
        } else {
            cm_meta_migrate_set_native($context, $id, $key, $native);
        }
    }

    return array('changed' => true, 'skipped' => false, 'error' => '');
}

/** Restore one object's pre-migration native values from its backup. */
function cm_meta_migrate_revert_object($obj)
{
    $context = $obj['context'];
    $id      = $obj['id'];
    if (! cm_meta_migrate_backup_exists($context, $id)) {
        return array('reverted' => false, 'error' => '');
    }
    $backup = cm_meta_migrate_get_native($context, $id, CM_META_BACKUP_META);
    if (! is_array($backup)) {
        $backup = array();
    }
    foreach ($backup as $field => $prior) {
        $key = '_' . $field;
        if ($prior === CM_META_ABSENT) {
            cm_meta_migrate_delete_native($context, $id, $key);
        } else {
            cm_meta_migrate_set_native($context, $id, $key, $prior);
        }
    }
    cm_meta_migrate_delete_native($context, $id, CM_META_BACKUP_META);
    return array('reverted' => true, 'error' => '');
}

function cm_meta_migrate_run_all()
{
    $changed = 0;
    $skipped = 0;
    foreach (cm_meta_migrate_objects() as $obj) {
        $r = cm_meta_migrate_run_object($obj);
        if ($r['changed']) {
            $changed++;
        }
        if (! empty($r['skipped'])) {
            $skipped++;
        }
    }
    return array('changed' => $changed, 'skipped' => $skipped);
}

function cm_meta_migrate_revert_all()
{
    $reverted = 0;
    foreach (cm_meta_migrate_objects() as $obj) {
        $r = cm_meta_migrate_revert_object($obj);
        if (! empty($r['reverted'])) {
            $reverted++;
        }
    }
    return array('reverted' => $reverted);
}

/* -------------------------------------------------------------------------- */
/* Tools page                                                                 */
/* -------------------------------------------------------------------------- */

add_action('admin_menu', 'cm_meta_migrate_admin_menu');
function cm_meta_migrate_admin_menu()
{
    add_management_page(
        __('Migrate Carbon Meta', 'glossop-caravans'),
        __('Migrate Carbon Meta', 'glossop-caravans'),
        'manage_options',
        'cm-migrate-meta',
        'cm_meta_migrate_admin_page'
    );
}

function cm_meta_migrate_admin_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'glossop-caravans'));
    }

    $action  = isset($_POST['cm_meta_migrate_action']) ? sanitize_text_field(wp_unslash($_POST['cm_meta_migrate_action'])) : '';
    $results = null;
    if ($action) {
        check_admin_referer('cm_meta_migrate');
        if ($action === 'run') {
            $results = cm_meta_migrate_run_all();
        } elseif ($action === 'revert') {
            $results = cm_meta_migrate_revert_all();
        }
    }

    $carbon_active = function_exists('carbon_get_post_meta');

    // Build a scan summary.
    $objects   = cm_meta_migrate_objects();
    $pending   = array();
    $migrated  = array();
    $preview   = array();
    foreach ($objects as $obj) {
        $info = cm_meta_migrate_inspect($obj);
        if ($info['migrated']) {
            $migrated[] = $obj;
        } elseif ($info['has_legacy']) {
            $pending[] = $obj;
            if (count($preview) < 8) {
                $preview[] = array('obj' => $obj, 'info' => $info);
            }
        }
    }

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Migrate Carbon Meta', 'glossop-caravans') . '</h1>';
    echo '<p>' . esc_html__('Converts the four Carbon Fields complex / association fields (technical_details, page, stocks, display_on) to native meta. Simple fields are already native and are not touched. Each object is backed up before writing and can be reverted below.', 'glossop-caravans') . '</p>';

    if (! $carbon_active) {
        echo '<div class="notice notice-warning"><p><strong>' . esc_html__('Carbon Fields is not active.', 'glossop-caravans') . '</strong> '
            . esc_html__('Legacy values are read through the Carbon Fields API, so migration cannot run until the plugin is active. (Revert still works.)', 'glossop-caravans') . '</p></div>';
    }

    if (is_array($results)) {
        if (isset($results['changed'])) {
            echo '<div class="notice notice-success"><p>'
                . sprintf(esc_html__('Migrated %1$d object(s); %2$d already migrated and skipped.', 'glossop-caravans'), (int) $results['changed'], (int) $results['skipped'])
                . '</p></div>';
        } elseif (isset($results['reverted'])) {
            echo '<div class="notice notice-success"><p>'
                . sprintf(esc_html__('Reverted %d object(s).', 'glossop-caravans'), (int) $results['reverted'])
                . '</p></div>';
        }
    }

    echo '<h2>' . esc_html__('Status', 'glossop-caravans') . '</h2>';
    echo '<p>' . sprintf(
        esc_html__('%1$d object(s) pending migration, %2$d already migrated.', 'glossop-caravans'),
        count($pending),
        count($migrated)
    ) . '</p>';

    // Actions.
    $nonce = wp_nonce_field('cm_meta_migrate', '_wpnonce', true, false);
    echo '<p>';
    if ($carbon_active && $pending) {
        echo '<form method="post" style="display:inline-block;margin-right:8px" onsubmit="return confirm(\''
            . esc_js(__('Migrate all pending objects? Each is backed up first.', 'glossop-caravans')) . '\');">' . $nonce
            . '<input type="hidden" name="cm_meta_migrate_action" value="run">'
            . '<button type="submit" class="button button-primary">' . esc_html__('Run migration', 'glossop-caravans') . '</button></form>';
    }
    if ($migrated) {
        echo '<form method="post" style="display:inline-block" onsubmit="return confirm(\''
            . esc_js(__('Revert all migrated objects to their pre-migration values?', 'glossop-caravans')) . '\');">' . $nonce
            . '<input type="hidden" name="cm_meta_migrate_action" value="revert">'
            . '<button type="submit" class="button">' . sprintf(esc_html__('Revert all (%d)', 'glossop-caravans'), count($migrated)) . '</button></form>';
    }
    echo '</p>';

    // Dry-run preview.
    echo '<h2>' . esc_html__('Preview (not saved)', 'glossop-caravans') . '</h2>';
    if (empty($preview)) {
        echo '<p>' . esc_html__('Nothing pending to preview.', 'glossop-caravans') . '</p>';
    } else {
        foreach ($preview as $row) {
            echo '<h3>' . esc_html($row['obj']['label']) . '</h3>';
            foreach ($row['info']['fields'] as $field => $data) {
                if (empty($data['carbon'])) {
                    continue;
                }
                echo '<p><strong>' . esc_html($field) . '</strong> (' . esc_html($data['kind']) . ')</p>';
                echo '<div style="display:flex;gap:16px;flex-wrap:wrap">';
                echo '<div><em>' . esc_html__('Carbon (read)', 'glossop-caravans') . '</em><pre style="background:#fff;border:1px solid #ccd0d4;padding:8px;max-width:420px;max-height:200px;overflow:auto">' . esc_html(print_r($data['carbon'], true)) . '</pre></div>';
                echo '<div><em>' . esc_html__('Native (would write)', 'glossop-caravans') . '</em><pre style="background:#fff;border:1px solid #ccd0d4;padding:8px;max-width:420px;max-height:200px;overflow:auto">' . esc_html(print_r($data['native'], true)) . '</pre></div>';
                echo '</div>';
            }
        }
    }

    echo '</div>';
}
