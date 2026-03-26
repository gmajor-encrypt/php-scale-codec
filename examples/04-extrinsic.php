<?php

/**
 * Example: Extrinsic Building and Signing
 * 
 * This example demonstrates how to build and sign extrinsics
 * for Substrate-based chains.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Extrinsic\ExtrinsicBuilder;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

echo "=== Extrinsic Building Examples ===\n\n";

// ============================================
// Extrinsic Structure
// ============================================
echo "--- Extrinsic Structure ---\n\n";

echo "A Substrate extrinsic consists of:\n";
echo "1. Version byte\n";
echo "2. Signature (if signed)\n";
echo "3. Call data\n";
echo "4. Extensions\n\n";

// ============================================
// Building an Unsigned Extrinsic
// ============================================
echo "--- Unsigned Extrinsic ---\n\n";

$registry = new TypeRegistry();

echo "Building an unsigned extrinsic:\n";
echo <<< 'CODE'
$builder = new ExtrinsicBuilder($registry);

$extrinsic = $builder
    ->setVersion(4)                    // Extrinsic version
    ->setPallet('Balances')            // Target pallet
    ->setFunction('transfer')          // Function name
    ->setArgs([
        'dest' => '5GrwvaEF5zXb26Fz9rcQpDWS57CtERHpNehXCPcNoHGKutQY',
        'value' => 1000000000,         // 1 DOT
    ])
    ->build();

echo "Unsigned extrinsic: " . $extrinsic->toHex() . "\n";
CODE;

echo "\n\n";

// ============================================
// Building a Signed Extrinsic
// ============================================
echo "--- Signed Extrinsic ---\n\n";

echo "Building a signed extrinsic:\n";
echo <<< 'CODE'
use Substrate\ScaleCodec\Crypto\Keypair;
use Substrate\ScaleCodec\Crypto\Sr25519;

// Create or load keypair
$keypair = Keypair::fromMnemonic('your mnemonic phrase here');
// Or from seed:
// $keypair = Keypair::fromSeed($seedBytes);

// Get chain metadata and genesis hash
$genesisHash = '0x...'; // Chain's genesis hash
$runtimeVersion = 100;  // Current runtime version

$builder = new ExtrinsicBuilder($registry);

$extrinsic = $builder
    ->setVersion(4)
    ->setPallet('Balances')
    ->setFunction('transfer')
    ->setArgs([
        'dest' => '5GrwvaEF5zXb26Fz9rcQpDWS57CtERHpNehXCPcNoHGKutQY',
        'value' => 1000000000,
    ])
    ->setSigner($keypair->getPublicKey())
    ->setEra(['period' => 64, 'blockNumber' => $currentBlock])
    ->setNonce($accountNonce)
    ->setTip(0)
    ->sign($keypair, $genesisHash, $runtimeVersion)
    ->build();

echo "Signed extrinsic: " . $extrinsic->toHex() . "\n";
CODE;

echo "\n\n";

// ============================================
// Transaction Era
// ============================================
echo "--- Transaction Era (Mortality) ---\n\n";

echo "Setting transaction validity period:\n";
echo <<< 'CODE'
// Immortal (valid forever - not recommended for production)
$builder->setEra(null);

// Mortal (valid for N blocks from a starting block)
$builder->setEra([
    'period' => 64,        // Valid for 64 blocks
    'blockNumber' => 1000, // Starting from block 1000
]);
CODE;

echo "\n\n";

// ============================================
// Batch Transactions
// ============================================
echo "--- Batch Transactions ---\n\n";

echo "Building a batch call:\n";
echo <<< 'CODE'
// Create batch call with multiple transfers
$batchBuilder = new ExtrinsicBuilder($registry);

$extrinsic = $batchBuilder
    ->setVersion(4)
    ->setPallet('Utility')
    ->setFunction('batch')
    ->setArgs([
        'calls' => [
            ['Balances', 'transfer', ['dest' => $addr1, 'value' => 1000]],
            ['Balances', 'transfer', ['dest' => $addr2, 'value' => 2000]],
            ['Balances', 'transfer', ['dest' => $addr3, 'value' => 3000]],
        ],
    ])
    ->sign($keypair, $genesisHash, $runtimeVersion)
    ->build();
CODE;

echo "\n\n";

// ============================================
// Submit to Chain
// ============================================
echo "--- Submitting to Chain ---\n\n";

echo "After building the extrinsic, submit via RPC:\n";
echo <<< 'CODE'
// Using WebSocket RPC
$client = new WebSocketClient('wss://rpc.polkadot.io');

// Submit and wait for inclusion
$result = $client->call('author_submitExtrinsic', [$extrinsic->toHex()]);
echo "Extrinsic hash: " . $result . "\n";

// Or watch for events
$client->subscribe('author_submitAndWatchExtrinsic', [$extrinsic->toHex()], function($event) {
    if ($event['finalized']) {
        echo "Transaction finalized in block: " . $event['finalized'] . "\n";
    }
});
CODE;

echo "\n\n=== Extrinsic Examples Complete ===\n";
