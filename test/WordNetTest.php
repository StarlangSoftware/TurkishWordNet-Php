<?php

use olcaytaner\Dictionary\Dictionary\Pos;
use olcaytaner\WordNet\SynSet;
use olcaytaner\WordNet\WordNet;
use PHPUnit\Framework\TestCase;

class WordNetTest extends TestCase
{
    private WordNet $turkish;

    protected function setUp(): void
    {
        ini_set('memory_limit', '600M');
        $this->turkish = new WordNet();
    }

    public function testSize()
    {
        $this->assertEquals(78327, $this->turkish->size());
    }

    public function testWikiPages()
    {
        $wikiCount = 0;
        foreach ($this->turkish->getSynSetList() as $synSet) {
            if ($synSet instanceof SynSet && $synSet->getWikiPage() !== null) {
                $wikiCount++;
            }
        }
        $this->assertEquals(11001, $wikiCount);
    }

    public function testTotalForeignLiterals()
    {
        $count = 0;
        foreach ($this->turkish->getSynSetList() as $synSet) {
            for ($i = 0; $i < $synSet->getSynonym()->literalSize(); $i++) {
                if ($synSet instanceof SynSet && $synSet->getSynonym()->getLiteral($i)->getOrigin() !== null) {
                    $count++;
                }
            }
        }
        $this->assertEquals(3981, $count);
    }

    public function testTotalGroupedLiterals()
    {
        $count = 0;
        foreach ($this->turkish->getSynSetList() as $synSet) {
            for ($i = 0; $i < $synSet->getSynonym()->literalSize(); $i++) {
                if ($synSet instanceof SynSet && $synSet->getSynonym()->getLiteral($i)->getGroupNo() != 0) {
                    $count++;
                }
            }
        }
        $this->assertEquals(5973, $count);
    }

    public function testSynSetList()
    {
        $count = 0;
        foreach ($this->turkish->getSynSetList() as $synSet) {
            $count += $synSet->getSynonym()->literalSize();
        }
        $this->assertEquals(110259, $count);
    }

    public function testLiteralList()
    {
        $this->assertCount(82276, $this->turkish->getLiteralList());
    }

    public function testGetSynSetWithId(){
        $this->assertTrue(null != $this->turkish->getSynSetWithId("TUR10-0000040"));
        $this->assertTrue(null != $this->turkish->getSynSetWithId("TUR10-0648550"));
        $this->assertTrue(null != $this->turkish->getSynSetWithId("TUR10-1034170"));
        $this->assertTrue(null != $this->turkish->getSynSetWithId("TUR10-1047180"));
        $this->assertTrue(null != $this->turkish->getSynSetWithId("TUR10-1196250"));
    }

    public function testGetSynSetWithLiteral(){
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("sıradaki", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("Türkçesi", 2));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("tropikal orman", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("mesut olmak", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("acı badem kurabiyesi", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("açık kapı siyaseti", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("bir baştan bir başa", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("eş zamanlı dil bilimi", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("bir iğne bir iplik olmak", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("yedi kat yerin dibine geçmek", 2));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("kedi gibi dört ayak üzerine düşmek", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("bir kulağından girip öbür kulağından çıkmak", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("anasından emdiği süt burnundan fitil fitil gelmek", 1));
        $this->assertTrue(null != $this->turkish->getSynSetWithLiteral("bir ayak üstünde kırk yalanın belini bükmek", 1));
    }

    public function testNumberOfSynSetsWithLiteral(){
        $this->assertEquals(1, $this->turkish->numberOfSynSetsWithLiteral("yolcu etmek"));
        $this->assertEquals(2, $this->turkish->numberOfSynSetsWithLiteral("açık pembe"));
        $this->assertEquals(3, $this->turkish->numberOfSynSetsWithLiteral("bürokrasi"));
        $this->assertEquals(4, $this->turkish->numberOfSynSetsWithLiteral("bordür"));
        $this->assertEquals(5, $this->turkish->numberOfSynSetsWithLiteral("duygulanım"));
        $this->assertEquals(6, $this->turkish->numberOfSynSetsWithLiteral("sarsıntı"));
        $this->assertEquals(7, $this->turkish->numberOfSynSetsWithLiteral("kuvvetli"));
        $this->assertEquals(8, $this->turkish->numberOfSynSetsWithLiteral("merkez"));
        $this->assertEquals(9, $this->turkish->numberOfSynSetsWithLiteral("yüksek"));
        $this->assertEquals(10, $this->turkish->numberOfSynSetsWithLiteral("biçim"));
        $this->assertEquals(11, $this->turkish->numberOfSynSetsWithLiteral("yurt"));
        $this->assertEquals(12, $this->turkish->numberOfSynSetsWithLiteral("iğne"));
        $this->assertEquals(13, $this->turkish->numberOfSynSetsWithLiteral("kol"));
        $this->assertEquals(14, $this->turkish->numberOfSynSetsWithLiteral("alem"));
        $this->assertEquals(15, $this->turkish->numberOfSynSetsWithLiteral("taban"));
        $this->assertEquals(16, $this->turkish->numberOfSynSetsWithLiteral("yer"));
        $this->assertEquals(17, $this->turkish->numberOfSynSetsWithLiteral("ağır"));
        $this->assertEquals(18, $this->turkish->numberOfSynSetsWithLiteral("iş"));
        $this->assertEquals(19, $this->turkish->numberOfSynSetsWithLiteral("dökmek"));
        $this->assertEquals(20, $this->turkish->numberOfSynSetsWithLiteral("kaldırmak"));
        $this->assertEquals(21, $this->turkish->numberOfSynSetsWithLiteral("girmek"));
        $this->assertEquals(22, $this->turkish->numberOfSynSetsWithLiteral("gitmek"));
        $this->assertEquals(23, $this->turkish->numberOfSynSetsWithLiteral("vermek"));
        $this->assertEquals(24, $this->turkish->numberOfSynSetsWithLiteral("olmak"));
        $this->assertEquals(25, $this->turkish->numberOfSynSetsWithLiteral("bırakmak"));
        $this->assertEquals(26, $this->turkish->numberOfSynSetsWithLiteral("çıkarmak"));
        $this->assertEquals(27, $this->turkish->numberOfSynSetsWithLiteral("kesmek"));
        $this->assertEquals(28, $this->turkish->numberOfSynSetsWithLiteral("açmak"));
        $this->assertEquals(33, $this->turkish->numberOfSynSetsWithLiteral("düşmek"));
        $this->assertEquals(38, $this->turkish->numberOfSynSetsWithLiteral("atmak"));
        $this->assertEquals(39, $this->turkish->numberOfSynSetsWithLiteral("geçmek"));
        $this->assertEquals(44, $this->turkish->numberOfSynSetsWithLiteral("çekmek"));
        $this->assertEquals(50, $this->turkish->numberOfSynSetsWithLiteral("tutmak"));
        $this->assertEquals(59, $this->turkish->numberOfSynSetsWithLiteral("çıkmak"));
    }

    public function testGetSynSetsWithPartOfSpeech(){
        $this->assertCount(43882, $this->turkish->getSynSetsWithPartOfSpeech(Pos::NOUN));
        $this->assertCount(17773, $this->turkish->getSynSetsWithPartOfSpeech(Pos::VERB));
        $this->assertCount(12406, $this->turkish->getSynSetsWithPartOfSpeech(Pos::ADJECTIVE));
        $this->assertCount(2549, $this->turkish->getSynSetsWithPartOfSpeech(Pos::ADVERB));
        $this->assertCount(1552, $this->turkish->getSynSetsWithPartOfSpeech(Pos::INTERJECTION));
        $this->assertCount(74, $this->turkish->getSynSetsWithPartOfSpeech(Pos::PRONOUN));
        $this->assertCount(61, $this->turkish->getSynSetsWithPartOfSpeech(Pos::CONJUNCTION));
        $this->assertCount(30, $this->turkish->getSynSetsWithPartOfSpeech(Pos::PREPOSITION));
    }

    public function testGetInterlingual(){
        $this->assertCount(1, $this->turkish->getInterlingual("ENG31-05674544-n"));
        $this->assertCount(2, $this->turkish->getInterlingual("ENG31-00220161-r"));
        $this->assertCount(3, $this->turkish->getInterlingual("ENG31-02294200-v"));
        $this->assertCount(4, $this->turkish->getInterlingual("ENG31-06205574-n"));
        $this->assertCount(5, $this->turkish->getInterlingual("ENG31-02687605-v"));
        $this->assertCount(6, $this->turkish->getInterlingual("ENG31-01099197-n"));
        $this->assertCount(7, $this->turkish->getInterlingual("ENG31-00587299-n"));
        $this->assertCount(9, $this->turkish->getInterlingual("ENG31-02214901-v"));
        $this->assertCount(10, $this->turkish->getInterlingual("ENG31-02733337-v"));
        $this->assertCount(19, $this->turkish->getInterlingual("ENG31-00149403-v"));
    }

    public function testFindPathToRoot(){
        $this->assertCount(1, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0814560")));
        $this->assertCount(2, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0755370")));
        $this->assertCount(3, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0516010")));
        $this->assertCount(4, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0012910")));
        $this->assertCount(5, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0046370")));
        $this->assertCount(6, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0186560")));
        $this->assertCount(7, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0172740")));
        $this->assertCount(8, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0195110")));
        $this->assertCount(9, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0285060")));
        $this->assertCount(10, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0066050")));
        $this->assertCount(11, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0226380")));
        $this->assertCount(12, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0490230")));
        $this->assertCount(13, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-1198750")));
        $this->assertCount(12, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0412120")));
        $this->assertCount(13, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-1116690")));
        $this->assertCount(13, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0621870")));
        $this->assertCount(14, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0822980")));
        $this->assertCount(15, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0178450")));
        $this->assertCount(16, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0600460")));
        $this->assertCount(17, $this->turkish->findPathToRoot($this->turkish->getSynSetWithId("TUR10-0656390")));
    }

}