<?php

namespace Tests\Unit\Libraries;

use App\Libraries\WizardProgress;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Test\CIUnitTestCase;

class WizardProgressTest extends CIUnitTestCase
{
    private CacheInterface $cache;
    private WizardProgress $progress;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->createStub(CacheInterface::class);
        $this->progress = new WizardProgress($this->cache);
    }

    public function testGenerateTokenReturnsHexString(): void
    {
        $token = $this->progress->generateToken();

        $this->assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $token);
    }

    public function testSaveThenLoadRoundTrips(): void
    {
        $state = ['step' => 3, 'flags' => ['a' => true]];

        $this->cache->method('save')->willReturn(true);
        $this->cache->method('get')->willReturn($state);

        $this->assertTrue($this->progress->save('tok', $state));
        $this->assertSame($state, $this->progress->load('tok'));
    }

    public function testLoadReturnsNullWhenMissing(): void
    {
        $this->cache->method('get')->willReturn(null);

        $this->assertNull($this->progress->load('missing'));
    }

    public function testClearDeletesStoredState(): void
    {
        $deleted = null;
        $this->cache->method('delete')->willReturnCallback(static function ($key) use (&$deleted) {
            $deleted = $key;

            return true;
        });

        $this->progress->clear('tok');

        $this->assertSame('wizard_progress_tok', $deleted);
    }
}
