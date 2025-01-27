<?php

namespace Filament\Support\View\Components;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\View\Components\Contracts\DoesNotHaveGrayColor;
use Filament\Support\View\Components\Contracts\HasColor;

class Button implements HasColor, DoesNotHaveGrayColor
{
    /**
     * @return array<string, string>
     */
    public function getCustomColorCssVariables(string $color): array
    {
        $palette = Color::generatePalette($color);
        $palette['hover:bg:light'] = $this->changeColorLightness($color, 0.1);
        $palette['bg'] = $color;
        $palette['hover:bg:dark'] = $this->changeColorLightness($color, -0.1);
        $palette = array_replace(
            $palette,
            Color::findMatchingAccessibleTextColorsForBackgroundColors($palette),
            Color::findMatchingAccessibleTextColorsForGrayBackgroundColors($palette),
        );

        $textLightnessIndex = [
            'hover:bg:light' => FilamentColor::isLight($palette['hover:bg:light']),
            'bg' => FilamentColor::isLight($palette['bg']),
            'hover:bg:dark' => FilamentColor::isLight($palette['hover:bg:dark']),
        ];

        $isHoverColorLighter = ($textLightnessIndex['bg'] === $textLightnessIndex['hover:bg:light'])
            || ($textLightnessIndex['bg'] !== $textLightnessIndex['hover:bg:dark']);

        return [
            ...$this->getCustomShadeCssVariablesFromPalette($palette, 'button', [400, 500, 600, 'gray-100-text', 'gray-700-text']),
            'bg' => $color,
            'hover-bg' => $hoverColor = $isHoverColorLighter ? $palette['hover:bg:light'] : $palette['hover:bg:dark'],
            'dark-bg' => $color,
            'dark-hover-bg' => $hoverColor,
            'text' => $textColor = Color::resolveShadeFromPalette($palette, 'bg-text'),
            'hover-text' => $hoverTextColor = Color::resolveShadeFromPalette($palette, $isHoverColorLighter ? 'hover:bg:light-text' : 'hover:bg:dark-text'),
            'dark-text' => $textColor,
            'dark-hover-text' => $hoverTextColor,
        ];
    }

    /**
     * @param array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} $color
     * @return array<string>
     */
    public function getColorClasses(array $color): array
    {
        $bg = $color['button:bg'] ?? null;
        $hoverBg = $color['button:hover:bg'] ?? null;
        $darkBg = $color['button:dark:bg'] ?? null;
        $darkHoverBg = $color['button:dark:hover:bg'] ?? null;

        if ($bg && $hoverBg && $darkBg && $darkHoverBg) {
            return [
                "fi-bg-color-{$bg}",
                "hover:fi-bg-color-{$hoverBg}",
                "dark:fi-bg-color-{$darkBg}",
                "dark:hover:fi-bg-color-{$darkHoverBg}",
            ];
        }

        $color = FilamentColor::normalizeColor($color);
        $textLightnessIndex = $this->generateTextLightnessIndexForColor($color);

        if (blank($bg)) {
            if ($textLightnessIndex[600] && $textLightnessIndex[500]) {
                $bg = 600;
                $matchingHoverBg = 500;
            } else {
                $bg = 400;
                $matchingHoverBg = ($textLightnessIndex[400] === $textLightnessIndex[300])
                    ? 300
                    : 500;
            }
        }

        if (blank($hoverBg)) {
            $hoverBg ??= $matchingHoverBg ?? match (true) {
                $textLightnessIndex[$bg] === $textLightnessIndex[$bg - 100] => $bg - 100,
                $textLightnessIndex[$bg] === $textLightnessIndex[$bg + 100] => $bg + 100,
                default => $bg - 100,
            };
        }

        if (blank($darkBg)) {
            $darkBg = 600;
            $matchingDarkHoverBg = ($textLightnessIndex[600] === $textLightnessIndex[500])
                ? 500
                : 700;
        }

        if (blank($darkHoverBg)) {
            $darkHoverBg ??= $matchingDarkHoverBg ?? match (true) {
                $textLightnessIndex[$darkBg] === $textLightnessIndex[$darkBg - 100] => $darkBg - 100,
                $textLightnessIndex[$darkBg] === $textLightnessIndex[$darkBg + 100] => $darkBg + 100,
                default => $darkBg - 100,
            };
        }

        return [
            "fi-bg-color-{$bg}",
            "hover:fi-bg-color-{$hoverBg}",
            "dark:fi-bg-color-{$darkBg}",
            "dark:hover:fi-bg-color-{$darkHoverBg}",
        ];
    }

    protected function getCustomShadeCssVariablesFromPalette(array $palette, string $alias, array $defaultShades): array
    {
        $shades = $defaultShades;

        if (($overridingShades = FilamentColor::getOverridingShades($alias)) !== null) {
            $shades = $overridingShades;
        } else {
            if ($addedShades = FilamentColor::getAddedShades($alias)) {
                $shades = [...$shades, ...$addedShades];
            }

            if ($removedShades = FilamentColor::getRemovedShades($alias)) {
                $shades = array_diff($shades, $removedShades);
            }
        }

        return collect($palette)
            ->only($shades)
            ->mapWithKeys(fn ($value, $key) => ["color-{$key}" => is_string($value) ? $value : Color::resolveShadeFromPalette($palette, $value)])
            ->all();
    }

    protected function generateTextLightnessIndexForColor(array $color): array
    {
        $textLightnessIndex = [];

        foreach (array_keys($color) as $shade) {
            if (! is_numeric($shade)) {
                continue;
            }

            $textShade = $color["{$shade}-text"];

            if ($textShade === 0) { // White
                $textLightnessIndex[$shade] = true;

                continue;
            }

            $textLightnessIndex[$shade] = FilamentColor::isLight($color[$textShade]);
        }

        return $textLightnessIndex;
    }

    protected function changeColorLightness(string $color, float $amount): string
    {
        [$lightness, $chroma, $hue] = sscanf($color, 'oklch(%f %f %f)');

        $lightness += $amount;

        return sprintf('oklch(%f %f %f)', $lightness, $chroma, $hue);
    }
}
