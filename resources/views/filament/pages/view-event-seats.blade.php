<x-filament::page>
    <table class="min-w-full bg-white border border-gray-200 text-sm">
        <thead class="bg-gray-100">
        <tr>
            <th class="border px-4 py-2 text-left">Event Name</th>
            <th class="border px-4 py-2 text-left">Rows</th>
            <th class="border px-4 py-2 text-left">Columns</th>
            <th class="border px-4 py-2 text-left">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->getEventsWithSeatData() as $event)
            <tr>
                <td class="border px-4 py-2">{{ $event['name'] }}</td>
                <td class="border px-4 py-2">{{ $event['rows'] }}</td>
                <td class="border px-4 py-2">{{ $event['cols'] }}</td>
                <td class="border px-4 py-2 flex gap-2">
                    <a href="{{ route('filament.admin.pages.edit-event-seats-grid', ['event_id' => $event['id']]) }}" class="bg-black/50 text-white px-3 py-1 rounded">Edit</a>


                    <form method="POST" action="{{ route('event-seats.delete', $event['id']) }}"
                          onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button class="bg-black/50 text-white px-3 py-1 rounded">Delete</button>
                    </form>
                </td>

            </tr>
        @endforeach
        </tbody>
    </table>
</x-filament::page>
