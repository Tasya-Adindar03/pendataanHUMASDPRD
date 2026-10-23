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
    let tooltipElement = document.getElementById('d2-floating-tooltip');
    if (!tooltipElement) {
        tooltipElement = document.createElement('div');
        tooltipElement.id = 'd2-floating-tooltip';
        tooltipElement.className = 'd2-floating-tooltip';
        tooltipElement.setAttribute('role','tooltip');
        document.body.appendChild(tooltipElement);
    }
    tooltipElement.hidden = true;
    const externalTooltip = ({chart, tooltip}) => {
        if (!tooltip || tooltip.opacity === 0) {tooltipElement.hidden=true;return;}
        const host = document.fullscreenElement || document.body;
        if (tooltipElement.parentElement !== host) host.appendChild(tooltipElement);
        tooltipElement.replaceChildren();
        for(const title of tooltip.title || []) {
            const heading=document.createElement('strong');heading.textContent=title;tooltipElement.appendChild(heading);
        }
        for(const [index,body] of (tooltip.body || []).entries()) {
            const row=document.createElement('div');row.className='d2-tooltip-row';
            const dot=document.createElement('i');
            const color=tooltip.labelColors?.[index]?.backgroundColor;
            dot.style.backgroundColor=typeof color==='string'?color:'#78a6d4';row.appendChild(dot);
            const text=document.createElement('span');text.textContent=[...(body.before || []),...(body.lines || []),...(body.after || [])].join(' · ');row.appendChild(text);tooltipElement.appendChild(row);
        }
        tooltipElement.hidden=false;
        const rect=chart.canvas.getBoundingClientRect();
        const box=tooltipElement.getBoundingClientRect();
        const anchorX=rect.left+tooltip.caretX*(rect.width/chart.width);
        const anchorY=rect.top+tooltip.caretY*(rect.height/chart.height);
        let left=anchorX+12;
        let top=anchorY+12;
        if(left+box.width>window.innerWidth-8)left=anchorX-box.width-12;
        if(top+box.height>window.innerHeight-8)top=anchorY-box.height-12;
        tooltipElement.style.left=Math.max(8,Math.min(left,window.innerWidth-box.width-8))+'px';
        tooltipElement.style.top=Math.max(8,Math.min(top,window.innerHeight-box.height-8))+'px';
    };
    const money = value => new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR', maximumFractionDigits:0}).format(value);
    const million = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits:1}).format(value / 1000000);
    const jump = params => {
        if (params.dari_tanggal) return window.setDashboardFocus({tanggal:params.dari_tanggal});
        window.setDashboardFocus(params);
    };
    const mode = localStorage.getItem('d2-visual-mode-v2') || 'strategy';
    const compare = mode === 'compare';
    document.getElementById('d2-visual-mode').value = mode;
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
        interaction:{mode:'nearest',intersect:false},
        plugins:{legend:{display:false}, tooltip:{enabled:false,external:externalTooltip,backgroundColor:'#18364c', padding:10, cornerRadius:8, callbacks:{label:c => `${c.dataset.label || c.label}: ${money(c.raw)}`}}},
        scales:{x:{grid:{display:false}, border:{display:false}, ticks:{maxRotation:0, maxTicksLimit:8, font:{size:9}}}, y:{beginAtZero:true, grid, border:{display:false}, ticks:currencyTicks}}
    });
    function render(id, type, labels, datasets, options, hasData) {
        const panel = document.getElementById('panel-' + id);
        panel.classList.add('d2-expandable');
        panel.dataset.originalTitle ??= panel.querySelector('h3').textContent;
        panel.querySelector('h3').textContent = panel.dataset.originalTitle;
        panel.dataset.originalSub ??= panel.querySelector('.d2-panel-sub').textContent;
        panel.dataset.originalFoot ??= panel.querySelector('.d2-foot').textContent;
        panel.querySelector('.d2-panel-sub').textContent = panel.dataset.originalSub;
        panel.querySelector('.d2-foot').textContent = panel.dataset.originalFoot;
        const horizontal = () => {
            const result = base(); result.indexAxis = 'y';
            result.scales.x = {beginAtZero:true,grid,border:{display:false},ticks:currencyTicks};
            result.scales.y = {grid:{display:false},border:{display:false},ticks:{font:{size:8},callback:function(v){const text=this.getLabelForValue(v);return text.length>18?text.slice(0,7)+'…'+text.slice(-9):text;}}};
            return result;
        };
        const caption = text => {panel.querySelector('.d2-panel-sub').textContent = text;};
        if(compare) {
            if(id === 'daily') {
                type='bar';datasets=[{label:'Nilai harian',data:d.daily.map(row=>row.value),backgroundColor:blue,borderRadius:3}];options.plugins.legend.display=false;
                caption('Nilai kwitansi harian · bukan kumulatif · Rp juta');
                panel.querySelector('.d2-foot').textContent='Batang menunjukkan nilai per hari. Klik untuk fokus tanggal.';
            } else if(id === 'status') {
                type='bar';options=horizontal();clickable(options,p=>jump({status:['sudah_dibayar','belum_dibayar','jatuh_tempo'][p.index]}));
                caption('Bandingkan nilai setiap status pada sumbu yang sama · Rp juta');
            } else if(id === 'type') {
                type='doughnut';options.cutout='60%';delete options.scales;
                caption('Porsi nilai cetak dan online · hover untuk rupiah');
            } else if(id === 'bubble') {
                type='bar';labels=d.media.map(row=>row.name);datasets=[{label:'Rata-rata nilai',data:d.media.map(row=>row.average),backgroundColor:violet,borderRadius:3}];
                options=horizontal();clickable(options,p=>jump({media_id:d.media[p.index].id}));
                caption('Rata-rata nilai per publikasi di setiap media · Rp juta');
                panel.querySelector('.d2-foot').textContent='Bandingkan ukuran transaksi rata-rata, tanpa dipengaruhi jumlah publikasi.';
            } else if(id === 'companies') {
                type='line';datasets=[{label:'Total nilai',data:d.companies.map(row=>row.total),borderColor:mint,backgroundColor:mint,pointRadius:5,pointHoverRadius:7,showLine:false}];
                options=horizontal();clickable(options,p=>jump({company_id:d.companies[p.index].id}));
                caption('Dot plot: total nilai lima perusahaan terbesar · Rp juta');
                panel.querySelector('.d2-foot').textContent='Posisi titik menunjukkan total nilai. Klik untuk memfokuskan perusahaan.';
            } else if(id === 'flow') {
                type='bar';labels=['Nilai publikasi'];datasets=[{label:'Lunas',data:[d.paid],backgroundColor:mint},{label:'Belum lunas',data:[d.outstanding],backgroundColor:red}];
                options=horizontal();options.scales.x.stacked=true;options.scales.y.stacked=true;options.plugins.legend=legend;
                clickable(options,p=>p.datasetIndex===0?jump({status:'sudah_dibayar'}):window.openDashboardDetails(true));
                caption('Batang komposisi nilai lunas dan sisa piutang · Rp juta');
            } else if(id === 'aging') {
                type='doughnut';options=base();delete options.scales;options.cutout='55%';options.plugins.legend={...legend,labels:{...legend.labels,font:{size:8},padding:4}};
                clickable(options,p=>window.setDashboardFocus({aging:String(p.index)}));caption('Porsi saldo menurut kelompok umur · hover untuk rupiah');
            } else if(id === 'plans') {
                type='bar';labels=['Kuota periode'];datasets=[{label:'Terpenuhi',data:[d.realized],backgroundColor:mint},{label:'Sisa',data:[Math.max(0,d.quota-d.realized)],backgroundColor:'#dce6ee'}];
                const originalClick=options.onClick;options=horizontal();options.scales.x.stacked=true;options.scales.y.stacked=true;
                options.scales.x.ticks={precision:0};options.plugins.legend=legend;options.plugins.tooltip.callbacks.label=c=>c.dataset.label+': '+c.raw+' slot';options.onClick=originalClick;
                caption('Bullet bar: slot terpenuhi dibanding sisa kuota');
            }
        }
        if(mode === 'strategy') {
            const title = text => {panel.querySelector('h3').textContent=text;};
            if(id === 'daily') {
                type='bar';title('Ritme volume & nilai');
                datasets=[{type:'bar',label:'Nilai publikasi',data:d.daily.map(row=>row.value),backgroundColor:'#739bf0',borderRadius:2,yAxisID:'y'}, {type:'line',label:'Jumlah publikasi',data:d.daily.map(row=>row.count),borderColor:'#e09942',backgroundColor:'#e09942',pointRadius:1,borderWidth:2,yAxisID:'volume',tension:.2}];
                options.scales.volume={position:'right',beginAtZero:true,grid:{display:false},ticks:{precision:0,maxTicksLimit:4},title:{display:true,text:'Jumlah',font:{size:8}}};
                options.plugins.tooltip.callbacks.label=c=>c.dataset.label+': '+(c.dataset.yAxisID==='volume'?c.raw+' publikasi':money(c.raw));
                caption('Batang: nilai (Rp jt) · garis: jumlah publikasi (sumbu kanan)');
                panel.querySelector('.d2-foot').textContent='Lihat apakah perubahan nilai diikuti perubahan volume. Klik tanggal untuk fokus.';
            } else if(id === 'status') {
                type='bar';title('Porsi nilai pembayaran');labels=['Komposisi'];
                datasets=['Lunas','Belum dibayar','Jatuh tempo'].map((name,i)=>({label:name,data:[d.total>0?d.status[i]/d.total*100:0],backgroundColor:[mint,'#e8b45a',red][i]}));
                options=horizontal();options.scales.x.max=100;options.scales.x.stacked=true;options.scales.y.stacked=true;options.scales.x.ticks={callback:v=>v+'%'};options.plugins.legend=legend;
                options.plugins.tooltip.callbacks.label=c=>c.dataset.label+': '+c.raw.toFixed(1)+'% · '+money(d.status[c.datasetIndex]);
                clickable(options,p=>jump({status:['sudah_dibayar','belum_dibayar','jatuh_tempo'][p.datasetIndex]}));caption('100% nilai periode · proporsi status tersimpan');
            } else if(id === 'type') {
                type='radar';title('Radar kontribusi kanal');labels=['Volume','Nilai','Lunas'];
                const fields=['count','total','paid'];
                datasets=['Cetak','Online'].map((name,i)=>({label:name,data:fields.map(field=>{const sum=d.typeProfiles.reduce((s,row)=>s+row[field],0);return sum>0?d.typeProfiles[i][field]/sum*100:0;}),borderColor:[violet,blue][i],backgroundColor:['rgba(139,115,219,.18)','rgba(84,137,237,.15)'][i],borderWidth:2,pointRadius:2}));
                options=base();options.scales={r:{min:0,max:100,ticks:{display:false,stepSize:25},pointLabels:{font:{size:9}},grid:{color:'#e4eaf2'}}};options.plugins.legend=legend;
                options.plugins.tooltip.callbacks.label=c=>c.dataset.label+': '+c.raw.toFixed(1)+'%';
                clickable(options,p=>jump({tipe:['cetak','online'][p.datasetIndex]}));caption('Porsi tiap kanal pada volume, nilai, dan nilai lunas · %');
                panel.querySelector('.d2-foot').textContent='Setiap sumbu berbagi 100% antara kanal. Bukan skor kualitas media.';
            } else if(id === 'bubble') {
                type='scatter';title('Matriks prioritas media');
                datasets=[{label:'Media',data:d.media.map(row=>({x:row.count,y:row.total>0?row.outstanding/row.total*100:0,name:row.name,id:row.id,outstanding:row.outstanding})),pointRadius:6,pointHoverRadius:9,backgroundColor:d.media.map(row=>row.total>0&&row.outstanding/row.total>.5?red:mint)}];
                options=base();options.scales.x={beginAtZero:true,grid,border:{display:false},ticks:{precision:0,maxTicksLimit:5},title:{display:true,text:'Jumlah publikasi',font:{size:8}}};
                options.scales.y={min:0,max:100,grid,ticks:{callback:v=>v+'%',maxTicksLimit:4},title:{display:true,text:'Belum lunas',font:{size:8}}};
                options.plugins.tooltip.callbacks={title:items=>items[0].raw.name,label:c=>[c.raw.x+' publikasi · '+c.raw.y.toFixed(1)+'% belum lunas',money(c.raw.outstanding)+' saldo']};
                clickable(options,p=>jump({media_id:d.media[p.index].id}));caption('X: volume · Y: porsi nilai belum lunas · klik titik untuk fokus');
                panel.querySelector('.d2-foot').textContent='Kanan atas: volume besar dan porsi piutang tinggi. Warna merah = lebih dari 50% belum lunas.';
            } else if(id === 'companies') {
                type='bar';title('Pareto konsentrasi nilai');let cumulative=0;
                datasets=[{type:'bar',label:'Nilai perusahaan',data:d.companies.map(row=>row.total),backgroundColor:violet,borderRadius:3,yAxisID:'y'}, {type:'line',label:'Porsi kumulatif',data:d.companies.map(row=>{cumulative+=row.total;return d.total>0?cumulative/d.total*100:0;}),borderColor:'#e09942',backgroundColor:'#e09942',borderWidth:2,pointRadius:3,yAxisID:'share'}];
                options=base();options.scales.share={position:'right',min:0,max:100,grid:{display:false},ticks:{callback:v=>v+'%',maxTicksLimit:4}};
                options.scales.x.ticks={font:{size:8},maxRotation:0,callback:function(v){const label=this.getLabelForValue(v);return label.length>12?'…'+label.slice(-11):label;}};
                options.plugins.tooltip.callbacks.label=c=>c.dataset.yAxisID==='share'?'Kumulatif: '+c.raw.toFixed(1)+'%':money(c.raw);
                options.plugins.legend=legend;clickable(options,p=>jump({company_id:d.companies[p.index].id}));caption('Batang: nilai · garis: porsi kumulatif terhadap seluruh periode');
                panel.querySelector('.d2-foot').textContent='Lima perusahaan terbesar. Garis tidak harus mencapai 100% jika masih ada perusahaan lain.';
            } else if(id === 'flow') {
                type='bar';title('Lunas vs piutang per kanal');labels=['Cetak','Online'];
                datasets=[{label:'Lunas',data:d.typeProfiles.map(row=>row.paid),backgroundColor:mint,borderRadius:3},{label:'Belum lunas',data:d.typeProfiles.map(row=>Math.max(0,row.total-row.paid)),backgroundColor:red,borderRadius:3}];
                options=base();options.plugins.legend=legend;clickable(options,p=>jump({tipe:['cetak','online'][p.index]}));caption('Grouped bar · bandingkan saldo pada skala rupiah yang sama');
                panel.querySelector('.d2-foot').textContent='Pilih kanal dengan selisih lunas dan piutang terbesar untuk ditelusuri.';
            }
        }
        if(id === 'plans') panel.querySelector('.d2-plan-center').style.display=compare?'none':'';
        panel.querySelectorAll('.d2-chart-actions').forEach(el=>el.remove());
        document.getElementById('d2-' + id).setAttribute('aria-label', panel.querySelector('h3').textContent + '. ' + panel.querySelector('.d2-panel-sub').textContent);
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
    render('daily','line',d.daily.map(row => d.monthCount > 1 ? row.date.slice(5).split('-').reverse().join('/') : row.day),[
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
        if(d.monthCount > 1) {document.querySelector('[data-expand-chart="plans"]').click();return;}
        const current = new URL(window.location.href); const month = document.getElementById('d2-month').value;
        const params = new URLSearchParams({bulan:Number(month.slice(5)),tahun:month.slice(0,4)});
        for (const key of ['company_id','media_id']) if(current.searchParams.has(key)) params.set(key,current.searchParams.get(key));
        window.location.href = '/plans?' + params;
    });
    render('plans','doughnut',['Terpenuhi','Sisa kuota'],[
        {data:[d.realized, Math.max(0,d.quota-d.realized)],backgroundColor:[mint,'#eaf0f5'],borderWidth:0,borderRadius:5}
    ],planOptions,d.quota > 0);

    const miniOptions = () => {
        const options=base();options.interaction={mode:'nearest',intersect:false,axis:'x'};options.plugins.legend.display=false;options.scales={x:{display:false,stacked:true,min:0,max:100},y:{display:false,stacked:true}};options.indexAxis='y';
        options.plugins.tooltip.callbacks.label=c=>c.dataset.label+': '+money(c.dataset.amount)+' ('+c.raw.toFixed(1)+'%)';
        return options;
    };
    const overdueOptions=miniOptions();
    clickable(overdueOptions,()=>window.openDashboardDetails(true));
    charts.push(new Chart(document.getElementById('d2-insight-overdue'),{type:'bar',data:{labels:['Saldo piutang'],datasets:[
        {label:'Lewat tenggat',amount:d.overdue,data:[d.outstanding>0?d.overdue/d.outstanding*100:0],backgroundColor:red,borderRadius:4,barPercentage:.7},
        {label:'Belum lewat / tanpa tenggat',amount:Math.max(0,d.outstanding-d.overdue),data:[d.outstanding>0?(d.outstanding-d.overdue)/d.outstanding*100:0],backgroundColor:'#c2dce8',borderRadius:4,barPercentage:.7}
    ]},options:overdueOptions}));
    const debtors=[...d.debtors];
    const remaining=Math.max(0,d.outstanding-debtors.reduce((sum,row)=>sum+row.outstanding,0));
    if(remaining>0)debtors.push({name:'Perusahaan lainnya',outstanding:remaining,id:null});
    const debtorOptions=miniOptions();clickable(debtorOptions,p=>debtors[p.datasetIndex].id?jump({company_id:debtors[p.datasetIndex].id}):window.openDashboardDetails(true));
    charts.push(new Chart(document.getElementById('d2-insight-debtors'),{type:'bar',data:{labels:['Piutang'],datasets:debtors.map((row,i)=>({label:row.name,amount:row.outstanding,data:[d.outstanding>0?row.outstanding/d.outstanding*100:0],backgroundColor:['#8b73db','#5489ed','#17b99b','#e8b45a','#d7e0ea'][i],borderWidth:1,borderColor:'#fff',barPercentage:.7}))},options:debtorOptions}));
    const momentumOptions=base();momentumOptions.scales={x:{display:false},y:{display:false,beginAtZero:true}};momentumOptions.interaction={mode:'index',intersect:false};
    momentumOptions.plugins.tooltip.callbacks.title=items=>d.daily[items[0].dataIndex].date;
    clickable(momentumOptions,p=>jump({tanggal:d.daily[p.index].date}));
    const peakValue=Math.max(0,...d.daily.map(row=>row.value));
    charts.push(new Chart(document.getElementById('d2-insight-momentum'),{type:'line',data:{labels:d.daily.map(row=>row.date),datasets:[{label:'Nilai harian',data:d.daily.map(row=>row.value),borderColor:mint,backgroundColor:'rgba(23,185,155,.08)',fill:true,borderWidth:1.5,tension:.2,pointRadius:d.daily.map(row=>row.value>0&&row.value===peakValue?3:0),pointBackgroundColor:'#db9e36',pointHoverRadius:4}]},options:momentumOptions}));
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
        dialog.querySelector('header p').textContent = document.getElementById('d2-month').value + ' – ' + document.getElementById('d2-month-end').value + ' · ' + [...dialog.querySelectorAll('tbody tr[data-status]')].filter(row => !row.hidden).length + ' baris ditampilkan (maks. 50 nilai terbesar dari fokus terpilih).';
        dialog.showModal();
    };
    function collapse() {
        const panel = document.querySelector('.d2-expandable.is-expanded');
        if (!panel) return;
        panel.classList.remove('is-expanded');
        panel.querySelector('[data-expand-chart]').setAttribute('aria-expanded','false');
        panel.querySelector('[data-expand-chart]').textContent='⛶';
        panel.querySelector('[data-expand-chart]').title='Perbesar kartu';
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
            const url = new URL(window.location.href); const start=document.getElementById('d2-month').value,end=document.getElementById('d2-month-end').value; url.search='';url.searchParams.set('bulan_awal',start);url.searchParams.set('bulan_akhir',end);load(url);
        } else if (target.hasAttribute('data-open-details')) {
            window.openDashboardDetails();
        } else if (target.hasAttribute('data-close-details')) {
            document.getElementById('d2-details').close();
        } else if (target.hasAttribute('data-expand-chart')) {
            const panel = document.getElementById('panel-' + target.dataset.expandChart);
            const expanded = panel.classList.contains('is-expanded'); collapse();
            if(!expanded) {panel.classList.add('is-expanded');target.setAttribute('aria-expanded','true');target.textContent='×';target.title='Kecilkan kartu (Esc)';}
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
            const link = document.createElement('a');link.download = 'detail-dashboard-'+document.getElementById('d2-month').value+'_'+document.getElementById('d2-month-end').value+'.csv';link.href = url;link.click();setTimeout(() => URL.revokeObjectURL(url),1000);
        }
    });
    document.addEventListener('change', event => {
        if(event.target.id !== 'd2-visual-mode') return;
        localStorage.setItem('d2-visual-mode-v2',event.target.value);
        window.initDashboardTwo();
    });
    document.addEventListener('submit', event => {
        if (!event.target.matches('.d2-filter')) return;
        event.preventDefault(); const url = new URL(window.location.href);url.searchParams.delete('bulan');url.searchParams.set('bulan_awal',document.getElementById('d2-month').value);url.searchParams.set('bulan_akhir',document.getElementById('d2-month-end').value);for(const key of ['hari','tanggal','periode'])url.searchParams.delete(key);load(url);
    });
    document.addEventListener('keydown', event => {if(event.key === 'Escape') collapse();});
    const hideTooltip=()=>{const tooltip=document.getElementById('d2-floating-tooltip');if(tooltip)tooltip.hidden=true;};
    document.addEventListener('scroll',hideTooltip,true);
    window.addEventListener('resize',hideTooltip);
    document.addEventListener('keydown',event=>{if(event.key==='Escape')hideTooltip();});
    window.addEventListener('popstate', () => load(window.location.href,false));
    window.initDashboardTwo();
})();
