<div id="doggl-shared-result" class="doggl-container doggl-shared">
    <div class="doggl-shared-header">
        <h1 class="doggl-shared-title">
            <?php printf(__('Ergebnis für %s', 'doggl-food-checker'), esc_html($food_data['name'])); ?>
        </h1>
        <p class="doggl-shared-subtitle">
            <?php printf(__('Für einen %s kg Hund', 'doggl-food-checker'), esc_html($share_data['weight'])); ?>
        </p>
    </div>

    <div class="doggl-result-card doggl-status-<?php echo esc_attr($food_data['status']); ?>">
        <!-- Status Header -->
        <div class="doggl-result-header">
            <div class="doggl-result-icon">
                <?php echo $this->get_status_icon($food_data['status']); ?>
            </div>
            <div class="doggl-result-info">
                <h2 class="doggl-result-name"><?php echo esc_html($food_data['name']); ?></h2>
                <?php if (!empty($food_data['altNames'])): ?>
                    <p class="doggl-result-alt-names">
                        <?php printf(__('auch bekannt als: %s', 'doggl-food-checker'), esc_html(implode(', ', $food_data['altNames']))); ?>
                    </p>
                <?php endif; ?>
            </div>
            <span class="doggl-status-badge doggl-status-<?php echo esc_attr($food_data['status']); ?>">
                <?php echo esc_html($this->get_status_title($food_data['status'])); ?>
            </span>
        </div>

        <!-- Quick Answer -->
        <div class="doggl-quick-answer">
            <h4 class="doggl-section-title"><?php _e('Schnelle Antwort', 'doggl-food-checker'); ?></h4>
            <p class="doggl-quick-answer-text"><?php echo esc_html($this->get_status_answer($food_data['status'])); ?></p>
        </div>

        <!-- Portion Recommendation -->
        <?php if ($food_data['portionGPerKg'] && $share_data['portion'] > 0 && in_array($food_data['status'], array('safe', 'caution'))): ?>
            <div class="doggl-portion-recommendation">
                <h4 class="doggl-portion-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-4 w-4 mr-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><?php _e('Empfohlene Portion', 'doggl-food-checker'); ?>
                </h4>
                <p class="doggl-portion-amount">
                    <span class="doggl-portion-value"><?php echo esc_html($share_data['portion']); ?>g</span> 
                    <?php printf(__('für deinen %s kg Hund', 'doggl-food-checker'), esc_html($share_data['weight'])); ?>
                </p>
                <?php if ($food_data['maxFrequency']): ?>
                    <p class="doggl-portion-frequency">
                        <?php printf(__('Häufigkeit: %s', 'doggl-food-checker'), esc_html($this->get_frequency_text($food_data['maxFrequency']))); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Reason & Details -->
        <div class="doggl-reason">
            <h4 class="doggl-section-title"><?php _e('Begründung', 'doggl-food-checker'); ?></h4>
            <p class="doggl-reason-text"><?php echo esc_html($food_data['reason']); ?></p>
            <?php if ($food_data['notes']): ?>
                <p class="doggl-notes"><?php echo esc_html($food_data['notes']); ?></p>
            <?php endif; ?>
        </div>

        <!-- Symptoms -->
        <?php if ($food_data['status'] !== 'safe' && !empty($food_data['symptoms'])): ?>
            <div class="doggl-symptoms">
                <h4 class="doggl-section-title"><?php _e('Mögliche Symptome', 'doggl-food-checker'); ?></h4>
                <ul class="doggl-symptoms-list">
                    <?php foreach ($food_data['symptoms'] as $symptom): ?>
                        <li><?php echo esc_html(trim($symptom)); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Emergency Warning -->
        <?php if ($food_data['emergency'] || in_array($food_data['status'], array('danger', 'toxic'))): ?>
            <div class="doggl-emergency">
                <div class="doggl-emergency-content">
                    <div class="doggl-emergency-icon">?</div>
                    <div>
                        <h4 class="doggl-emergency-title"><?php _e('?? NOTFALL – Sofort handeln!', 'doggl-food-checker'); ?></h4>
                        <p class="doggl-emergency-text">
                            <?php _e('Kontaktiere sofort deinen Tierarzt oder den tierärztlichen Notdienst!', 'doggl-food-checker'); ?>
                        </p>
                        <div class="doggl-emergency-actions">
                            <h5 class="doggl-emergency-actions-title"><?php _e('Sofort-Maßnahmen:', 'doggl-food-checker'); ?></h5>
                            <ul class="doggl-emergency-actions-list">
                                <li><?php _e('• Hund beobachten und beruhigen', 'doggl-food-checker'); ?></li>
                                <li><?php _e('• Menge und Uhrzeit notieren', 'doggl-food-checker'); ?></li>
                                <li><?php _e('• NICHT zum Erbrechen bringen', 'doggl-food-checker'); ?></li>
                                <li><?php _e('• Tierarzt anrufen und Situation schildern', 'doggl-food-checker'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="doggl-shared-footer">
        <p><?php _e('Erstellt mit', 'doggl-food-checker'); ?> <strong>doggl</strong> - <?php _e('Darf mein Hund das essen?', 'doggl-food-checker'); ?></p>
        <p class="doggl-timestamp"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($share_data['timestamp'])); ?></p>
    </div>
</div>