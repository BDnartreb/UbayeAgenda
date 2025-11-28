<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use IntlDateFormatter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('fr_date', [$this, 'formatFrenchDate']),
        ];
    }

    public function formatFrenchDate(\DateTimeInterface $date): string
    {
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
            $date->getTimezone()
        );

        return $formatter->format($date);
    }
}
