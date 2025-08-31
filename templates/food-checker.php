<div id="doggl-food-checker" class="doggl-container">
    <!-- Main Tool -->
    <div class="doggl-tool">
        <!-- Search Section -->
        <div class="doggl-search-section">
            <div class="doggl-search-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search doggl-search-icon"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
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
                <div class="doggl-loading" id="doggl-loading" style="display: none;"></div>
            </div>
            
            <!-- Search Results Dropdown -->
            <ul id="doggl-search-results" class="doggl-search-results" role="listbox" style="display: none;"></ul>
        </div>

        <!-- Weight Slider -->
        <div id="doggl-weight-section" class="doggl-weight-section" style="display: none;">
            <div class="doggl-weight-header">
                <span class="doggl-weight-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-weight h-5 w-5 text-gray-600 mr-2"><circle cx="12" cy="5" r="3"></circle><path d="M6.5 8a2 2 0 0 0-1.905 1.46L2.1 18.5A2 2 0 0 0 4 21h16a2 2 0 0 0 1.925-2.54L19.4 9.5A2 2 0 0 0 17.48 8Z"></path></svg></span>
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
            <div class="doggl-search-placeholder"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-8 w-8 text-gray-400"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg></div>
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
        <div class="doggl-disclaimer-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle h-6 w-6 text-yellow-600 mr-3 mt-0.5 flex-shrink-0"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg></div>
        <div>
            <h4 class="doggl-disclaimer-title"><?php _e('Wichtiger Hinweis', 'doggl-food-checker'); ?></h4>
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