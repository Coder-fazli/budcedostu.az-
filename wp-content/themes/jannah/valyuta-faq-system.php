<?php
/**
 * Valyuta FAQ Management System
 * Creates admin interface and database for FAQ management
 */

// Create FAQ table on activation
function create_valyuta_faq_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'valyuta_faqs';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        question text NOT NULL,
        answer longtext NOT NULL,
        display_order int(11) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insert default FAQs
    $default_faqs = [
        [
            'question' => 'Valyuta kalkulyatoru necə işləyir?',
            'answer' => 'Valyuta kalkulyatoru real vaxt məzənnələri əsasında hesablamaları aparır. Sadəcə məbləği və valyutaları seçin, sistem avtomatik olaraq konvertasiya edəcək.',
            'display_order' => 1
        ],
        [
            'question' => 'Valyuta yenilənirmi?',
            'answer' => 'Bəli, valyuta məzənnələri hər 10 dəqiqədən bir avtomatik olaraq bankların rəsmi saytlarından yenilənir.',
            'display_order' => 2
        ],
        [
            'question' => 'Hansı bankların məzənnələri göstərilir?',
            'answer' => 'Sistemimiz Azərbaycanın aparıcı banklarının məzənnələrini göstərir: ABB, Kapital Bank, PASHA Bank, Unibank və digər böyük banklar.',
            'display_order' => 3
        ],
        [
            'question' => 'Məzənnə fərqləri niyə olur?',
            'answer' => 'Hər bank öz məzənnələrini müstəqil olaraq müəyyən edir. Bu səbəbdən banklar arasında kiçik fərqlər ola bilər.',
            'display_order' => 4
        ],
        [
            'question' => 'Sizinlə necə əlaqə qura bilərik?',
            'answer' => 'Bizimlə əlaqə üçün saytın "Əlaqə" bölməsindən istifadə edə bilərsiniz və ya elektron poçt vasitəsilə yazışa bilərsiniz.',
            'display_order' => 5
        ]
    ];
    
    foreach ($default_faqs as $faq) {
        $wpdb->insert($table_name, $faq);
    }
}

// Add admin menu
function valyuta_faq_admin_menu() {
    add_submenu_page(
        'valyuta-rates',
        'FAQ İdarəetmə',
        'FAQ İdarəetmə',
        'manage_options',
        'valyuta-faq',
        'valyuta_faq_admin_page'
    );
}
add_action('admin_menu', 'valyuta_faq_admin_menu', 20);

// Admin page
function valyuta_faq_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'valyuta_faqs';
    
    // Handle form submissions
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_faq') {
            $wpdb->insert($table_name, [
                'question' => sanitize_textarea_field($_POST['question']),
                'answer' => wp_kses_post($_POST['answer']),
                'display_order' => intval($_POST['display_order']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);
            echo '<div class="notice notice-success"><p>FAQ uğurla əlavə edildi!</p></div>';
        } elseif ($_POST['action'] === 'update_faq') {
            $wpdb->update($table_name, [
                'question' => sanitize_textarea_field($_POST['question']),
                'answer' => wp_kses_post($_POST['answer']),
                'display_order' => intval($_POST['display_order']),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ], ['id' => intval($_POST['faq_id'])]);
            echo '<div class="notice notice-success"><p>FAQ uğurla yeniləndi!</p></div>';
        } elseif ($_POST['action'] === 'delete_faq') {
            $wpdb->delete($table_name, ['id' => intval($_POST['faq_id'])]);
            echo '<div class="notice notice-success"><p>FAQ uğurla silindi!</p></div>';
        }
    }
    
    // Get all FAQs
    $faqs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY display_order ASC, created_at DESC");
    
    // Get FAQ for editing
    $edit_faq = null;
    if (isset($_GET['edit'])) {
        $edit_faq = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['edit'])));
    }
    
    ?>
    <div class="wrap">
        <h1>FAQ İdarəetmə</h1>
        
        <div class="card" style="max-width: 800px; margin: 20px 0;">
            <h2><?php echo $edit_faq ? 'FAQ Redaktə Et' : 'Yeni FAQ Əlavə Et'; ?></h2>
            <form method="post">
                <input type="hidden" name="action" value="<?php echo $edit_faq ? 'update_faq' : 'add_faq'; ?>">
                <?php if ($edit_faq): ?>
                    <input type="hidden" name="faq_id" value="<?php echo $edit_faq->id; ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Sual</th>
                        <td>
                            <textarea name="question" rows="3" class="regular-text" style="width: 100%;" required><?php echo $edit_faq ? esc_textarea($edit_faq->question) : ''; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Cavab</th>
                        <td>
                            <?php 
                            wp_editor(
                                $edit_faq ? $edit_faq->answer : '', 
                                'answer', 
                                [
                                    'textarea_name' => 'answer',
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny' => true
                                ]
                            ); 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Göstərilmə sırası</th>
                        <td>
                            <input type="number" name="display_order" value="<?php echo $edit_faq ? $edit_faq->display_order : '0'; ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Aktiv</th>
                        <td>
                            <input type="checkbox" name="is_active" value="1" <?php echo (!$edit_faq || $edit_faq->is_active) ? 'checked' : ''; ?>>
                            <label>Bu FAQ-ı göstər</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($edit_faq ? 'FAQ Yenilə' : 'FAQ Əlavə Et'); ?>
                
                <?php if ($edit_faq): ?>
                    <a href="<?php echo admin_url('admin.php?page=valyuta-faq'); ?>" class="button">Ləğv et</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <h2>Mövcud FAQ-lar</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 40%;">Sual</th>
                        <th style="width: 60px;">Sıra</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 120px;">Tarix</th>
                        <th style="width: 150px;">Əməllər</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqs as $faq): ?>
                    <tr>
                        <td><?php echo $faq->id; ?></td>
                        <td>
                            <strong><?php echo esc_html(wp_trim_words($faq->question, 8)); ?></strong>
                            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                <?php echo esc_html(wp_trim_words(strip_tags($faq->answer), 15)); ?>
                            </div>
                        </td>
                        <td><?php echo $faq->display_order; ?></td>
                        <td>
                            <span style="color: <?php echo $faq->is_active ? 'green' : 'red'; ?>;">
                                <?php echo $faq->is_active ? 'Aktiv' : 'Deaktiv'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d.m.Y H:i', strtotime($faq->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=valyuta-faq&edit=' . $faq->id); ?>" class="button button-small">Redaktə</a>
                            <form method="post" style="display: inline;" onsubmit="return confirm('Bu FAQ-ı silmək istədiyinizə əminsiniz?');">
                                <input type="hidden" name="action" value="delete_faq">
                                <input type="hidden" name="faq_id" value="<?php echo $faq->id; ?>">
                                <input type="submit" class="button button-small button-link-delete" value="Sil">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Get FAQs for display
function get_valyuta_faqs() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'valyuta_faqs';
    
    return $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
}

// Initialize FAQ system
function init_valyuta_faq_system() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'valyuta_faqs';
    
    // Check if table exists, if not create it
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_valyuta_faq_table();
    }
    
    // Debug: Log that FAQ system is initialized
    if (WP_DEBUG) {
        error_log('Valyuta FAQ System initialized - Table exists: ' . ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name ? 'Yes' : 'No'));
    }
}
add_action('init', 'init_valyuta_faq_system');

// Also run on admin_init to ensure it's available in admin
function init_valyuta_faq_admin() {
    init_valyuta_faq_system();
}
add_action('admin_init', 'init_valyuta_faq_admin');

?>