<?php
use PHPUnit\Framework\TestCase;

define('PHPUNIT_TEST', true);
require_once __DIR__ . '/get_products.php';

class ExampleTest extends TestCase
{
    public function testSzovegMezoTisztitas()
    {
        $bemenet = [
            'marka' => '  Apple  ',
            'ar' => 5000,
            'hibas' => ['tomb']
        ];

        $this->assertEquals('Apple', getMezo($bemenet, 'marka'));
        $this->assertEquals('5000', getMezo($bemenet, 'ar'));
        $this->assertEquals('', getMezo($bemenet, 'hibas'));
        $this->assertEquals('', getMezo($bemenet, 'nem_letezo'));
    }

    public function testListaMezoTisztitasEsDuplikacioSzures()
    {
        $bemenet = [
            'anyag' => ['  bőr ', 'szilikon', 'bőr', '', '   ']
        ];

        $this->assertEquals(['bőr', 'szilikon'], getListaMezo($bemenet, 'anyag'));
    }

    public function testListaMezoEgyetlenErtekbolTombotCsinal()
    {
        $bemenet = ['minta' => '  virágos '];

        $this->assertEquals(['virágos'], getListaMezo($bemenet, 'minta'));
    }

    public function testKeresesiSzavakFelbontasa()
    {
        $this->assertEquals(['iPhone', '15', 'bőr'], bontKeresesiSzavakra('  iPhone   15   bőr  '));
        $this->assertEquals([], bontKeresesiSzavakra('   '));
    }
}
