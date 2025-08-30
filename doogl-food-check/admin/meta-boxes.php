<?php
/**
 * Meta boxes for doggl_food post type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DogglFoodMetaBoxes {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'doggl_food_details',
            __('Lebensmittel Details', 'doggl-food-checker'),
            array($this, 'render_food_details_meta_box'),
            'doggl_food',
            'normal',
            'high'
        );
        
        add_meta_box(
            'doggl_food_safety',
            __('Sicherheit & Portionen', 'doggl-food-checker'),
            array($this, 'render_safety_meta_box'),
            'doggl_food',
            'normal',
            'high'
        );
    }
    
    public function render_food_details_meta_box($post) {
        wp_nonce_field('doggl_food_meta_box', 'doggl_food_meta_box_nonce');
        
        $status = get_post_meta($post->ID, 'status', true) ?: 'caution';
        $category = get_post_meta($post->ID, 'category', true) ?: 'others';
        $alt_names = get_post_meta($post->ID, 'alt_names', true);
        $reason = get_post_meta($post->ID, 'reason', true);
        $notes = get_post_meta($post->ID, 'notes', true);
        $age_notes = get_post_meta($post->ID, 'age_notes', true);
        $sources = get_post_meta($post->ID, 'sources', true);
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="doggl_status"><?php _e('Risiko-Status', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <select name="doggl_status" id="doggl_status" class="regular-text">
                        <option value="safe" <?php selected($status, 'safe'); ?>><?php _e('Sicher (Grün)', 'doggl-food-checker'); ?></option>
                        <option value="caution" <?php selected($status, 'caution'); ?>><?php _e('Vorsicht (Gelb)', 'doggl-food-checker'); ?></option>
                        <option value="danger" <?php selected($status, 'danger'); ?>><?php _e('Gefährlich (Rot)', 'doggl-food-checker'); ?></option>
                        <option value="toxic" <?php selected($status, 'toxic'); ?>><?php _e('Hochgiftig (Rot)', 'doggl-food-checker'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_category"><?php _e('Kategorie', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <select name="doggl_category" id="doggl_category" class="regular-text">
                        <option value="fruit" <?php selected($category, 'fruit'); ?>><?php _e('Obst', 'doggl-food-checker'); ?></option>
                        <option value="vegetable" <?php selected($category, 'vegetable'); ?>><?php _e('Gemüse', 'doggl-food-checker'); ?></option>
                        <option value="meat" <?php selected($category, 'meat'); ?>><?php _e('Fleisch', 'doggl-food-checker'); ?></option>
                        <option value="dairy" <?php selected($category, 'dairy'); ?>><?php _e('Milchprodukte', 'doggl-food-checker'); ?></option>
                        <option value="nuts" <?php selected($category, 'nuts'); ?>><?php _e('Nüsse', 'doggl-food-checker'); ?></option>
                        <option value="sweets" <?php selected($category, 'sweets'); ?>><?php _e('Süßwaren', 'doggl-food-checker'); ?></option>
                        <option value="drinks" <?php selected($category, 'drinks'); ?>><?php _e('Getränke', 'doggl-food-checker'); ?></option>
                        <option value="others" <?php selected($category, 'others'); ?>><?php _e('Sonstiges', 'doggl-food-checker'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_alt_names"><?php _e('Alternative Namen', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <input type="text" name="doggl_alt_names" id="doggl_alt_names" value="<?php echo esc_attr($alt_names); ?>" class="regular-text" />
                    <p class="description"><?php _e('Komma-getrennt (z.B. Weintrauben,Rosinen,Sultaninen)', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_reason"><?php _e('Begründung', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <textarea name="doggl_reason" id="doggl_reason" rows="3" class="large-text"><?php echo esc_textarea($reason); ?></textarea>
                    <p class="description"><?php _e('Kurze Erklärung, warum das Lebensmittel sicher/gefährlich ist', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_notes"><?php _e('Zusätzliche Hinweise', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <textarea name="doggl_notes" id="doggl_notes" rows="3" class="large-text"><?php echo esc_textarea($notes); ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_age_notes"><?php _e('Altershinweise', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <textarea name="doggl_age_notes" id="doggl_age_notes" rows="2" class="large-text"><?php echo esc_textarea($age_notes); ?></textarea>
                    <p class="description"><?php _e('Besondere Hinweise für Welpen oder Senioren', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_sources"><?php _e('Quellen', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <textarea name="doggl_sources" id="doggl_sources" rows="2" class="large-text"><?php echo esc_textarea($sources); ?></textarea>
                    <p class="description"><?php _e('URLs oder Referenzen, komma-getrennt', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php
    }
    
    public function render_safety_meta_box($post) {
        $portion_g_per_kg = get_post_meta($post->ID, 'portion_g_per_kg', true);
        $max_frequency = get_post_meta($post->ID, 'max_frequency', true) ?: 'occasional';
        $symptoms = get_post_meta($post->ID, 'symptoms', true);
        $emergency = get_post_meta($post->ID, 'emergency', true);
        ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="doggl_portion_g_per_kg"><?php _e('Portion (g/kg)', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <input type="number" name="doggl_portion_g_per_kg" id="doggl_portion_g_per_kg" 
                           value="<?php echo esc_attr($portion_g_per_kg); ?>" 
                           step="0.1" min="0" max="100" class="small-text" />
                    <p class="description"><?php _e('Gramm pro Kilogramm Körpergewicht (0 = nicht empfohlen)', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_max_frequency"><?php _e('Maximale Häufigkeit', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <select name="doggl_max_frequency" id="doggl_max_frequency" class="regular-text">
                        <option value="never" <?php selected($max_frequency, 'never'); ?>><?php _e('Niemals', 'doggl-food-checker'); ?></option>
                        <option value="rare" <?php selected($max_frequency, 'rare'); ?>><?php _e('Sehr selten', 'doggl-food-checker'); ?></option>
                        <option value="occasional" <?php selected($max_frequency, 'occasional'); ?>><?php _e('Gelegentlich', 'doggl-food-checker'); ?></option>
                        <option value="often" <?php selected($max_frequency, 'often'); ?>><?php _e('Häufiger möglich', 'doggl-food-checker'); ?></option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_symptoms"><?php _e('Symptome', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <textarea name="doggl_symptoms" id="doggl_symptoms" rows="3" class="large-text"><?php echo esc_textarea($symptoms); ?></textarea>
                    <p class="description"><?php _e('Komma-getrennt (z.B. Erbrechen,Durchfall,Apathie)', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="doggl_emergency"><?php _e('Notfall', 'doggl-food-checker'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="doggl_emergency" id="doggl_emergency" value="1" <?php checked($emergency, '1'); ?> />
                        <?php _e('Sofortiger tierärztlicher Notfall', 'doggl-food-checker'); ?>
                    </label>
                    <p class="description"><?php _e('Aktivieren bei lebensbedrohlichen Lebensmitteln', 'doggl-food-checker'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php
    }
    
    public function save_meta_boxes($post_id) {
        // Check if nonce is valid
        if (!isset($_POST['doggl_food_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['doggl_food_meta_box_nonce'], 'doggl_food_meta_box')) {
            return;
        }
        
        // Check if user has permission to edit
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if not an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check post type
        if (get_post_type($post_id) !== 'doggl_food') {
            return;
        }
        
        // Save meta fields
        $fields = array(
            'status', 'category', 'alt_names', 'portion_g_per_kg', 
            'max_frequency', 'reason', 'symptoms', 'notes', 
            'age_notes', 'sources'
        );
        
        foreach ($fields as $field) {
            $key = 'doggl_' . $field;
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                if ($field === 'reason' || $field === 'notes' || $field === 'age_notes' || $field === 'sources' || $field === 'symptoms') {
                    $value = sanitize_textarea_field($_POST[$key]);
                }
                update_post_meta($post_id, $field, $value);
            }
        }
        
        // Handle emergency checkbox
        $emergency = isset($_POST['doggl_emergency']) ? '1' : '0';
        update_post_meta($post_id, 'emergency', $emergency);
    }
}

new DogglFoodMetaBoxes();