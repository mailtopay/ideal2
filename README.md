# iDEAL | Wero PHP Library

A PHP library for the iDEAL | Wero payment protocol, supporting both the Currence iDEAL Hub (via ING) and Worldline integration paths.

## Requirements

- PHP >= 8.2
- OpenSSL extension

## Installation

```bash
composer require peaceofmind/ideal2
```

## Supported Banks

| Bank | Integration | Code |
|------|-------------|------|
| ABN AMRO | Worldline | `abn` |
| BNG | Worldline | `bng` |
| BNP Paribas | Worldline | `bnp` |
| Deutsche Bank | Worldline | `db` |
| ING | Hub | `ing` |
| ING Sandbox | Hub | `ingsandbox` |
| Rabobank | Worldline | `rabo` |
| Worldline (test) | Worldline | `worldline` |

## Hub Integration (ING)

### Configuration

```php
use POM\iDEAL\Banks\BankFactory;
use POM\iDEAL\Hub\Config;
use POM\iDEAL\Hub\SigningAlgorithm;
use POM\iDEAL\Hub\iDEAL;

$config = new Config(
    merchantId: 'your-merchant-id',
    testMode: true,
    bank: BankFactory::bankFromString('ing'),
    INGmTLSCertificatePath: '/path/to/ing-mtls-cert.pem',
    INGmTLSKeyPath: '/path/to/ing-mtls-key.pem',
    INGmTLSPassPhrase: 'passphrase',
    INGSigningKey: file_get_contents('/path/to/ing-signing-key.pem'),
    INGSigningPassphrase: 'passphrase',
    INGSigningCertificate: file_get_contents('/path/to/ing-signing-cert.pem'),
    hubmTLSCertificatePath: '/path/to/hub-mtls-cert.pem',
    hubmTLSKeyPath: '/path/to/hub-mtls-key.pem',
    hubmTLSPassphrase: 'passphrase',
    hubSigningKey: file_get_contents('/path/to/hub-signing-key.pem'),
    hubSigningCertificate: file_get_contents('/path/to/hub-signing-cert.pem'),
    hubSigningPassphrase: 'passphrase',
    signingAlgorithm: SigningAlgorithm::ES256,
    cache: $yourPsr16Cache, // any PSR-16 CacheInterface implementation
);

$ideal = new iDEAL($config);
```

### Creating a Transaction

```php
$transaction = $ideal->createTransactionRequest()->execute(
    amount: 1000,                    // amount in cents (e.g. 1000 = EUR 10.00)
    description: 'Order #123',       // shown to the payer
    reference: 'INV-2024-001',       // appears on bank statements
    returnUrl: 'https://example.com/return',
    callbackUrl: 'https://example.com/webhook', // optional
);

// redirect the payer
header('Location: ' . $transaction->getRedirectUrl());
```

### Checking Transaction Status

```php
$transaction = $ideal->createTransactionStatusRequest()->execute($transactionId);

$status = $transaction->getStatus(); // TransactionStatus enum: OPEN, SUCCESS, CANCELLED, EXPIRED, FAILURE, IDENTIFIED
```

The returned `Transaction` object provides:

- `getTransactionId()` - unique transaction ID
- `getStatus()` - current status
- `getAmount()` - amount in cents
- `getDescription()` - transaction description
- `getReference()` - payment reference
- `getRedirectUrl()` - payer redirect URL
- `getCreated()` / `getExpire()` - timestamps
- `getIban()` / `getBic()` / `getAccountOwner()` - payer details (after completion)

### Verifying Webhook Callbacks

```php
$isValid = $ideal->verifyCallbackResponse(
    callbackResponse: file_get_contents('php://input'),
);
```

Or pass headers manually:

```php
$isValid = $ideal->verifyCallbackResponse(
    callbackResponse: file_get_contents('php://input'),
    headers: [
        'Request-Id' => $requestId,
        'X-Sender' => $sender,
        'Signature' => $signature,
    ],
);
```

## Worldline Integration

### Configuration

```php
use POM\iDEAL\Banks\BankFactory;
use POM\iDEAL\Worldline\Config;
use POM\iDEAL\Worldline\iDEAL;

$config = new Config(
    merchantId: 'your-merchant-id',
    testMode: true,
    merchantCertificate: file_get_contents('/path/to/merchant-cert.pem'),
    merchantKey: file_get_contents('/path/to/merchant-key.pem'),
    merchantPassphrase: 'passphrase',
    acquirerCertificate: file_get_contents('/path/to/acquirer-cert.pem'),
    bank: BankFactory::bankFromString('abn'),
    notificationUrl: 'https://example.com/webhook',
    cache: $yourPsr16Cache,
);

$ideal = new iDEAL($config);
```

### Creating a Transaction

```php
$transaction = $ideal->createTransactionRequest()->execute(
    amount: 1000,
    reference: 'INV-2024-001',
    description: 'Order #123',
    transactionIdentifier: 'your-unique-id',
    returnUrl: 'https://example.com/return',
);

header('Location: ' . $transaction->getRedirectUrl());
```

### Checking Transaction Status

```php
$status = $ideal->doStatusRequest()->execute($transactionId);

$status->getPaymentStatus();          // e.g. "SettlementCompleted"
$status->getIdentification();         // payer IBAN
$status->getName();                   // payer name
$status->getGuaranteedAmount();       // settled amount
$status->getTransactionIdentifier();  // your original transaction ID
```

### Verifying Webhook Callbacks

```php
$isValid = $ideal->verifyCallbackResponse(
    callbackResponse: file_get_contents('php://input'),
);
```

## Caching

Both integration paths require a PSR-16 (`psr/simple-cache`) implementation for caching access tokens and (for Hub) acquirer certificates. Any compatible cache library will work, for example `symfony/cache` or `matthiasmullie/scrapbook`.

## License

MIT
