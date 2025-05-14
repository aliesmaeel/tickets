
<style>
    #balloon{
        width: 150px;
    }
</style>
<x-filament::page>
    <div class=" flex justify-between gap-4 items-center">
        <select id="event_id" class="block w-full rounded border-gray-300 shadow-sm">
            <option value="">-- Select an event --</option>
            @foreach ($this->events as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>

        <input type="number" id="rows" placeholder="Rows" class="block w-full rounded border-gray-300 shadow-sm " />
        <input type="number" id="cols" placeholder="Columns" class="block w-full rounded border-gray-300 shadow-sm" />

        <button onclick="generateGrid()" class="text-white px-4 py-2 rounded bg-black/50">
            Generate
        </button>

        <button onclick="saveGrid()" class="mt-4 bg-black/50 text-white px-4 py-2 rounded w-full">
            Save Seats
        </button>
    </div>

    <div id="grid-container" class="mt-8 overflow-x-auto"></div>

    <!-- Balloon popup -->
    <div id="balloon" onclick="event.stopPropagation()" class="hidden absolute z-50 bg-white border rounded shadow p-2">
        <select id="seat-class" class="block mb-2 border rounded w-full p-1">
        </select>

        <button onclick="applySelection()" class="bg-black/50 text-white px-3 py-1 rounded w-full">Submit</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showToast(message, icon = 'success') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true,
                icon: icon,
                title: message
            });
        }

    </script>


    <script>
        let selectedCells = [];

        const eventSelect = document.getElementById('event_id');
        const seatClassSelect = document.getElementById('seat-class');
        const gridContainer = document.getElementById('grid-container');
        const balloon = document.getElementById('balloon');

        eventSelect.addEventListener('change', function () {
            const eventId = this.value;
            seatClassSelect.innerHTML = ''; // Clear previous options

            if (!eventId) return;

            fetch(`/get-seat-classes/${eventId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = JSON.stringify({ id: cls.id, name: cls.name, color: cls.color });
                        option.textContent = cls.name;
                        option.style.color = cls.color;
                        seatClassSelect.appendChild(option);
                    });
                })
                .catch(console.error);
        });

        function generateGrid() {
            const rows = parseInt(document.getElementById('rows').value);
            const cols = parseInt(document.getElementById('cols').value);
            gridContainer.innerHTML = '';
            hideBalloon();
            selectedCells = [];

            // Find the stage class
            let stageClass = null;
            Array.from(seatClassSelect.options).forEach(opt => {
                const cls = JSON.parse(opt.value);
                if (cls.name.toLowerCase() === 'empty') {
                    stageClass = cls;
                }
            });

            const table = document.createElement('table');
            table.classList.add('table-auto', 'border-collapse');

            // Column selector header
            const header = document.createElement('tr');
            header.appendChild(document.createElement('th'));
            for (let col = 0; col < cols; col++) {
                const th = document.createElement('th');
                const btn = document.createElement('button');
                btn.textContent = '↓';
                btn.className = 'bg-gray-200 px-2 py-1 rounded text-xs mx-auto block';
                btn.onclick = (e) => { e.stopPropagation(); selectColumn(col); };
                th.appendChild(btn);
                header.appendChild(th);
            }
            table.appendChild(header);

            for (let row = 0; row < rows; row++) {
                const tr = document.createElement('tr');

                const th = document.createElement('th');
                const btn = document.createElement('button');
                btn.textContent = '→';
                btn.className = 'bg-gray-200 px-2 py-1 rounded text-xs mx-auto block';
                btn.onclick = (e) => { e.stopPropagation(); selectRow(row); };
                th.appendChild(btn);
                tr.appendChild(th);

                for (let col = 0; col < cols; col++) {
                    const td = document.createElement('td');
                    td.className = 'border w-16 h-12 text-center text-sm cursor-pointer';
                    td.dataset.row = row;
                    td.dataset.col = col;

                    // Fill with Stage data if available
                    if (stageClass) {
                        td.textContent = 'empty';
                        td.style.backgroundColor = stageClass.color;
                        td.style.color = '#000000';
                        td.dataset.seatClass = JSON.stringify({
                            id: stageClass.id,
                            name: stageClass.name,
                            color: stageClass.color
                        });
                    }

                    td.onclick = (e) => {
                        e.stopPropagation();
                        clearSelection();
                        td.classList.add('ring', 'ring-blue-500');
                        selectedCells = [td];
                        showBalloon(e.pageX, e.pageY);
                    };

                    tr.appendChild(td);
                }

                table.appendChild(tr);
            }

            gridContainer.appendChild(table);
        }


        function clearSelection() {
            document.querySelectorAll('td').forEach(cell =>
                cell.classList.remove('ring', 'ring-blue-500', 'ring-yellow-400')
            );
            selectedCells = [];
        }

        function selectRow(rowIndex) {
            clearSelection();
            selectedCells = Array.from(document.querySelectorAll(`[data-row="${rowIndex}"]`));
            selectedCells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            const { x, y } = selectedCells[0].getBoundingClientRect();
            showBalloon(x + window.scrollX, y + window.scrollY);
        }

        function selectColumn(colIndex) {
            clearSelection();
            selectedCells = Array.from(document.querySelectorAll(`[data-col="${colIndex}"]`));
            selectedCells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            const { x, y } = selectedCells[0].getBoundingClientRect();
            showBalloon(x + window.scrollX, y + window.scrollY);
        }

        function showBalloon(x, y) {
            balloon.style.left = `${x + 10}px`;
            balloon.style.top = `${y + 10}px`;
            balloon.classList.remove('hidden');
        }

        function hideBalloon() {
            balloon.classList.add('hidden');
        }

        function applySelection() {
            const selected = seatClassSelect.value;
            if (!selected) return;

            const { id, name, color } = JSON.parse(selected);

            selectedCells.forEach(cell => {
                cell.textContent = name;
                cell.style.backgroundColor = color;

                cell.dataset.seatClass = JSON.stringify({ id, name, color });
            });
            hideBalloon();
            clearSelection();
        }

        function saveGrid() {
            const eventId = eventSelect.value;
            const rows = parseInt(document.getElementById('rows').value);
            const cols = parseInt(document.getElementById('cols').value);

            if (!eventId) return alert('Please select an event.');

            const seats = [];
            document.querySelectorAll('#grid-container td').forEach(cell => {
                if (cell.dataset.seatClass) {
                    const seatClass = JSON.parse(cell.dataset.seatClass);
                    seats.push({
                        row: parseInt(cell.dataset.row),
                        col: parseInt(cell.dataset.col),
                        seat_class_id: seatClass.id,
                        seat_class_name: seatClass.name,
                    });
                }
            });

            fetch('/store-event-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_id: eventId,
                    data: { rows, cols, seats }
                })
            })
                .then(() => {
                    showToast('Seat layout saved successfully!');
                    window.setTimeout(() => {
                        window.location.href = '/admin/view-event-seats';
                    }, 1000);
                })
                .catch(err => {
                    console.error('Error saving:', err);
                    alert('Failed to save seat layout.');
                });

        }

        // Close balloon when clicking outside
        document.addEventListener('click', (e) => {
            if (!balloon.contains(e.target)) {
                hideBalloon();
                clearSelection();
            }
        });
    </script>



</x-filament::page>
