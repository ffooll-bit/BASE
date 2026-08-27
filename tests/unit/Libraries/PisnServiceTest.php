<?php

namespace Tests\Unit\Libraries;

use App\Libraries\PisnService;
use CodeIgniter\Test\CIUnitTestCase;

class PisnServiceTest extends CIUnitTestCase
{
    public function testCheckEligibilityReturnsDeferred(): void
    {
        $pisn   = new PisnService();
        $result = $pisn->checkEligibility(['nim' => '201731009', 'nama' => 'Joko']);

        $this->assertFalse($result['available']);
        $this->assertNull($result['eligible']);
        $this->assertIsString($result['reason']);
    }
}
