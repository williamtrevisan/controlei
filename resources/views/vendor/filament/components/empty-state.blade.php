@php
    use Filament\Support\Enums\IconSize;
    use Filament\Support\View\Components\SectionComponent\IconComponent;
@endphp

@props([
    'description' => null,
    'footer' => null,
    'heading',
    'headingTag' => 'h2',
    'icon' => null,
    'iconColor' => 'primary',
    'iconSize' => null,
])

@php
    if (filled($iconSize) && (! $iconSize instanceof IconSize)) {
        $iconSize = IconSize::tryFrom($iconSize) ?? $iconSize;
    }

    $hasDescription = filled((string) $description);
    $hasIcon = filled($icon);
@endphp

<section
    {{
        $attributes->class([
            'fi-empty-state',
        ])
    }}
>
    <div class="fi-empty-state-content">
        @if ($hasIcon)
            <div
                @class([
                    'fi-empty-state-icon-bg',
                    'fi-color ' . ('fi-color-' . $iconColor) => $iconColor !== 'gray',
                ])
            >
                {{
                    \Filament\Support\generate_icon_html($icon, attributes: (new \Illuminate\View\ComponentAttributeBag)
                        ->color(IconComponent::class, $iconColor), size: $iconSize ?? IconSize::Large)
                }}
            </div>
        @endif

        <{{ $headingTag }} class="fi-empty-state-heading">
            {{ $heading }}
        </{{ $headingTag }}>

        @if ($hasDescription)
            <p class="fi-empty-state-description">
                {{ $description }}
            </p>
        @endif

        <footer class="fi-empty-state-footer">
            {{ $footer }}
        </footer>
    </div>
</section>
