// ============================================================
// src/components/Dashboard.jsx
// ============================================================

function Dashboard({ products, onViewProduct, onGoCategory }) {
  const stats = React.useMemo(() => {
    const byCategory = {};
    CATEGORIES.filter(c => c.id !== 'all').forEach(c => {
      byCategory[c.id] = { count: 0, minPrice: Infinity, maxPrice: 0, label: c.label };
    });
    products.forEach(p => {
      if (byCategory[p.category]) {
        byCategory[p.category].count++;
        if (p.price) {
          byCategory[p.category].minPrice = Math.min(byCategory[p.category].minPrice, p.price);
          byCategory[p.category].maxPrice = Math.max(byCategory[p.category].maxPrice, p.price);
        }
      }
    });
    return byCategory;
  }, [products]);

  const avgPrice = products.filter(p => p.price).reduce((s, p) => s + p.price, 0) / (products.filter(p => p.price).length || 1);
  const maxPrice = Math.max(...products.map(p => p.price || 0));
  const recent   = [...products].sort((a, b) =>
    (b.editHistory?.slice(-1)[0]?.date || '').localeCompare(a.editHistory?.slice(-1)[0]?.date || '')
  ).slice(0, 6);

  const fmt = n => n ? n.toLocaleString('th-TH', { maximumFractionDigits: 0 }) : '—';

  return (
    <div style={dashSt.page}>
      <div style={dashSt.pageHeader}>
        <h2 style={dashSt.pageTitle}>ภาพรวมระบบ</h2>
        <span style={dashSt.pageDate}>ข้อมูล ณ วันที่ 21 พฤษภาคม 2569</span>
      </div>

      {/* KPI cards */}
      <div style={dashSt.kpiGrid}>
        <KpiCard
          color="#1B3A6B" bg="#EFF6FF"
          icon={<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>}
          num={products.length}
          unit="รายการ"
          label="สินค้าทั้งหมด"
        />
        <KpiCard
          color="#059669" bg="#F0FFF4"
          icon={<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>}
          num={fmt(avgPrice)}
          unit="บาท"
          label="ราคากลางเฉลี่ย"
        />
        <KpiCard
          color="#7C3AED" bg="#FAF5FF"
          icon={<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>}
          num={Object.values(stats).filter(s => s.count > 0).length}
          unit="หมวด"
          label="ประเภทสินค้า"
        />
        <KpiCard
          color="#DC2626" bg="#FFF5F5"
          icon={<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>}
          num={fmt(maxPrice)}
          unit="บาท"
          label="ราคาสูงสุด"
        />
      </div>

      <div style={dashSt.twoCol}>
        {/* Category breakdown */}
        <div style={dashSt.panel}>
          <div style={dashSt.panelHead}>
            <h3 style={dashSt.panelTitle}>จำนวนสินค้าตามหมวดหมู่</h3>
          </div>
          <div style={dashSt.catList}>
            {CATEGORIES.filter(c => c.id !== 'all').map(cat => {
              const s = stats[cat.id];
              if (!s || s.count === 0) return null;
              const pct = (s.count / products.length) * 100;
              return (
                <div key={cat.id} style={dashSt.catRow} onClick={() => onGoCategory(cat.id)}>
                  <div style={{ ...dashSt.catDot, background: CAT_COLORS[cat.id] || '#64748B' }}></div>
                  <div style={dashSt.catMid}>
                    <div style={dashSt.catName}>{cat.label}</div>
                    <div style={dashSt.catBar}>
                      <div style={{ ...dashSt.catFill, width: pct + '%', background: CAT_COLORS[cat.id] || '#64748B' }}></div>
                    </div>
                  </div>
                  <div style={dashSt.catRight}>
                    <div style={dashSt.catCount}>{s.count}</div>
                    {s.minPrice !== Infinity && (
                      <div style={dashSt.catPrice}>{fmt(s.minPrice)}–{fmt(s.maxPrice)} ฿</div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Recent */}
        <div style={dashSt.panel}>
          <div style={dashSt.panelHead}>
            <h3 style={dashSt.panelTitle}>รายการล่าสุด</h3>
          </div>
          <div style={dashSt.recentList}>
            {recent.map((p, i) => (
              <div key={p.id} style={dashSt.recentRow} onClick={() => onViewProduct(p)}>
                <div style={{ ...dashSt.recentNum, color: CAT_COLORS[p.category] || '#64748B' }}>{String(i + 1).padStart(2, '0')}</div>
                <div style={dashSt.recentBody}>
                  <div style={dashSt.recentCat}>
                    <span style={{ ...dashSt.recentBadge, background: (CAT_COLORS[p.category] || '#64748B') + '18', color: CAT_COLORS[p.category] || '#64748B' }}>
                      {CATEGORIES.find(c => c.id === p.category)?.label}
                    </span>
                    <span style={dashSt.recentBrand}>{p.brand}</span>
                  </div>
                  <div style={dashSt.recentModel}>{p.model}</div>
                </div>
                <div style={dashSt.recentPrice}>{fmt(p.price)} ฿</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

function KpiCard({ color, bg, icon, num, unit, label }) {
  return (
    <div style={dashSt.kpiCard}>
      <div style={{ ...dashSt.kpiIcon, background: bg, color }}>
        {icon}
      </div>
      <div style={dashSt.kpiRight}>
        <div style={{ ...dashSt.kpiNum, color }}>
          {num} <span style={dashSt.kpiUnit}>{unit}</span>
        </div>
        <div style={dashSt.kpiLabel}>{label}</div>
      </div>
    </div>
  );
}

const dashSt = {
  page:       { padding:'28px 28px 40px' },
  pageHeader: { display:'flex', alignItems:'baseline', gap:'16px', marginBottom:'24px' },
  pageTitle:  { fontSize:'22px', fontWeight:'800', color:'#1E293B', margin:0 },
  pageDate:   { fontSize:'13px', color:'#94A3B8' },
  kpiGrid:    { display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:'16px', marginBottom:'24px' },
  kpiCard:    { background:'white', borderRadius:'14px', padding:'20px', border:'1px solid #E2E8F0', display:'flex', gap:'16px', alignItems:'center' },
  kpiIcon:    { width:'48px', height:'48px', borderRadius:'12px', display:'flex', alignItems:'center', justifyContent:'center', flexShrink:0 },
  kpiRight:   {},
  kpiNum:     { fontSize:'26px', fontWeight:'800', lineHeight:'1', marginBottom:'4px' },
  kpiUnit:    { fontSize:'14px', fontWeight:'600' },
  kpiLabel:   { fontSize:'12px', color:'#94A3B8', fontWeight:'500' },
  twoCol:     { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'16px' },
  panel:      { background:'white', borderRadius:'14px', border:'1px solid #E2E8F0', overflow:'hidden' },
  panelHead:  { padding:'18px 20px 14px', borderBottom:'1px solid #F1F5F9' },
  panelTitle: { fontSize:'14px', fontWeight:'800', color:'#1E293B', margin:0 },
  catList:    { padding:'8px 12px 12px', display:'flex', flexDirection:'column', gap:'2px' },
  catRow:     { display:'flex', alignItems:'center', gap:'12px', padding:'10px 8px', borderRadius:'8px', cursor:'pointer' },
  catDot:     { width:'10px', height:'10px', borderRadius:'50%', flexShrink:0 },
  catMid:     { flex:1, minWidth:0 },
  catName:    { fontSize:'13px', fontWeight:'600', color:'#374151', marginBottom:'5px' },
  catBar:     { height:'5px', background:'#F1F5F9', borderRadius:'3px', overflow:'hidden' },
  catFill:    { height:'100%', borderRadius:'3px', transition:'width 0.5s ease' },
  catRight:   { textAlign:'right', flexShrink:0 },
  catCount:   { fontSize:'16px', fontWeight:'800', color:'#1E293B' },
  catPrice:   { fontSize:'10px', color:'#94A3B8', marginTop:'1px' },
  recentList: { display:'flex', flexDirection:'column' },
  recentRow:  { display:'flex', alignItems:'center', gap:'14px', padding:'12px 20px', borderBottom:'1px solid #F8FAFC', cursor:'pointer' },
  recentNum:  { fontSize:'18px', fontWeight:'800', width:'28px', flexShrink:0 },
  recentBody: { flex:1, minWidth:0 },
  recentCat:  { display:'flex', alignItems:'center', gap:'8px', marginBottom:'3px' },
  recentBadge:{ fontSize:'11px', fontWeight:'700', padding:'2px 7px', borderRadius:'4px' },
  recentBrand:{ fontSize:'12px', color:'#64748B' },
  recentModel:{ fontSize:'13px', fontWeight:'700', color:'#1E293B', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' },
  recentPrice:{ fontSize:'14px', fontWeight:'800', color:'#059669', flexShrink:0 },
};

Object.assign(window, { Dashboard });
