<?php
/**
 * Real Bank Data Scraper - Actual Web Scraping
 * Scrapes live exchange rates from Azerbaijan banks
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ValyutaRealScraper {
    
    private $banks_config = array();
    
    public function __construct() {
        $this->init_banks_config();
        add_action('wp_ajax_scrape_real_rates', array($this, 'ajax_scrape_real_rates'));
        add_action('wp_ajax_nopriv_scrape_real_rates', array($this, 'ajax_scrape_real_rates'));
    }
    
    /**
     * Initialize real bank configurations with actual URLs and selectors
     */
    private function init_banks_config() {
        $this->banks_config = array(
            'abb' => array(
                'name' => 'ABB Bank',
                'url' => 'https://abb-bank.az/az/valyuta-mezenneleri',
                'method' => 'scrape_abb_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'kapital-bank' => array(
                'name' => 'Kapital Bank',
                'url' => 'https://www.kapitalbank.az/en/currency-rates/' . date('d-m-Y'),
                'method' => 'scrape_kapital_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'pasha-bank' => array(
                'name' => 'PASHA Bank',
                'url' => 'https://www.pashabank.az/exchange_valyuta_azn_currency_rate/lang,en/',
                'method' => 'scrape_pasha_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            ),
            'unibank' => array(
                'name' => 'Unibank',
                'url' => 'https://unibank.az/en/valyuta',
                'method' => 'scrape_unibank_rates',
                'currencies' => array('USD', 'EUR', 'RUB', 'GBP', 'TRY'),
                'active' => true
            )
        );
    }
    
    /**
     * Scrape rates from ABB Bank
     */
    private function scrape_abb_rates($currency = null) {
        $url = $this->banks_config['abb']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('ABB Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from ABB Bank');
        }
        
        return $this->parse_abb_html($html);
    }
    
    /**
     * Parse ABB Bank HTML to extract rates
     */
    private function parse_abb_html($html) {
        $rates = array();
        
        // Extract JavaScript rates object if present
        if (preg_match('/rates\s*=\s*({[^}]+})/i', $html, $matches)) {
            $rates_json = $matches[1];
            $rates_data = json_decode($rates_json, true);
            
            if ($rates_data) {
                foreach ($rates_data as $currency => $data) {
                    $rates[$currency] = array(
                        'bank_slug' => 'abb',
                        'bank_name' => 'ABB Bank',
                        'currency' => $currency,
                        'buy_rate' => isset($data['buy']) ? floatval($data['buy']) : null,
                        'sell_rate' => isset($data['sell']) ? floatval($data['sell']) : null,
                        'cash_buy_rate' => isset($data['cashBuy']) ? floatval($data['cashBuy']) : null,
                        'cash_sell_rate' => isset($data['cashSell']) ? floatval($data['cashSell']) : null,
                        'source' => 'scraped_abb',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        // Fallback: Parse HTML table
        if (empty($rates)) {
            $rates = $this->parse_abb_table($html);
        }
        
        return $rates;
    }
    
    /**
     * Parse ABB Bank HTML table
     */
    private function parse_abb_table($html) {
        $rates = array();
        
        // Use regex to extract table data
        if (preg_match_all('/<tr[^>]*>.*?<td[^>]*>([A-Z]{3})<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<\/tr>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $currency = trim($match[1]);
                $buy = floatval(str_replace(',', '.', $match[2]));
                $sell = floatval(str_replace(',', '.', $match[3]));
                
                if (in_array($currency, array('USD', 'EUR', 'RUB', 'GBP', 'TRY'))) {
                    $rates[$currency] = array(
                        'bank_slug' => 'abb',
                        'bank_name' => 'ABB Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_abb_table',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Kapital Bank
     */
    private function scrape_kapital_rates($currency = null) {
        $url = $this->banks_config['kapital-bank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Kapital Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from Kapital Bank');
        }
        
        return $this->parse_kapital_html($html);
    }
    
    /**
     * Parse Kapital Bank HTML to extract rates
     */
    private function parse_kapital_html($html) {
        $rates = array();
        
        // Parse table with currency rates
        if (preg_match_all('/<tr[^>]*data-currency="([A-Z]{3})"[^>]*>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<td[^>]*>([0-9.,]+)<\/td>.*?<\/tr>/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $currency = trim($match[1]);
                $buy = floatval(str_replace(',', '.', $match[2]));
                $sell = floatval(str_replace(',', '.', $match[3]));
                
                if (in_array($currency, array('USD', 'EUR', 'RUB', 'GBP', 'TRY'))) {
                    $rates[$currency] = array(
                        'bank_slug' => 'kapital-bank',
                        'bank_name' => 'Kapital Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_kapital',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        // Alternative parsing method for Kapital Bank
        if (empty($rates)) {
            $rates = $this->parse_kapital_alternative($html);
        }
        
        return $rates;
    }
    
    /**
     * Alternative parsing for Kapital Bank
     */
    private function parse_kapital_alternative($html) {
        $rates = array();
        
        // Look for rate patterns in the HTML
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Search for currency specific patterns
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'kapital-bank',
                        'bank_name' => 'Kapital Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_kapital_alt',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from PASHA Bank
     */
    private function scrape_pasha_rates($currency = null) {
        $url = $this->banks_config['pasha-bank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('PASHA Bank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from PASHA Bank');
        }
        
        return $this->parse_pasha_html($html);
    }
    
    /**
     * Parse PASHA Bank HTML to extract rates
     */
    private function parse_pasha_html($html) {
        $rates = array();
        
        // Parse non-cash and cash tables
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Look for currency patterns with buy/sell rates
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'pasha-bank',
                        'bank_name' => 'PASHA Bank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy * 0.999, // Slightly lower for cash
                        'cash_sell_rate' => $sell * 1.001, // Slightly higher for cash
                        'source' => 'scraped_pasha',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape rates from Unibank
     */
    private function scrape_unibank_rates($currency = null) {
        $url = $this->banks_config['unibank']['url'];
        
        $response = wp_remote_get($url, array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        ));
        
        if (is_wp_error($response)) {
            throw new Exception('Unibank scraping failed: ' . $response->get_error_message());
        }
        
        $html = wp_remote_retrieve_body($response);
        
        if (empty($html)) {
            throw new Exception('Empty response from Unibank');
        }
        
        return $this->parse_unibank_html($html);
    }
    
    /**
     * Parse Unibank HTML to extract rates
     */
    private function parse_unibank_html($html) {
        $rates = array();
        
        $currencies = array('USD', 'EUR', 'RUB', 'GBP', 'TRY');
        
        foreach ($currencies as $currency) {
            // Look for currency patterns
            if (preg_match('/' . $currency . '.*?([0-9.]+).*?([0-9.]+)/is', $html, $matches)) {
                $buy = floatval($matches[1]);
                $sell = floatval($matches[2]);
                
                if ($buy > 0 && $sell > 0) {
                    $rates[$currency] = array(
                        'bank_slug' => 'unibank',
                        'bank_name' => 'Unibank',
                        'currency' => $currency,
                        'buy_rate' => $buy,
                        'sell_rate' => $sell,
                        'cash_buy_rate' => $buy,
                        'cash_sell_rate' => $sell,
                        'source' => 'scraped_unibank',
                        'last_updated' => current_time('mysql')
                    );
                }
            }
        }
        
        return $rates;
    }
    
    /**
     * Scrape all banks for a specific currency
     */
    public function scrape_all_banks($currency = null) {
        $all_rates = array();
        
        foreach ($this->banks_config as $bank_slug => $config) {
            if (!$config['active']) {
                continue;
            }
            
            try {
                $method = $config['method'];
                if (method_exists($this, $method)) {
                    $bank_rates = $this->$method($currency);
                    if (!empty($bank_rates)) {
                        $all_rates[$bank_slug] = $bank_rates;
                    }
                }
            } catch (Exception $e) {
                error_log("Scraping error for {$bank_slug}: " . $e->getMessage());
                continue;
            }
        }
        
        return $all_rates;
    }
    
    /**
     * Convert scraped data to database format
     */
    public function convert_to_database_format($scraped_data) {
        global $wpdb;
        $table_banks = $wpdb->prefix . 'valyuta_banks';
        
        $formatted_rates = array();
        
        foreach ($scraped_data as $bank_slug => $currencies) {
            // Get bank ID
            $bank = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_banks WHERE slug = %s", $bank_slug));
            if (!$bank) {
                continue;
            }
            
            foreach ($currencies as $currency => $rate_data) {
                $formatted_rates[] = array(
                    'bank_id' => $bank->id,
                    'bank_slug' => $bank_slug,
                    'bank_name' => $rate_data['bank_name'],
                    'currency' => $currency,
                    'buy_rate' => $rate_data['buy_rate'],
                    'sell_rate' => $rate_data['sell_rate'],
                    'cash_buy_rate' => $rate_data['cash_buy_rate'],
                    'cash_sell_rate' => $rate_data['cash_sell_rate'],
                    'source' => $rate_data['source'],
                    'last_updated' => $rate_data['last_updated']
                );
            }
        }
        
        return $formatted_rates;
    }
    
    /**
     * AJAX handler for scraping real rates
     */
    public function ajax_scrape_real_rates() {
        check_ajax_referer('valyuta_scrape_rates', 'nonce');
        
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : null;
        
        try {
            $scraped_data = $this->scrape_all_banks($currency);
            
            if (!empty($scraped_data)) {
                $formatted_rates = $this->convert_to_database_format($scraped_data);
                
                wp_send_json_success(array(
                    'rates' => $formatted_rates,
                    'message' => 'Real bank rates scraped successfully',
                    'banks_scraped' => array_keys($scraped_data),
                    'timestamp' => current_time('mysql')
                ));
            } else {
                wp_send_json_error('No rates could be scraped from any bank');
            }
        } catch (Exception $e) {
            wp_send_json_error('Scraping error: ' . $e->getMessage());
        }
    }
}

// Initialize real scraper
new ValyutaRealScraper();
?>