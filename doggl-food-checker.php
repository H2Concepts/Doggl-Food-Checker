<?php
/**
 * Plugin Name: Doggl Food Checker
 * Plugin URI: https://doggl.de
 * Description: Interaktives Tool zur Überprüfung, ob Lebensmittel für Hunde sicher sind. Mit Risikobewertung, Portionsempfehlungen und Notfall-Hinweisen.
 * Version: 1.0.0
 * Author: Doggl Team
 * Text Domain: doggl-food-checker
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOGGL_FOOD_CHECKER_VERSION', '1.0.0');
define('DOGGL_FOOD_CHECKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOGGL_FOOD_CHECKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class DogglFoodChecker {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_shortcode('doggl_food_check', array($this, 'render_shortcode'));
        
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('doggl-food-checker', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Register custom post type
        $this->register_food_post_type();
        
        // Add rewrite rules for share tokens
        add_rewrite_tag('%doggl_token%', '([^&]+)');
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Handle share token display
        add_action('template_redirect', array($this, 'handle_share_token'));
    }
    
    public function register_food_post_type() {
        $args = array(
            'label' => __('Lebensmittel', 'doggl-food-checker'),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'rewrite' => false,
            'query_var' => false,
            'menu_icon' => 'dashicons-carrot',
            'supports' => array('title', 'editor', 'custom-fields'),
            'labels' => array(
                'name' => __('Lebensmittel', 'doggl-food-checker'),
                'singular_name' => __('Lebensmittel', 'doggl-food-checker'),
                'add_new' => __('Neues Lebensmittel', 'doggl-food-checker'),
                'add_new_item' => __('Neues Lebensmittel hinzufügen', 'doggl-food-checker'),
                'edit_item' => __('Lebensmittel bearbeiten', 'doggl-food-checker'),
                'new_item' => __('Neues Lebensmittel', 'doggl-food-checker'),
                'view_item' => __('Lebensmittel anzeigen', 'doggl-food-checker'),
                'search_items' => __('Lebensmittel suchen', 'doggl-food-checker'),
                'not_found' => __('Keine Lebensmittel gefunden', 'doggl-food-checker'),
                'not_found_in_trash' => __('Keine Lebensmittel im Papierkorb', 'doggl-food-checker'),
            ),
        );
        register_post_type('doggl_food', $args);
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script(
            'doggl-food-checker',
            DOGGL_FOOD_CHECKER_PLUGIN_URL . 'assets/food-checker.js',
            array('jquery'),
            DOGGL_FOOD_CHECKER_VERSION,
            true
        );
        
        wp_enqueue_style(
            'doggl-food-checker',
            DOGGL_FOOD_CHECKER_PLUGIN_URL . 'assets/food-checker.css',
            array(),
            DOGGL_FOOD_CHECKER_VERSION
        );
        
        // Localize script
        wp_localize_script('doggl-food-checker', 'doggl_food', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('doggl/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'search_placeholder' => __('Lebensmittel eingeben (z.B. Schokolade, Trauben, Käse)', 'doggl-food-checker'),
                'no_results' => __('Keine Ergebnisse gefunden', 'doggl-food-checker'),
                'loading' => __('Suche läuft...', 'doggl-food-checker'),
                'emergency_title' => __('NOTFALL – Sofort handeln!', 'doggl-food-checker'),
                'emergency_text' => __('Kontaktiere sofort deinen Tierarzt oder den tierärztlichen Notdienst!', 'doggl-food-checker'),
                'share_success' => __('Link wurde in die Zwischenablage kopiert!', 'doggl-food-checker'),
                'pdf_generating' => __('PDF wird erstellt...', 'doggl-food-checker'),
            )
        ));
    }
    
    public function register_rest_routes() {
        register_rest_route('doggl/v1', '/food/search', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'search_foods'),
            'permission_callback' => '__return_true',
            'args' => array(
                'q' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'weight_kg' => array(
                    'required' => false,
                    'type' => 'number',
                    'default' => 15,
                ),
            ),
        ));
        
        register_rest_route('doggl/v1', '/food/share', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_share_token'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('doggl/v1', '/food/export', array(
            'methods' => 'POST',
            'callback' => array($this, 'export_pdf'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('doggl/v1', '/food/item/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_food_item'),
            'permission_callback' => '__return_true',
        ));
    }
    
    public function search_foods($request) {
        // erlaubt auch GET ?q=... ohne JSON
        if ($request->get_method() === 'GET' && !$request->get_param('q')) {
            return new WP_Error('query_missing', __('Suchbegriff fehlt', 'doggl-food-checker'), array('status' => 400));
        }

        $query = $request->get_param('q');
        $weight_kg = $request->get_param('weight_kg');
        
        if (strlen($query) < 2) {
            return new WP_Error('query_too_short', __('Suchbegriff zu kurz', 'doggl-food-checker'), array('status' => 400));
        }
        
        $normalized_query = $this->normalize_string($query);

        // Build additional search terms for simple plural forms
        $search_terms = array($query, $normalized_query);
        if (substr($normalized_query, -1) === 'n') {
            $search_terms[] = substr($normalized_query, 0, -1);
        }
        $search_terms = array_unique($search_terms);

        // Search posts by title/content
        $posts_query = get_posts(array(
            'post_type'      => 'doggl_food',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            's'              => $query
        ));

        // Search posts by alternative names
        $meta_queries = array('relation' => 'OR');
        foreach ($search_terms as $term) {
            $meta_queries[] = array(
                'key'     => 'alt_names',
                'value'   => $term,
                'compare' => 'LIKE'
            );
        }

        $alt_posts_query = get_posts(array(
            'post_type'      => 'doggl_food',
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'meta_query'     => $meta_queries
        ));

        // Merge and deduplicate results by post ID
        $posts = array_merge($posts_query, $alt_posts_query);
        $posts_by_id = array();
        foreach ($posts as $post) {
            $posts_by_id[$post->ID] = $post;
        }
        $posts = array_values($posts_by_id);

        if (empty($posts)) {
            return new WP_Error('no_matches', __('Keine Treffer gefunden', 'doggl-food-checker'), array('status' => 404));
        }
        
        $results = array();
        foreach ($posts as $post) {
            $food_data = $this->get_food_data($post->ID);
            $results[] = $food_data;
        }
        
        // Sort by relevance (exact matches first)
        usort($results, function($a, $b) use ($normalized_query) {
            $a_exact = $this->normalize_string($a['name']) === $normalized_query;
            $b_exact = $this->normalize_string($b['name']) === $normalized_query;
            
            if ($a_exact && !$b_exact) return -1;
            if (!$a_exact && $b_exact) return 1;
            return 0;
        });
        
        return array(
            'best' => $results[0],
            'alternatives' => array_slice($results, 1, 5)
        );
    }

    private function get_status_config($status) {
        $configs = array(
            'safe' => array(
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle h-8 w-8 mr-3 text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>',
                'title' => __('Erlaubt', 'doggl-food-checker'),
                'answer' => __('Ja, in Maßen erlaubt', 'doggl-food-checker')
            ),
            'caution' => array(
                'icon' => '⚠️',
                'title' => __('Vorsicht', 'doggl-food-checker'),
                'answer' => __('Nur selten und wenig', 'doggl-food-checker')
            ),
            'danger' => array(
                'icon' => '🚨',
                'title' => __('Gefährlich', 'doggl-food-checker'),
                'answer' => __('Nein – gefährlich!', 'doggl-food-checker')
            ),
            'toxic' => array(
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-xcircle h-8 w-8 mr-3"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>',
                'title' => __('Hochgiftig', 'doggl-food-checker'),
                'answer' => __('Nein – hochgiftig!', 'doggl-food-checker')
            ),
        );
        return $configs[$status] ?? $configs['caution'];
    }

    public function get_status_icon($status) {
        $config = $this->get_status_config($status);
        return $config['icon'];
    }

    public function get_status_title($status) {
        $config = $this->get_status_config($status);
        return $config['title'];
    }

    public function get_status_answer($status) {
        $config = $this->get_status_config($status);
        return $config['answer'];
    }

    public function get_frequency_text($frequency) {
        $texts = array(
            'never' => __('Niemals', 'doggl-food-checker'),
            'rare' => __('Sehr selten (max. 1x pro Monat)', 'doggl-food-checker'),
            'occasional' => __('Gelegentlich (max. 1x pro Woche)', 'doggl-food-checker'),
            'often' => __('Häufiger möglich', 'doggl-food-checker'),
        );
        return $texts[$frequency] ?? __('Unbekannt', 'doggl-food-checker');
    }
    
    public function create_share_token($request) {
        $data = $request->get_json_params();
        
        $token = wp_generate_password(12, false);
        $share_data = array(
            'foodId' => intval($data['foodId']),
            'foodName' => sanitize_text_field($data['foodName']),
            'status' => sanitize_text_field($data['status']),
            'weight' => floatval($data['weight']),
            'portion' => floatval($data['portion']),
            'timestamp' => current_time('mysql')
        );
        
        // Store for 7 days
        set_transient('doggl_food_' . $token, $share_data, 7 * DAY_IN_SECONDS);
        
        $share_url = add_query_arg('doggl_token', $token, home_url());
        
        return array('url' => $share_url, 'token' => $token);
    }
    
    public function export_pdf($request) {
        $data = $request->get_json_params();
        
        // In a real implementation, you'd use a PDF library like TCPDF or Dompdf
        // For now, return a placeholder response
        return array(
            'success' => true,
            'message' => __('PDF-Export würde hier implementiert werden', 'doggl-food-checker'),
            'download_url' => '#'
        );
    }
    
    public function get_food_item($request) {
        $id = $request->get_param('id');
        $food_data = $this->get_food_data($id);
        
        if (!$food_data) {
            return new WP_Error('not_found', __('Lebensmittel nicht gefunden', 'doggl-food-checker'), array('status' => 404));
        }
        
        return $food_data;
    }
    
    private function get_food_data($post_id) {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'doggl_food') {
            return false;
        }
        
        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'status' => get_post_meta($post->ID, 'status', true) ?: 'caution',
            'category' => get_post_meta($post->ID, 'category', true) ?: 'others',
            'altNames' => array_filter(explode(',', get_post_meta($post->ID, 'alt_names', true))),
            'portionGPerKg' => floatval(get_post_meta($post->ID, 'portion_g_per_kg', true)),
            'maxFrequency' => get_post_meta($post->ID, 'max_frequency', true) ?: 'occasional',
            'reason' => get_post_meta($post->ID, 'reason', true) ?: '',
            'symptoms' => array_filter(explode(',', get_post_meta($post->ID, 'symptoms', true))),
            'emergency' => (bool) get_post_meta($post->ID, 'emergency', true),
            'notes' => get_post_meta($post->ID, 'notes', true) ?: '',
            'info' => get_post_meta($post->ID, 'info', true) ?: '',
            'ageNotes' => get_post_meta($post->ID, 'age_notes', true) ?: '',
            'sources' => array_filter(explode(',', get_post_meta($post->ID, 'sources', true))),
            'updatedAt' => $post->post_modified
        );
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'weight' => 15,
            'theme' => 'default'
        ), $atts, 'doggl_food_check');
        
        // Check for share token
        $token = get_query_var('doggl_token');
        if ($token) {
            return $this->render_shared_result($token);
        }
        
        ob_start();
        include DOGGL_FOOD_CHECKER_PLUGIN_DIR . 'templates/food-checker.php';
        return ob_get_clean();
    }
    
    private function render_shared_result($token) {
        $share_data = get_transient('doggl_food_' . $token);
        
        if (!$share_data) {
            return '<div class="doggl-error">' . __('Geteiltes Ergebnis nicht gefunden oder abgelaufen.', 'doggl-food-checker') . '</div>';
        }
        
        $food_data = $this->get_food_data($share_data['foodId']);
        if (!$food_data) {
            return '<div class="doggl-error">' . __('Lebensmittel nicht gefunden.', 'doggl-food-checker') . '</div>';
        }
        
        ob_start();
        include DOGGL_FOOD_CHECKER_PLUGIN_DIR . 'templates/shared-result.php';
        return ob_get_clean();
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'doggl_token';
        return $vars;
    }
    
    public function handle_share_token() {
        $token = get_query_var('doggl_token');
        if ($token && !is_admin()) {
            // Add special body class for shared results
            add_filter('body_class', function($classes) {
                $classes[] = 'doggl-shared-result';
                return $classes;
            });
        }
    }
    
    private function normalize_string($str) {
        $str = strtolower(trim($str));
        $str = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $str);
        $str = preg_replace('/[^a-z0-9\s]/', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        return $str;
    }
    
    public function activate() {
        $this->register_food_post_type();
        flush_rewrite_rules();
        $this->create_sample_data();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_sample_data() {
        $sample_foods = array(
            array(
                'name' => 'Trauben',
                'status' => 'toxic',
                'category' => 'fruit',
                'alt_names' => 'Weintrauben,Rosinen,Sultaninen',
                'portion_g_per_kg' => 0,
                'max_frequency' => 'never',
                'reason' => 'Enthalten nephrotoxische Substanzen, die zu akutem Nierenversagen führen können',
                'symptoms' => 'Erbrechen,Durchfall,Apathie,Dehydration,Nierenversagen',
                'emergency' => true,
                'notes' => 'Auch getrocknete Trauben (Rosinen) sind hochgiftig. Bereits kleine Mengen können tödlich sein.'
            ),
            array(
                'name' => 'Schokolade (dunkel)',
                'status' => 'toxic',
                'category' => 'sweets',
                'alt_names' => 'Zartbitterschokolade,Bitterschokolade,Kakao',
                'portion_g_per_kg' => 0,
                'max_frequency' => 'never',
                'reason' => 'Theobromin ist für Hunde hochgiftig und kann zu Herzrhythmusstörungen führen',
                'symptoms' => 'Unruhe,Tachykardie,Krämpfe,Erbrechen,Durchfall',
                'emergency' => true,
                'notes' => 'Je dunkler die Schokolade, desto gefährlicher. Auch Kakao und Backschokolade sind extrem toxisch.'
            ),
            array(
                'name' => 'Käse',
                'status' => 'caution',
                'category' => 'dairy',
                'alt_names' => 'Hartkäse,Weichkäse,Gouda,Cheddar',
                'portion_g_per_kg' => 5,
                'max_frequency' => 'rare',
                'reason' => 'Enthält Laktose, die bei vielen Hunden Verdauungsprobleme verursacht',
                'symptoms' => 'Durchfall,Blähungen,Bauchschmerzen',
                'emergency' => false,
                'notes' => 'Hartkäse ist besser verträglich als Weichkäse. Auf salzarme Sorten achten.'
            ),
            array(
                'name' => 'Gurke',
                'status' => 'safe',
                'category' => 'vegetable',
                'alt_names' => 'Salatgurke,Schlangengurke',
                'portion_g_per_kg' => 10,
                'max_frequency' => 'often',
                'reason' => 'Wasserreich, kalorienarm und gut verträglich',
                'symptoms' => '',
                'emergency' => false,
                'notes' => 'Ideal als kalorienarmer Snack, besonders im Sommer. Schale entfernen für bessere Verträglichkeit.'
            ),
            array(
                'name' => 'Apfel',
                'status' => 'safe',
                'category' => 'fruit',
                'alt_names' => 'Äpfel',
                'portion_g_per_kg' => 8,
                'max_frequency' => 'often',
                'reason' => 'Vitaminreich und ballaststoffhaltig, unterstützt die Verdauung',
                'symptoms' => '',
                'emergency' => false,
                'notes' => 'Kerngehäuse entfernen - Kerne enthalten geringe Mengen Blausäure.'
            )
        );
        
        foreach ($sample_foods as $food) {
            $existing = get_posts(array(
                'post_type' => 'doggl_food',
                'title' => $food['name'],
                'posts_per_page' => 1
            ));
            
            if (empty($existing)) {
                $post_id = wp_insert_post(array(
                    'post_title' => $food['name'],
                    'post_type' => 'doggl_food',
                    'post_status' => 'publish'
                ));
                
                if ($post_id) {
                    foreach ($food as $key => $value) {
                        if ($key !== 'name') {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }
            }
        }
    }
}

require_once DOGGL_FOOD_CHECKER_PLUGIN_DIR . 'admin/import.php';

// Initialize the plugin
new DogglFoodChecker();

if (is_admin()) {
    add_action('add_meta_boxes_doggl_food', function ($post) {
        $info = get_post_meta($post->ID, 'info', true);
        if ($info) {
            add_meta_box(
                'doggl_food_info',
                __('Kunden-Info', 'doggl-food-checker'),
                function () use ($info) {
                    echo wpautop(esc_html($info));
                },
                'doggl_food',
                'normal',
                'high'
            );
        }
    });
}
