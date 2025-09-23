<?php

namespace App\ValueObjects;

use Illuminate\Support\Carbon;

class StatementPeriod
{
    public function __construct(protected string $value = '')
    {
    }

    public function current(): self
    {
        $year = now()->year;
        $month = now()->addMonth()->month;

        return new self(sprintf('%04d-%02d', $year, $month));
    }

    public function previous(): self
    {
        $year = $this->year();
        $month = $this->month();

        while (--$month === 0) {
            $month += 12;

            $year--;
        }

        return new self(sprintf('%04d-%02d', $year, $month));
    }

    public function next(): self
    {
        $year = $this->year();
        $month = $this->month();

        while (++$month > 12) {
            $month -= 12;

            $year++;
        }

        return new self(sprintf('%04d-%02d', $year, $month));
    }

    public function rewind(int $months): self
    {
        $year = $this->year();

        $month = $this->month();
        $month -= $months;

        while ($month <= 0) {
            $month += 12;

            $year--;
        }

        return new self(sprintf('%04d-%02d', $year, $month));
    }

    public function advance(int $months): self
    {
        $year = $this->year();

        $month = $this->month();
        $month += $months;

        while ($month > 12) {
            $month -= 12;

            $year++;
        }

        return new self(sprintf('%04d-%02d', $year, $month));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function year(): int
    {
        return str($this->value)
            ->explode('-')
            ->first();
    }

    public function month(): int
    {
        return str($this->value)
            ->explode('-')
            ->last();
    }

    public function __toString(): string
    {
        return Carbon::createFromFormat('Y-m', $this->value)
            ->translatedFormat('M/Y');
    }
}
