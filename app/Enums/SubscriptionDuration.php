<?php

namespace App\Enums;

enum SubscriptionDuration: int
{
    case ONE_MONTH   = 1;
    case TWO_MONTHS  = 2;
    case THREE_MONTHS = 3;
    case FOUR_MONTHS  = 4;
    case FIVE_MONTHS  = 5;
    case SIX_MONTHS   = 6;
    case TWELVE_MONTHS = 12;

    public function labelEn(): string
    {
        return match ($this) {
            self::ONE_MONTH    => 'Monthly',
            self::TWO_MONTHS   => 'Every 2 Months',
            self::THREE_MONTHS => 'Quarterly',
            self::FOUR_MONTHS  => 'Every 4 Months',
            self::FIVE_MONTHS  => 'Every 5 Months',
            self::SIX_MONTHS   => 'Half-Yearly',
            self::TWELVE_MONTHS => 'Yearly',
        };
    }

    public function labelId(): string
    {
        return match ($this) {
            self::ONE_MONTH    => 'Bulanan',
            self::TWO_MONTHS   => '2 Bulanan',
            self::THREE_MONTHS => 'Triwulanan',
            self::FOUR_MONTHS  => '4 Bulanan',
            self::FIVE_MONTHS  => '5 Bulanan',
            self::SIX_MONTHS   => 'Semesteran',
            self::TWELVE_MONTHS => 'Tahunan',
        };
    }

    public function months(): int
    {
        return $this->value;
    }

    public function label(): string
    {
        return match (app()->getLocale()) {
            'id' => $this->labelId(),
            default => $this->labelEn(),
        };
    }
}
