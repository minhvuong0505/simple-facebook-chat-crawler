<?php
$accessToken = "Page access token here"; // Replace with your page access token
$pageId = "1111221111"; // Replace with your page ID
$baseUrl = "https://graph.facebook.com/v21.0/$pageId?fields=conversations.limit(100){messages.limit(1000){message,from,created_time},link}&access_token=" . $accessToken;

$outputFile = 'leads.csv';
$logFile = 'crawl_log.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

$fp = fopen($outputFile, 'w');
logMessage("Opened CSV file for writing: $outputFile");

fputcsv($fp, [ 'Created time', 'Customer name', 'Phone', 'Message', 'Last message', 'PUID' ,'Inbox link']);
function containsPhoneNumber($text) {
    $text = str_replace([' ', '.', ',', '-'], '', $text);
    return preg_match('/\b\d{10,11}\b/', $text, $matches) ? $matches[0] : false;
}

$apiUrl = $baseUrl;
$totalConversationsProcessed = 0;

do {
    logMessage("Fetching data from API: $apiUrl");
    $response = file_get_contents($apiUrl);
    if ($response === false) {
        logMessage("Error fetching data from API: $apiUrl");
        break;
    }

    $data = json_decode($response, true);
    $conversations = $data['conversations']['data'] ?? $data['data'];
    if (!isset($conversations)) {
        logMessage("No conversation data found in API response");
        break;
    }

    logMessage("Fetched " . count($conversations) . " conversations");
    foreach ($conversations as $conversation) {
        $chatLink = isset($conversation['link']) ? $conversation['link'] : '';
        logMessage("Processing conversation with link: $chatLink");
        $totalConversationsProcessed++;
        logMessage("Total conversations processed: $totalConversationsProcessed");
        $foundPhone = false;
        $invertedConversation = array_reverse($conversation['messages']['data'] ?? []);
        if (isset($invertedConversation)) {
            $lastMessage = '';
            foreach ($invertedConversation as $messageData) {
                if ($foundPhone) {
                    break;
                }

                if ($messageData['from']['id'] == $pageId) {
                    $lastMessage = str_replace(["\r", "\n"], ' ', $messageData['message']);
                    continue;
                } 

                $customerName = $messageData['from']['name'];
                $phone = containsPhoneNumber($messageData['message']);
                if ($phone) {
                    $textMessage = str_replace(["\r", "\n"], ' ', $messageData['message']);
                    $createdTime = $messageData['created_time'];

                    fputcsv($fp, [ $createdTime, $customerName, $phone, $textMessage,  $lastMessage, $messageData['from']['id'],  $chatLink]);
                    logMessage("Found phone number: $phone in message from $customerName");

                    $foundPhone = true;
                }
                $lastMessage = str_replace(["\r", "\n"], ' ', $messageData['message']);
            }
        }
    }

    $apiUrl = $data['conversations']['paging']['next'] ?? $data['paging']['next'] ?? false;
    logMessage("Next API URL: " . ($apiUrl ?: 'None'));

} while ($apiUrl);

fclose($fp);
logMessage("Closed CSV file: $outputFile");

logMessage("Processing completed successfully");
logMessage("Total conversations processed: $totalConversationsProcessed");
logMessage("Script finished at " . date('Y-m-d H:i:s'));
?>