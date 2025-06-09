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
    public function 行頭と行末を指定し、前後にスラッシュがある行もない行も置き換える()
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
        # スラッシュの有無で意味が変わるためどのパターンにマッチさせるかは注意が必要
        # dir	すべての階層	ファイルとディレクトリ	プロジェクト内のどこにあっても dir を無視します。
        # /dir	ルート直下	ファイルとディレクトリ	ルート直下の dir だけを無視します。
        # /dir/	ルート直下	ディレクトリのみ	ルート直下の dir というディレクトリだけを無視します。
        #
        # ^は行頭、\Rは全ての種類の改行文字、$で行末を表す
        # mはマルチライン修飾子
        # https://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
        $search = '#^/?es/?$\R?#m';

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
/es
/es/

EOF;

        $actual = preg_replace($search, '', $contents);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function スラッシュの有無とその後の文字列の有無を包含する正規表現になっているか()
    {
        $expected = <<<'EOF'
.php_cs.cache
/build

EOF;
        $search = '#^!?/certs(/?|/.*)$\R?#m';

        $contents = <<<'EOF'
.php_cs.cache
/build
/certs
/certs/*
/certs/*.pem
/certs/certificate.pem
!/certs/.gitkeep

EOF;

        $actual = preg_replace($search, '', $contents);

        $this->assertEquals($expected, $actual);
    }
}
