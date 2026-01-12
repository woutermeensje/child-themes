<?php

function display_tax_terms($tax, $post_id)
{

    $terms = wp_get_post_terms($post_id, $tax, array('fields' => 'names'));


    return implode(',', $terms);
}


function get_secondary_imageurl($post_id)
{
    $image_id = get_post_meta($post_id, '_uncode_secondary_thumbnail_id', true);

    return wp_get_attachment_image_url($image_id, 'large');
}

add_action('job_manager_job_submitted_content_after', 'create_thankyou_page_pageview');
function create_thankyou_page_pageview()
{
    echo "<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('event', 'page_view',{
			'page_title': 'Vacature geplaatst',
			'page_location': '/plaats-een-vacature/',
			'page_path': '/plaats-een-vacature/bedankt',
			'send_to': 'G-G3HL6WW75F'
		});
	</script>";
}
add_filter('submit_job_form_fields', 'frontend_add_indeed_field');
function frontend_add_indeed_field($fields)
{
    $fields['company']['company_indeed'] = array(
        'label'       => __('Indeed', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'Indeed link',
        'priority'    => 5
    );
    return $fields;
}

add_filter('job_manager_job_listing_data_fields', 'admin_add_indeed_field');
function admin_add_indeed_field($fields)
{
    $fields['_company_indeed'] = array(
        'label'       => __('indeed', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'Indeed link',
        'description' => ''
    );
    return $fields;
}

add_filter('submit_job_form_fields', 'frontend_add_facebook_field');
function frontend_add_facebook_field($fields)
{
    $fields['company']['company_facebook'] = array(
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://faceboook.com/your-company',
        'priority'    => 5
    );
    return $fields;
}

add_filter('job_manager_job_listing_data_fields', 'admin_add_facebook_field');
function admin_add_facebook_field($fields)
{
    $fields['_company_facebook'] = array(
        'label'       => __('Facebook', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://faceboook.com/your-company',
        'description' => ''
    );
    return $fields;
}

add_filter('submit_job_form_fields', 'frontend_add_linkedin_field');
function frontend_add_linkedin_field($fields)
{
    $fields['company']['company_linkedin'] = array(
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'required'    => false,
        'placeholder' => 'https://linkedin.com/your-company',
        'priority'    => 5
    );
    return $fields;
}

add_filter('job_manager_job_listing_data_fields', 'admin_add_linkedin_field');
function admin_add_linkedin_field($fields)
{
    $fields['_company_linkedin'] = array(
        'label'       => __('LinkedIn', 'job_manager'),
        'type'        => 'text',
        'placeholder' => 'https://linkedin.com/in/your-company',
        'description' => ''
    );
    return $fields;
}

add_filter('submit_job_form_fields', 'frontend_add_cover_image_field');
function frontend_add_cover_image_field($fields)
{
    $fields['job']['cover_image'] = array(
        'label'       => __('Cover afbeelding', 'job_manager'),
        'type'        => 'file',
        'accept'        => 'image/png, image/jpeg',
        'required'    => false,
        'priority'    => 7
    );
    return $fields;
}

add_filter('job_manager_job_listing_data_fields', 'admin_add_cover_image_field');
function admin_add_cover_image_field($fields)
{
    $fields['_cover_image'] = array(
        'label'       => __('Cover afbeelding', 'job_manager'),
        'type'        => 'file',
    );
    return $fields;
}

add_action('init', 'register_opleiding_niveaus');
function register_opleiding_niveaus()
{
    $args = [
        'label'  => esc_html__('Opleiding niveaus', 'sustainablejobs'),
        'labels' => [
            'menu_name'                  => esc_html__('Opleiding niveaus', 'sustainablejobs'),
            'all_items'                  => esc_html__('All Opleiding niveaus', 'sustainablejobs'),
            'edit_item'                  => esc_html__('Edit Opleiding niveaus', 'sustainablejobs'),
            'view_item'                  => esc_html__('View Opleiding niveaus', 'sustainablejobs'),
            'update_item'                => esc_html__('Update Opleiding niveaus', 'sustainablejobs'),
            'add_new_item'               => esc_html__('Add new Opleiding niveaus', 'sustainablejobs'),
            'new_item'                   => esc_html__('New Opleiding niveaus', 'sustainablejobs'),
            'parent_item'                => esc_html__('Parent Opleiding niveaus', 'sustainablejobs'),
            'parent_item_colon'          => esc_html__('Parent Opleiding niveaus', 'sustainablejobs'),
            'search_items'               => esc_html__('Search Opleiding niveaus', 'sustainablejobs'),
            'popular_items'              => esc_html__('Popular Opleiding niveaus', 'sustainablejobs'),
            'separate_items_with_commas' => esc_html__('Separate Opleiding niveaus with commas', 'sustainablejobs'),
            'add_or_remove_items'        => esc_html__('Add or remove Opleiding niveaus', 'sustainablejobs'),
            'choose_from_most_used'      => esc_html__('Choose most used Opleiding niveaus', 'sustainablejobs'),
            'not_found'                  => esc_html__('No Opleiding niveaus found', 'sustainablejobs'),
            'name'                       => esc_html__('Opleiding niveaus', 'sustainablejobs'),
            'singular_name'              => esc_html__('Opleiding niveaus', 'sustainablejobs'),
        ],
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'show_in_nav_menus'    => true,
        'show_tagcloud'        => true,
        'show_in_quick_edit'   => true,
        'show_admin_column'    => false,
        'show_in_rest'         => true,
        'hierarchical'         => true,
        'query_var'            => true,
        'sort'                 => false,
        'rewrite_no_front'     => false,
        'rewrite_hierarchical' => false,
        'rewrite' => true
    ];
    register_taxonomy('opleiding-niveau', ['job_listing'], $args);
}


add_action('init', 'register_werkervaring');
function register_werkervaring()
{
    $args = [
        'label'  => esc_html__('Werkervaring', 'sustainablejobs'),
        'labels' => [
            'menu_name'                  => esc_html__('Werkervaring', 'sustainablejobs'),
            'all_items'                  => esc_html__('All Werkervaring', 'sustainablejobs'),
            'edit_item'                  => esc_html__('Edit Werkervaring', 'sustainablejobs'),
            'view_item'                  => esc_html__('View Werkervaring', 'sustainablejobs'),
            'update_item'                => esc_html__('Update Werkervaring', 'sustainablejobs'),
            'add_new_item'               => esc_html__('Add new Werkervaring', 'sustainablejobs'),
            'new_item'                   => esc_html__('New Werkervaring', 'sustainablejobs'),
            'parent_item'                => esc_html__('Parent Werkervaring', 'sustainablejobs'),
            'parent_item_colon'          => esc_html__('Parent Werkervaring', 'sustainablejobs'),
            'search_items'               => esc_html__('Search Werkervaring', 'sustainablejobs'),
            'popular_items'              => esc_html__('Popular Werkervaring', 'sustainablejobs'),
            'separate_items_with_commas' => esc_html__('Separate Werkervaring with commas', 'sustainablejobs'),
            'add_or_remove_items'        => esc_html__('Add or remove Werkervaring', 'sustainablejobs'),
            'choose_from_most_used'      => esc_html__('Choose most used Werkervaring', 'sustainablejobs'),
            'not_found'                  => esc_html__('No Werkervaring found', 'sustainablejobs'),
            'name'                       => esc_html__('Werkervaring', 'sustainablejobs'),
            'singular_name'              => esc_html__('Werkervaring', 'sustainablejobs'),
        ],
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'show_in_nav_menus'    => true,
        'show_tagcloud'        => true,
        'show_in_quick_edit'   => true,
        'show_admin_column'    => false,
        'show_in_rest'         => true,
        'hierarchical'         => true,
        'query_var'            => true,
        'sort'                 => false,
        'rewrite_no_front'     => false,
        'rewrite_hierarchical' => false,
        'rewrite' => true
    ];
    register_taxonomy('werkervaring', ['job_listing'], $args);
}

add_action('init', 'register_vakgebied');
function register_vakgebied()
{
    $args = [
        'label'  => esc_html__('Vakgebieden', 'sustainablejobs'),
        'labels' => [
            'menu_name'                  => esc_html__('Vakgebieden', 'sustainablejobs'),
            'all_items'                  => esc_html__('All Vakgebieden', 'sustainablejobs'),
            'edit_item'                  => esc_html__('Edit Vakgebied', 'sustainablejobs'),
            'view_item'                  => esc_html__('View Vakgebied', 'sustainablejobs'),
            'update_item'                => esc_html__('Update Vakgebied', 'sustainablejobs'),
            'add_new_item'               => esc_html__('Add new Vakgebied', 'sustainablejobs'),
            'new_item'                   => esc_html__('New Vakgebied', 'sustainablejobs'),
            'parent_item'                => esc_html__('Parent Vakgebied', 'sustainablejobs'),
            'parent_item_colon'          => esc_html__('Parent Vakgebied', 'sustainablejobs'),
            'search_items'               => esc_html__('Search Vakgebieden', 'sustainablejobs'),
            'popular_items'              => esc_html__('Popular Vakgebieden', 'sustainablejobs'),
            'separate_items_with_commas' => esc_html__('Separate Vakgebieden with commas', 'sustainablejobs'),
            'add_or_remove_items'        => esc_html__('Add or remove Vakgebieden', 'sustainablejobs'),
            'choose_from_most_used'      => esc_html__('Choose most used Vakgebieden', 'sustainablejobs'),
            'not_found'                  => esc_html__('No Vakgebieden found', 'sustainablejobs'),
            'name'                       => esc_html__('Vakgebieden', 'sustainablejobs'),
            'singular_name'              => esc_html__('Vakgebied', 'sustainablejobs'),
        ],
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'show_in_nav_menus'    => true,
        'show_tagcloud'        => true,
        'show_in_quick_edit'   => true,
        'show_admin_column'    => false,
        'show_in_rest'         => true,
        'hierarchical'         => true,
        'query_var'            => true,
        'sort'                 => false,
        'rewrite_no_front'     => false,
        'rewrite_hierarchical' => false,
        'rewrite' => true
    ];
    register_taxonomy('vakgebied', ['job_listing'], $args);
}
