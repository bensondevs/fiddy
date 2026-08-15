@props([
    'title' => null,
    'description' => null,
    'hint' => null,
    'aboveTitle' => null,
    'aboveDescription' => null,
    'tooltip' => null,
    'prefixImage' => null,
    'suffixImage' => null,
    'circularImage' => false,
    'prefixIconHtml' => null,
    'suffixIconHtml' => null,
    'titlePrefixIconHtml' => null,
    'titleSuffixIconHtml' => null,
    'descriptionPrefixIconHtml' => null,
    'descriptionSuffixIconHtml' => null,
    'hintPrefixIconHtml' => null,
    'hintSuffixIconHtml' => null,
    'aboveTitlePrefixIconHtml' => null,
    'aboveTitleSuffixIconHtml' => null,
    'aboveDescriptionPrefixIconHtml' => null,
    'aboveDescriptionSuffixIconHtml' => null,
    'wrapperClass',
    'mediaClass',
    'mediaSize',
    'mediaStyle' => '',
])

<div
    class="{{ $wrapperClass }}"
    @if (filled($tooltip)) title="{{ $tooltip }}" @endif
>
    @if (filled($prefixImage))
        <img
            src="{{ $prefixImage }}"
            alt="{{ $title }}"
            class="{{ $mediaClass }} mr-1 shrink-0"
            @if (filled($mediaStyle)) style="{{ $mediaStyle }}" @endif
        />
    @elseif ($prefixIconHtml)
        <span class="{{ $mediaSize }} mr-1 flex shrink-0 items-center justify-center">
            {{ $prefixIconHtml }}
        </span>
    @endif

    <div class="flex min-w-0 flex-1 items-center gap-2">
        <div class="flex min-w-0 flex-1 flex-col">
            @if (filled($aboveTitle))
                <div class="flex min-w-0 items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($aboveTitlePrefixIconHtml)
                        <span class="mr-1 inline-flex shrink-0 items-center justify-center">
                            {{ $aboveTitlePrefixIconHtml }}
                        </span>
                    @endif

                    <span class="truncate">{{ $aboveTitle }}</span>

                    @if ($aboveTitleSuffixIconHtml)
                        <span class="ml-1 inline-flex shrink-0 items-center justify-center">
                            {{ $aboveTitleSuffixIconHtml }}
                        </span>
                    @endif
                </div>
            @endif

            @if (filled($aboveDescription))
                <div class="flex min-w-0 items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                    @if ($aboveDescriptionPrefixIconHtml)
                        <span class="mr-1 inline-flex shrink-0 items-center justify-center">
                            {{ $aboveDescriptionPrefixIconHtml }}
                        </span>
                    @endif

                    <span class="truncate">{{ $aboveDescription }}</span>

                    @if ($aboveDescriptionSuffixIconHtml)
                        <span class="ml-1 inline-flex shrink-0 items-center justify-center">
                            {{ $aboveDescriptionSuffixIconHtml }}
                        </span>
                    @endif
                </div>
            @endif

            @if (filled($title))
                <div class="flex min-w-0 items-center gap-1 text-sm font-medium text-gray-950 dark:text-white">
                    @if ($titlePrefixIconHtml)
                        <span class="mr-1 inline-flex shrink-0 items-center justify-center">
                            {{ $titlePrefixIconHtml }}
                        </span>
                    @endif

                    <span class="truncate">{{ $title }}</span>

                    @if ($titleSuffixIconHtml)
                        <span class="ml-1 inline-flex shrink-0 items-center justify-center">
                            {{ $titleSuffixIconHtml }}
                        </span>
                    @endif
                </div>
            @endif

            @if (filled($description))
                <div class="flex min-w-0 items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($descriptionPrefixIconHtml)
                        <span class="mr-1 inline-flex shrink-0 items-center justify-center">
                            {{ $descriptionPrefixIconHtml }}
                        </span>
                    @endif

                    <span class="truncate">{{ $description }}</span>

                    @if ($descriptionSuffixIconHtml)
                        <span class="ml-1 inline-flex shrink-0 items-center justify-center">
                            {{ $descriptionSuffixIconHtml }}
                        </span>
                    @endif
                </div>
            @endif

            @if (filled($hint))
                <div class="flex min-w-0 items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                    @if ($hintPrefixIconHtml)
                        <span class="mr-1 inline-flex shrink-0 items-center justify-center">
                            {{ $hintPrefixIconHtml }}
                        </span>
                    @endif

                    <span class="truncate">{{ $hint }}</span>

                    @if ($hintSuffixIconHtml)
                        <span class="ml-1 inline-flex shrink-0 items-center justify-center">
                            {{ $hintSuffixIconHtml }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if ($suffixIconHtml)
            <span class="ml-1 flex shrink-0 items-center justify-center">
                {{ $suffixIconHtml }}
            </span>
        @endif
    </div>

    @if (filled($suffixImage))
        <img
            src="{{ $suffixImage }}"
            alt="{{ $title }}"
            class="{{ $mediaClass }} ml-1 shrink-0"
            @if (filled($mediaStyle)) style="{{ $mediaStyle }}" @endif
        />
    @endif
</div>
