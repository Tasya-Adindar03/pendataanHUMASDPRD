(() => {
    if (typeof Chart === 'undefined') {
        document.getElementById('d2-error').style.display = 'block';
        return;
    }
    const d = JSON.parse(document.getElementById('d2-data').textContent);
    const links = JSON.parse(document.getElementById('d2-links').textContent);
    const mint = '#17b99b', blue = '#5489ed', red = '#dc6b7d', violet = '#8b73db';
    const money = value => new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(value);
    const million = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits:1}).format(value / 1000000);
    const jump = (params, base = links.base) => { window.location.href = base + '?' + new URLSearchParams({...links.filters, ...params}); };
    const charts = [];
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
    render('flow','bar',['Total nilai','− Lunas','Sisa piutang'],[
        {data:[[0,d.total],[d.outstanding,d.total],[0,d.outstanding]],backgroundColor:[blue,mint,red],borderRadius:5,barPercentage:.55}
    ],flowOptions,d.total > 0);

    const ageOptions = base();
    ageOptions.indexAxis = 'y';
    ageOptions.scales.x = {beginAtZero:true,grid,border:{display:false},ticks:currencyTicks};
    ageOptions.scales.y = {grid:{display:false},border:{display:false},ticks:{font:{size:9}}};
    render('aging','bar',['Belum lewat','1–30 hari','31–60 hari','>60 hari','Tanpa tenggat'],[
        {label:'Saldo',data:d.aging,backgroundColor:['#8dafcf','#e6bc6e','#e69c71',red,'#b6c1ce'],borderRadius:5,barPercentage:.7}
    ],ageOptions,d.outstanding > 0);

    const planOptions = base();
    delete planOptions.scales;
    planOptions.rotation = -90; planOptions.circumference = 180; planOptions.cutout = '84%';
    planOptions.layout = {padding:{top:12,bottom:8}};
    planOptions.plugins.tooltip.callbacks.label = c => `${c.label}: ${c.raw} slot`;
    render('plans','doughnut',['Terpenuhi','Sisa kuota'],[
        {data:[d.realized, Math.max(0,d.quota-d.realized)],backgroundColor:[mint,'#eaf0f5'],borderWidth:0,borderRadius:5}
    ],planOptions,d.quota > 0);

    new ResizeObserver(() => charts.forEach(chart => chart.resize())).observe(document.querySelector('.d2-grid'));
})();
