# Laravel SSExpert

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fe-techteam/laravel-ssexpertsystem.svg?style=flat-square)](https://packagist.org/packages/fe-techteam/laravel-ssexpertsystem)
[![Total Downloads](https://img.shields.io/packagist/dt/fe-techteam/laravel-ssexpertsystem.svg?style=flat-square)](https://packagist.org/packages/fe-techteam/laravel-ssexpertsystem)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-777bb4.svg?style=flat-square)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x%20%7C%2011.x%20%7C%2012.x%20%7C%2013.x-ff2d20.svg?style=flat-square)](https://laravel.com/)

A modern, robust, and strongly-typed Laravel client SDK for the **[SSExpertSystem SMS & Template Gateway API](http://api.ssexpertsystem.com)** ([Swagger Specification](http://api.ssexpertsystem.com/swagger/v1/swagger.json)).

Engineered for seamless developer experience with a single unified `SSExpert` facade, full TRAI DLT compliance (PEID, Templates, Sender IDs), automated exponential retries, strongly-typed DTOs, and zero-effort test mocking.

---

## ⚡ 10-Second Quickstart

```bash
composer require fe-techteam/laravel-ssexpertsystem
```

```php
use FeTech\SSExpert\Facades\SSExpert;

// Send an OTP SMS instantly
$response = SSExpert::sendOtp('9876543210', '582914');

if ($response->isSuccess()) {
    echo "Sent! Message ID: " . $response->getMessageId();
}
```

---

## Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
  - [Environment Variables (.env)](#environment-variables-env)
  - [Publish Configuration](#publish-configuration)
- [API Usage Guide](#-api-usage-guide)
  - [1. Sending Single SMS & OTPs](#1-sending-single-sms--otps)
  - [2. Sending Bulk SMS Campaigns](#2-sending-bulk-sms-campaigns)
  - [3. Checking Account Balance & Credits](#3-checking-account-balance--credits)
  - [4. DLT Template Management](#4-dlt-template-management)
  - [5. Sender ID (Header) Management](#5-sender-id-header-management)
  - [6. Contact Group Management](#6-contact-group-management)
  - [7. Message Status & Delivery Reports](#7-message-status--delivery-reports)
  - [8. Artisan CLI Commands](#8-artisan-cli-commands)
  - [9. Dependency Injection](#9-dependency-injection)
  - [10. Laravel Notification Channel](#10-laravel-notification-channel)
- [Error Handling & Exceptions](#-error-handling--exceptions)
- [Testing & Mocking](#-testing--mocking)
- [License](#-license)

---

## ✨ Features

- 💎 **Single Unified Entrypoint**: Access all gateway operations fluently via `SSExpert` without namespace pollution.
- 📲 **Single & OTP SMS**: Low-latency OTP and transactional message delivery.
- 🚀 **Bulk SMS Campaigns**: High-throughput personalized multi-recipient messaging with `BulkSmsData`.
- 💰 **Real-time Balance Queries**: Instant credit tracking via `SSExpert::getCredits()` and `php artisan ssexpert:balance`.
- 🇮🇳 **TRAI DLT Ready**: Full regulatory compliance with automated `principleEntityId` (PEID) injection from `.env`.
- 📑 **DLT Template Management**: Complete programmatic CRUD and lookup for registered DLT templates.
- 🏷️ **Sender ID (Header) Management**: Manage and query approved 6-character sender headers.
- 👥 **Contact Groups**: Manage address book groups and contact counts.
- ⚡ **Resilient HTTP Engine**: Automated exponential retry backoff (`Http::retry()`) and timeout management.
- 🧪 **100% Testable**: Comprehensive unit test suite and seamless mocking with `Http::fake()`.

---

## 📋 Requirements

| Component | Supported Versions |
|---|---|
| **PHP** | `^8.2` or higher |
| **Laravel Framework** | `^10.0`, `^11.0`, `^12.0`, or `^13.0` |
| **Guzzle HTTP Client** | `^7.8` |

---

## 📦 Installation

Install the package via Composer:

```bash
composer require fe-techteam/laravel-ssexpertsystem
```

The package will automatically register its Service Provider and the `SSExpert` facade alias using Laravel package auto-discovery.

---

## ⚙️ Configuration

### Environment Variables (`.env`)

Add your SSExpert credentials to your application's `.env` file:

```env
# SSExpert Gateway Credentials
SSEXPERT_BASE_URL=http://api.ssexpertsystem.com
SSEXPERT_API_KEY=your_api_key_here
SSEXPERT_CLIENT_ID=your_client_id_here

# TRAI DLT Compliance (India)
SSEXPERT_PEID=your_dlt_principal_entity_id_here
SSEXPERT_SENDER_ID=YOUR_SENDER_ID

# HTTP Resilience & Performance
SSEXPERT_TIMEOUT=15
SSEXPERT_RETRY_TIMES=3
SSEXPERT_RETRY_SLEEP=100
```

#### Configuration Reference

| Option | Type | Description | Default |
|---|---|---|---|
| `SSEXPERT_BASE_URL` | `string` | Base API gateway endpoint | `http://api.ssexpertsystem.com` |
| `SSEXPERT_API_KEY` | `string` | Your SSExpert account API key | `""` *(Required)* |
| `SSEXPERT_CLIENT_ID` | `string` | Your SSExpert account Client ID | `""` *(Required)* |
| `SSEXPERT_PEID` | `string` | Registered DLT Principal Entity ID (Corporate ID) | `""` |
| `SSEXPERT_SENDER_ID` | `string` | Default approved 6-character sender ID / header | `TESTID` |
| `SSEXPERT_TIMEOUT` | `int` | Maximum request timeout in seconds | `15` |
| `SSEXPERT_RETRY_TIMES` | `int` | Maximum automatic retries on connection glitch | `3` |
| `SSEXPERT_RETRY_SLEEP` | `int` | Sleep delay in milliseconds between retries | `100` |

---

### Publish Configuration

You can publish the configuration file to `config/ssexpert.php` (optional):

```bash
php artisan vendor:publish --tag=ssexpert-config
```

---

## 🚀 API Usage Guide

All services are accessible through the single **`SSExpert`** facade.

---

### 1. Sending Single SMS & OTPs

> [!IMPORTANT]
> **TRAI DLT Entity ID (`principleEntityId`)**:
> Telecom operators require a registered **Principal Entity ID (PEID)** for DLT compliance. Once `SSEXPERT_PEID` is set in `.env`, `SSExpert` **automatically attaches** your PEID to every outgoing SMS. You can also override it per request.

#### Recommended: Using Strongly Typed DTOs
```php
use FeTech\SSExpert\Facades\SSExpert;
use FeTech\SSExpert\DTOs\SmsData;

$sms = new SmsData(
    mobileNumbers: '9876543210',
    message: 'Dear Customer, your request ID 98765 is confirmed.',
    templateId: '1107160000000000002',              // DLT Content Template ID
    principleEntityId: '1101554433000000000',       // DLT Principal Entity ID (Optional override)
    senderId: 'TESTID'                              // Sender ID / Header (Optional override)
);

$response = SSExpert::sms()->send($sms);
```

#### Quick OTP Shortcut
```php
use FeTech\SSExpert\Facades\SSExpert;

// Sends OTP using registered template (PEID and Sender ID injected from .env)
$response = SSExpert::sendOtp(
    mobile: '9876543210',
    otp: '582914',
    templateId: '1107160000000000001' // Optional custom template ID
);

if ($response->isSuccess()) {
    echo "Dispatched! Message ID: " . $response->getMessageId();
} else {
    echo "Failed: " . $response->getErrorMessage();
}
```

#### Handling the Gateway Response
```php
$response = SSExpert::sendOtp('9876543210', '123456');

// 1. Check success boolean
$isOk = $response->isSuccess();

// 2. Extract Gateway UUID (e.g. 1f66ba54-5066-428a-b36b-0f3199318dab)
$messageId = $response->getMessageId();

// 3. Error details if failed
$errorCode = $response->errorCode;
$errorMessage = $response->getErrorMessage();

// 4. Access raw JSON array
$raw = $response->raw;
```

---

### 2. Sending Bulk SMS Campaigns

Send customized, personalized messages to multiple recipients in a single HTTP request:

#### Recommended: Using Strongly Typed DTOs
```php
use FeTech\SSExpert\Facades\SSExpert;
use FeTech\SSExpert\DTOs\BulkSmsData;
use FeTech\SSExpert\DTOs\BulkMessageItem;

$bulkData = new BulkSmsData(
    messages: [
        new BulkMessageItem('9876543210', 'Dear User 1, your OTP is 112233.'),
        new BulkMessageItem('9123456780', 'Dear User 2, your OTP is 445566.'),
    ],
    templateId: '1107160000000000001',
    principleEntityId: '1101554433000000000', // Optional override
    senderId: 'TESTID'                       // Optional override
);

$response = SSExpert::sms()->sendBulk($bulkData);

if ($response->isSuccess()) {
    echo "Bulk campaign accepted!";
}
```

#### Alternative: Using Array Payloads
```php
use FeTech\SSExpert\Facades\SSExpert;

// 1. Associative payload array
$response = SSExpert::sms()->sendBulk([
    'template_id' => '1107160000000000001',
    'messages' => [
        ['number' => '9876543210', 'text' => 'Dear User 1, your OTP is 112233.'],
        ['number' => '9123456780', 'text' => 'Dear User 2, your OTP is 445566.'],
    ],
]);

// 2. Direct key-value map shortcut
$response = SSExpert::sendBulk([
    '9876543210' => 'Dear User 1, your OTP is 112233.',
    '9123456780' => 'Dear User 2, your OTP is 445566.',
], templateId: '1107160000000000001');
```

---

### 3. Checking Account Balance & Credits

```php
use FeTech\SSExpert\Facades\SSExpert;

// 1. Quick shortcut: Get available SMS credits as a float
$credits = SSExpert::getCredits(); // e.g. 17935.0
echo "Available Credits: " . number_format($credits, 2);

// 2. Get detailed primary balance DTO
$balance = SSExpert::balance()->get();
echo "Product Type: " . $balance->pluginType; // "SMS"
echo "Credits: " . $balance->credits;

// 3. List all product balance records
$records = SSExpert::balance()->list();
```

---

### 4. DLT Template Management

Programmatically list, query, create, update, and delete DLT templates registered with your telecom gateway:

#### Recommended: Using Strongly Typed DTOs
```php
use FeTech\SSExpert\Facades\SSExpert;
use FeTech\SSExpert\DTOs\TemplateData;

// 1. List all templates (returns Collection<int, TemplateResponse>)
$templates = SSExpert::template()->list();

// 2. Find template by DLT Template ID
$template = SSExpert::template()->findByDltTemplateId('1107160000000000001');

// 3. Find template by name
$template = SSExpert::template()->findByName('OTP_SECURITY');

// 4. Create a new template with DTO
$response = SSExpert::template()->create(new TemplateData(
    templateName: 'PAYMENT_RECEIVED',
    messageTemplate: 'Dear {#var#}, payment of INR {#var#} received.',
    dltTemplateId: '1107160000000000003'
));

// 5. Update an existing template
SSExpert::template()->update(101, new TemplateData(
    templateName: 'OTP_SECURITY_V2',
    messageTemplate: 'Your Login OTP is {#var#}. Valid for 2 mins.',
    dltTemplateId: '1107160000000000001'
));

// 6. Delete a template by ID
SSExpert::template()->delete(101);
```

#### Alternative: Using Array Payloads
```php
use FeTech\SSExpert\Facades\SSExpert;

$response = SSExpert::template()->create([
    'name' => 'PAYMENT_RECEIVED',
    'template' => 'Dear {#var#}, payment of INR {#var#} received.',
    'dlt_template_id' => '1107160000000000003',
]);
```

---

### 5. Sender ID (Header) Management

```php
use FeTech\SSExpert\Facades\SSExpert;

// 1. List all approved headers
$headers = SSExpert::senderId()->list();

// 2. Find header by name
$header = SSExpert::senderId()->findByName('TESTID');

// 3. Submit request for new Sender ID
SSExpert::senderId()->create('NEWHDR', 'Transaction and Alert Notifications');

// 4. Delete Sender ID by ID
SSExpert::senderId()->delete(292);
```

---

### 6. Contact Group Management

```php
use FeTech\SSExpert\Facades\SSExpert;

// 1. List contact groups
$groups = SSExpert::group()->list();

// 2. Create a contact group
SSExpert::group()->create('Support Team');

// 3. Update group name
SSExpert::group()->update(10, 'Support Team North');

// 4. Delete group
SSExpert::group()->delete(10);
```

---

### 7. Message Status & Delivery Reports

```php
use FeTech\SSExpert\Facades\SSExpert;

// 1. Query delivery status by Gateway Message ID
$status = SSExpert::sms()->getMessageStatus('mock-uuid-112233');

// 2. Query delivery logs for recent days
$report = SSExpert::sms()->getDeliveryReport(days: 7);

// 3. Paginated transmission logs with date filtering
$logs = SSExpert::sms()->getSmsLogs(
    start: 0,
    length: 50,
    fromDate: '2026-08-01',
    endDate: '2026-08-21'
);

// 4. Summary statistics
$summary = SSExpert::sms()->getReportSummary(fromDate: '2026-08-01', endDate: '2026-08-21');
```

---

### 8. Artisan CLI Commands

Check your live account balance directly from the terminal:

```bash
php artisan ssexpert:balance
```

**Terminal Output:**
```
Checking SSExpertSystem account balance...
+-----------------------+-------------------+--------------------+----------+
| Plugin / Product Type | Available Credits | Raw Value          | Currency |
+-----------------------+-------------------+--------------------+----------+
| SMS                   | 17,935.00         | credit17935.000000 | N/A      |
+-----------------------+-------------------+--------------------+----------+
✔ Total Available SMS Credits: 17,935.00
```

---

### 9. Dependency Injection

Inject service contracts directly into your Controllers, Queue Jobs, or Commands:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\Contracts\BalanceServiceInterface;

class AuthController extends Controller
{
    public function sendOtp(Request $request, SmsServiceInterface $sms, BalanceServiceInterface $balance)
    {
        if ($balance->getCredits() < 1) {
            return response()->json(['error' => 'Insufficient SMS credits'], 402);
        }

        $response = $sms->sendOtp(
            mobile: $request->input('mobile'),
            otp: (string) rand(100000, 999999)
        );

        return response()->json([
            'success' => $response->isSuccess(),
            'message_id' => $response->getMessageId(),
        ]);
    }
}
```

---

### 10. Laravel Notification Channel

Send SMS directly through Laravel's built-in Notification system:

#### Create the Channel:
```php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use FeTech\SSExpert\Contracts\SmsServiceInterface;
use FeTech\SSExpert\DTOs\SmsData;

class SSExpertChannel
{
    public function __construct(protected SmsServiceInterface $sms) {}

    public function send($notifiable, Notification $notification): void
    {
        $data = $notification->toSSExpert($notifiable);

        if ($data instanceof SmsData) {
            $this->sms->send($data);
        }
    }
}
```

#### Use in Notification Class:
```php
namespace App\Notifications;

use App\Channels\SSExpertChannel;
use Illuminate\Notifications\Notification;
use FeTech\SSExpert\DTOs\SmsData;

class OrderConfirmedNotification extends Notification
{
    public function via($notifiable): array
    {
        return [SSExpertChannel::class];
    }

    public function toSSExpert($notifiable): SmsData
    {
        return new SmsData(
            mobileNumbers: $notifiable->mobile,
            message: "Dear {$notifiable->name}, your order #1234 is confirmed.",
            templateId: '1107160000000000002'
        );
    }
}
```

---

## 🛡️ Error Handling & Exceptions

All gateway operations throw domain-specific exceptions on critical failures:

- **`FeTech\SSExpert\Exceptions\SSExpertAuthException`**: Thrown on invalid credentials or `401 Unauthorized`.
- **`FeTech\SSExpert\Exceptions\SSExpertApiException`**: Thrown on network drops, HTTP client timeouts, or gateway `500+` errors.

```php
use FeTech\SSExpert\Facades\SSExpert;
use FeTech\SSExpert\Exceptions\SSExpertAuthException;
use FeTech\SSExpert\Exceptions\SSExpertApiException;

try {
    $response = SSExpert::sendOtp('9876543210', '123456');
} catch (SSExpertAuthException $e) {
    Log::critical('SSExpert Auth Failed: ' . $e->getMessage());
} catch (SSExpertApiException $e) {
    Log::error('SSExpert Connection Error: ' . $e->getMessage());
}
```

---

## 🧪 Testing & Mocking

Test your controllers and services offline using Laravel's standard `Http::fake()`:

```php
use Illuminate\Support\Facades\Http;
use FeTech\SSExpert\Facades\SSExpert;

public function test_user_receives_otp()
{
    // Mock the SSExpert SendSMS gateway endpoint
    Http::fake([
        'http://api.ssexpertsystem.com/api/v2/SendSMS' => Http::response([
            'ErrorCode' => 0,
            'Data' => [['MessageErrorCode' => 0, 'MessageId' => 'mock-uuid-123']],
        ], 200),
    ]);

    $res = SSExpert::sendOtp('9876543210', '582914');

    $this->assertTrue($res->isSuccess());
    $this->assertEquals('mock-uuid-123', $res->getMessageId());

    // Assert that correct payload was sent
    Http::assertSent(function ($request) {
        return $request->url() === 'http://api.ssexpertsystem.com/api/v2/SendSMS'
            && $request['mobileNumbers'] === '9876543210';
    });
}
```

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more details.

**Author**: [Rahul Jat](https://github.com/fe-techTeam)
