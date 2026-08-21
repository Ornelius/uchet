<x-filament::section
    icon="heroicon-o-map"
    heading="Схема котельной"
    description="Масштабирование — колёсико мыши, перемещение — перетаскивание"
>
    @if (blank($record?->scheme))
        <div class="flex h-64 items-center justify-center text-sm text-gray-500">
            Схема не загружена. Загрузите файл JPG в форме редактирования объекта.
        </div>
    @else
        <x-facility-scheme
            :src="$record->scheme_url"
            :id="'facility-scheme-'.$record->id"
            height="55vh"
        />
    @endif
</x-filament::section>