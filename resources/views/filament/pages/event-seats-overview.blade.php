<style>
    .btn-info {
        background-color: #2196F3; /* Blue */
        border: none;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
    }
    .btn-danger {
        background-color: #f44336; /* Red */
        border: none;
        color: white;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
    }
</style>
<x-filament::page>
    <table class="w-full border">
        <thead class="bg-gray-200">
        <tr>
            <th class="border px-4 py-2 text-left">Event</th>
            <th class="border px-4 py-2 text-left">Rows</th>
            <th class="border px-4 py-2 text-left">Columns</th>
            <th class="border px-4 py-2 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($this->seatData as $seat)
            <tr>
                <td class="border px-4 py-2">{{ $seat['name'] }}</td>
                <td class="border px-4 py-2">{{ $seat['rows'] }}</td>
                <td class="border px-4 py-2">{{ $seat['cols'] }}</td>
                <td class="border px-4 py-2">
                    <button
                       wire:click.prevent="editSeat({{ $seat['id'] }})"
                       class="btn-info mr-2"
                    >Edit</button>

                    <button
                        wire:click="deleteSeat({{ $seat['id'] }})"
                        class="btn-danger"
                        onclick="confirm('Are you sure you want to delete this seat data?') || event.stopImmediatePropagation()"
                    >Delete</button>
                </td>



            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-gray-500">No seat data available.</td>
            </tr>
        @endforelse

        </tbody>
    </table>
</x-filament::page>
