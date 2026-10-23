window.initDashboardTwo = () => {
    if (typeof Chart === 'undefined') {
        document.getElementById('d2-error').style.display = 'block';
        return;
    }
    (window.d2Charts || []).forEach(chart => chart.destroy());
    if (window.d2Observer) window.d2Observer.disconnect();
    const d = JSON.parse(document.getElementById('d2-data').textContent);
    const links = JSON.parse(document.getElementById('d2-links').textContent);
    const mint = '#17b99b', blue = '#5489ed', red = '#dc6b7d', violet = '#8b73db';
    const money = value => new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(value);
    const million = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits:1}).format(value / 1000000);
    const jump = params => {
        if (params.dari_tanggal) return window.setDashboardFocus({hari:params.dari_tanggal.slice(-2).replace(/^0/,'')});
        window.setDashboardFocus(params);
    };
    const charts = [];
    window.d2Charts = charts;
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#8596a8';
    const legend = {position:'bottom', labels:{boxWidth:7, boxHeight:7, usePointStyle:true, padding:10, font:{size:9}}};
    const currencyTicks = {maxTicksLimit:5, callback:million};
    const grid = {color:'#eef2f7', drawTicks:false};
    const base = () => ({
        maintainAspectRatio:false,
        responsive:true,
        animation:{duration:window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 600},
        plugins:{legend:{display:false}, tooltip:{backgroundColor:'#18364c', padding:10, cornerRadius:8, callbacks:{label:c => `${c.dataset.label || c.label}: ${money(c.raw)}`}}},
        scales:{x:{grid:{display:false}, border:{display:false}, ticks:{maxRotation:0, maxTicksLimit:8, font:{size:9}}}, y:{beginAtZero:true, grid, border:{display:false}, ticks:currencyTicks}}
    });
    function render(id, type, labels, datasets, options, hasData) {
        document.getElementById('panel-' + id).classList.toggle('empty', !hasData);
        charts.push(new Chart(document.getElementById('d2-' + id), {type, data:{labels,datasets}, options}));
        const head = document.querySelector('#panel-' + id + ' .d2-panel-head');
        const tools = document.createElement('div'); tools.className = 'd2-chart-actions';
        for (const [action, text, label] of [['expand','⛶','Perbesar grafik'],['download','↓','Unduh grafik PNG']]) {
            const button = document.createElement('button'); button.type = 'button'; button.dataset[action + 'Chart'] = id;
            button.textContent = text; button.title = label; button.setAttribute('aria-label', label + ' ' + head.querySelector('h3').textContent);
            tools.appendChild(button);
        }
        head.appendChild(tools);
    }
    function clickable(options, callback) {
        options.onHover = (e, points) => { e.native.target.style.cursor = points.length ? 'pointer' : 'default'; };
        options.onClick = (e, points) => { if (points.length) callback(points[0]); };
        return options;
    }
    function gradient(id, color) {
        const ctx = document.getElementById('d2-' + id).getContext('2d');
        const result = ctx.createLinearGradient(0,0,0,220);
        result.addColorStop(0,color); result.addColorStop(1,'rgba(255,255,255,0)');
        return result;
    }
    const dailyOptions = base();
    dailyOptions.plugins.legend = legend;
    dailyOptions.interaction = {mode:'index', intersect:false};
    dailyOptions.plugins.tooltip.callbacks.title = items => d.daily[items[0].dataIndex].date;
    clickable(dailyOptions, point => jump({dari_tanggal:d.daily[point.index].date, sampai_tanggal:d.daily[point.index].date}));
    render('daily','line',d.daily.map(row => row.day),[
        {label:'Nilai kumulatif', data:d.daily.map(row => row.cumulative), borderColor:blue, backgroundColor:gradient('daily','rgba(84,137,237,.30)'), fill:true, tension:.3, pointRadius:0, pointHoverRadius:4, borderWidth:2},
        {label:'Bagian lunas', data:d.daily.map(row => row.paid), borderColor:mint, backgroundColor:'rgba(23,185,155,.08)', fill:true, tension:.3, pointRadius:0, pointHoverRadius:4, borderWidth:2}
    ],dailyOptions,d.total > 0);

    const statusOptions = base();
    delete statusOptions.scales;
    statusOptions.cutout = '73%';
    statusOptions.plugins.legend = legend;
    clickable(statusOptions, point => jump({status:['sudah_dibayar','belum_dibayar','jatuh_tempo'][point.index]}));
    render('status','doughnut',['Lunas','Belum dibayar','Jatuh tempo'],[
        {data:d.status, backgroundColor:[mint,'#e8b45a',red], borderWidth:3, borderColor:'#fff', hoverOffset:5, borderRadius:4}
    ],statusOptions,d.total > 0);

    const typeOptions = base();
    typeOptions.scales = {r:{beginAtZero:true, grid:{color:'#edf1f7'}, ticks:{display:false}, border:{display:false}, angleLines:{display:false}}};
    typeOptions.plugins.legend = legend;
    clickable(typeOptions, point => jump({tipe:['cetak','online'][point.index]}, links.media));
    render('type','polarArea',['Cetak','Online'],[{data:d.type, backgroundColor:['rgba(139,115,219,.75)','rgba(84,137,237,.75)'], borderColor:'#fff', borderWidth:2}],typeOptions,d.type.some(v => v > 0));

    const bubbleOptions = base();
    bubbleOptions.scales.x = {min:0, grid, border:{display:false}, ticks:{precision:0,maxTicksLimit:5}, title:{display:true,text:'Jumlah publikasi',font:{size:9}}};
    bubbleOptions.scales.y.title = {display:true,text:'Rata-rata (Rp jt)',font:{size:9}};
    bubbleOptions.plugins.legend = legend;
    bubbleOptions.plugins.tooltip.callbacks = {
        title:items => items[0].raw.name,
        label:c => [`${c.raw.x} publikasi · total ${money(c.raw.total)}`,`Rata-rata ${money(c.raw.y)}`]
    };
    clickable(bubbleOptions, point => {
        const row = charts.find(chart => chart.canvas.id === 'd2-bubble').data.datasets[point.datasetIndex].data[point.index];
        jump({media_id:row.id});
    });
    const maxMedia = Math.max(1,...d.media.map(row => row.total));
    render('bubble','bubble',[],['cetak','online','lainnya'].map((type,i) => ({
        label:['Cetak','Online','Lainnya'][i],
        data:d.media.filter(row => row.type === type).map(row => ({x:row.count,y:row.average,r:Math.max(4,Math.sqrt(row.total / maxMedia) * 22),id:row.id,name:row.name,total:row.total})),
        backgroundColor:['rgba(139,115,219,.55)','rgba(84,137,237,.55)','rgba(122,146,162,.55)'][i],
        borderColor:[violet,blue,'#7a92a2'][i], borderWidth:1.5
    })),bubbleOptions,d.media.length > 0);

    const companyOptions = base();
    companyOptions.indexAxis = 'y';
    companyOptions.scales.x = {stacked:true,beginAtZero:true,grid,border:{display:false},ticks:currencyTicks};
    companyOptions.scales.y = {stacked:true,grid:{display:false},border:{display:false},ticks:{font:{size:9},callback:function(v) {const text=this.getLabelForValue(v); return text.length > 19 ? text.slice(0,8)+'…'+text.slice(-10) : text;}}};
    companyOptions.plugins.legend = legend;
    clickable(companyOptions, point => jump({company_id:d.companies[point.index].id}));
    render('companies','bar',d.companies.map(row => row.name),[
        {label:'Lunas',data:d.companies.map(row => row.paid),backgroundColor:mint,borderRadius:3},
        {label:'Belum lunas',data:d.companies.map(row => row.outstanding),backgroundColor:red,borderRadius:3}
    ],companyOptions,d.companies.length > 0);

    const flowOptions = base();
    flowOptions.plugins.tooltip.callbacks.label = c => money([d.total,d.paid,d.outstanding][c.dataIndex]);
    clickable(flowOptions, point => point.index === 1 ? window.setDashboardFocus({status:'sudah_dibayar'}) : window.openDashboardDetails(point.index === 2));
    render('flow','bar',['Total nilai','− Lunas','Sisa piutang'],[
        {data:[[0,d.total],[d.outstanding,d.total],[0,d.outstanding]],backgroundColor:[blue,mint,red],borderRadius:5,barPercentage:.55}
    ],flowOptions,d.total > 0);

    const ageOptions = base();
    ageOptions.indexAxis = 'y';
    ageOptions.scales.x = {beginAtZero:true,grid,border:{display:false},ticks:currencyTicks};
    ageOptions.scales.y = {grid:{display:false},border:{display:false},ticks:{font:{size:9}}};
    clickable(ageOptions, point => window.setDashboardFocus({aging:String(point.index)}));
    render('aging','bar',['Belum lewat','1–30 hari','31–60 hari','>60 hari','Tanpa tenggat'],[
        {label:'Saldo',data:d.aging,backgroundColor:['#8dafcf','#e6bc6e','#e69c71',red,'#b6c1ce'],borderRadius:5,barPercentage:.7}
    ],ageOptions,d.outstanding > 0);

    const planOptions = base();
    delete planOptions.scales;
    planOptions.rotation = -90; planOptions.circumference = 180; planOptions.cutout = '84%';
    planOptions.layout = {padding:{top:12,bottom:8}};
    planOptions.plugins.tooltip.callbacks.label = c => `${c.label}: ${c.raw} slot`;
    clickable(planOptions, () => {
        const current = new URL(window.location.href); const month = current.searchParams.get('bulan') || document.getElementById('d2-month').value;
        const params = new URLSearchParams({bulan:Number(month.slice(5)),tahun:month.slice(0,4)});
        for (const key of ['company_id','media_id']) if(current.searchParams.has(key)) params.set(key,current.searchParams.get(key));
        window.location.href = '/plans?' + params;
    });
    render('plans','doughnut',['Terpenuhi','Sisa kuota'],[
        {data:[d.realized, Math.max(0,d.quota-d.realized)],backgroundColor:[mint,'#eaf0f5'],borderWidth:0,borderRadius:5}
    ],planOptions,d.quota > 0);

    window.d2Observer = new ResizeObserver(() => charts.forEach(chart => chart.resize()));
    window.d2Observer.observe(document.querySelector('.d2-grid'));
};

(() => {
    let requestId = 0;
    let pending;
    async function load(url, push = true) {
        const id = ++requestId;
        if (pending) pending.abort();
        pending = new AbortController();
        const dashboard = document.querySelector('.d2');
        dashboard.classList.add('is-loading');
        dashboard.setAttribute('aria-busy','true');
        dashboard.querySelector('.d2-live').textContent = 'Memperbarui seluruh grafik dan insight…';
        try {
            const response = await fetch(url, {signal:pending.signal,headers:{'X-Requested-With':'XMLHttpRequest'}});
            if (!response.ok) throw new Error('Gagal memuat filter');
            const doc = new DOMParser().parseFromString(await response.text(),'text/html');
            if (!doc.querySelector('.d2') || !doc.getElementById('d2-data')) throw new Error('Respons tidak valid');
            if (id !== requestId) return;
            dashboard.innerHTML = doc.querySelector('.d2').innerHTML;
            for (const key of ['d2-data','d2-links']) document.getElementById(key).textContent = doc.getElementById(key).textContent;
            if (push) history.pushState({},'',url);
            window.initDashboardTwo();
            const data = JSON.parse(document.getElementById('d2-data').textContent);
            document.querySelector('.d2-live').textContent = `Fokus diperbarui · ${data.daily.reduce((sum,day) => sum+day.count,0)} advertorial.`;
        } catch (error) {
            if (error.name !== 'AbortError') dashboard.querySelector('.d2-live').textContent = 'Filter belum dapat dimuat. Periksa koneksi lalu coba lagi; data sebelumnya tetap ditampilkan.';
        } finally {
            if (id === requestId) {
                document.querySelector('.d2').classList.remove('is-loading');
                document.querySelector('.d2').removeAttribute('aria-busy');
            }
        }
    }
    window.setDashboardFocus = params => {
        const url = new URL(window.location.href);
        for (const [key, value] of Object.entries(params)) {
            if (!value || url.searchParams.get(key) === String(value)) url.searchParams.delete(key);
            else url.searchParams.set(key,value);
        }
        load(url);
    };
    window.openDashboardDetails = (outstandingOnly = false) => {
        const dialog = document.getElementById('d2-details');
        dialog.querySelectorAll('tbody tr[data-status]').forEach(row => {row.hidden = outstandingOnly && row.dataset.status === 'sudah_dibayar';});
        dialog.querySelector('#d2-detail-title').textContent = outstandingOnly ? 'Telusuri saldo belum lunas' : 'Telusuri data terpilih';
        dialog.querySelector('header p').textContent = document.getElementById('d2-month').value + ' · ' + [...dialog.querySelectorAll('tbody tr[data-status]')].filter(row => !row.hidden).length + ' baris ditampilkan (maks. 50 nilai terbesar dari fokus terpilih).';
        dialog.showModal();
    };
    function collapse() {
        const panel = document.querySelector('.d2-panel.is-expanded');
        if (!panel) return;
        panel.classList.remove('is-expanded');
        panel.querySelector('[data-expand-chart]').setAttribute('aria-expanded','false');
        panel.querySelector('[data-expand-chart]').focus();
        (window.d2Charts || []).forEach(chart => chart.resize());
    }
    document.addEventListener('click', async event => {
        const target = event.target.closest('button,a');
        if (!target || !target.closest('.d2')) return;
        if (target.hasAttribute('data-focus-key')) {
            event.preventDefault(); window.setDashboardFocus({[target.dataset.focusKey]:target.dataset.focusValue});
        } else if (target.hasAttribute('data-clear-focus')) {
            const url = new URL(window.location.href); url.searchParams.delete(target.dataset.clearFocus); load(url);
        } else if (target.hasAttribute('data-reset-focus')) {
            const url = new URL(window.location.href); const month = url.searchParams.get('bulan'); url.search = ''; if(month) url.searchParams.set('bulan',month); load(url);
        } else if (target.hasAttribute('data-open-details')) {
            window.openDashboardDetails();
        } else if (target.hasAttribute('data-close-details')) {
            document.getElementById('d2-details').close();
        } else if (target.hasAttribute('data-expand-chart')) {
            const panel = document.getElementById('panel-' + target.dataset.expandChart);
            const expanded = panel.classList.contains('is-expanded'); collapse();
            if(!expanded) {panel.classList.add('is-expanded');target.setAttribute('aria-expanded','true');}
            (window.d2Charts || []).forEach(chart => chart.resize());
        } else if (target.hasAttribute('data-download-chart')) {
            const chart = (window.d2Charts || []).find(item => item.canvas.id === 'd2-' + target.dataset.downloadChart);
            if(!chart) return;
            const link = document.createElement('a');link.download = 'dashboard-'+target.dataset.downloadChart+'.png';link.href = chart.toBase64Image();link.click();
        } else if (target.hasAttribute('data-fullscreen')) {
            try {
                if(document.fullscreenElement) await document.exitFullscreen();
                else await document.querySelector('.d2').requestFullscreen();
            } catch { document.querySelector('.d2-live').textContent = 'Layar penuh tidak didukung browser ini. Gunakan tombol perbesar pada grafik.'; }
        } else if (target.hasAttribute('data-export-csv')) {
            const rows = [...document.querySelectorAll('#d2-details tr')].filter(row => !row.hidden);
            const csv = rows.map(row => [...row.children].slice(0,5).map(cell => {
                let text = cell.innerText.trim(); if (/^[=+@\-\t\r]/.test(text)) text = "'" + text;
                return '"'+text.replace(/"/g,'""')+'"';
            }).join(',')).join('\r\n');
            const url = URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'}));
            const link = document.createElement('a');link.download = 'detail-dashboard-'+document.getElementById('d2-month').value+'.csv';link.href = url;link.click();setTimeout(() => URL.revokeObjectURL(url),1000);
        }
    });
    document.addEventListener('input', event => {
        if (event.target.id !== 'd2-recovery') return;
        const ratio = Number(event.target.value);
        const data = JSON.parse(document.getElementById('d2-data').textContent);
        const extra = data.outstanding * ratio / 100;
        const format = value => new Intl.NumberFormat('id-ID',{maximumFractionDigits:1}).format(value / 1000000);
        document.getElementById('d2-recovery-value').textContent = ratio + '%';
        document.querySelector('[data-recovery-result]').textContent = `Simulasi: +Rp ${format(extra)} jt → porsi lunas ${data.total > 0 ? ((data.paid+extra)/data.total*100).toFixed(1) : 0}%. Data tidak diubah.`;
    });
    document.addEventListener('submit', event => {
        if (!event.target.matches('.d2-filter')) return;
        event.preventDefault(); const url = new URL(window.location.href);url.searchParams.set('bulan',document.getElementById('d2-month').value);url.searchParams.delete('hari');load(url);
    });
    document.addEventListener('keydown', event => {if(event.key === 'Escape') collapse();});
    window.addEventListener('popstate', () => load(window.location.href,false));
    window.initDashboardTwo();
})();
