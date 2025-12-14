<?php
// ==================== CONFIGURATION ====================
define('API_BASE', 'ANISH_EXPLOITE_API');
define('BOT_TOKEN', 'TELEGRAM_BOT_TOKEN');

$HEADERS = [
    "User-Agent: Mozilla/5.0 (Linux; Android 13; Termux) Gecko/117.0 Firefox/117.0",
    "Accept: application/json,text/html,application/xhtml+xml,application/xml;q=0.9,/;q=0.8",
    "Referer: https://anish-axploits.vercel.app/",
    "Connection: keep-alive"
];

// ==================== ERROR LOGGING ====================
function log_error($message) {
    file_put_contents('bot_errors.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// ==================== TELEGRAM API FUNCTIONS ====================
function send_message($chat_id, $text, $parse_mode = 'Markdown', $reply_markup = null) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode
    ];
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

function delete_message($chat_id, $message_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/deleteMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

function send_chat_action($chat_id, $action = "typing") {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendChatAction";
    
    $data = [
        'chat_id' => $chat_id,
        'action' => $action
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// ==================== WELCOME MESSAGE ====================
function handle_start($chat_id) {
    $welcome_text = "👋 *WELCOME TO OLIVER EXPLOITS*\n\n";
    
    $keyboard = [
        'keyboard' => [[
            'text' => '📞 ENTER NUMBER'
        ]],
        'resize_keyboard' => true,
        'one_time_keyboard' => false
    ];
    
    return send_message($chat_id, $welcome_text, 'Markdown', $keyboard);
}

// ==================== API CALL FUNCTION ====================
function search_number_api($number) {
    global $HEADERS;
    
    $url = API_BASE . $number;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $HEADERS);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        return "🛡️ OLIVER EXPLOITS CYBERSECURITY INFORMATION 🛡️\n\n" .
               "🎯 TARGET: {$number}\n\n" .
               "❌ DATABASE ERROR\n\n" .
               "Server connection failed.\n\n" .
               "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
               "🔐 END OF REPORT";
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return "🛡️ OLIVER EXPLOITS CYBERSECURITY INFORMATION 🛡️\n\n" .
               "🎯 TARGET: {$number}\n\n" .
               "❌ DATA ERROR\n\n" .
               "Invalid response format.\n\n" .
               "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
               "🔐 END OF REPORT";
    }
    
    $current_time = date('Y-m-d H:i:s');
    list($user_data, $record_count) = extract_user_data($data);
    
    if ($user_data) {
        return format_cybersecurity_report($user_data, $number, $record_count, $current_time);
    } else {
        return "🛡️ OLIVER EXPLOITS CYBERSECURITY INFORMATION 🛡️\n\n" .
               "🎯 TARGET: {$number}\n\n" .
               "⚠️ NO INFORMATION FOUND\n\n" .
               "Number not found in database.\n\n" .
               "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
               "🔐 END OF REPORT";
    }
}

// ==================== DATA EXTRACTION ====================
function extract_user_data($data) {
    $user_data = null;
    $record_count = 1;
    
    if (is_array($data) && isset($data['success']) && isset($data['result'])) {
        $results = $data['result'];
        if (is_array($results) && count($results) > 0) {
            $user_data = $results[0];
            $record_count = count($results);
        }
    } elseif (is_array($data) && (isset($data['mobile']) || isset($data['name']))) {
        $user_data = $data;
    } elseif (is_array($data) && count($data) > 0) {
        $user_data = $data[0];
        $record_count = count($data);
    } elseif (is_array($data) && isset($data['status']) && $data['status'] == 'success') {
        $user_data = isset($data['data']) ? $data['data'] : [];
    }
    
    return [$user_data, $record_count];
}

// ==================== REPORT FORMATTING ====================
function format_cybersecurity_report($user_data, $number, $record_count, $current_time) {
    // Extract all data
    $phone = isset($user_data['mobile']) ? $user_data['mobile'] : $number;
    $alt = isset($user_data['alt_mobile']) ? $user_data['alt_mobile'] : '';
    $aadhar = isset($user_data['id_number']) ? $user_data['id_number'] : (isset($user_data['aadhar']) ? $user_data['aadhar'] : '');
    $name = isset($user_data['name']) ? $user_data['name'] : 'None';
    $father = isset($user_data['father_name']) ? $user_data['father_name'] : 'None';
    $address = isset($user_data['address']) ? $user_data['address'] : '';
    $circle = isset($user_data['circle']) ? $user_data['circle'] : '';
    
    // Clean address
    if ($address) {
        $address = str_replace(['!', '|', 'NA', 'l\'', 'Ii'], [' ', ' ', '', '', ''], $address);
        $address = preg_replace('/\s+/', ' ', $address);
    }
    
    // Extract actual circle/state
    $actual_circle = 'Unknown';
    if ($circle) {
        $parts = explode(' ', $circle);
        if (count($parts) >= 2) {
            $actual_circle = $parts[0];
        } else {
            $actual_circle = $circle;
        }
    }
    
    // Determine network
    $network = 'Unknown';
    $circle_upper = strtoupper($circle);
    if (strpos($circle_upper, 'JIO') !== false) {
        $network = 'JIO';
    } elseif (strpos($circle_upper, 'VODAFONE') !== false) {
        $network = 'VODAFONE';
    } elseif (strpos($circle_upper, 'AIRTEL') !== false) {
        $network = 'AIRTEL';
    } elseif (strpos($circle_upper, 'BSNL') !== false) {
        $network = 'BSNL';
    } elseif ($circle) {
        $operators = ['JIO', 'VODAFONE', 'AIRTEL', 'BSNL', 'IDEA', 'AIRCEL'];
        foreach ($operators as $operator) {
            if (strpos($circle_upper, $operator) !== false) {
                $network = $operator;
                break;
            }
        }
    }
    
    // Calculate risk level
    $data_points = 0;
    if ($name && $name != 'None' && trim($name)) $data_points++;
    if ($father && $father != 'None' && trim($father)) $data_points++;
    if ($aadhar && trim($aadhar)) $data_points++;
    if ($address && trim($address)) $data_points++;
    if ($alt && trim($alt)) $data_points++;
    
    if ($data_points >= 4) {
        $risk_emoji = "🔴";
        $exposure = "🔓 SEVERE";
    } elseif ($data_points >= 2) {
        $risk_emoji = "🟠";
        $exposure = "🔓 HIGH";
    } else {
        $risk_emoji = "🟡";
        $exposure = "🔐 MODERATE";
    }
    
    // Build the report
    $report = "🛡️ OLIVER EXPLOITS CYBERSECURITY INFORMATION 🛡️\n\n";
    
    $report .= "🎯 OLIVER EXPLOITS\n";
    $report .= "├─ 📞 Primary Vector: {$phone}\n";
    $report .= "├─ 📱 Secondary Vector: " . ($alt ?: 'None') . "\n";
    $report .= "└─ 🆔 Identity Marker: " . ($aadhar ?: 'None') . "\n\n";
    
    $report .= "👤 TARGET PROFILE\n";
    $report .= "├─ 🎭 Owner: " . ($name != 'None' ? $name : 'Not Available') . "\n";
    $report .= "├─ 👨‍👦 Father : " . ($father != 'None' ? $father : 'Not Available') . "\n";
    $report .= "└─ 📍 Circle : " . ($actual_circle != 'Unknown' ? $actual_circle : 'Not Available') . "\n\n";
    
    $report .= "📍 DIGITAL GEO-LOCK\n";
    if ($address) {
        if (strlen($address) > 80) {
            $address = substr($address, 0, 77) . "...";
        }
        $report .= "├─ 🏠 Address : {$address}\n";
    } else {
        $report .= "├─ 🏠 Address : Not Available\n";
    }
    
    // Check for landmark
    $landmark = 'Not Specified';
    if ($address) {
        $address_lower = strtolower($address);
        if (strpos($address_lower, 'chowk') !== false) {
            $landmark = 'Katar Chowk';
        } elseif (strpos($address_lower, 'market') !== false) {
            $landmark = 'Market Area';
        } elseif (strpos($address_lower, 'station') !== false) {
            $landmark = 'Railway Station';
        }
    }
    
    $report .= "├─ 🚩 Landmark: {$landmark}\n";
    $report .= "├─ 🏛️ District : Samastipur\n";
    
    if ($aadhar) {
        $report .= "├─ 🪪 Aadhar: {$aadhar}\n";
    }
    
    $report .= "├─ 📡 Network: {$network}\n";
    $report .= "└─ 🌍 Country : India\n\n";
    
    $report .= "📊 DIGITAL FOOTPRINT\n";
    $report .= "├─ 🗃️ Database Traces: {$record_count}\n";
    $report .= "├─ ✅ Verification: CONFIRMED\n";
    $report .= "└─ ⏰ Last Detection: {$current_time}\n\n";
    
    $report .= "⚠️ THREAT ASSESSMENT\n";
    $risk_level = $risk_emoji == '🔴' ? 'CRITICAL' : ($risk_emoji == '🟠' ? 'HIGH' : 'MEDIUM');
    $report .= "├─ 🚨 Risk Level: {$risk_emoji} {$risk_level}\n";
    $report .= "├─ 🔓 Exposure: {$exposure}\n";
    $report .= "└─ 🛡️ Protection: COMPROMISED\n\n";
    
    $report .= "🔍 INTELLIGENCE SOURCE\n";
    $report .= "├─ 🛡️ Oliver Exploits\n";
    $report .= "├─ 👨‍💻 Developer: @Cyb3rS0ldier\n";
    $report .= "└─ ⚡ Status: ACTIVE MONITORING\n\n";
    
    $report .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $report .= "🔐 END OF REPORT";
    
    return $report;
}

// ==================== MAIN PROCESSING ====================
function process_update($update) {
    if (!isset($update['message'])) {
        return;
    }
    
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = isset($message['text']) ? $message['text'] : '';
    $message_id = $message['message_id'];
    
    // Log the request
    file_put_contents('bot_requests.log', date('Y-m-d H:i:s') . " - Chat: {$chat_id} - Text: {$text}\n", FILE_APPEND);
    
    if ($text == '/start') {
        handle_start($chat_id);
    } elseif ($text == '📞 ENTER NUMBER') {
        send_message($chat_id, "📤 *Send Your 10-digit Number Without +91:*", 'Markdown');
    } elseif (preg_match('/^\d{10}$/', $text)) {
        // Process number
        $processing_msg = send_message($chat_id, "🔍 *Scanning Database...*", 'Markdown');
        $processing_data = json_decode($processing_msg, true);
        
        if (isset($processing_data['result']['message_id'])) {
            send_chat_action($chat_id, "typing");
            sleep(2);
            
            $result = search_number_api($text);
            
            delete_message($chat_id, $processing_data['result']['message_id']);
            
            send_message($chat_id, $result, 'Markdown');
        } else {
            $result = search_number_api($text);
            send_message($chat_id, $result, 'Markdown');
        }
    } else {
        send_message($chat_id, "❌ *INVALID INPUT*\nPlease send 10-digit number only.", 'Markdown');
    }
}

// ==================== WEBHOOK HANDLER ====================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = file_get_contents('php://input');
    $update = json_decode($content, true);
    
    if ($update) {
        process_update($update);
    }
    
    http_response_code(200);
    echo "OK";
} else {
    // Set webhook info
    echo "<h1>🤖 Oliver Exploits Bot</h1>";
    echo "<p>Status: Online</p>";
    echo "<p>To set webhook: https://api.telegram.org/bot" . BOT_TOKEN . "/setWebhook?url=" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
}
?>
