/*
 * Budcedostu Custom Multilingual System - Admin JavaScript
 */

(function($) {
    'use strict';

    var BudcedostuAdminMultilingual = {
        
        init: function() {
            this.bindEvents();
            this.handleTranslationCreation();
            this.addBulkActions();
            this.enhanceLanguageColumns();
        },
        
        bindEvents: function() {
            // Handle + button clicks for creating translations
            $(document).on('click', '.lang-missing', this.handleCreateTranslation);
            
            // Handle bulk translation actions
            $(document).on('change', '#bulk-action-selector-top, #bulk-action-selector-bottom', this.handleBulkAction);
            
            // Language filter in post lists
            $(document).on('change', 'select[name="language_filter"]', this.handleLanguageFilter);
            
            // Translation metabox interactions
            $(document).on('click', '.translation-create-link', this.handleTranslationLink);
            
            // Quick edit language selector
            $(document).on('click', '.editinline', this.addLanguageSelectorToQuickEdit);
        },
        
        handleCreateTranslation: function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var postId = $button.closest('tr').find('.check-column input[type="checkbox"]').val();
            var targetLang = $button.closest('td').data('lang') || $button.data('lang');
            
            if (!postId || !targetLang) {
                alert('Error: Missing post ID or target language');
                return;
            }
            
            // Show loading state
            $button.html('...');
            
            // Redirect to new post page with translation parameters
            var postType = $('body').hasClass('post-type-page') ? 'page' : 'post';
            var createUrl = ajaxurl.replace('admin-ajax.php', '') + 'post-new.php?post_type=' + postType + 
                           '&translate_from=' + postId + '&target_lang=' + targetLang;
            
            window.location.href = createUrl;
        },
        
        handleTranslationCreation: function() {
            // Handle translation creation from URL parameters
            var urlParams = new URLSearchParams(window.location.search);
            var translateFrom = urlParams.get('translate_from');
            var targetLang = urlParams.get('target_lang');
            
            if (translateFrom && targetLang) {
                this.setupTranslationEditor(translateFrom, targetLang);
            }
        },
        
        setupTranslationEditor: function(sourcePostId, targetLang) {
            // Add notice about translation source
            var $notice = $('<div class="notice notice-info translation-source-info">' +
                '<h3>Creating Translation</h3>' +
                '<p>Target Language: <strong>' + targetLang.toUpperCase() + '</strong></p>' +
                '<p>Source Post ID: <strong>' + sourcePostId + '</strong></p>' +
                '<div class="source-content" id="source-content-preview">Loading source content...</div>' +
                '</div>');
            
            $('.wrap h1').after($notice);
            
            // Load source content via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'budcedostu_get_source_content',
                    post_id: sourcePostId,
                    nonce: multilingual_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#source-content-preview').html(response.data.content.substring(0, 300) + '...');
                        
                        // Pre-fill title with indication it needs translation
                        if (response.data.title && !$('#title').val()) {
                            $('#title').val('[TRANSLATE] ' + response.data.title);
                        }
                        
                        // Set language meta
                        $('<input type="hidden" name="budcedostu_post_language" value="' + targetLang + '">').appendTo('form#post');
                        $('<input type="hidden" name="budcedostu_translation_source" value="' + sourcePostId + '">').appendTo('form#post');
                    }
                },
                error: function() {
                    $('#source-content-preview').html('Error loading source content.');
                }
            });
        },
        
        handleBulkAction: function() {
            var $select = $(this);
            var action = $select.val();
            
            if (action.startsWith('create_translations_')) {
                var targetLang = action.replace('create_translations_', '');
                this.addBulkTranslationNotice(targetLang);
            }
        },
        
        addBulkActions: function() {
            // Add bulk translation options
            var $bulkSelectors = $('#bulk-action-selector-top, #bulk-action-selector-bottom');
            
            $bulkSelectors.each(function() {
                var $select = $(this);
                $select.append('<option value="create_translations_ru">Create Russian Translations</option>');
                $select.append('<option value="create_translations_en">Create English Translations</option>');
                $select.append('<option value="create_translations_az">Create Azerbaijani Translations</option>');
            });
        },
        
        addBulkTranslationNotice: function(targetLang) {
            var $notice = $('.bulk-translate-notice');
            if ($notice.length === 0) {
                $notice = $('<div class="notice notice-warning bulk-translate-notice">' +
                    '<p><strong>Bulk Translation:</strong> Select posts/pages and apply the bulk action to create ' + 
                    targetLang.toUpperCase() + ' translations.</p>' +
                    '</div>');
                $('.tablenav.top').before($notice);
            }
        },
        
        enhanceLanguageColumns: function() {
            // Add tooltips to language status indicators
            $(document).on('mouseenter', '.lang-status', function() {
                var $this = $(this);
                var lang = $this.data('lang');
                var status = $this.data('status');
                
                var tooltipText = '';
                switch(status) {
                    case 'current':
                        tooltipText = 'Current language version';
                        break;
                    case 'translated':
                        tooltipText = 'Translation exists - click to edit';
                        break;
                    case 'missing':
                        tooltipText = 'Click + to create translation';
                        break;
                }
                
                if (!$this.attr('title')) {
                    $this.attr('title', tooltipText);
                }
            });
        },
        
        handleLanguageFilter: function() {
            var $select = $(this);
            var selectedLang = $select.val();
            
            if (selectedLang) {
                // Add language filter to current URL
                var url = new URL(window.location);
                url.searchParams.set('language_filter', selectedLang);
                window.location.href = url.toString();
            }
        },
        
        handleTranslationLink: function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var href = $link.attr('href');
            
            // Add confirmation for translation creation
            if ($link.text().includes('Create')) {
                var targetLang = $link.data('lang') || 'unknown';
                var confirmed = confirm('Create a new ' + targetLang.toUpperCase() + ' translation? This will open a new post editor.');
                
                if (confirmed) {
                    window.location.href = href;
                }
            } else {
                window.location.href = href;
            }
        },
        
        addLanguageSelectorToQuickEdit: function() {
            var $row = $(this).closest('tr');
            var postId = $row.find('.check-column input').val();
            
            // Add language selector to quick edit row (this would need more complex implementation)
            // For now, we'll just add a note about full edit being needed
            setTimeout(function() {
                var $quickEdit = $('.inline-edit-row');
                if ($quickEdit.length && !$quickEdit.find('.language-note').length) {
                    $('<div class="language-note" style="clear:both;padding:5px 0;"><em>Note: Use full editor to change post language or manage translations.</em></div>')
                        .appendTo($quickEdit.find('.inline-edit-col-right'));
                }
            }, 100);
        },
        
        // Translation statistics dashboard widget
        loadTranslationStats: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'budcedostu_translation_stats',
                    nonce: multilingual_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#translation-stats-widget .inside').html(response.data.html);
                    }
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BudcedostuAdminMultilingual.init();
        
        // Load translation stats if widget exists
        if ($('#translation-stats-widget').length) {
            BudcedostuAdminMultilingual.loadTranslationStats();
        }
    });

    // Make it available globally
    window.BudcedostuAdminMultilingual = BudcedostuAdminMultilingual;

})(jQuery);