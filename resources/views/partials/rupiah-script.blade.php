<script>
    function initRupiahInput(displayId, hiddenId) {
        const display = document.getElementById(displayId);
        const hidden = document.getElementById(hiddenId);
        if (!display || !hidden) return;

        function formatWithDots(numStr) {
            return numStr.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // format nilai awal (misal saat halaman edit dibuka)
        if (hidden.value) {
            display.value = formatWithDots(hidden.value.toString());
        }

        display.addEventListener('input', function () {
            const rawDigits = display.value.replace(/\D/g, '');
            hidden.value = rawDigits;
            display.value = rawDigits ? formatWithDots(rawDigits) : '';
        });
    }
</script>