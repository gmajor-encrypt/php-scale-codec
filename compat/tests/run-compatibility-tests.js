// @ts-check
/**
 * Run compatibility tests between php-scale-codec and polkadot.js
 * 
 * Usage: node tests/run-compatibility-tests.js
 * 
 * This script:
 * 1. Loads test vectors (expected encodings from polkadot.js)
 * 2. Runs PHP encoder/decoder
 * 3. Compares results
 * 4. Reports any discrepancies
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Test categories
const categories = [
  'integers',
  'compact',
  'bool',
  'string',
  'vec',
  'option',
  'tuple',
  'fixed_array'
];

// Run PHP test
function runPhpTest(type, value) {
  const phpScript = `
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Substrate\\ScaleCodec\\Types\\{TypeRegistry, TypeFactory};

$registry = new TypeRegistry();
$type = $registry->get('${type}');
$encoded = $type->encode(${JSON.stringify(value)});
echo json_encode([
  'hex' => $encoded->toHex(),
  'value' => ${JSON.stringify(value)}
]);
`;

  try {
    const result = execSync(`php -r '${phpScript.replace(/'/g, "\\'")}'`, {
      cwd: path.join(__dirname, '..'),
      encoding: 'utf-8'
    });
    return JSON.parse(result);
  } catch (error) {
    return { error: error.message };
  }
}

// Compare encodings
function compareEncoding(type, value, expectedHex) {
  const phpResult = runPhpTest(type, value);
  
  if (phpResult.error) {
    return {
      type,
      value,
      status: 'ERROR',
      error: phpResult.error
    };
  }

  const actualHex = phpResult.hex.toLowerCase();
  const expected = expectedHex.toLowerCase();

  if (actualHex !== expected) {
    return {
      type,
      value,
      status: 'MISMATCH',
      expected: expectedHex,
      actual: actualHex
    };
  }

  return {
    type,
    value,
    status: 'PASS',
    hex: actualHex
  };
}

// Run all tests
function runAllTests() {
  const results = {
    passed: 0,
    failed: 0,
    errors: 0,
    details: []
  };

  // Load test vectors
  const vectorsPath = path.join(__dirname, 'test-vectors.json');
  
  if (!fs.existsSync(vectorsPath)) {
    console.log('Test vectors not found. Run `npm run generate` first.');
    return results;
  }

  const vectors = JSON.parse(fs.readFileSync(vectorsPath, 'utf-8'));

  // Run comparison for each type
  for (const [type, testCases] of Object.entries(vectors)) {
    for (const testCase of testCases) {
      const result = compareEncoding(type, testCase.value, testCase.expected);
      results.details.push(result);

      switch (result.status) {
        case 'PASS':
          results.passed++;
          break;
        case 'MISMATCH':
          results.failed++;
          break;
        case 'ERROR':
          results.errors++;
          break;
      }
    }
  }

  // Print summary
  console.log('\n=== Compatibility Test Results ===\n');
  console.log(`Passed:  ${results.passed}`);
  console.log(`Failed:  ${results.failed}`);
  console.log(`Errors:  ${results.errors}`);
  console.log(`Total:   ${results.passed + results.failed + results.errors}`);
  console.log('\n');

  // Print failures
  const failures = results.details.filter(r => r.status !== 'PASS');
  if (failures.length > 0) {
    console.log('=== Failures ===\n');
    failures.forEach(f => {
      console.log(`Type: ${f.type}`);
      console.log(`Value: ${JSON.stringify(f.value)}`);
      if (f.expected) {
        console.log(`Expected: ${f.expected}`);
        console.log(`Actual:   ${f.actual}`);
      } else {
        console.log(`Error: ${f.error}`);
      }
      console.log('');
    });
  }

  return results;
}

// Export for programmatic use
module.exports = { runAllTests, compareEncoding };

// Run if called directly
if (require.main === module) {
  runAllTests();
}
