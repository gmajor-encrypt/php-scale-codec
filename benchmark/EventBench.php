<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Benchmark;

use Substrate\ScaleCodec\Event\{EventParser, EventIndex};
use Substrate\ScaleCodec\Bytes\ScaleBytes;

/**
 * @BeforeMethods("setUp")
 * @Iterations(10)
 * @Revs(50)
 * @Warmup(2)
 */
class EventBench
{
    private EventParser $parser;
    private string $sampleEvents;

    public function setUp(): void
    {
        $this->parser = new EventParser();
        
        // Create sample events for benchmarking
        // 2 EventRecords
        $this->sampleEvents = '0x'
            . '08'       // count (compact: 2)
            . '00'       // ApplyExtrinsic variant
            . '00000000' // extrinsic index
            . '00'       // pallet index
            . '00'       // event index
            . '00'       // topics count
            . '00'       // ApplyExtrinsic variant
            . '01000000' // extrinsic index (1)
            . '01'       // pallet index (1)
            . '00'       // event index
            . '00';      // topics count
    }

    public function benchParseEvents(): void
    {
        $this->parser->parseHex($this->sampleEvents);
    }

    public function benchCreateIndex(): void
    {
        $events = $this->parser->parseHex($this->sampleEvents);
        new EventIndex($events);
    }

    public function benchFindByExtrinsic(): void
    {
        $events = $this->parser->parseHex($this->sampleEvents);
        $index = new EventIndex($events);
        $index->findByExtrinsic(0);
    }
}
