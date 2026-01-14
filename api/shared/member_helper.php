<?php

function get_isc_members() {
    $url = "https://unsatirical-sharda-calorimetric.ngrok-free.dev/ISC-Student-Organization-System/api-connections/sharedMembers-api.php";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'ngrok-skip-browser-warning: true'
    ));

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return [];
    }

    curl_close($ch);
    
    return json_decode($response, true);
}

function check_for_discount($email, $member_list) {
    
    $emails = array_column($member_list, 'email'); 
    
    if (in_array($email, $emails)) {
        return 0.10;
    }
    return 0; 
}