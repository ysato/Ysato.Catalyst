<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MiscTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function test_str_replace()
    {
        $expected = <<<'EOF'
/node_modules
/.fleet
/.idea
/.nova

EOF;

        $idea = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles

EOF;

        $contents = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles
/node_modules
/.fleet
/.idea
/.nova

EOF;

        $actual = str_replace($idea, '', $contents);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function 短い文言で検索すると意図しない場所を書き換えてしまう()
    {
        $expected = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles
/node_modules
/.fleet
/.idea
/.nova

EOF;

        $search = "es\n";

        $contents = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles
/node_modules
/.fleet
/.idea
/.nova
es

EOF;

        $actual = str_replace($search, '', $contents);

        $this->assertNotEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function 行頭、行末を指定し意図した場所のみを置き換える()
    {
        $expected = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles
/node_modules
/.fleet
/.idea
/.nova

EOF;

        # ^は行頭、\Rは全ての種類の改行文字、$で行末を表す
        # mはマルチライン修飾子
        # https://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
        $search = '/^es\R$/m';

        $contents = <<<'EOF'
/.idea/*
!/.idea/codeStyles
!/.idea/fileTemplates
!/.idea/inspectionProfiles
/node_modules
/.fleet
/.idea
/.nova
es

EOF;

        $actual = preg_replace($search, '', $contents);

        $this->assertEquals($expected, $actual);
    }
}
