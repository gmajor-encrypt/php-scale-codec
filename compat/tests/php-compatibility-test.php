<?php

declare(strict_types=1);

/**
 * PHP Compatibility Test Runner
 * 
 * This script compares php-scale-codec encoding results against
 * expected values from polkadot.js SCALE codec reference.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Substrate\ScaleCodec\Types\TypeRegistry;
use Substrate\ScaleCodec\Bytes\ScaleBytes;

class CompatibilityTestRunner
{
    private TypeRegistry $registry;
    private int $passed = 0;
    private int $failed = 0;
    private int $errors = 0;
    private array $failures = [];

    public function __construct()
    {
        $this->registry = new TypeRegistry();
    }

    /**
     * Run all compatibility tests
     */
    public function run(): array
    {
        $vectorsPath = __DIR__ . '/test-vectors.json';
        
        if (!file_exists($vectorsPath)) {
            echo "Test vectors not found at: $vectorsPath\n";
            return ['error' => 'Test vectors not found'];
        }

        $vectors = json_decode(file_get_contents($vectorsPath), true);

        foreach ($vectors as $typeName => $testCases) {
            if (in_array($typeName, ['description', 'version', 'generated', 'source'])) {
                continue;
            }

            foreach ($testCases as $testCase) {
                $this->runTest($typeName, $testCase);
            }
        }

        $this->printResults();

        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'errors' => $this->errors,
            'failures' => $this->failures,
        ];
    }

    /**
     * Run a single test
     */
    private function runTest(string $typeName, array $testCase): void
    {
        $value = $testCase['value'];
        $expected = strtolower($testCase['expected']);

        try {
            // Normalize type name
            $normalizedName = $this->normalizeTypeName($typeName);
            $type = $this->registry->get($normalizedName);
            $encoded = $type->encode($value);
            $actual = strtolower($encoded->toHex());

            if ($actual !== $expected) {
                $this->failed++;
                $this->failures[] = [
                    'type' => $typeName,
                    'value' => $value,
                    'expected' => $expected,
                    'actual' => $actual,
                    'error' => 'Mismatch',
                ];
            } else {
                $this->passed++;
            }
        } catch (\Throwable $e) {
            $this->errors++;
            $this->failures[] = [
                'type' => $typeName,
                'value' => $value,
                'expected' => $expected,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Normalize type name for registry lookup
     */
    private function normalizeTypeName(string $typeName): string
    {
        // Handle Vec<U8> -> Vec with U8 element type
        if (preg_match('/^Vec<(\w+)>$/', $typeName, $matches)) {
            return 'Vec'; // Will need to set element type
        }

        // Handle Option<U8> -> Option with U8 inner type
        if (preg_match('/^Option<(\w+)>$/', $typeName, $matches)) {
            return 'Option'; // Will need to set inner type
        }

        return $typeName;
    }

    /**
     * Print test results
     */
    private function printResults(): void
    {
        echo "\n=== PHP Compatibility Test Results ===\n\n";
        echo "Passed:  {$this->passed}\n";
        echo "Failed:  {$this->failed}\n";
        echo "Errors:  {$this->errors}\n";
        echo "Total:   " . ($this->passed + $this->failed + $this->errors) . "\n\n";

        if (!empty($this->failures)) {
            echo "=== Failures ===\n\n";
            foreach ($this->failures as $failure) {
                echo "Type: {$failure['type']}\n";
                echo "Value: " . json_encode($failure['value']) . "\n";
                if (isset($failure['expected'])) {
                    echo "Expected: {$failure['expected']}\n";
                    echo "Actual:   {$failure['actual']}\n";
                }
                if (isset($failure['error']) && $failure['error'] !== 'Mismatch') {
                    echo "Error: {$failure['error']}\n";
                }
                echo "\n";
            }
        }
    }
}

// Run tests
$runner = new CompatibilityTestRunner();
$results = $runner->run();

exit($results['failed'] > 0 || $results['errors'] > 0 ? 1 : 0);
