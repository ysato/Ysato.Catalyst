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

        $actual = str_replace(["$idea"], [''], $contents);

        $this->assertEquals($expected, $actual);
    }
}
