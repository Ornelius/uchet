<?php

namespace App\Filament\Resources\Facilities\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class FacilitySchemeMap extends Widget
{
    public ?Model $record = null;

    protected string $view = 'filament.resources.facilities.widgets.facility-scheme-map';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;
}