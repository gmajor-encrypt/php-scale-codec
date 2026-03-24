<?php

declare(strict_types=1);

namespace Substrate\ScaleCodec\Tests\Event;

use PHPUnit\Framework\TestCase;
use Substrate\ScaleCodec\Bytes\ScaleBytes;
use Substrate\ScaleCodec\Event\{Event, EventRecord, EventParser, EventIndex, ErrorEventDetector, Phase};

class EventTest extends TestCase
{
    // ==================== Phase Tests ====================

    public function testPhaseFromIndex(): void
    {
        $this->assertEquals(Phase::ApplyExtrinsic, Phase::fromIndex(0));
        $this->assertEquals(Phase::Finalization, Phase::fromIndex(1));
        $this->assertEquals(Phase::Initialization, Phase::fromIndex(2));
        // Default to ApplyExtrinsic for unknown
        $this->assertEquals(Phase::ApplyExtrinsic, Phase::fromIndex(99));
    }

    public function testPhaseToIndex(): void
    {
        $this->assertEquals(0, Phase::ApplyExtrinsic->toIndex());
        $this->assertEquals(1, Phase::Finalization->toIndex());
        $this->assertEquals(2, Phase::Initialization->toIndex());
    }

    public function testPhaseValue(): void
    {
        $this->assertEquals('ApplyExtrinsic', Phase::ApplyExtrinsic->value);
        $this->assertEquals('Finalization', Phase::Finalization->value);
        $this->assertEquals('Initialization', Phase::Initialization->value);
    }

    // ==================== Event Tests ====================

    public function testEventCreation(): void
    {
        $event = new Event(
            pallet: 'System',
            name: 'ExtrinsicSuccess',
            palletIndex: 0,
            eventIndex: 0,
            data: ['dispatchInfo' => ['weight' => 100]]
        );

        $this->assertEquals('System', $event->pallet);
        $this->assertEquals('ExtrinsicSuccess', $event->name);
        $this->assertEquals(0, $event->palletIndex);
        $this->assertEquals(0, $event->eventIndex);
        $this->assertEquals('System.ExtrinsicSuccess', $event->getIdentifier());
    }

    public function testEventGetField(): void
    {
        $event = new Event(
            pallet: 'Balances',
            name: 'Transfer',
            palletIndex: 5,
            eventIndex: 0,
            data: [
                'from' => '0x' . str_repeat('01', 32),
                'to' => '0x' . str_repeat('02', 32),
                'amount' => 1000,
            ]
        );

        $this->assertEquals('0x' . str_repeat('01', 32), $event->getField('from'));
        $this->assertEquals('0x' . str_repeat('02', 32), $event->getField('to'));
        $this->assertEquals(1000, $event->getField('amount'));
        $this->assertNull($event->getField('nonexistent'));
    }

    public function testEventHasField(): void
    {
        $event = new Event(
            pallet: 'System',
            name: 'Test',
            palletIndex: 0,
            eventIndex: 0,
            data: ['a' => 1]
        );

        $this->assertTrue($event->hasField('a'));
        $this->assertFalse($event->hasField('b'));
    }

    public function testEventToArray(): void
    {
        $event = new Event(
            pallet: 'System',
            name: 'Test',
            palletIndex: 0,
            eventIndex: 1,
            data: ['value' => 42]
        );

        $array = $event->toArray();

        $this->assertEquals('System', $array['pallet']);
        $this->assertEquals('Test', $array['name']);
        $this->assertEquals(0, $array['palletIndex']);
        $this->assertEquals(1, $array['eventIndex']);
        $this->assertEquals(['value' => 42], $array['data']);
    }

    // ==================== EventRecord Tests ====================

    public function testEventRecordCreation(): void
    {
        $event = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $record = new EventRecord(
            phase: Phase::ApplyExtrinsic,
            extrinsicIndex: 5,
            event: $event,
            topics: ['0x' . str_repeat('aa', 32)]
        );

        $this->assertEquals(Phase::ApplyExtrinsic, $record->phase);
        $this->assertEquals(5, $record->extrinsicIndex);
        $this->assertSame($event, $record->event);
        $this->assertCount(1, $record->topics);
    }

    public function testEventRecordPhaseCheckers(): void
    {
        $event = new Event('System', 'Test', 0, 0);

        $applyRecord = new EventRecord(Phase::ApplyExtrinsic, 1, $event);
        $this->assertTrue($applyRecord->isApplyExtrinsic());
        $this->assertFalse($applyRecord->isFinalization());
        $this->assertFalse($applyRecord->isInitialization());

        $finalRecord = new EventRecord(Phase::Finalization, null, $event);
        $this->assertFalse($finalRecord->isApplyExtrinsic());
        $this->assertTrue($finalRecord->isFinalization());
        $this->assertFalse($finalRecord->isInitialization());

        $initRecord = new EventRecord(Phase::Initialization, null, $event);
        $this->assertFalse($initRecord->isApplyExtrinsic());
        $this->assertFalse($initRecord->isFinalization());
        $this->assertTrue($initRecord->isInitialization());
    }

    public function testEventRecordGetExtrinsicIndex(): void
    {
        $event = new Event('System', 'Test', 0, 0);

        $applyRecord = new EventRecord(Phase::ApplyExtrinsic, 5, $event);
        $this->assertEquals(5, $applyRecord->getExtrinsicIndex());

        $finalRecord = new EventRecord(Phase::Finalization, null, $event);
        $this->assertNull($finalRecord->getExtrinsicIndex());
    }

    public function testEventRecordHasTopics(): void
    {
        $event = new Event('System', 'Test', 0, 0);

        $withTopics = new EventRecord(Phase::ApplyExtrinsic, 0, $event, ['0x' . str_repeat('aa', 32)]);
        $this->assertTrue($withTopics->hasTopics());

        $withoutTopics = new EventRecord(Phase::ApplyExtrinsic, 0, $event, []);
        $this->assertFalse($withoutTopics->hasTopics());
    }

    // ==================== EventParser Tests ====================

    public function testParseEventRecord(): void
    {
        // Minimal EventRecord:
        // Phase: ApplyExtrinsic(0) = 0x00 + u32(0) = 0x00000000
        // Event: pallet_index(0) + event_index(0) = 0x0000
        // Topics: count(0) = 0x00
        $hex = '0x'
            . '00'       // ApplyExtrinsic variant
            . '00000000' // extrinsic index (u32)
            . '00'       // pallet index
            . '00'       // event index
            . '00';      // topics count

        $parser = new EventParser();
        $record = $parser->parseEventRecord(ScaleBytes::fromHex($hex));

        $this->assertEquals(Phase::ApplyExtrinsic, $record->phase);
        $this->assertEquals(0, $record->extrinsicIndex);
        $this->assertEquals(0, $record->event->palletIndex);
        $this->assertEquals(0, $record->event->eventIndex);
    }

    public function testParseFinalizationPhase(): void
    {
        // Finalization phase
        $hex = '0x'
            . '01'   // Finalization variant
            . '00'   // pallet index
            . '00'   // event index
            . '00';  // topics count

        $parser = new EventParser();
        $record = $parser->parseEventRecord(ScaleBytes::fromHex($hex));

        $this->assertEquals(Phase::Finalization, $record->phase);
        $this->assertNull($record->extrinsicIndex);
    }

    public function testParseInitializationPhase(): void
    {
        // Initialization phase
        $hex = '0x'
            . '02'   // Initialization variant
            . '00'   // pallet index
            . '00'   // event index
            . '00';  // topics count

        $parser = new EventParser();
        $record = $parser->parseEventRecord(ScaleBytes::fromHex($hex));

        $this->assertEquals(Phase::Initialization, $record->phase);
        $this->assertNull($record->extrinsicIndex);
    }

    public function testParseMultipleEventRecords(): void
    {
        // 2 EventRecords
        $hex = '0x'
            . '08'       // count (compact: 2 = 0x08)
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

        $parser = new EventParser();
        $records = $parser->parseHex($hex);

        $this->assertCount(2, $records);
        $this->assertEquals(0, $records[0]->extrinsicIndex);
        $this->assertEquals(1, $records[1]->extrinsicIndex);
    }

    public function testParseTopics(): void
    {
        // EventRecord with 1 topic
        $topic = str_repeat('aa', 32);
        $hex = '0x'
            . '00'       // ApplyExtrinsic
            . '00000000' // extrinsic index
            . '00'       // pallet index
            . '00'       // event index
            . '04'       // topics count (compact: 1 = 0x04)
            . $topic;    // topic (32 bytes)

        $parser = new EventParser();
        $record = $parser->parseEventRecord(ScaleBytes::fromHex($hex));

        $this->assertCount(1, $record->topics);
        $this->assertEquals('0x' . $topic, $record->topics[0]);
    }

    // ==================== EventIndex Tests ====================

    public function testEventIndexFindByName(): void
    {
        $event1 = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $event2 = new Event('System', 'ExtrinsicFailed', 0, 1);
        $event3 = new Event('Balances', 'Transfer', 5, 0);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $event1),
            new EventRecord(Phase::ApplyExtrinsic, 1, $event2),
            new EventRecord(Phase::ApplyExtrinsic, 2, $event3),
        ];

        $index = new EventIndex($records);

        $success = $index->findByName('System', 'ExtrinsicSuccess');
        $this->assertCount(1, $success);
        $this->assertEquals('ExtrinsicSuccess', $success[0]->event->name);

        $transfers = $index->findByName('Balances', 'Transfer');
        $this->assertCount(1, $transfers);
    }

    public function testEventIndexFindByPallet(): void
    {
        $event1 = new Event('System', 'Event1', 0, 0);
        $event2 = new Event('System', 'Event2', 0, 1);
        $event3 = new Event('Balances', 'Transfer', 5, 0);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $event1),
            new EventRecord(Phase::ApplyExtrinsic, 1, $event2),
            new EventRecord(Phase::ApplyExtrinsic, 2, $event3),
        ];

        $index = new EventIndex($records);

        $systemEvents = $index->findByPallet(0);
        $this->assertCount(2, $systemEvents);

        $balancesEvents = $index->findByPallet(5);
        $this->assertCount(1, $balancesEvents);
    }

    public function testEventIndexFindByExtrinsic(): void
    {
        $event = new Event('System', 'Test', 0, 0);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $event),
            new EventRecord(Phase::ApplyExtrinsic, 1, $event),
            new EventRecord(Phase::ApplyExtrinsic, 1, $event),
        ];

        $index = new EventIndex($records);

        $extrinsic0 = $index->findByExtrinsic(0);
        $this->assertCount(1, $extrinsic0);

        $extrinsic1 = $index->findByExtrinsic(1);
        $this->assertCount(2, $extrinsic1);
    }

    public function testEventIndexHas(): void
    {
        $event = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $records = [new EventRecord(Phase::ApplyExtrinsic, 0, $event)];

        $index = new EventIndex($records);

        $this->assertTrue($index->has('System', 'ExtrinsicSuccess'));
        $this->assertFalse($index->has('System', 'NonExistent'));
    }

    public function testEventIndexCount(): void
    {
        $event = new Event('System', 'Test', 0, 0);
        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $event),
            new EventRecord(Phase::ApplyExtrinsic, 1, $event),
        ];

        $index = new EventIndex($records);
        $this->assertEquals(2, $index->count());
    }

    // ==================== ErrorEventDetector Tests ====================

    public function testErrorDetectorHasErrors(): void
    {
        $success = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $failed = new Event('System', 'ExtrinsicFailed', 0, 1);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $success),
            new EventRecord(Phase::ApplyExtrinsic, 1, $failed),
        ];

        $detector = new ErrorEventDetector($records);
        $this->assertTrue($detector->hasErrors());
    }

    public function testErrorDetectorNoErrors(): void
    {
        $success = new Event('System', 'ExtrinsicSuccess', 0, 0);

        $records = [new EventRecord(Phase::ApplyExtrinsic, 0, $success)];

        $detector = new ErrorEventDetector($records);
        $this->assertFalse($detector->hasErrors());
    }

    public function testErrorDetectorExtrinsicFailed(): void
    {
        $success = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $failed = new Event('System', 'ExtrinsicFailed', 0, 1);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $success),
            new EventRecord(Phase::ApplyExtrinsic, 1, $failed),
        ];

        $detector = new ErrorEventDetector($records);

        $this->assertFalse($detector->extrinsicFailed(0));
        $this->assertTrue($detector->extrinsicFailed(1));
    }

    public function testErrorDetectorGetExtrinsicError(): void
    {
        $failed = new Event('System', 'ExtrinsicFailed', 0, 1);
        $records = [new EventRecord(Phase::ApplyExtrinsic, 5, $failed)];

        $detector = new ErrorEventDetector($records);
        $error = $detector->getExtrinsicError(5);

        $this->assertNotNull($error);
        $this->assertEquals('ExtrinsicFailed', $error->event->name);
        $this->assertEquals(5, $error->getExtrinsicIndex());
    }

    public function testErrorDetectorGetErrorSummary(): void
    {
        $success = new Event('System', 'ExtrinsicSuccess', 0, 0);
        $failed = new Event('System', 'ExtrinsicFailed', 0, 1);

        $records = [
            new EventRecord(Phase::ApplyExtrinsic, 0, $success),
            new EventRecord(Phase::ApplyExtrinsic, 1, $failed),
            new EventRecord(Phase::ApplyExtrinsic, 2, $failed),
        ];

        $detector = new ErrorEventDetector($records);
        $summary = $detector->getErrorSummary();

        $this->assertEquals(2, $summary['System.ExtrinsicFailed']);
    }
}
