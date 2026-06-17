<?php

/**
 * Bulk-migrate legacy Carbon Fields blocks (carbon-fields/*) to the native
 * coachman/* blocks.
 *
 * The two block families are nearly 1:1, so this rewrites the stored block
 * markup in post_content: parse → swap blockName → remap attributes →
 * re-serialize → save. A Tools page (Tools → Migrate Carbon Blocks) drives it
 * with a dry-run preview, a run action, and a revert action. Original content
 * is backed up to the `_cm_premigration_content` post meta before any write.
 *
 * The old carbon-fields/* blocks remain registered (in post-meta.php) so any
 * un-migrated content still renders.
 */

if (! defined('ABSPATH')) {
    exit;
}

const CM_MIGRATE_BACKUP_META = '_cm_premigration_content';

/* -------------------------------------------------------------------------- */
/* Small helpers                                                              */
/* -------------------------------------------------------------------------- */

/**
 * Carbon Fields stores field values either flat in the block attrs or nested
 * under a `data` key depending on version. Return whichever holds the fields.
 */
function cm_migrate_fields($attrs)
{
    if (is_array($attrs) && isset($attrs['data']) && is_array($attrs['data'])) {
        return $attrs['data'];
    }
    return is_array($attrs) ? $attrs : array();
}

/** Coerce a Carbon Fields checkbox value (true / 1 / "yes" / "true") to bool. */
function cm_migrate_truthy($value)
{
    if (is_bool($value)) {
        return $value;
    }
    if (is_numeric($value)) {
        return (int) $value === 1;
    }
    if (is_string($value)) {
        return in_array(strtolower($value), array('1', 'yes', 'true', 'on'), true);
    }
    return (bool) $value;
}

/** Build a fresh parsed-block array for a dynamic (self-closing) block. */
function cm_migrate_make_block($name, array $attrs = array())
{
    return array(
        'blockName'    => $name,
        'attrs'        => $attrs,
        'innerBlocks'  => array(),
        'innerHTML'    => '',
        'innerContent' => array(),
    );
}

/* -------------------------------------------------------------------------- */
/* Transform                                                                  */
/* -------------------------------------------------------------------------- */

/**
 * Recursively migrate a list of parsed blocks.
 *
 * @param array $blocks Output of parse_blocks().
 * @return array
 */
function cm_migrate_block_tree($blocks)
{
    $out = array();
    foreach ($blocks as $block) {
        $out[] = cm_migrate_block($block);
    }
    return $out;
}

/**
 * Migrate a single parsed block (and its descendants).
 */
function cm_migrate_block($block)
{
    // Always migrate descendants first.
    if (! empty($block['innerBlocks'])) {
        $block['innerBlocks'] = cm_migrate_block_tree($block['innerBlocks']);
    }

    $name = isset($block['blockName']) ? $block['blockName'] : '';
    if (strpos($name, 'carbon-fields/') !== 0) {
        return $block; // leave non-CF blocks (incl. already-migrated children) untouched.
    }

    $slug      = substr($name, strlen('carbon-fields/'));
    $attrs     = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();
    $fields    = cm_migrate_fields($attrs);
    $className  = isset($attrs['className']) ? $attrs['className'] : null;
    $newAttrs  = array();

    switch ($slug) {
        case 'icon':
            $newAttrs = array(
                'iconId'        => isset($fields['icon']) ? (int) $fields['icon'] : 0,
                'iconColor'     => isset($fields['icon_color']) ? $fields['icon_color'] : '',
                'iconAlignment' => isset($fields['icon_alignment']) ? $fields['icon_alignment'] : '',
                'iconWidth'     => isset($fields['icon_width']) ? $fields['icon_width'] : '',
                'iconHeight'    => isset($fields['icon_height']) ? $fields['icon_height'] : '',
            );
            break;

        case 'tabs-navigation':
            $newAttrs = array(
                'tabId'     => isset($fields['tab_id']) ? $fields['tab_id'] : '',
                'isSwiper'  => cm_migrate_truthy(isset($fields['is_swiper']) ? $fields['is_swiper'] : false),
                'direction' => isset($fields['direction']) ? $fields['direction'] : '',
                'tabStyle'  => isset($fields['style']) ? $fields['style'] : '',
            );
            break;

        case 'tabs-navigation-item':
            $newAttrs = array(
                'tabItemId' => isset($fields['tab_item_id']) ? $fields['tab_item_id'] : '',
            );
            break;

        case 'tabs-content':
            $newAttrs = array(
                'tabId' => isset($fields['tab_id']) ? $fields['tab_id'] : '',
            );
            break;

        case 'tabs-content-item':
            $newAttrs = array(
                'tabContentId' => isset($fields['tab_content_id']) ? $fields['tab_content_id'] : '',
            );
            break;

        case 'swiper':
            $block = cm_migrate_swiper($block, $fields, $className);
            return $block;

        case 'listing-models':
            $newAttrs = cm_migrate_listing_models_attrs($fields);
            break;

        case 'model-technical-details':
            $model              = isset($fields['model']) && is_array($fields['model']) ? $fields['model'] : array();
            $modelId            = isset($model[0]['model']) ? (string) $model[0]['model'] : '';
            $newAttrs           = array(
                'buttonText' => isset($fields['button_text']) && $fields['button_text'] !== ''
                    ? $fields['button_text']
                    : 'View all features',
                'modelId'    => $modelId,
            );
            break;

        case 'partner':
            $groups   = isset($fields['partner_blocks']) && is_array($fields['partner_blocks']) ? $fields['partner_blocks'] : array();
            $types    = array();
            foreach ($groups as $group) {
                if (isset($group['_type'])) {
                    $types[] = $group['_type'];
                }
            }
            // Match the legacy presence-based render. If no groups were stored
            // (older content), fall back to the new block's "show both" default.
            if (empty($types)) {
                $newAttrs = array('showLogo' => true, 'showWebsite' => true);
            } else {
                $newAttrs = array(
                    'showLogo'    => in_array('partner_logo', $types, true),
                    'showWebsite' => in_array('partner_website', $types, true),
                );
            }
            break;

        // No-attribute blocks — name swap only.
        case 'video-gallery':
        case 'swiper-wrapper':
        case 'swiper-slide':
        case 'swiper-pagination':
        case 'swiper-navigation':
        case 'listing-title':
        case 'listing-feature':
        case 'listing-buttons':
        case 'event-date':
        default:
            $newAttrs = array();
            break;
    }

    if ($className !== null) {
        $newAttrs['className'] = $className;
    }

    $block['blockName'] = 'coachman/' . $slug;
    $block['attrs']     = $newAttrs;

    return $block;
}

/**
 * Flatten the legacy listing-models `posts` complex field into the three flat
 * term-ID arrays the coachman/listing-models block expects.
 */
function cm_migrate_listing_models_attrs($fields)
{
    $newAttrs = array(
        'isSwiper'            => cm_migrate_truthy(isset($fields['is_swiper']) ? $fields['is_swiper'] : false),
        'displayModelLayouts' => cm_migrate_truthy(isset($fields['display_model_layouts']) ? $fields['display_model_layouts'] : false),
        'caravanModels'      => array(),
        'motorhomeModels'    => array(),
        'campervanModels'    => array(),
    );

    $map = array(
        'caravan'   => 'caravanModels',
        'motorhome' => 'motorhomeModels',
        'campervan' => 'campervanModels',
    );

    $groups = isset($fields['posts']) && is_array($fields['posts']) ? $fields['posts'] : array();
    foreach ($groups as $group) {
        $type = isset($group['_type']) ? $group['_type'] : '';
        if (! isset($map[$type])) {
            continue;
        }
        $models = isset($group['model']) && is_array($group['model']) ? $group['model'] : array();
        $newAttrs[$map[$type]] = array_values(array_map('intval', $models));
    }

    return $newAttrs;
}

/**
 * Migrate a legacy swiper block. The legacy block stored pagination/navigation
 * as flags inside the `swiper_options` complex field; the coachman/swiper block
 * auto-detects them from child blocks, so we inject swiper-pagination /
 * swiper-navigation children as needed (carrying the legacy style).
 */
function cm_migrate_swiper($block, $fields, $className)
{
    $newAttrs = array(
        'swiperId' => isset($fields['swiper_id']) ? $fields['swiper_id'] : '',
    );

    $hasPagination = false;
    $hasNavigation = false;
    $navStyle      = '';

    $options = isset($fields['swiper_options']) && is_array($fields['swiper_options']) ? $fields['swiper_options'] : array();
    foreach ($options as $option) {
        $type = isset($option['_type']) ? $option['_type'] : '';
        switch ($type) {
            case 'autoplay':
                $newAttrs['enableAutoplay']       = true;
                $newAttrs['autoplayDelay']        = isset($option['delay']) && $option['delay'] !== '' ? (int) $option['delay'] : 3000;
                $newAttrs['disableOnInteraction'] = cm_migrate_truthy(isset($option['disableoninteraction']) ? $option['disableoninteraction'] : false);
                break;
            case 'spacebetween':
                if (isset($option['spacebetween']) && $option['spacebetween'] !== '') {
                    $newAttrs['spaceBetween'] = (string) $option['spacebetween'];
                }
                break;
            case 'slidesperview':
                if (isset($option['slidesperview']) && $option['slidesperview'] !== '') {
                    $newAttrs['slidesPerView'] = (string) $option['slidesperview'];
                }
                break;
            case 'pagination_navigation':
                $navStyle      = isset($option['style']) ? $option['style'] : '';
                $hasPagination = cm_migrate_truthy(isset($option['has_pagination']) ? $option['has_pagination'] : false);
                $hasNavigation = cm_migrate_truthy(isset($option['has_navigation']) ? $option['has_navigation'] : false);
                break;
        }
    }

    if ($className !== null) {
        $newAttrs['className'] = $className;
    }

    // Inner blocks were already migrated to coachman/* by the recursion.
    // Detect any pre-existing pagination/navigation children and stamp the style.
    $innerBlocks  = isset($block['innerBlocks']) ? $block['innerBlocks'] : array();
    $innerContent = isset($block['innerContent']) ? $block['innerContent'] : array();
    $foundPag     = false;
    $foundNav     = false;

    foreach ($innerBlocks as $idx => $child) {
        if ($child['blockName'] === 'coachman/swiper-pagination') {
            $innerBlocks[$idx]['attrs']['style'] = $navStyle;
            $foundPag = true;
        } elseif ($child['blockName'] === 'coachman/swiper-navigation') {
            $innerBlocks[$idx]['attrs']['style'] = $navStyle;
            $foundNav = true;
        }
    }

    // Inject children that were only represented as flags in the legacy block.
    if ($hasPagination && ! $foundPag) {
        $innerBlocks[]  = cm_migrate_make_block('coachman/swiper-pagination', array('style' => $navStyle));
        $innerContent[] = null;
    }
    if ($hasNavigation && ! $foundNav) {
        $innerBlocks[]  = cm_migrate_make_block('coachman/swiper-navigation', array('style' => $navStyle));
        $innerContent[] = null;
    }

    $block['blockName']    = 'coachman/swiper';
    $block['attrs']        = $newAttrs;
    $block['innerBlocks']  = $innerBlocks;
    $block['innerContent'] = $innerContent;

    return $block;
}

/* -------------------------------------------------------------------------- */
/* Scan                                                                       */
/* -------------------------------------------------------------------------- */

/**
 * Find all non-revision posts whose content still contains carbon-fields blocks.
 *
 * @return array Array of stdClass rows (ID, post_title, post_type).
 */
function cm_migrate_scan()
{
    global $wpdb;
    $like = '%' . $wpdb->esc_like('wp:carbon-fields/') . '%';
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title, post_type
             FROM {$wpdb->posts}
             WHERE post_content LIKE %s
               AND post_type != 'revision'
               AND post_status IN ('publish','draft','pending','private','future')
             ORDER BY post_type, ID",
            $like
        )
    );
}

/**
 * Recursively collect carbon-fields blocks from a parsed tree (for the preview
 * attribute dump).
 */
function cm_migrate_collect_cf_blocks($blocks, &$collected, $limit = 20)
{
    foreach ($blocks as $block) {
        if (count($collected) >= $limit) {
            return;
        }
        if (isset($block['blockName']) && strpos((string) $block['blockName'], 'carbon-fields/') === 0) {
            $collected[] = array(
                'blockName' => $block['blockName'],
                'attrs'     => isset($block['attrs']) ? $block['attrs'] : array(),
            );
        }
        if (! empty($block['innerBlocks'])) {
            cm_migrate_collect_cf_blocks($block['innerBlocks'], $collected, $limit);
        }
    }
}

/* -------------------------------------------------------------------------- */
/* Run / revert                                                               */
/* -------------------------------------------------------------------------- */

/**
 * Migrate a single post. Backs up the original content first.
 *
 * @return array { changed:bool, error:string }
 */
function cm_migrate_run_post($post_id)
{
    $post = get_post($post_id);
    if (! $post) {
        return array('changed' => false, 'error' => 'Post not found');
    }

    $original = $post->post_content;
    if (strpos($original, 'wp:carbon-fields/') === false) {
        return array('changed' => false, 'error' => '');
    }

    $new = serialize_blocks(cm_migrate_block_tree(parse_blocks($original)));

    if ($new === $original) {
        return array('changed' => false, 'error' => '');
    }

    // Back up the earliest pre-migration content only (don't clobber on re-run).
    if (! metadata_exists('post', $post_id, CM_MIGRATE_BACKUP_META)) {
        update_post_meta($post_id, CM_MIGRATE_BACKUP_META, $original);
    }

    $result = wp_update_post(array(
        'ID'           => $post_id,
        'post_content' => $new,
    ), true);

    if (is_wp_error($result)) {
        return array('changed' => false, 'error' => $result->get_error_message());
    }

    return array('changed' => true, 'error' => '');
}

/**
 * Restore a post's pre-migration content from the backup meta.
 *
 * @return array { reverted:bool, error:string }
 */
function cm_migrate_revert_post($post_id)
{
    if (! metadata_exists('post', $post_id, CM_MIGRATE_BACKUP_META)) {
        return array('reverted' => false, 'error' => 'No backup found');
    }
    $original = get_post_meta($post_id, CM_MIGRATE_BACKUP_META, true);

    $result = wp_update_post(array(
        'ID'           => $post_id,
        'post_content' => $original,
    ), true);

    if (is_wp_error($result)) {
        return array('reverted' => false, 'error' => $result->get_error_message());
    }

    delete_post_meta($post_id, CM_MIGRATE_BACKUP_META);
    return array('reverted' => true, 'error' => '');
}

/** Posts that currently hold a migration backup (i.e. are revertable). */
function cm_migrate_backed_up_posts()
{
    global $wpdb;
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_type
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
             WHERE m.meta_key = %s
             ORDER BY p.post_type, p.ID",
            CM_MIGRATE_BACKUP_META
        )
    );
}

/* -------------------------------------------------------------------------- */
/* Tools page                                                                 */
/* -------------------------------------------------------------------------- */

add_action('admin_menu', 'cm_migrate_admin_menu');
function cm_migrate_admin_menu()
{
    add_management_page(
        __('Migrate Carbon Blocks', 'glossop-caravans'),
        __('Migrate Carbon Blocks', 'glossop-caravans'),
        'manage_options',
        'cm-migrate-blocks',
        'cm_migrate_admin_page'
    );
}

function cm_migrate_admin_page()
{
    if (! current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'glossop-caravans'));
    }

    $action  = isset($_POST['cm_migrate_action']) ? sanitize_text_field(wp_unslash($_POST['cm_migrate_action'])) : '';
    $results = null;
    $preview = null;

    if ($action) {
        check_admin_referer('cm_migrate_blocks');
    }

    if ($action === 'preview') {
        $preview = cm_migrate_build_preview();
    } elseif ($action === 'run') {
        $results = cm_migrate_run_all();
    } elseif ($action === 'revert') {
        $results = cm_migrate_revert_all();
    }

    $scan       = cm_migrate_scan();
    $backed_up  = cm_migrate_backed_up_posts();

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__('Migrate Carbon Blocks', 'glossop-caravans') . '</h1>';
    echo '<p>' . esc_html__('Rewrites legacy carbon-fields/* block markup to the native coachman/* blocks. Originals are backed up before any write and can be reverted below.', 'glossop-caravans') . '</p>';

    // --- Status banners --------------------------------------------------- //
    if (is_array($results)) {
        $changed = isset($results['changed']) ? $results['changed'] : 0;
        $errors  = isset($results['errors']) ? $results['errors'] : array();
        echo '<div class="notice notice-success"><p>'
            . sprintf(esc_html__('%d post(s) processed.', 'glossop-caravans'), (int) $changed)
            . '</p></div>';
        if (! empty($errors)) {
            echo '<div class="notice notice-error"><p>' . esc_html__('Errors:', 'glossop-caravans') . '</p><ul>';
            foreach ($errors as $err) {
                echo '<li>' . esc_html($err) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    // --- Scan summary ----------------------------------------------------- //
    echo '<h2>' . esc_html__('Posts still using carbon-fields blocks', 'glossop-caravans') . '</h2>';
    echo '<p>' . sprintf(esc_html__('%d post(s) found.', 'glossop-caravans'), count($scan)) . '</p>';
    if ($scan) {
        echo '<table class="widefat striped" style="max-width:760px"><thead><tr><th>ID</th><th>Type</th><th>Title</th></tr></thead><tbody>';
        foreach ($scan as $row) {
            echo '<tr><td>' . (int) $row->ID . '</td><td>' . esc_html($row->post_type) . '</td><td>'
                . esc_html($row->post_title ? $row->post_title : '(no title)') . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    // --- Actions ---------------------------------------------------------- //
    $nonce_field = wp_nonce_field('cm_migrate_blocks', '_wpnonce', true, false);

    echo '<p style="margin-top:1.5em">';
    echo '<form method="post" style="display:inline-block;margin-right:8px">' . $nonce_field
        . '<input type="hidden" name="cm_migrate_action" value="preview">'
        . '<button type="submit" class="button">' . esc_html__('Dry run / preview', 'glossop-caravans') . '</button></form>';

    if ($scan) {
        echo '<form method="post" style="display:inline-block;margin-right:8px" onsubmit="return confirm(\''
            . esc_js(__('Migrate all listed posts? Originals will be backed up.', 'glossop-caravans')) . '\');">' . $nonce_field
            . '<input type="hidden" name="cm_migrate_action" value="run">'
            . '<button type="submit" class="button button-primary">' . esc_html__('Run migration', 'glossop-caravans') . '</button></form>';
    }

    if ($backed_up) {
        echo '<form method="post" style="display:inline-block" onsubmit="return confirm(\''
            . esc_js(__('Revert all migrated posts to their pre-migration content?', 'glossop-caravans')) . '\');">' . $nonce_field
            . '<input type="hidden" name="cm_migrate_action" value="revert">'
            . '<button type="submit" class="button">' . sprintf(esc_html__('Revert all (%d)', 'glossop-caravans'), count($backed_up)) . '</button></form>';
    }
    echo '</p>';

    // --- Preview output --------------------------------------------------- //
    if (is_array($preview)) {
        echo '<h2>' . esc_html__('Preview', 'glossop-caravans') . '</h2>';
        if (empty($preview['post_id'])) {
            echo '<p>' . esc_html__('No carbon-fields blocks found to preview.', 'glossop-caravans') . '</p>';
        } else {
            echo '<p>' . sprintf(
                esc_html__('Showing parsed carbon-fields block attributes from post #%d (%s). Confirm the attribute shape matches the migration map before running.', 'glossop-caravans'),
                (int) $preview['post_id'],
                esc_html($preview['post_title'])
            ) . '</p>';
            echo '<h3>' . esc_html__('Source attributes (parse_blocks)', 'glossop-caravans') . '</h3>';
            echo '<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:360px;overflow:auto">'
                . esc_html(print_r($preview['source'], true)) . '</pre>';
            echo '<h3>' . esc_html__('Migrated result (preview only — not saved)', 'glossop-caravans') . '</h3>';
            echo '<pre style="background:#fff;border:1px solid #ccd0d4;padding:12px;max-height:360px;overflow:auto">'
                . esc_html($preview['serialized']) . '</pre>';
        }
    }

    echo '</div>';
}

/** Build the dry-run preview from the first post that still has CF blocks. */
function cm_migrate_build_preview()
{
    $scan = cm_migrate_scan();
    if (empty($scan)) {
        return array('post_id' => 0);
    }
    $post   = get_post($scan[0]->ID);
    $blocks = parse_blocks($post->post_content);

    $collected = array();
    cm_migrate_collect_cf_blocks($blocks, $collected);

    return array(
        'post_id'    => $post->ID,
        'post_title' => $post->post_title ? $post->post_title : '(no title)',
        'source'     => $collected,
        'serialized' => serialize_blocks(cm_migrate_block_tree($blocks)),
    );
}

/** Migrate every scanned post. */
function cm_migrate_run_all()
{
    $changed = 0;
    $errors  = array();
    foreach (cm_migrate_scan() as $row) {
        $r = cm_migrate_run_post($row->ID);
        if ($r['changed']) {
            $changed++;
        }
        if ($r['error'] !== '') {
            $errors[] = sprintf('#%d: %s', $row->ID, $r['error']);
        }
    }
    return array('changed' => $changed, 'errors' => $errors);
}

/** Revert every post that has a backup. */
function cm_migrate_revert_all()
{
    $reverted = 0;
    $errors   = array();
    foreach (cm_migrate_backed_up_posts() as $row) {
        $r = cm_migrate_revert_post($row->ID);
        if ($r['reverted']) {
            $reverted++;
        }
        if ($r['error'] !== '') {
            $errors[] = sprintf('#%d: %s', $row->ID, $r['error']);
        }
    }
    return array('changed' => $reverted, 'errors' => $errors);
}
