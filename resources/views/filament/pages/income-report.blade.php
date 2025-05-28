<x-filament::page>
    <div class="mb-6 p-4 bg-white rounded-lg shadow">
        <h2 class="text-xl font-semibold text-gray-800">Total Income: <span class="text-green-600">{{ number_format($totalIncome, 2) }} USD</span></h2>
    </div>

    {{ $this->table }}
</x-filament::page>
