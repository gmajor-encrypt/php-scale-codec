// @ts-check
/**
 * Generate test data using polkadot.js SCALE codec
 * This file generates test vectors for PHP compatibility testing
 */

const { encode, decode } = require('@polkadot/codec');
const { u8aToHex, hexToU8a } = require('@polkadot/util');
const fs = require('fs');
const path = require('path');

// Test data generator
const testData = {
  // Integer types
  integers: {
    u8: {
      min: 0,
      max: 255,
      samples: [0, 1, 127, 255]
    },
    u16: {
      min: 0,
      max: 65535,
      samples: [0, 1, 32767, 65535]
    },
    u32: {
      min: 0,
      max: 4294967295,
      samples: [0, 1, 2147483647, 4294967295]
    },
    u64: {
      min: '0',
      max: '18446744073709551615',
      samples: ['0', '1', '9223372036854775807', '18446744073709551615']
    },
    u128: {
      min: '0',
      max: '340282366920938463463374607431768211455',
      samples: ['0', '1', '340282366920938463463374607431768211455']
    },
    i8: {
      min: -128,
      max: 127,
      samples: [-128, -1, 0, 1, 127]
    },
    i16: {
      min: -32768,
      max: 32767,
      samples: [-32768, -1, 0, 1, 32767]
    },
    i32: {
      min: -2147483648,
      max: 2147483647,
      samples: [-2147483648, -1, 0, 1, 2147483647]
    },
    i64: {
      min: '-9223372036854775808',
      max: '9223372036854775807',
      samples: ['-9223372036854775808', '-1', '0', '1', '9223372036854775807']
    }
  },

  // Compact integers
  compact: {
    singleByte: [0, 1, 63],
    twoBytes: [64, 16383],
    fourBytes: [16384, 1073741823],
    bigInt: ['1073741824', '1000000000000000000']
  },

  // Boolean
  bool: [true, false],

  // Strings
  string: ['', 'a', 'Hello, World!', '区块链'],

  // Vectors
  vec_u8: [[], [1, 2, 3], [255, 255, 255]],
  vec_u32: [[], [0, 1, 2], [4294967295]],

  // Options
  option_u8: [null, 0, 255],
  option_bool: [null, true, false],

  // Tuples
  tuple_u8_u32: [[0, 0], [255, 4294967295]],

  // Fixed arrays
  fixed_u8_32: [new Uint8Array(32).fill(0), new Uint8Array(32).fill(255)]
};

// Generate encoded test vectors
function generateTestVectors() {
  const vectors = {};

  // This is a placeholder - actual implementation would use polkadot.js types
  // to encode all test data and generate expected outputs
  
  console.log('Generated test vectors for compatibility testing');
  return vectors;
}

// Save test vectors to JSON file
function saveTestVectors(vectors) {
  const outputPath = path.join(__dirname, 'test-vectors.json');
  fs.writeFileSync(outputPath, JSON.stringify(vectors, null, 2));
  console.log(`Test vectors saved to ${outputPath}`);
}

// Main
const vectors = generateTestVectors();
saveTestVectors(vectors);

module.exports = { testData, generateTestVectors };
