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

    // Ellenőrzi hogy hiányzó, null vagy nem szöveges mezőnél üres szöveget kapunk e
    public function testSzovegMezoUresErteketAdHianyzoVagyNemSzovegesAdatra()
    {
        $this->assertSame('', getMezo([], 'marka'));
        $this->assertSame('', getMezo(['marka' => null], 'marka'));
        $this->assertSame('', getMezo(['marka' => false], 'marka'));
    }

    // Ellenőrzi egy szám értékből is lista készül ami egy elemet tartalmaz
    public function testListaMezoEgyetlenSzamotIsListavaAlakit()
    {
        $this->assertSame(['42'], getListaMezo(['anyag' => 42], 'anyag'));
    }

    // Ellenőrzi hogy az üres és nem megengedett dolgok kimaradnak e a listából
    public function testListaMezoKiszuriAzUresEsNemSzovegesErtekeket()
    {
        $bemenet = [
            'anyag' => ['bőr', '', '   ', ['hibás adat'], false, 12]
        ];

        $this->assertSame(['bőr', '12'], getListaMezo($bemenet, 'anyag'));
    }

    // Ellenőrzi hogy a tabulátor és a sortörés is elválasztja e a keresési szavakat
    public function testKeresesiSzavakTabulatorEsSortoresAlapjanIsFeldarabol()
    {
        $this->assertSame(
            ['iPhone', '15', 'Pro'],
            bontKeresesiSzavakra("iPhone\t15\nPro")
        );
    }

    // Ellenőrzi hogyha az "anyag" nem szerepel a bemenetben, a függvény üres listát ade
    public function testListaMezoHianyzoMezonelUresListatAd()
    {
        $this->assertSame([], getListaMezo([], 'anyag'));
    }

    // Ellenőrzi hogy a 0 ár szövegként '0' legyen és ne üres szöveg
    public function testSzovegMezoNullaErteketNullaSzoveggeAlakit()
    {
        $this->assertSame('0', getMezo(['ar' => 0], 'ar'));
    }
}