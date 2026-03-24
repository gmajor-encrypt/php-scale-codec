<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Metadata\{MetadataParser, MetadataVersion};
use Substrate\ScaleCodec\Bytes\ScaleBytes;

/**
 * @BeforeMethods("setUp")
 * @Iterations(5)
 * @Revs(10)
 * @Warmup(1)
 */
class MetadataBench
{
    private string $sampleMetadata;
    private MetadataParser $parser;

    public function setUp(): void
    {
        $this->parser = new MetadataParser();
        
        // Create a minimal v14 metadata for benchmarking
        $this->sampleMetadata = '0x6d657461' // magic "meta"
            . '0e' // version 14
            . '00' // 0 types
            . '00' // 0 pallets
            . '04' // extrinsic version
            . '00' // address type
            . '00' // call type
            . '00' // signature type
            . '00'; // extra type
    }

    public function benchParseMetadata(): void
    {
        $this->parser->parse($this->sampleMetadata, useCache: false);
    }

    public function benchParseMetadataCached(): void
    {
        $this->parser->parse($this->sampleMetadata, useCache: true);
    }
}
