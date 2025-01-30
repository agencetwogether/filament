<?php

namespace Filament\Support\View\Components;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\Contracts\HasColor;
use Filament\Support\View\Components\Contracts\HasDefaultGrayColor;

class Link implements HasColor, HasDefaultGrayColor
{
    /**
     * @param  array<int, string>  $color
     * @return array<string>
     */
    public function getColorClasses(array $color): array
    {
        $gray = FilamentColor::getColor('gray');

        ksort($color);

        $darkestLightGrayBg = $gray[50];

        foreach ($color as $shade => $shadeValue) {
            if (Color::isTextContrastRatioAccessible($darkestLightGrayBg, $shadeValue)) {
                $text = $shade;

                break;
            }
        }

        $text ??= 900;

        krsort($color);

        $lightestDarkGrayBg = $gray[700];

        foreach ($color as $shade => $shadeValue) {
            if ($shade > 400) {
                continue;
            }

            if (Color::isTextContrastRatioAccessible($lightestDarkGrayBg, $shadeValue)) {
                $darkText = $shade;

                break;
            }
        }

        $darkText ??= 200;

        return [
            "fi-text-color-{$text}",
            "dark:fi-text-color-{$darkText}",
        ];
    }
}
