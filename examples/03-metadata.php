<?php

/**
 * Example: Metadata Parsing
 * 
 * This example demonstrates how to parse Substrate metadata
 * and extract type information.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Metadata\MetadataParser;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

echo "=== Metadata Parsing Examples ===\n\n";

// ============================================
// Metadata from hex string
// ============================================
echo "--- Parsing Metadata ---\n\n";

// Sample metadata (truncated for example - use real chain metadata)
// In production, fetch from RPC: state_getMetadata
$metadataHex = '0x...'; // Replace with actual metadata

echo "To parse metadata:\n";
echo "1. Fetch metadata from chain via RPC: state_getMetadata\n";
echo "2. Pass the hex result to MetadataParser\n\n";

echo "Example code:\n";
echo <<< 'CODE'
$rpc = new SubstrateRpc('wss://rpc.polkadot.io');
$metadataHex = $rpc->call('state_getMetadata');

$parser = new MetadataParser();
$metadata = $parser->parse($metadataHex);

// Access pallets
$pallets = $metadata->getPallets();
foreach ($pallets as $pallet) {
    echo "Pallet: " . $pallet->getName() . "\n";
}

// Get specific pallet
$systemPallet = $metadata->getPallet('System');

// Get events for a pallet
$events = $metadata->getPalletEvents('Balances');

// Get calls for a pallet
$calls = $metadata->getPalletCalls('Balances');

// Get storage items
$storage = $metadata->getPalletStorage('System');
CODE;

echo "\n\n";

// ============================================
// Type Registry Usage
// ============================================
echo "--- Type Registry ---\n\n";

$registry = new TypeRegistry();

// Register custom types
echo "Registering custom types:\n";
echo <<< 'CODE'
// Register a simple alias
$registry->register('AccountId', $registry->get('Bytes32'));

// Register a complex type
$registry->register('Balance', $registry->get('U128'));
CODE;

echo "\n\n";

// ============================================
// Type Factory
// ============================================
echo "--- Type Factory ---\n\n";

echo "Creating parameterized types:\n";
echo <<< 'CODE'
use Substrate\ScaleCodec\Types\TypeFactory;

$factory = new TypeFactory($registry);

// Create Vec<U8>
$vecU8 = $factory->create('Vec<U8>');

// Create Option<U32>
$optionU32 = $factory->create('Option<U32>');

// Create complex nested type
$complexType = $factory->create('Option<Vec<U8>>');

// Create tuple
$tuple = $factory->create('(U8, U32, Bool)');
CODE;

echo "\n\n";

// ============================================
// Practical Example: Balance Transfer
// ============================================
echo "--- Practical: Balance Transfer Call ---\n\n";

echo "Encoding a balance transfer call:\n";
echo <<< 'CODE'
// From metadata, get the Balances.transfer call
$transferCall = $metadata->getCall('Balances', 'transfer');

// Build call data
$callData = [
    'callIndex' => [0x05, 0x00], // Balances pallet, transfer function
    'args' => [
        'dest' => $recipientAccountId,
        'value' => 1000000000, // 1 DOT (in plancks)
    ],
];

// Encode the call
$encoded = $transferCall->encode($callData);
CODE;

echo "\n\n=== Metadata Examples Complete ===\n";
