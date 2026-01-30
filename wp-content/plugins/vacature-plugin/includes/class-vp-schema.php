<?php
if (!defined('ABSPATH')) exit;

class VP_Schema {

  public static function hook() {
    if (!is_singular('vp_vacature')) return;
    add_action('wp_head', [ __CLASS__, 'output_jobposting_jsonld' ], 20);
  }

  public static function output_jobposting_jsonld() {
    global $post;
    if (!$post || $post->post_type !== 'vp_vacature') return;

    $company_name  = get_post_meta($post->ID, '_vp_company_name', true);
    $company_url   = get_post_meta($post->ID, '_vp_company_url', true);
    $apply_url     = get_post_meta($post->ID, '_vp_apply_url', true);
    $apply_email   = get_post_meta($post->ID, '_vp_apply_email', true);
    $location      = get_post_meta($post->ID, '_vp_location', true);
    $salary        = get_post_meta($post->ID, '_vp_salary', true);
    $validThrough  = get_post_meta($post->ID, '_vp_valid_through', true);

    // employment type from taxonomy vp_job_type
    $types = wp_get_post_terms($post->ID, 'vp_job_type', [ 'fields' => 'names' ]);
    $employmentType = !empty($types) ? $types : [];

    $data = [
      '@context' => 'https://schema.org',
      '@type'    => 'JobPosting',
      'title'    => get_the_title($post),
      'description' => wp_kses_post(apply_filters('the_content', $post->post_content)),
      'datePosted'  => get_the_date('c', $post),
      'hiringOrganization' => array_filter([
        '@type' => 'Organization',
        'name'  => $company_name ?: get_bloginfo('name'),
        'sameAs'=> $company_url ?: '',
        'url'   => $company_url ?: '',
      ]),
      'employmentType' => $employmentType,
      'jobLocation' => [
        '@type' => 'Place',
        'address' => [
          '@type' => 'PostalAddress',
          'addressLocality' => $location ?: '',
          'addressCountry'  => 'NL',
        ],
      ],
      'directApply' => true,
    ];

    if ($validThrough) $data['validThrough'] = date('c', strtotime($validThrough));

    // Apply
    if ($apply_url) {
      $data['applicationContact'] = [
        '@type' => 'ContactPoint',
        'url'   => $apply_url,
      ];
    } elseif ($apply_email) {
      $data['applicationContact'] = [
        '@type' => 'ContactPoint',
        'email' => $apply_email,
      ];
    }

    // Salary: als vrije tekst (neutraal). Later kunnen we dit structured maken.
    if ($salary) $data['baseSalary'] = [
      '@type' => 'MonetaryAmount',
      'currency' => 'EUR',
      'value' => [
        '@type' => 'QuantitativeValue',
        'value' => $salary,
      ],
    ];

    echo "\n<script type=\"application/ld+json\">" . wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
  }
}