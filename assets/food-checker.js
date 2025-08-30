jQuery(document).ready(function($) {
    'use strict';
    
    let searchTimeout;
    let currentResults = null;
    let selectedFood = null;
    let dogWeight = 15;
    let activeIndex = -1;
    
    const $searchInput = $('#doggl-search-input');
    const $searchResults = $('#doggl-search-results');
    const $weightSection = $('#doggl-weight-section');
    const $weightSlider = $('#doggl-weight-slider');
    const $weightValue = $('#doggl-weight-value');
    const $resultCard = $('#doggl-result-card');
    const $noSelection = $('#doggl-no-selection');
    const $loading = $('#doggl-loading');
    
    // Initialize
    init();
    
    function init() {
        bindEvents();
        setupFAQ();
        
        // Check if we're on a shared result page
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('doggl_token')) {
            // Hide search and weight sections for shared results
            $('.doggl-search-section, .doggl-weight-section, .doggl-no-selection').hide();
        }
    }
    
    function bindEvents() {
        // Search input
        $searchInput.on('input', handleSearchInput);
        $searchInput.on('keydown', handleKeyDown);
        $searchInput.on('blur', function() {
            setTimeout(() => hideResults(), 150);
        });
        
        // Weight slider
        $weightSlider.on('input', handleWeightChange);
        
        // FAQ toggle
        $(document).on('click', '.doggl-faq-question', toggleFAQ);
        
        // Click outside to close results
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.doggl-search-section').length) {
                hideResults();
            }
        });
    }
    
    function handleSearchInput() {
        const query = $searchInput.val().trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideResults();
            clearSelection();
            return;
        }
        
        showLoading();
        
        searchTimeout = setTimeout(() => {
            searchFoods(query);
        }, 300);
    }
    
    function handleKeyDown(e) {
        const $items = $searchResults.find('.doggl-search-result');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, $items.length - 1);
                updateActiveItem($items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                updateActiveItem($items);
                break;
            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0 && $items.eq(activeIndex).length) {
                    selectFood($items.eq(activeIndex).data('food'));
                }
                break;
            case 'Escape':
                hideResults();
                activeIndex = -1;
                break;
        }
    }
    
    function updateActiveItem($items) {
        $items.removeClass('active');
        if (activeIndex >= 0) {
            $items.eq(activeIndex).addClass('active');
        }
    }
    
    function handleWeightChange() {
        dogWeight = parseInt($weightSlider.val());
        $weightValue.text(dogWeight + ' kg');
        
        // Update slider background
        const percentage = ((dogWeight - 2) / (70 - 2)) * 100;
        $weightSlider.css('background', 
            `linear-gradient(to right, #3b82f6 0%, #3b82f6 ${percentage}%, #e5e7eb ${percentage}%, #e5e7eb 100%)`
        );
        
        // Update portion if food is selected
        if (selectedFood) {
            updateResultCard();
        }
        
        // Analytics
        trackEvent('food_change_weight', { weight: dogWeight });
    }
    
    function showLoading() {
        $loading.show();
    }
    
    function hideLoading() {
        $loading.hide();
    }
    
    function showResults() {
        $searchResults.show();
        $searchInput.attr('aria-expanded', 'true');
    }
    
    function hideResults() {
        $searchResults.hide();
        $searchInput.attr('aria-expanded', 'false');
        activeIndex = -1;
    }
    
    function searchFoods(query) {
        $.ajax({
            url: doggl_food.rest_url + 'food/search',
            method: 'GET',
            data: { q: query, weight_kg: dogWeight },
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', doggl_food.nonce);
            }
        })
        .done(function(response) {
            currentResults = response;
            displayResults(response);
            trackEvent('food_search', { query: query });
        })
        .fail(function(xhr) {
            console.error('Food search failed', xhr.status, xhr.responseText);
            hideLoading();
            $searchResults
                .html('<li class="doggl-search-result doggl-empty">Keine Ergebnisse oder Fehler bei der Suche.</li>');
            showResults();
        });
    }

    function displayResults(results) {
        hideLoading();

        if (!results || (!results.best && !Array.isArray(results.alternatives))) {
            $searchResults.html(
                '<li class="doggl-search-result doggl-empty">Keine Ergebnisse</li>'
            );
            showResults();
            return;
        }

        const allResults = [results.best, ...results.alternatives].filter(Boolean);
        let html = '';
        
        allResults.forEach((food, index) => {
            const statusIcon = getStatusIcon(food.status);
            const altNamesText = food.altNames && food.altNames.length > 0 
                ? `auch: ${food.altNames.slice(0, 2).join(', ')}${food.altNames.length > 2 ? ` +${food.altNames.length - 2} weitere` : ''}`
                : '';
            
            html += `
                <li class="doggl-search-result" data-food='${JSON.stringify(food)}' role="option" id="option-${index}">
                    <div class="doggl-result-main">
                        <span class="doggl-result-icon">${statusIcon}</span>
                        <div>
                            <div class="doggl-result-name">${escapeHtml(food.name)}</div>
                            ${altNamesText ? `<div class="doggl-result-alt-names">${escapeHtml(altNamesText)}</div>` : ''}
                        </div>
                    </div>
                    <span class="doggl-status-badge doggl-status-${food.status}">
                        ${getStatusTitle(food.status)}
                    </span>
                </li>
            `;
        });
        
        $searchResults.html(html).show();
        
        // Bind click events
        $searchResults.find('.doggl-search-result').on('click', function() {
            const food = $(this).data('food');
            selectFood(food);
        });
        
        showResults();
    }
    
    function selectFood(food) {
        selectedFood = food;
        $searchInput.val(food.name);
        hideResults();
        
        $weightSection.show();
        $noSelection.hide();
        
        updateResultCard();
        trackEvent('food_select', { food_name: food.name, status: food.status });
    }
    
    function clearSelection() {
        selectedFood = null;
        $weightSection.hide();
        $resultCard.hide();
        $noSelection.show();
    }
    
    function updateResultCard() {
        if (!selectedFood) return;
        
        const portion = calculatePortion(selectedFood, dogWeight);
        const statusConfig = getStatusConfig(selectedFood.status);
        const isEmergency = selectedFood.emergency || ['danger', 'toxic'].includes(selectedFood.status);
        
        let html = `
            <div class="doggl-result-header">
                <div style="display: flex; align-items: center;">
                    <span class="doggl-result-icon">${statusConfig.icon}</span>
                    <div class="doggl-result-info">
                        <h2 class="doggl-result-name">${escapeHtml(selectedFood.name)}</h2>
                        ${selectedFood.altNames && selectedFood.altNames.length > 0 ? 
                            `<p class="doggl-result-alt-names">auch bekannt als: ${escapeHtml(selectedFood.altNames.join(', '))}</p>` : ''}
                    </div>
                </div>
                <span class="doggl-status-badge doggl-status-${selectedFood.status}">
                    ${statusConfig.title}
                </span>
            </div>
            
            <div class="doggl-quick-answer">
                <h3 class="doggl-section-title">Schnelle Antwort</h3>
                <p class="doggl-quick-answer-text">${statusConfig.shortAnswer}</p>
            </div>
        `;
        
        // Portion recommendation
        if (selectedFood.portionGPerKg && portion > 0 && ['safe', 'caution'].includes(selectedFood.status)) {
            html += `
                <div class="doggl-portion-recommendation">
                    <h4 class="doggl-portion-title">
                        🕐 Empfohlene Portion
                    </h4>
                    <p class="doggl-portion-amount">
                        <span class="doggl-portion-value">${portion}g</span> für deinen ${dogWeight}kg Hund
                    </p>
                    ${selectedFood.maxFrequency ? 
                        `<p class="doggl-portion-frequency">Häufigkeit: ${getFrequencyText(selectedFood.maxFrequency)}</p>` : ''}
                </div>
            `;
        }
        
        // Reason & Details
        html += `
            <div class="doggl-reason">
                <h4 class="doggl-section-title">Begründung</h4>
                <p class="doggl-reason-text">${escapeHtml(selectedFood.reason)}</p>
                ${selectedFood.notes ? `<p class="doggl-notes">${escapeHtml(selectedFood.notes)}</p>` : ''}
            </div>
        `;
        
        // Symptoms
        if (selectedFood.symptoms && selectedFood.symptoms.length > 0) {
            html += `
                <div class="doggl-symptoms">
                    <h4 class="doggl-section-title">Mögliche Symptome</h4>
                    <ul class="doggl-symptoms-list">
                        ${selectedFood.symptoms.map(symptom => `<li>${escapeHtml(symptom.trim())}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        // Emergency warning
        if (isEmergency) {
            html += `
                <div class="doggl-emergency">
                    <div class="doggl-emergency-content">
                        <div class="doggl-emergency-icon">📞</div>
                        <div>
                            <h4 class="doggl-emergency-title">⚠️ NOTFALL – Sofort handeln!</h4>
                            <p class="doggl-emergency-text">
                                Kontaktiere sofort deinen Tierarzt oder den tierärztlichen Notdienst!
                            </p>
                            <div class="doggl-emergency-actions">
                                <h5 class="doggl-emergency-actions-title">Sofort-Maßnahmen:</h5>
                                <ul class="doggl-emergency-actions-list">
                                    <li>• Hund beobachten und beruhigen</li>
                                    <li>• Menge und Uhrzeit notieren</li>
                                    <li>• NICHT zum Erbrechen bringen</li>
                                    <li>• Tierarzt anrufen und Situation schildern</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Action buttons
        html += `
            <div class="doggl-actions">
                <button class="doggl-btn doggl-btn-primary" onclick="dogglShare()">
                    <span class="doggl-btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-share2 h-4 w-4 mr-2"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"></line><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"></line></svg></span>
                    Ergebnis teilen
                </button>
                <button class="doggl-btn doggl-btn-secondary" onclick="dogglExportPDF()">
                    <span class="doggl-btn-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4 mr-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg></span>
                    Als PDF speichern
                </button>
            </div>
        `;
        
        $resultCard.html(html).removeClass().addClass(`doggl-result-card doggl-status-${selectedFood.status}`).show();
        
        trackEvent('food_result_view', { 
            food_name: selectedFood.name, 
            status: selectedFood.status,
            weight: dogWeight 
        });
    }
    
    function calculatePortion(food, weight) {
        if (!food.portionGPerKg) return 0;
        return Math.round(weight * food.portionGPerKg);
    }
    
    function getStatusConfig(status) {
        const configs = {
            safe: {
                icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle h-8 w-8 mr-3 text-green-600"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>',
                title: 'Erlaubt',
                shortAnswer: 'Ja, in Maßen erlaubt'
            },
            caution: {
                icon: '⚠️',
                title: 'Vorsicht',
                shortAnswer: 'Nur selten und wenig'
            },
            danger: {
                icon: '🚨',
                title: 'Gefährlich',
                shortAnswer: 'Nein – gefährlich!'
            },
            toxic: {
                icon: '☠️',
                title: 'Hochgiftig',
                shortAnswer: 'Nein – hochgiftig!'
            }
        };
        return configs[status] || configs.caution;
    }
    
    function getStatusIcon(status) {
        return getStatusConfig(status).icon;
    }
    
    function getStatusTitle(status) {
        return getStatusConfig(status).title;
    }
    
    function getFrequencyText(frequency) {
        const texts = {
            never: 'Niemals',
            rare: 'Sehr selten (max. 1x pro Monat)',
            occasional: 'Gelegentlich (max. 1x pro Woche)',
            often: 'Häufiger möglich'
        };
        return texts[frequency] || 'Unbekannt';
    }
    
    function setupFAQ() {
        $('.doggl-faq-question').on('click', function() {
            const $this = $(this);
            const $answer = $this.next('.doggl-faq-answer');
            const isOpen = $this.attr('aria-expanded') === 'true';
            
            // Close all other FAQ items
            $('.doggl-faq-question').attr('aria-expanded', 'false');
            $('.doggl-faq-answer').removeClass('open').slideUp(200);
            
            if (!isOpen) {
                $this.attr('aria-expanded', 'true');
                $answer.addClass('open').slideDown(200);
            }
        });
    }
    
    function toggleFAQ() {
        // Handled by setupFAQ
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function trackEvent(eventName, data) {
        // Analytics tracking - implement as needed
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, data);
        }
        console.log('Event:', eventName, data);
    }
    
    // Global functions for button clicks
    window.dogglShare = function() {
        if (!selectedFood) return;
        
        const shareData = {
            foodId: selectedFood.id,
            foodName: selectedFood.name,
            status: selectedFood.status,
            weight: dogWeight,
            portion: calculatePortion(selectedFood, dogWeight)
        };
        
        $.ajax({
            url: doggl_food.rest_url + 'food/share',
            method: 'POST',
            data: JSON.stringify(shareData),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', doggl_food.nonce);
            }
        })
        .done(function(response) {
            if (navigator.share) {
                navigator.share({
                    title: `Darf mein Hund ${selectedFood.name} essen?`,
                    text: `Ergebnis: ${getStatusConfig(selectedFood.status).shortAnswer}`,
                    url: response.url
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(response.url).then(() => {
                    alert(doggl_food.strings.share_success);
                });
            }
            
            trackEvent('food_share', { food_name: selectedFood.name });
        })
        .fail(function(xhr) {
            console.error('Share failed:', xhr.responseJSON);
            alert('Teilen fehlgeschlagen. Bitte versuche es erneut.');
        });
    };
    
    window.dogglExportPDF = function() {
        if (!selectedFood) return;
        
        const exportData = {
            food: selectedFood,
            weight: dogWeight,
            portion: calculatePortion(selectedFood, dogWeight),
            timestamp: new Date().toISOString()
        };
        
        $.ajax({
            url: doggl_food.rest_url + 'food/export',
            method: 'POST',
            data: JSON.stringify(exportData),
            contentType: 'application/json',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', doggl_food.nonce);
            }
        })
        .done(function(response) {
            alert(doggl_food.strings.pdf_generating);
            trackEvent('food_pdf', { food_name: selectedFood.name });
        })
        .fail(function(xhr) {
            console.error('PDF export failed:', xhr.responseJSON);
            alert('PDF-Export fehlgeschlagen. Bitte versuche es erneut.');
        });
    };
});