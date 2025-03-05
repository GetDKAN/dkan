<?php

namespace Drupal\harvest;

class ResultInterpreter
{
    private array $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function countCreated(): int
    {
        return $this->loadCount("NEW");
    }

    public function countUpdated(): int
    {
        return $this->loadCount("UPDATED");
    }

    public function countFailed(): int
    {
        $load_failures = $this->loadCount("FAILURE");
        $transform_failures = $this->transformFailures();
        return $load_failures + $transform_failures;
    }

    public function countProcessed(): int
    {

        $ids = [];

        if (isset($this->result['status']['load'])) {
            $ids = array_merge($ids, array_keys($this->result['status']['load']));
        }

        if (isset($this->result['status']['transform'])) {
            foreach (array_keys($this->result['status']['transform']) as $transformer) {
                $ids = [...$ids, ...array_keys($this->result['status']['transform'][$transformer])];
            }
        }

        $ids = array_unique($ids);

        return count($ids);
    }

    private function loadCount(string $status): int
    {
        $count = 0;
        if (!isset($this->result['status']['load'])) {
            return $count;
        }

        foreach ($this->result['status']['load'] as $stat) {
            if ($stat == $status) {
                $count++;
            }
        }

        return $count;
    }

    private function transformFailures(): int
    {
        $count = 0;

        if (!isset($this->result['status']['transform'])) {
            return $count;
        }

        foreach ($this->result['status']['transform'] as $results) {
            foreach ($results as $result) {
                if ($result == "FAILURE") {
                    $count++;
                }
            }
        }

        return $count;
    }
}
