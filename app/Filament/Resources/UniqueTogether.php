<?php

namespace App\Filament\Resources;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueTogether implements Rule
{
    protected string $table;
    protected string $column1;
    protected string $column2;
    protected mixed $value2;
    protected ?int $ignoreId;

    public function __construct(
        string $table,
        string $column1,
        string $column2,
        mixed $value2,
        ?int $ignoreId = null
    ) {
        $this->table = $table;
        $this->column1 = $column1;
        $this->column2 = $column2;
        $this->value2 = $value2;
        $this->ignoreId = $ignoreId;
    }

    public function passes($attribute, $value): bool
    {
        $query = DB::table($this->table)
            ->where($this->column1, $value)
            ->where($this->column2, $this->value2);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        return $query->doesntExist();
    }

    public function message(): string
    {
        return 'The combination of name and event already exists.';
    }
}
