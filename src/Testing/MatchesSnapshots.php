<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Testing;

use PHPUnit\Framework\ExpectationFailedException;
use Spatie\Snapshots\Filename;
use Spatie\Snapshots\MatchesSnapshots as SpatieMatchesSnapshots;
use Ysato\Catalyst\Testing\Drivers\FileTreeDriver;

trait MatchesSnapshots
{
    use SpatieMatchesSnapshots;

    protected function assertMatchesSnapshot(string $actualPath, DriverInterface|null $driver = null): void
    {
        if ($driver === null) {
            $driver = new FileTreeDriver();
        }

        $this->doSnapshotAssertion($actualPath, $driver);
    }

    protected function doSnapshotAssertion(string $actualPath, DriverInterface $driver): void
    {
        $this->snapshotIncrementor++;

        $snapshot = new Snapshot($this->getSnapshotId(), $this->getSnapshotPath(), $driver);

        if (! $snapshot->exists()) {
            $this->assertSnapshotShouldBeCreated($this->getSnapshotId());

            $this->createSnapshotAndMarkTestIncomplete($snapshot, $actualPath);
        }

        if ($this->shouldUpdateSnapshots()) {
            try {
                $snapshot->create($actualPath);
            } catch (ExpectationFailedException $e) {
                $this->updateSnapshotAndMarkTestIncomplete($snapshot, $actualPath);
            }

            return;
        }

        try {
            $snapshot->assertMatches($actualPath);
        } catch (ExpectationFailedException $e) {
            $this->rethrowExpectationFailedExceptionWithUpdateSnapshotsPrompt($e);
        }
    }

    protected function createSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actualPath): void
    {
        $snapshot->create($actualPath);

        $this->registerSnapshotChange("Snapshot created for {$snapshot->id()}");
    }

    protected function updateSnapshotAndMarkTestIncomplete(Snapshot $snapshot, string $actualPath): void
    {
        $snapshot->create($actualPath);

        $this->registerSnapshotChange("Snapshot updated for {$snapshot->id()}");
    }

    private function getSnapshotPath(): string
    {
        $snapshotDir = $this->getSnapshotDirectory();

        return $snapshotDir . '/' . Filename::cleanFilename($this->getSnapshotId());
    }
}
