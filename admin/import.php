<?php
// Admin import functionality for Doggl Food Checker

add_action('admin_menu', 'doggl_food_register_import_page');
function doggl_food_register_import_page() {
    add_submenu_page(
        'edit.php?post_type=doggl_food',
        __('Lebensmittel Import', 'doggl-food-checker'),
        __('Import', 'doggl-food-checker'),
        'manage_options',
        'doggl-food-import',
        'doggl_food_import_page'
    );
}

function doggl_food_import_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(__('Lebensmittel CSV-Import', 'doggl-food-checker')); ?></h1>
        <p><?php echo wp_kses_post(__('Laden Sie eine CSV mit den Spalten <code>title,alt_names,category,status,max_frequency,emergency,portion_g_per_kg,reason,symptoms,notes,slug</code> hoch.', 'doggl-food-checker')); ?></p>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('doggl_food_import'); ?>
            <input type="hidden" name="action" value="doggl_food_import">
            <p><input type="file" name="csv" accept=".csv" required></p>
            <p><label><input type="checkbox" name="overwrite" value="1"> <?php echo wp_kses_post(__('Duplikate überschreiben (per <strong>slug</strong> oder <strong>title</strong>)', 'doggl-food-checker')); ?></label></p>
            <p><button class="button button-primary"><?php echo esc_html(__('Import starten', 'doggl-food-checker')); ?></button></p>
        </form>
    </div>
    <?php
}

add_action('admin_post_doggl_food_import', 'doggl_food_handle_import');
function doggl_food_handle_import() {
    if (!current_user_can('manage_options') || !check_admin_referer('doggl_food_import')) {
        wp_die('Unauthorized');
    }
    if (empty($_FILES['csv']['tmp_name'])) {
        wp_safe_redirect(add_query_arg(['page' => 'doggl-food-import', 'err' => 'nofile'], admin_url('edit.php?post_type=doggl_food')));
        exit;
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $uploaded = wp_handle_upload($_FILES['csv'], ['test_form' => false]);
    if (!empty($uploaded['error'])) {
        wp_safe_redirect(add_query_arg(['page' => 'doggl-food-import', 'err' => 'upload'], admin_url('edit.php?post_type=doggl_food')));
        exit;
    }
    $res = doggl_food_import_from_csv($uploaded['file'], !empty($_POST['overwrite']));
    wp_safe_redirect(add_query_arg(['page' => 'doggl-food-import', 'ok' => 1] + $res, admin_url('edit.php?post_type=doggl_food')));
    exit;
}

/**
 * CSV fields: title, alt_names, category, status, max_frequency, emergency, portion_g_per_kg, reason, symptoms, notes, slug
 */
function doggl_food_import_from_csv($filepath, $overwrite = false) {
    $cpt = 'doggl_food';
    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    $errors = 0;

    if (!file_exists($filepath) || !($fh = fopen($filepath, 'r'))) {
        return ['errors' => 1];
    }

    $headers = fgetcsv($fh, 0, ',');
    $headers = array_map('trim', $headers);
    $required = ['title', 'status', 'max_frequency', 'emergency'];
    foreach ($required as $h) {
        if (!in_array($h, $headers, true)) {
            fclose($fh);
            return ['errors' => 1, 'missing' => $h];
        }
    }

    $map = array_flip($headers);

    while (($row = fgetcsv($fh, 0, ',')) !== false) {
        if (count($row) === 1 && trim($row[0]) === '') {
            continue;
        }

        $get = function ($key) use ($map, $row) {
            return isset($map[$key]) ? trim((string) $row[$map[$key]]) : '';
        };

        $title = $get('title');
        $slug_input = $get('slug');
        $slug = sanitize_title($slug_input ? $slug_input : $title);
        if ($title === '') {
            $skipped++;
            continue;
        }

        // Duplicate by slug or title
        $existing = get_page_by_path($slug, OBJECT, $cpt);
        if (!$existing) {
            $q = new WP_Query(['post_type' => $cpt, 'title' => $title, 'posts_per_page' => 1, 'post_status' => 'any']);
            $existing = $q->have_posts() ? $q->posts[0] : null;
            wp_reset_postdata();
        }

        $meta = [
            'alt_names'        => $get('alt_names'),
            'category'         => $get('category'),
            'status'           => strtolower($get('status')),
            'max_frequency'    => strtolower($get('max_frequency')),
            'emergency'        => (int) !!$get('emergency'),
            'portion_g_per_kg' => floatval($get('portion_g_per_kg') ? $get('portion_g_per_kg') : 0),
            'reason'           => $get('reason'),
            'symptoms'         => $get('symptoms'),
            'notes'            => $get('notes'),
        ];

        if (!in_array($meta['status'], ['safe', 'caution', 'toxic'], true)) {
            $meta['status'] = 'caution';
        }
        if (!in_array($meta['max_frequency'], ['daily', 'occasional', 'never'], true)) {
            $meta['max_frequency'] = 'occasional';
        }

        if ($existing && $overwrite) {
            wp_update_post(['ID' => $existing->ID, 'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish']);
            foreach ($meta as $k => $v) {
                update_post_meta($existing->ID, $k, $v);
            }
            $updated++;
        } elseif ($existing && !$overwrite) {
            $skipped++;
        } else {
            $post_id = wp_insert_post(['post_type' => $cpt, 'post_title' => $title, 'post_name' => $slug, 'post_status' => 'publish']);
            if (is_wp_error($post_id) || !$post_id) {
                $errors++;
                continue;
            }
            foreach ($meta as $k => $v) {
                update_post_meta($post_id, $k, $v);
            }
            $inserted++;
        }
    }

    fclose($fh);
    return compact('inserted', 'updated', 'skipped', 'errors');
}
