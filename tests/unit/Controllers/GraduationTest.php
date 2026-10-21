<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Graduation;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;

class GraduationTest extends CIUnitTestCase
{
    /**
     * Final-project names verified live per program studi, plus the intermediate
     * milestones that must NOT count as the final requirement.
     *
     * @return array<string, array{string, string, bool}>
     */
    public static function courseProvider(): array
    {
        return [
            'S1 Skripsi'                          => ['S1 Manajemen', 'Skripsi', true],
            'S1 THESIS'                           => ['S1 Manajemen', 'THESIS', true],
            'S1 Tugas Akhir'                      => ['S1 Manajemen', 'Tugas Akhir', true],
            'S2 Manajemen Tesis'                  => ['S2 Ilmu Manajemen', 'Tesis', true],
            'S2 Manajemen final exam'             => ['S2 Ilmu Manajemen', 'Ujian Hasil Penelitian dan Tutup', true],
            'S2 Akuntansi Ujian Tutup'            => ['S2 Ilmu Akuntansi', 'Ujian Tutup', true],
            'S2 Akuntansi results milestone'      => ['S2 Ilmu Akuntansi', 'Ujian Hasil Penelitian', false],
            'S2 Akuntansi proposal milestone'     => ['S2 Ilmu Akuntansi', 'Ujian Proposal Penelitian', false],
            'S3 Ujian Terbuka (Promosi)'          => ['S3 Ilmu Manajemen', 'Ujian Terbuka (Promosi)', true],
            'S3 closed exam is intermediate'      => ['S3 Ilmu Manajemen', 'Ujian Tutup', false],
            'S3 results-exam milestone'           => ['S3 Ilmu Manajemen', 'Ujian Hasil Penelitian Disertasi', false],
            'S3 proposal milestone'               => ['S3 Ilmu Manajemen', 'Ujian Seminar Proposal', false],
            'S3 publication requirement'          => ['S3 Ilmu Manajemen', 'Publikasi Jurnal Internasional', false],
            'S2 results-exam milestone'           => ['S2 Ilmu Manajemen', 'Ujian Hasil Penelitian Disertasi', false],
            'S2 methodology course'               => ['S2 Ilmu Manajemen', 'Metodologi Penelitian Lanjutan', false],
            'S1 closed exam is not final'         => ['S1 Manajemen', 'Ujian Tutup', false],
            'regular course'                      => ['S1 Manajemen', 'Manajemen Strategik', false],
        ];
    }

    #[DataProvider('courseProvider')]
    public function testFinalProjectDetectionPerProgram(string $programStudi, string $courseName, bool $expected): void
    {
        $result = $this->completeness([
            ['nm_mk' => $courseName, 'nilai_huruf' => 'A', 'choosed' => false],
        ], $programStudi);

        $this->assertSame($expected, $result['complete'], "Course '{$courseName}' for {$programStudi}");
    }

    public function testUngradedFinalProjectIsIncomplete(): void
    {
        $result = $this->completeness([
            ['nm_mk' => 'Ujian Tutup', 'nilai_huruf' => '', 'nilai_angka' => null],
        ], 'S2 Ilmu Akuntansi');

        $this->assertFalse($result['complete']);
        $this->assertStringContainsString('belum tersedia', $result['reason']);
    }

    public function testChoosedFalseDoesNotBlockCompleteness(): void
    {
        $result = $this->completeness([
            ['nm_mk' => 'Ujian Tutup', 'nilai_huruf' => 'A', 'choosed' => false],
        ], 'S2 Ilmu Akuntansi');

        $this->assertTrue($result['complete']);
    }

    public function testMissingGradeIgnoresS3ClosedExam(): void
    {
        // S3 "Ujian Tutup" is an intermediate milestone; only the open exam is final.
        $this->assertNull($this->missing([
            ['nm_mk' => 'Ujian Tutup', 'id_smt' => '20261', 'nilai_huruf' => '', 'nilai_angka' => null, 'id_reg_pd' => 'reg', 'id_kls' => 'tutup'],
        ], 'S3 Ilmu Manajemen'));

        $this->assertNull($this->missing([
            ['nm_mk' => 'Ujian Tutup', 'id_smt' => '20261', 'nilai_huruf' => '', 'nilai_angka' => null, 'id_reg_pd' => 'reg', 'id_kls' => 'tutup'],
            ['nm_mk' => 'Ujian Terbuka (Promosi)', 'id_smt' => '20261', 'nilai_huruf' => 'A', 'nilai_angka' => '90', 'id_reg_pd' => 'reg', 'id_kls' => 'terbuka'],
        ], 'S3 Ilmu Manajemen'));
    }

    public function testMissingGradeTargetsS2ClosedExam(): void
    {
        $row = $this->missing([
            ['nm_mk' => 'Ujian Hasil Penelitian', 'id_smt' => '20251', 'nilai_huruf' => 'A', 'nilai_angka' => '90', 'id_reg_pd' => 'reg', 'id_kls' => 'results'],
            ['nm_mk' => 'Ujian Tutup', 'id_smt' => '20261', 'nilai_huruf' => '', 'nilai_angka' => null, 'id_reg_pd' => 'reg', 'id_kls' => 'tutup'],
        ], 'S2 Ilmu Akuntansi');

        $this->assertSame(['id_reg_pd' => 'reg', 'id_kls' => 'tutup', 'id_smt' => '20261'], $row);
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return array{complete:bool, reason:string, thesis:array}
     */
    private function completeness(array $rows, string $programStudi): array
    {
        $controller = (new ReflectionClass(Graduation::class))->newInstanceWithoutConstructor();
        $method     = new ReflectionMethod(Graduation::class, 'checkTranscriptCompleteness');
        $method->setAccessible(true);

        return $method->invoke($controller, $rows, $programStudi);
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function missing(array $rows, string $programStudi): ?array
    {
        $controller = (new ReflectionClass(Graduation::class))->newInstanceWithoutConstructor();
        $method     = new ReflectionMethod(Graduation::class, 'findMissingGradeThesis');
        $method->setAccessible(true);

        return $method->invoke($controller, $rows, $programStudi);
    }
}
