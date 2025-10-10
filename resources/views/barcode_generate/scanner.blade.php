<form method="GET" action="{{ route('client.search') }}" id="barcode-form">
    <input type="text" name="client_id" id="scanner-input" autofocus style="position:absolute; left:-9999px;">
</form>

<script>
    const scannerInput = document.getElementById('scanner-input');
    const barcodeForm = document.getElementById('barcode-form');

    // نخلي input دايمًا واخد focus
    function focusScanner() {
        scannerInput.focus();
    }

    window.onload = focusScanner;
    document.addEventListener('click', focusScanner);

    // أول ما يتقرا الباركود ويتعمل Enter
    scannerInput.addEventListener('change', function () {
        barcodeForm.submit();
    });
</script>
