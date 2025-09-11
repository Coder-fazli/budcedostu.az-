/*
 * Budcedostu Custom Multilingual System - Frontend JavaScript
 */

(function($) {
    'use strict';

    var BudcedostuMultilingual = {
        
        init: function() {
            this.bindEvents();
            this.handleLanguageSwitching();
            this.addKeyboardNavigation();
        },
        
        bindEvents: function() {
            // Language switcher clicks
            $(document).on('click', '.budcedostu-language-switcher .lang-switch:not(.disabled)', this.handleLanguageSwitch);
            
            // Keyboard navigation for language switcher
            $(document).on('keydown', '.budcedostu-language-switcher .lang-switch', this.handleKeyboardNavigation);
            
            // Handle browser back/forward with language context
            $(window).on('popstate', this.handlePopState);
        },
        
        handleLanguageSwitch: function(e) {
            var $link = $(this);
            var targetLang = $link.data('lang');
            var currentUrl = window.location.href;
            
            // Add loading state
            $link.addClass('loading');
            
            // Store language preference in localStorage
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem('budcedostu_preferred_language', targetLang);
            }
            
            // Set cookie for server-side detection
            BudcedostuMultilingual.setCookie('budcedostu_lang', targetLang, 30);
            
            // Analytics tracking if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'language_switch', {
                    'event_category': 'multilingual',
                    'event_label': targetLang,
                    'value': 1
                });
            }
            
            // Don't prevent default - let normal navigation happen
        },
        
        handleLanguageSwitching: function() {
            // Check for language preference on page load
            if (typeof(Storage) !== "undefined") {
                var preferredLang = localStorage.getItem('budcedostu_preferred_language');
                var currentLang = multilingual_ajax.current_lang;
                
                // If preferred language differs and we're on homepage, suggest switching
                if (preferredLang && preferredLang !== currentLang && this.isHomePage()) {
                    this.showLanguageSuggestion(preferredLang);
                }
            }
        },
        
        showLanguageSuggestion: function(suggestedLang) {
            var langData = multilingual_ajax.languages[suggestedLang];
            if (!langData) return;
            
            var message = 'Would you like to view this site in ' + langData.name + '?';
            var $suggestion = $('<div class="language-suggestion">' +
                '<p>' + message + '</p>' +
                '<button class="switch-lang" data-lang="' + suggestedLang + '">Yes, switch to ' + langData.name + '</button>' +
                '<button class="dismiss-suggestion">No, stay in current language</button>' +
                '</div>');
            
            $suggestion.prependTo('body');
            
            $suggestion.find('.switch-lang').on('click', function() {
                var lang = $(this).data('lang');
                var homeUrl = lang === 'az' ? '/' : '/' + multilingual_ajax.languages[lang].url_prefix + '/';
                window.location.href = homeUrl;
            });
            
            $suggestion.find('.dismiss-suggestion').on('click', function() {
                $suggestion.remove();
                localStorage.removeItem('budcedostu_preferred_language');
            });
            
            // Auto-dismiss after 10 seconds
            setTimeout(function() {
                $suggestion.fadeOut(function() {
                    $suggestion.remove();
                });
            }, 10000);
        },
        
        handleKeyboardNavigation: function(e) {
            var $switcher = $('.budcedostu-language-switcher');
            var $links = $switcher.find('.lang-switch:not(.disabled)');
            var currentIndex = $links.index($(this));
            
            switch(e.keyCode) {
                case 37: // Left arrow
                    e.preventDefault();
                    var prevIndex = currentIndex > 0 ? currentIndex - 1 : $links.length - 1;
                    $links.eq(prevIndex).focus();
                    break;
                case 39: // Right arrow
                    e.preventDefault();
                    var nextIndex = currentIndex < $links.length - 1 ? currentIndex + 1 : 0;
                    $links.eq(nextIndex).focus();
                    break;
                case 13: // Enter
                case 32: // Space
                    e.preventDefault();
                    $(this)[0].click();
                    break;
            }
        },
        
        addKeyboardNavigation: function() {
            // Add ARIA attributes for accessibility
            $('.budcedostu-language-switcher').attr({
                'role': 'navigation',
                'aria-label': 'Language selector'
            });
            
            $('.budcedostu-language-switcher .lang-switch').attr({
                'role': 'link',
                'tabindex': '0'
            });
            
            $('.budcedostu-language-switcher .lang-switch.disabled').attr({
                'aria-disabled': 'true',
                'tabindex': '-1'
            });
            
            $('.budcedostu-language-switcher .lang-switch.current').attr({
                'aria-current': 'page'
            });
        },
        
        handlePopState: function(e) {
            // Handle browser navigation while maintaining language context
            if (e.originalEvent.state && e.originalEvent.state.language) {
                var lang = e.originalEvent.state.language;
                BudcedostuMultilingual.updateLanguageSwitcher(lang);
            }
        },
        
        updateLanguageSwitcher: function(currentLang) {
            $('.budcedostu-language-switcher .lang-switch').removeClass('current');
            $('.budcedostu-language-switcher .lang-switch[data-lang="' + currentLang + '"]').addClass('current');
        },
        
        isHomePage: function() {
            var currentPath = window.location.pathname;
            return currentPath === '/' || currentPath === '/ru/' || currentPath === '/en/';
        },
        
        setCookie: function(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        },
        
        getCookie: function(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BudcedostuMultilingual.init();
    });

    // Make it available globally
    window.BudcedostuMultilingual = BudcedostuMultilingual;

})(jQuery);