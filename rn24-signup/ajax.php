<?php

require_once 'utils.php';

function rn24_select_groups() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $q = strtoupper(isset($_POST['q']) ? sanitize_text_field($_POST['q']) : '');
    $region = stripslashes(isset($_POST['region']) ? strtoupper($_POST['region']) : '');

    $groups = rn24_get_groups();
	
    $filtered_group = array_filter(
        $groups,
        function($item) use ($region) {
            return stripslashes(strtoupper($item['Regione'])) === $region;
        }
    );

    if($q) {
        $filtered_group = array_filter(
            $filtered_group,
            function($item) use ($q) {
                return str_starts_with($item['Denominazione Gruppo'], $q);
            }
        );
    }

    $items_per_page = 5;
    $offset = ($page - 1) * $items_per_page;
    $paged_results = array_slice($filtered_group, $offset, $items_per_page);

    $total_pages = ceil(count($filtered_group) / $items_per_page);
    $more_pages = $page < $paged_results;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'results' => array_values(
            array_map(
                function($item) {
                    return [
                        'id' => $item['Ordinale'],
                        'text' => $item['Denominazione Gruppo'],
                        'email' => $item['GruppoEmail'],
                    ];
                },
                $filtered_group
            )
        ),
        // 'paginationa' => ['more' => $more_pages]
    ]);
    wp_die();
}

add_action( 'wp_ajax_nopriv_rn24_select_groups', 'rn24_select_groups' );
add_action( 'wp_ajax_rn24_select_groups', 'rn24_select_groups' );
