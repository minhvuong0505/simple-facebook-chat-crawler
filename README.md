## Simple Facebook Chat Crawler

This script is designed to crawl Facebook page conversations using the Facebook Graph API. It extracts messages containing phone numbers, along with other relevant details, and saves the data into a CSV file for further analysis.

## Features
- Fetches conversations and messages from a Facebook page.
- Extracts phone numbers from messages (supports 10- or 11-digit numbers).
- Logs all activities and errors to a log file for debugging and monitoring.
- Saves extracted data into a CSV file with the following fields:
  - Created time
  - Customer name
  - Phone number
  - Message
  - Last message
  - PUID (Page User ID)
  - Inbox link

## Prerequisites
- PHP installed on your system.
- A valid Facebook Page Access Token with the required permissions (`pages_messaging` and `pages_read_engagement`).
- The Facebook Page ID for the page you want to crawl.

## Installation
1. Clone or download this repository to your local machine.
2. Open the `crawlingConverstation.php` file and update the following variables:
   - `$accessToken`: Replace with your Facebook Page Access Token.
   - `$pageId`: Replace with your Facebook Page ID.

## Usage
1. Run the script using the PHP CLI:
   ```bash
   php crawlingConverstation.php