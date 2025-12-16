<?php

namespace App\Exports;

use App\Models\Booking\VehicleBookingModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleBookingsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private readonly Carbon $from,
        private readonly Carbon $to,
    ) {}

    public function collection(): Collection
    {
        return VehicleBookingModel::query()
            ->with(['vehicle.type', 'driver', 'createdBy'])
            ->whereBetween('start_at', [$this->from, $this->to])
            ->orderByDesc('start_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Booking Code',
            'Start At',
            'End At',
            'Vehicle',
            'Plate',
            'Type',
            'Driver',
            'Created By',
            'Status',
            'Purpose',
        ];
    }

    /**
     * @param  VehicleBookingModel  $row
     */
    public function map($row): array
    {
        return [
            $row->booking_code,
            optional($row->start_at)->format('Y-m-d H:i'),
            optional($row->end_at)->format('Y-m-d H:i'),
            $row->vehicle?->name,
            $row->vehicle?->plate_number,
            $row->vehicle?->type?->name,
            $row->driver?->name,
            $row->createdBy?->name,
            $row->status,
            $row->purpose,
        ];
    }
}


