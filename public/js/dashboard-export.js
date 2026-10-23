/* Shared CSV writer for aggregated dashboard card data. */
(function (root) {
    function csv(headers, rows) {
        const cell = value => {
            let text = value == null ? '' : String(value);
            if (typeof value !== 'number' && /^\s*[=+@-]/.test(text)) text = "'" + text;
            return '"' + text.replace(/"/g, '""') + '"';
        };
        return '\uFEFF' + [headers, ...rows].map(row => row.map(cell).join(',')).join('\r\n');
    }
    function download(name, headers, rows) {
        const url = URL.createObjectURL(new Blob([csv(headers, rows)], {type:'text/csv;charset=utf-8;'}));
        const link = document.createElement('a');
        link.href = url; link.download = name + '.csv'; link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
    root.DashboardExport = {csv, download};
})(typeof window === 'undefined' ? globalThis : window);
