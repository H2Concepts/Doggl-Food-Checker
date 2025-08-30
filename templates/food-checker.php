<div id="doggl-food-checker" class="doggl-container">
    <!-- Main Tool -->
    <div class="doggl-tool">
        <!-- Search Section -->
        <div class="doggl-search-section">
            <div class="doggl-search-container">
                <div class="doggl-search-icon">🔍</div>
                <input 
                    type="text" 
                    id="doggl-search-input"
                    class="doggl-search-input" 
                    placeholder="<?php echo esc_attr(__('Lebensmittel eingeben (z.B. Schokolade, Trauben, Käse)', 'doggl-food-checker')); ?>"
                    autocomplete="off"
                    role="combobox"
                    aria-expanded="false"
                    aria-controls="doggl-search-results"
                />
                <div class="doggl-loading" id="doggl-loading" style="display: none;">
                    <div class="doggl-spinner"></div>
                </div>
            </div>
            
            <!-- Search Results Dropdown -->
            <ul id="doggl-search-results" class="doggl-search-results" role="listbox" style="display: none;"></ul>
        </div>

        <!-- Weight Slider -->
        <div id="doggl-weight-section" class="doggl-weight-section" style="display: none;">
            <div class="doggl-weight-header">
                <span class="doggl-weight-icon">⚖️</span>
                <label for="doggl-weight-slider" class="doggl-weight-label">
                    <?php _e('Gewicht deines Hundes', 'doggl-food-checker'); ?>
                </label>
            </div>
            
            <div class="doggl-weight-slider-container">
                <span class="doggl-weight-min">2kg</span>
                <input 
                    type="range" 
                    id="doggl-weight-slider"
                    class="doggl-weight-slider"
                    min="2" 
                    max="70" 
                    value="15"
                />
                <span class="doggl-weight-max">70kg</span>
            </div>
            
            <div class="doggl-weight-display">
                <span id="doggl-weight-value" class="doggl-weight-value">15 kg</span>
            </div>
        </div>

        <!-- Result Card -->
        <div id="doggl-result-card" class="doggl-result-card" style="display: none;"></div>

        <!-- No selection state -->
        <div id="doggl-no-selection" class="doggl-no-selection">
            <div class="doggl-search-placeholder">🔍</div>
            <p class="doggl-placeholder-text">
                <?php _e('Gib ein Lebensmittel ein, um zu erfahren, ob es für deinen Hund sicher ist', 'doggl-food-checker'); ?>
            </p>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="doggl-faq">
        <h2 class="doggl-faq-title"><?php _e('Häufig gestellte Fragen', 'doggl-food-checker'); ?></h2>
        
        <div class="doggl-faq-items">
            <div class="doggl-faq-item">
                <button class="doggl-faq-question" aria-expanded="false">
                    <span><?php _e('Wie zuverlässig sind die Informationen?', 'doggl-food-checker'); ?></span>
                    <span class="doggl-faq-icon">▼</span>
                </button>
                <div class="doggl-faq-answer">
                    <p><?php _e('Unsere Datenbank basiert auf veterinärmedizinischen Quellen und wird regelmäßig aktualisiert. Dennoch ersetzen diese Informationen keinen tierärztlichen Rat.', 'doggl-food-checker'); ?></p>
                </div>
            </div>
            
            <div class="doggl-faq-item">
                <button class="doggl-faq-question" aria-expanded="false">
                    <span><?php _e('Was mache ich bei einem Notfall?', 'doggl-food-checker'); ?></span>
                    <span class="doggl-faq-icon">▼</span>
                </button>
                <div class="doggl-faq-answer">
                    <p><?php _e('Bei Vergiftungsverdacht sofort den Tierarzt oder tierärztlichen Notdienst kontaktieren. Notiere die aufgenommene Menge und Uhrzeit.', 'doggl-food-checker'); ?></p>
                </div>
            </div>
            
            <div class="doggl-faq-item">
                <button class="doggl-faq-question" aria-expanded="false">
                    <span><?php _e('Wie berechnen sich die Portionsempfehlungen?', 'doggl-food-checker'); ?></span>
                    <span class="doggl-faq-icon">▼</span>
                </button>
                <div class="doggl-faq-answer">
                    <p><?php _e('Die Empfehlungen basieren auf dem Körpergewicht und berücksichtigen die Toxizität der Substanz. Sie gelten für gesunde, ausgewachsene Hunde.', 'doggl-food-checker'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="doggl-disclaimer">
        <div class="doggl-disclaimer-icon">⚠️</div>
        <div>
            <h3 class="doggl-disclaimer-title"><?php _e('Wichtiger Hinweis', 'doggl-food-checker'); ?></h3>
            <p class="doggl-disclaimer-text">
                <?php _e('Diese Informationen ersetzen keinen tierärztlichen Rat. Bei Vergiftungsverdacht oder Unsicherheiten kontaktiere sofort deinen Tierarzt oder den tierärztlichen Notdienst.', 'doggl-food-checker'); ?>
            </p>
        </div>
    </div>
</div>

<!-- JSON-LD Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "<?php _e('Wie zuverlässig sind die Informationen?', 'doggl-food-checker'); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php _e('Unsere Datenbank basiert auf veterinärmedizinischen Quellen und wird regelmäßig aktualisiert. Dennoch ersetzen diese Informationen keinen tierärztlichen Rat.', 'doggl-food-checker'); ?>"
      }
    },
    {
      "@type": "Question",
      "name": "<?php _e('Was mache ich bei einem Notfall?', 'doggl-food-checker'); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php _e('Bei Vergiftungsverdacht sofort den Tierarzt oder tierärztlichen Notdienst kontaktieren. Notiere die aufgenommene Menge und Uhrzeit.', 'doggl-food-checker'); ?>"
      }
    }
  ]
}
</script>