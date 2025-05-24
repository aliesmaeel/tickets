<x-filament::page>
    <div x-data="qrScanner()" x-init="startScanner" class="space-y-4">
        <div class="text-center">
            <h2 class="text-xl font-bold mb-4">Scan Ticket QR Code</h2>
            <div id="qr-reader" class="mx-auto border-2 border-gray-200 rounded-lg overflow-hidden"
                 style="width: 300px; height: 300px;"></div>
            <p class="mt-2 text-sm text-gray-500">Point your camera at the QR code</p>
        </div>

        <template x-if="qrCode">
            <div class="p-4 bg-green-100 text-green-800 rounded-lg border border-green-200">
                <div class="flex justify-between items-center">
                    <div>
                        <strong class="block">Scanned QR Code:</strong>
                        <span x-text="qrCode" class="font-mono"></span>
                    </div>
                    <button @click="resetScanner" class="text-green-600 hover:text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
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
