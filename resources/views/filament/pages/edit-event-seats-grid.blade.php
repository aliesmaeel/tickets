<style>
    #balloon {
        width: 150px;
    }
    #grid-container {
        min-height: 200px;
        border: 1px dashed #ccc;
        padding: 1rem;
    }
    .bg-orange {
        background-color: #FFA500;
        color: white;
    }
    .btn-success {
        background-color: #28a745;
        color: white;
    }
    .add{
        background-color: #007bff;
        color: white;
    }
    .delete{
        background-color: #dc3545;
        color: white;
    }
    .Reserved{
        opacity: 0.3;
    }
</style>

<x-filament::page>
    <div class="flex flex-wrap items-center gap-4">
        <input type="hidden" id="event_id" value="{{ $event_id }}" />

        <input type="number" id="rows" placeholder="Rows" class="w-24 rounded border-gray-300 shadow-sm" />
        <input type="number" id="cols" placeholder="Columns" class="w-24 rounded border-gray-300 shadow-sm" />

        <button onclick="generateGridFromInputs()" class="bg-black/50 text-white px-4 py-2 rounded">
            Generate
        </button>

        <button onclick="saveGrid()" class="btn-success text-white px-4 py-2 rounded">
            Save Seats
        </button>
    </div>
    <div class="flex flex-row gap-4">
        <button onclick="addRow()" class="add text-white px-3 py-1 rounded">
            Add Row
        </button>
        <button onclick="deleteLastRow()" class="delete text-white px-3 py-1 rounded">
            Delete Last Row
        </button>
        <button onclick="addColumn()" class="add text-white px-3 py-1 rounded">
            Add Column
        </button>
        <button onclick="deleteLastColumn()" class="delete text-white px-3 py-1 rounded">
            Delete Last Column
        </button>
    </div>

    <div id="grid-container" class="mt-8 overflow-x-auto"></div>

    <!-- Balloon popup -->
    <div id="balloon" onclick="event.stopPropagation()" class="hidden absolute z-50 bg-white border rounded shadow p-2 flex gap-4 flex-col">
        <select id="seat-class" class="block mb-2 border rounded w-full p-1"></select>
        <button onclick="applySelection()" class="bg-black/50 text-white px-3 py-1 rounded w-full">Submit</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showToast(message, icon = 'success', title = null, timer = 1000) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: timer,
                timerProgressBar: true,
                icon: icon,
                title: message
            });
        }

    </script>

    <script>
        let currentSeats = [];

        let selectedCells = [];
        const eventId = document.getElementById('event_id').value;
        const gridContainer = document.getElementById('grid-container');
        const seatClassSelect = document.getElementById('seat-class');
        const balloon = document.getElementById('balloon');
        let stageClass = null;

        window.addEventListener('DOMContentLoaded', async () => {
            if (!eventId) return;

            try {
                const [seatClassesRes, seatDataRes] = await Promise.all([
                    fetch(`/get-seat-classes/${eventId}`),
                    fetch(`/get-event-seats/${eventId}`)
                ]);

                const seatClasses = await seatClassesRes.json();
                const seatData = await seatDataRes.json();
                // Populate dropdown
                seatClassSelect.innerHTML = '';
                seatClasses.forEach(cls => {
                    const option = document.createElement('option');
                    option.value = JSON.stringify(cls);
                    option.textContent = cls.name;
                    option.style.color = cls.color;
                    seatClassSelect.appendChild(option);
                });

                stageClass = seatClasses.find(cls => cls.name.toLowerCase() === 'empty');

                document.getElementById('rows').value = seatData.rows;
                document.getElementById('cols').value = seatData.cols;

                generateInitialGrid(seatData);

            } catch (err) {
                console.error('Failed to load data:', err);
            }
        });

        function generateInitialGrid(seatData) {
            const rows = parseInt(seatData.rows);
            const cols = parseInt(seatData.cols);
            generateGrid(rows, cols, seatData.seats);
        }

        function generateGridFromInputs() {
            const rows = parseInt(document.getElementById('rows').value);
            const cols = parseInt(document.getElementById('cols').value);
            generateGrid(rows, cols);
        }

        function generateGrid(rows, cols, seats = []) {
            currentSeats = seats; // <-- Add this line

            gridContainer.innerHTML = '';
            hideBalloon();
            selectedCells = [];

            const table = document.createElement('table');
            table.className = 'table-auto border-collapse';

            // Header row
            const header = document.createElement('tr');
            header.appendChild(document.createElement('th'));

            for (let col = 0; col < cols; col++) {
                const th = document.createElement('th');
                const btn = document.createElement('button');
                btn.textContent = '↓';
                btn.className = 'bg-gray-200 px-2 py-1 rounded text-xs mx-auto block';
                btn.onclick = (e) => {
                    e.stopPropagation();
                    selectColumn(col);
                };
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
                btn.onclick = (e) => {
                    e.stopPropagation();

                    selectRow(row);
                };
                th.appendChild(btn);
                tr.appendChild(th);

                for (let col = 0; col < cols; col++) {
                    const td = document.createElement('td');
                    td.className = 'border w-16 h-12 text-center text-sm cursor-pointer';
                    td.dataset.row = row;
                    td.dataset.col = col;
                    const matched = seats.find(seat => +seat.row === row && +seat.col === col);
                    const seatInfo = matched || stageClass;

                    if (seatInfo) {

                        td.textContent = seatInfo.seat_class_name || seatInfo.name || 'empty';
                        td.style.backgroundColor = seatInfo.color || '#ccc';
                        td.style.color = '#000';
                        td.classList.add(seatInfo.status);
                        td.dataset.seatClass = JSON.stringify({
                            id: seatInfo.seat_class_id || seatInfo.id,
                            name: seatInfo.seat_class_name || seatInfo.name,
                            color: seatInfo.color,
                            status: seatInfo.status
                        });
                    }

                    td.onclick = (e) => {
                        if (e.target.classList.contains('Reserved')) {
                            showToast('This seat is reserved.', 'error');
                            return;
                        }
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
            document.querySelectorAll('td').forEach(cell => {
                cell.classList.remove('ring', 'ring-blue-500', 'ring-yellow-400');
            });
            selectedCells = [];
        }

        function selectRow(rowIndex) {

            clearSelection();
            selectedCells = Array.from(document.querySelectorAll(`td[data-row="${rowIndex}"]`));
            selectedCells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            showPopupAtCell(selectedCells[0]);
        }

        function selectColumn(colIndex) {
            clearSelection();
            selectedCells = Array.from(document.querySelectorAll(`td[data-col="${colIndex}"]`));
            selectedCells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            showPopupAtCell(selectedCells[0]);
        }

        function showPopupAtCell(cell) {
            const rect = cell.getBoundingClientRect();
            showBalloon(rect.left + window.scrollX, rect.top + window.scrollY);
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

            for (const cell of selectedCells) {
                if (cell.classList.contains('Reserved')) {
                    showToast('There is a seat in Row/Col is reserved.', 'error', null, 2000);
                    hideBalloon();
                    return;
                }
            }

            for (const cell of selectedCells) {
                cell.textContent = name;
                cell.style.backgroundColor = color;
                cell.dataset.seatClass = JSON.stringify({ id, name, color });
            }


            hideBalloon();
            clearSelection();
        }

        function saveGrid() {
            const rows = parseInt(document.getElementById('rows').value);
            const cols = parseInt(document.getElementById('cols').value);

            if (!eventId) {
                showToast('Event ID missing.', 'error');
                return;
            }

            const seats = Array.from(document.querySelectorAll('#grid-container td'))
                .map(cell => {
                    if (!cell.dataset.seatClass) return null;
                    const seatClass = JSON.parse(cell.dataset.seatClass);
                    return {
                        row: parseInt(cell.dataset.row),
                        col: parseInt(cell.dataset.col),
                        seat_class_id: seatClass.id,
                        seat_class_name: seatClass.name,
                        status: seatClass.status
                    };
                }).filter(Boolean);

            fetch('/update-event-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_id: eventId,
                    data: { rows, cols, seats }
                })
            }).then(() => {
                showToast('Seat layout saved successfully!', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }).catch(() => {
                showToast('Failed to save layout.', 'error');
            });
        }


        document.addEventListener('click', (e) => {
            if (!balloon.contains(e.target)) {
                hideBalloon();
                clearSelection();
            }
        });

        function addRow() {
            const rowsInput = document.getElementById('rows');
            const rows = parseInt(rowsInput.value);
            const cols = parseInt(document.getElementById('cols').value);
            rowsInput.value = rows + 1;


            for (let col = 0; col < cols; col++) {
                currentSeats.push({
                    row: rows,
                    col: col,
                    seat_class_id: stageClass?.id || null,
                    seat_class_name: stageClass?.name || 'empty',
                    color: stageClass?.color || '#ccc',
                    status:'Available'
                });
            }

            generateGrid(rows + 1, cols, currentSeats);
        }

        function addColumn() {
            const colsInput = document.getElementById('cols');
            const cols = parseInt(colsInput.value);
            const rows = parseInt(document.getElementById('rows').value);
            colsInput.value = cols + 1;

            // Add empty column to currentSeats
            for (let row = 0; row < rows; row++) {
                currentSeats.push({
                    row: row,
                    col: cols,
                    seat_class_id: stageClass?.id || null,
                    seat_class_name: stageClass?.name || 'empty',
                    color: stageClass?.color || '#ccc',
                    status:'Available'
                });
            }

            generateGrid(rows, cols + 1, currentSeats);
        }

        function deleteLastRow() {
            const rowsInput = document.getElementById('rows');
            let rows = parseInt(rowsInput.value);
            if (rows <= 0) return;

            rowsInput.value = rows - 1;

            const cols = parseInt(document.getElementById('cols').value);
            currentSeats = currentSeats.filter(seat => seat.row < rows - 1);

            generateGrid(rows - 1, cols, currentSeats);
        }


        function deleteLastColumn() {
            const colsInput = document.getElementById('cols');
            let cols = parseInt(colsInput.value);
            if (cols <= 0) return;

            colsInput.value = cols - 1;

            const rows = parseInt(document.getElementById('rows').value);
            currentSeats = currentSeats.filter(seat => seat.col < cols - 1);

            generateGrid(rows, cols - 1, currentSeats);
        }


    </script>
</x-filament::page>
