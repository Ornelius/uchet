@props(['src', 'id', 'height' => '600px'])

<div
    id="{{ $id }}"
    wire:ignore
    style="height: {{ $height }}; width: 100%; border: 1px solid var(--fi-color-gray-200); border-radius: 0.5rem; overflow: hidden; background: var(--fi-color-gray-50); position: relative; z-index: 0; isolation: isolate;"
></div>

<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
<script>
    (function () {
        var container = document.getElementById(@js($id));
        var src = @js($src);

        if (!container || container._leafletScheme) {
            return;
        }

        container._leafletScheme = true;

        var image = new Image();

        image.onload = function () {
            var bounds = [[0, 0], [image.naturalHeight, image.naturalWidth]];

            var map = L.map(container, {
                crs: L.CRS.Simple,
                minZoom: -4,
                maxZoom: 8,
                zoomSnap: 0,
                zoomDelta: 0.5,
                wheelPxPerZoomLevel: 40,
                zoomControl: true,
                attributionControl: false,
                scrollWheelZoom: true,
                dragging: true,
            });

            L.imageOverlay(src, bounds).addTo(map);
            map.fitBounds(bounds);
            container._leafletMap = map;
        };

        image.onerror = function () {
            container.innerHTML =
                '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--fi-color-gray-500);font-size:0.875rem;">' +
                'Не удалось загрузить схему</div>';
        };

        image.src = src;
    })();
</script>
