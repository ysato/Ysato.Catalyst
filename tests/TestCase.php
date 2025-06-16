<?php

declare(strict_types=1);

namespace Tests;

use Ysato\Catalyst\Testing\MatchesSnapshots;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use MatchesSnapshots {
        assertMatchesFileHashSnapshot as private;
        assertMatchesFileSnapshot as private;
        assertMatchesHtmlSnapshot as private;
        assertMatchesJsonSnapshot as private;
        assertMatchesObjectSnapshot as private;
        assertMatchesTextSnapshot as private;
        assertMatchesXmlSnapshot as private;
        assertMatchesYamlSnapshot as private;
        assertMatchesImageSnapshot as private;
        getFileSnapshotDirectory as private;
        doFileSnapshotAssertion as private;
    }
}
