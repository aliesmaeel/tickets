<x-filament::page>
    <div x-data="qrScanner()" x-init="startScanner" class="space-y-4">
        <div class="text-center">
            <h2 class="text-xl font-bold mb-4">Scan Ticket QR Code</h2>
            <div id="qr-reader" class="mx-auto border-2 border-gray-200 rounded-lg overflow-hidden"
                 style="width: 300px; height: 300px;"></div>
            <p class="mt-2 text-sm text-gray-500">Point your camera at the QR code</p>
        </div>

        <template x-if="qrCode">
            <div class="bg-white shadow p-4 rounded-lg border">
                <div><strong>Event:</strong> {{ $event ?? '...' }}</div>
                <div><strong>Row:</strong> {{ $row ?? '...' }}</div>
                <div><strong>Column:</strong> {{ $col ?? '...' }}</div>
                <div><strong>Customer:</strong> {{ $customer?->name ?? '...' }}</div>
                <div><strong>Price:</strong> {{ $price ?? '...' }}</div>
                <div><strong>Event Start Time:</strong> {{ $event_start_time ?? '...' }}</div>
            </div>
        </template>


        <form wire:submit.prevent="verifyScan" class="space-y-4">
            <input type="hidden" wire:model="qrData" />


            <div class="flex space-x-4 justify-center">


                <x-filament::button type="button" @click="resetScanner" x-show="qrCode" color="secondary">
                    Scan Again
                </x-filament::button>
            </div>
        </form>

        <form wire:submit.prevent="markAsAttended" x-show="qrData">
            <x-filament::button type="submit" color="success" class="w-full justify-center">
                Mark as Attended
            </x-filament::button>
        </form>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function qrScanner() {
            return {
                qrCode: '',
                scanner: null,
                isScanning: false,
                startScanner() {
                    if (this.scanner) return;

                    this.scanner = new Html5Qrcode("qr-reader");
                    this.isScanning = true;

                    this.scanner.start(
                        { facingMode: "environment" },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            if (!this.qrCode) {
                                this.qrCode = decodedText;
                                this.scanner.stop().then(() => {
                                    this.isScanning = false;
                                    this.$wire.set('qrData', decodedText);
                                    this.$wire.call('verifyScan');
                                }).catch(err => {
                                    console.error("Failed to stop scanner:", err);
                                });
                            }
                        },
                        (errorMessage) => {
                            if (errorMessage !== "NotFoundException: No MultiFormat Readers were able to detect the code.") {
                                console.error("QR Scan Error:", errorMessage);
                            }
                        }
                    ).catch(err => {
                        console.error("Camera start failed:", err);
                        if (err.message.includes('Permission denied')) {
                            alert('Camera access was denied. Please enable camera permissions to scan QR codes.');
                        }
                    });
                },
                resetScanner() {
                    this.qrCode = '';
                    this.$wire.set('qrData', null);
                    if (!this.isScanning) {
                        this.startScanner();
                    }
                }
            }
        }
    </script>
</x-filament::page>
