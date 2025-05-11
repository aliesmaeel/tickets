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
    </div>

    <div id="grid-container" class="mt-8 overflow-x-auto"></div>

    <!-- Balloon popup -->
    <div id="balloon" onclick="event.stopPropagation()" class="hidden absolute z-50 bg-white border rounded shadow p-2 w-40">
        <select id="seat-class" class="block mb-2 border rounded w-full p-1">
            <option value="">Seat Class</option>
        </select>
        <select id="seat-color" class="block mb-2 border rounded w-full p-1">
            <option value="">Choose Color</option>
            <option value="mediumvioletred" style="color:mediumvioletred;">Red</option>
            <option value="deepskyblue" style="color:deepskyblue;">Blue</option>
            <option value="gold" style="color:gold;">Yellow</option>
            <option value="green" style="color:green;">Green</option>
            <option value="gray" style="color:gray;">Gray</option>
            <option value="black" style="color:black;">Black</option>
        </select>

        <button onclick="applySelection()" class="bg-black/50 text-white px-3 py-1 rounded w-full">Submit</button>
    </div>

    <script>
        let selectedCells = [];

        function generateGrid() {
            const rows = parseInt(document.getElementById('rows').value);
            const cols = parseInt(document.getElementById('cols').value);
            const container = document.getElementById('grid-container');
            const balloon = document.getElementById('balloon');
            container.innerHTML = '';
            balloon.classList.add('hidden');
            selectedCells = [];

            const table = document.createElement('table');
            table.classList.add('table-auto', 'border-collapse');

            // Column selector row
            const colHeader = document.createElement('tr');
            colHeader.appendChild(document.createElement('th')); // Empty corner

            for (let j = 0; j < cols; j++) {
                const colBtn = document.createElement('button');
                colBtn.textContent = `↓`;
                colBtn.className = 'bg-gray-200 px-2 py-1 rounded text-xs mx-auto block';
                colBtn.onclick = (e) => {
                    e.stopPropagation();
                    selectColumn(j);
                };

                const th = document.createElement('th');
                th.className = 'text-center';
                th.appendChild(colBtn);
                colHeader.appendChild(th);
            }

            table.appendChild(colHeader);

            for (let i = 0; i < rows; i++) {
                const tr = document.createElement('tr');

                // Row selector button
                const rowBtn = document.createElement('button');
                rowBtn.textContent = `→`;
                rowBtn.className = 'bg-gray-200 px-2 py-1 rounded text-xs mx-auto block';
                rowBtn.onclick = (e) => {
                    e.stopPropagation();
                    selectRow(i);
                };

                const th = document.createElement('th');
                th.appendChild(rowBtn);
                tr.appendChild(th);

                for (let j = 0; j < cols; j++) {
                    const td = document.createElement('td');
                    td.className = 'border w-16 h-12 text-center align-middle text-sm cursor-pointer';
                    td.dataset.row = i;
                    td.dataset.col = j;

                    td.addEventListener('click', (e) => {
                        e.stopPropagation();
                        clearSelection();
                        td.classList.add('ring', 'ring-blue-500');
                        selectedCells = [td];
                        showBalloon(e.pageX, e.pageY);
                    });

                    tr.appendChild(td);
                }

                table.appendChild(tr);
            }

            container.appendChild(table);
        }

        function clearSelection() {
            document.querySelectorAll('td').forEach(el =>
                el.classList.remove('ring', 'ring-blue-500', 'ring-yellow-400')
            );
            selectedCells = [];
        }

        function selectRow(rowIndex) {
            clearSelection();
            const cells = document.querySelectorAll(`[data-row="${rowIndex}"]`);
            cells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            selectedCells = Array.from(cells);
            const firstCell = selectedCells[0];
            const rect = firstCell.getBoundingClientRect();
            showBalloon(rect.x + window.scrollX, rect.y + window.scrollY);
        }

        function selectColumn(colIndex) {
            clearSelection();
            const cells = document.querySelectorAll(`[data-col="${colIndex}"]`);
            cells.forEach(cell => cell.classList.add('ring', 'ring-yellow-400'));
            selectedCells = Array.from(cells);
            const firstCell = selectedCells[0];
            const rect = firstCell.getBoundingClientRect();
            showBalloon(rect.x + window.scrollX, rect.y + window.scrollY);
        }

        function showBalloon(x, y) {
            const balloon = document.getElementById('balloon');
            balloon.style.left = `${x + 10}px`;
            balloon.style.top = `${y + 10}px`;
            balloon.classList.remove('hidden');
        }

        function applySelection() {
            const seatClassSelect = document.getElementById('seat-class');
            const seatClassId = seatClassSelect.value;
            const seatClassName = seatClassSelect.options[seatClassSelect.selectedIndex].text;
            const color = document.getElementById('seat-color').value;

            selectedCells.forEach(cell => {
                cell.textContent = seatClassName;
                cell.style.backgroundColor = color;

                cell.dataset.seatClassId = seatClassId;

                cell.dataset.seatColor = color;
            });

            document.getElementById('balloon').classList.add('hidden');
            clearSelection();
        }


        // Don't close the balloon when clicking inside it
        document.addEventListener('click', (e) => {
            const balloon = document.getElementById('balloon');
            if (!balloon.contains(e.target)) {
                balloon.classList.add('hidden');
                clearSelection();
            }
        });
    </script>

    <script>
        document.getElementById('event_id').addEventListener('change', function (event) {
            this.disabled=true;
            const eventId = this.value;

            const seatClassSelect = document.getElementById('seat-class');

            // Reset seat class dropdown
            seatClassSelect.innerHTML = '<option value="">Empty</option>';

            if (!eventId) return;

            fetch(`/get-seat-classes/${eventId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = cls.id;  // or cls.name if needed
                        option.textContent = cls.name;
                        seatClassSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading seat classes:', error);
                });
        });
    </script>


</x-filament::page>
