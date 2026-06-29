<?php

namespace App\Repositories;

use App\Models\CustomerComplaint;

class ComplaintRepository extends BaseRepository
{
    public function __construct(CustomerComplaint $model)
    {
        parent::__construct($model);
    }

    public function nextComplaintNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "REC-{$year}-";

        /** @var string|null $last */
        $last = $this->model->newQuery()
            ->where('complaint_number', 'like', "{$prefix}%")
            ->orderByDesc('complaint_number')
            ->value('complaint_number');

        $sequence = $last !== null ? ((int) substr($last, -6)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
