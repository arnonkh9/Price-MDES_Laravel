// ============================================================
// src/components/VendorCompare.jsx
// VendorCompareList + VendorCompareDetail + VendorCompareForm
// ============================================================

const V_STATUSES = { draft: { label: 'ร่าง', color: '#94A3B8', bg: '#F1F5F9' }, final: { label: 'สรุปแล้ว', color: '#059669', bg: '#F0FFF4' } };

// ─────────────────────────────────────────────────────────────
// Excel Export Helper
// ─────────────────────────────────────────────────────────────
function exportComparisonExcel(cmp, allSpecs) {
  if (!window.XLSX) { alert('กำลังโหลด SheetJS...'); return; }

  const spec = allSpecs.find(s => s.id === cmp.specId);
  const v    = cmp.vendors || [];
  const fmt  = n => n ? Number(n).toLocaleString('th-TH') : '';

  const rows = [];

  // Title block
  rows.push(['ตารางเปรียบเทียบราคาและคุณลักษณะ 3 เจ้า', '', '', '', '']);
  rows.push([`ชื่อการเปรียบเทียบ: ${cmp.name}`, '', '', '', '']);
  rows.push([`ประเภท: ${CATEGORIES.find(c => c.id === cmp.category)?.label || cmp.category}`, `ปี พ.ศ.: ${cmp.year}`, `เดือน: ${cmp.month ? THAI_MONTHS[parseInt(cmp.month,10)-1] : '-'}`, `สร้างโดย: ${cmp.createdBy}`, `วันที่: ${cmp.createdDate}`]);
  if (spec) rows.push([`คุณลักษณะเฉพาะอ้างอิง: ${spec.name}`, `วงเงิน: ${fmt(spec.budget)} บาท`, '', '', '']);
  rows.push([]);

  // Vendor header
  rows.push(['รายการ / ข้อกำหนด', spec ? 'คุณลักษณะเฉพาะ' : '', v[0]?.name || 'เจ้าที่ 1', v[1]?.name || 'เจ้าที่ 2', v[2]?.name || 'เจ้าที่ 3']);
  rows.push(['แบรนด์',  '', `${v[0]?.brand || ''}`, `${v[1]?.brand || ''}`, `${v[2]?.brand || ''}`]);
  rows.push(['รุ่น / โมเดล', '', v[0]?.model || '', v[1]?.model || '', v[2]?.model || '']);
  rows.push(['ราคาเสนอ (บาท)', spec ? `วงเงิน ${fmt(spec.budget)} ฿` : '', fmt(v[0]?.price), fmt(v[1]?.price), fmt(v[2]?.price)]);
  rows.push([]);

  // Spec rows grouped
  SPEC_GROUPS.forEach(group => {
    const activeFields = group.fields.filter(f =>
      (spec?.specs?.[f]) || v.some(vd => vd?.specs?.[f])
    );
    if (activeFields.length === 0) return;

    rows.push([`[${group.label}]`, '', '', '', '']);
    activeFields.forEach(field => {
      rows.push([
        field,
        spec?.specs?.[field] || '',
        v[0]?.specs?.[field] || '',
        v[1]?.specs?.[field] || '',
        v[2]?.specs?.[field] || '',
      ]);
    });
    rows.push([]);
  });

  // Summary
  rows.push(['สรุปผล', '', '', '', '']);
  const priceRow = ['ราคาเสนอ (บาท)', '', fmt(v[0]?.price), fmt(v[1]?.price), fmt(v[2]?.price)];
  rows.push(priceRow);

  const minPrice = Math.min(...v.map(vd => vd?.price || Infinity).filter(p => p < Infinity));
  const winner   = v.find(vd => vd?.price === minPrice);
  rows.push(['ราคาต่ำสุด', '', v[0]?.id === winner?.id ? '✓ ต่ำสุด' : '', v[1]?.id === winner?.id ? '✓ ต่ำสุด' : '', v[2]?.id === winner?.id ? '✓ ต่ำสุด' : '']);
  if (cmp.notes) rows.push(['หมายเหตุ', cmp.notes]);

  // Build workbook
  const ws = XLSX.utils.aoa_to_sheet(rows);

  // Column widths
  ws['!cols'] = [{ wch: 30 }, { wch: 36 }, { wch: 34 }, { wch: 34 }, { wch: 34 }];

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'เปรียบเทียบ 3 เจ้า');
  XLSX.writeFile(wb, `เปรียบเทียบ_${cmp.name}.xlsx`);
}

// ─────────────────────────────────────────────────────────────
// List View
// ─────────────────────────────────────────────────────────────
function VendorCompareList({ comparisons, specs, onView, onEdit, onDelete, onExcel, user }) {
  const [selYear,  setSelYear]  = React.useState('all');
  const [selMonth, setSelMonth] = React.useState('all');
  const [hovered,  setHovered]  = React.useState(null);

  const getYear  = c => c.year  || (c.createdDate || '').substring(0,4) || '2569';
  const getMonth = c => c.month || '';

  const availYears  = [...new Set(comparisons.map(getYear))].sort().reverse();
  const availMonths = React.useMemo(() => {
    const src = selYear === 'all' ? comparisons : comparisons.filter(c => getYear(c) === selYear);
    return [...new Set(src.map(getMonth).filter(Boolean))].sort();
  }, [comparisons, selYear]);

  const filtered = comparisons.filter(c => {
    if (selYear  !== 'all' && getYear(c)  !== selYear)  return false;
    if (selMonth !== 'all' && getMonth(c) !== selMonth) return false;
    return true;
  });

  const periodLabel = selYear === 'all' ? 'ทุกช่วงเวลา'
    : selMonth === 'all' ? `ปี ${selYear}`
    : `${THAI_MONTHS[parseInt(selMonth,10)-1]} ${selYear}`;

  return (
    <div style={vcSt.page}>
      <div style={vcSt.toolbar}>
        <div>
          <h2 style={vcSt.pageTitle}>เทียบราคา 3 เจ้า</h2>
          <p style={vcSt.pageCount}>{filtered.length} รายการ · {periodLabel}</p>
        </div>
        {user.role === 'admin' && (
          <button style={vcSt.addBtn} onClick={() => onEdit(null)}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            สร้างการเปรียบเทียบใหม่
          </button>
        )}
      </div>

      {/* Year filter */}
      <div style={vcSt.filterRow}>
        <span style={vcSt.filterLabel}>ปี พ.ศ.:</span>
        {['all', ...availYears].map(y => (
          <button key={y}
            style={{ ...vcSt.pill, ...(selYear === y ? vcSt.pillOn : {}) }}
            onClick={() => { setSelYear(y); setSelMonth('all'); }}>
            {y === 'all' ? 'ทั้งหมด' : `ปี ${y}`}
            {y !== 'all' && <span style={vcSt.pillCnt}>{comparisons.filter(c => getYear(c) === y).length}</span>}
          </button>
        ))}
      </div>
      {selYear !== 'all' && availMonths.length > 0 && (
        <div style={vcSt.filterRow}>
          <span style={vcSt.filterLabel}>เดือน:</span>
          <button style={{ ...vcSt.pill, ...(selMonth === 'all' ? vcSt.pillOn : {}) }} onClick={() => setSelMonth('all')}>ทุกเดือน</button>
          {availMonths.map(m => (
            <button key={m} style={{ ...vcSt.pill, ...(selMonth === m ? vcSt.pillOn : {}) }} onClick={() => setSelMonth(m)}>
              {THAI_MONTHS[parseInt(m,10)-1]}
              <span style={vcSt.pillCnt}>{comparisons.filter(c => getYear(c) === selYear && getMonth(c) === m).length}</span>
            </button>
          ))}
        </div>
      )}

      {filtered.length === 0 && (
        <div style={vcSt.empty}>
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" strokeWidth="1.5" strokeLinecap="round">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
          </svg>
          <p style={{ color: '#94A3B8', marginTop: '12px' }}>ยังไม่มีรายการเปรียบเทียบ</p>
        </div>
      )}

      <div style={vcSt.grid}>
        {filtered.map(cmp => {
          const color   = CAT_COLORS[cmp.category] || '#64748B';
          const st      = V_STATUSES[cmp.status] || V_STATUSES.draft;
          const linkSpec = specs.find(s => s.id === cmp.specId);
          const minPrice = Math.min(...(cmp.vendors || []).map(v => v.price || Infinity).filter(p => p < Infinity));
          const isHov   = hovered === cmp.id;

          return (
            <div key={cmp.id}
              style={{ ...vcSt.card, ...(isHov ? vcSt.cardHov : {}) }}
              onMouseEnter={() => setHovered(cmp.id)}
              onMouseLeave={() => setHovered(null)}>

              <div style={{ ...vcSt.cardTop, background: color }}>
                <span style={vcSt.cardCat}>{CATEGORIES.find(c => c.id === cmp.category)?.label}</span>
                <span style={{ ...vcSt.statusBadge, background: st.bg, color: st.color }}>{st.label}</span>
              </div>

              <div style={vcSt.cardBody}>
                <div style={vcSt.cardName}>{cmp.name}</div>
                {linkSpec && <div style={vcSt.cardSpec}>สเปคอ้างอิง: {linkSpec.name}</div>}

                <div style={vcSt.vendorList}>
                  {(cmp.vendors || []).map((v, i) => (
                    <div key={v.id} style={vcSt.vendorRow}>
                      <span style={vcSt.vendorNum}>{i + 1}</span>
                      <span style={vcSt.vendorName}>{v.name}</span>
                      <span style={vcSt.vendorPrice}>{v.price ? v.price.toLocaleString('th-TH') + ' ฿' : '—'}</span>
                      {v.price === minPrice && <span style={vcSt.lowestTag}>ต่ำสุด</span>}
                    </div>
                  ))}
                </div>

                <div style={vcSt.cardMeta}>
                  <span style={vcSt.metaTxt}>{cmp.year}{cmp.month ? '/' + cmp.month : ''}</span>
                  <span style={vcSt.metaTxt}>สร้างโดย {cmp.createdBy}</span>
                </div>
              </div>

              <div style={vcSt.cardFooter}>
                <button style={vcSt.btnView} onClick={() => onView(cmp)}>ดูรายงาน</button>
                <button style={{ ...vcSt.btnExcel, background: color }} onClick={() => onExcel(cmp)}>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  Excel
                </button>
                {user.role === 'admin' && (
                  <div style={{ display: 'flex', gap: '5px', marginLeft: 'auto' }}>
                    <ActionBtn title="แก้ไข" color="#D97706" onClick={() => onEdit(cmp)}>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </ActionBtn>
                    <ActionBtn title="ลบ" color="#DC2626" onClick={() => onDelete(cmp.id)}>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </ActionBtn>
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Detail Modal
// ─────────────────────────────────────────────────────────────
function VendorCompareDetail({ cmp, specs, onClose, onEdit, onExcel, user }) {
  if (!cmp) return null;
  const spec  = specs.find(s => s.id === cmp.specId);
  const color = CAT_COLORS[cmp.category] || '#64748B';
  const v     = cmp.vendors || [];
  const fmt   = n => n ? Number(n).toLocaleString('th-TH') + ' ฿' : '—';
  const minP  = Math.min(...v.map(vd => vd?.price || Infinity).filter(p => p < Infinity));

  const tableRows = [];
  SPEC_GROUPS.forEach(group => {
    const fields = group.fields.filter(f => (spec?.specs?.[f]) || v.some(vd => vd?.specs?.[f]));
    if (fields.length === 0) return;
    tableRows.push({ type: 'group', label: group.label });
    fields.forEach(f => tableRows.push({ type: 'field', field: f }));
  });

  return (
    <div style={vdSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={vdSt.modal}>
        {/* Header */}
        <div style={{ ...vdSt.header, borderTop: `4px solid ${color}` }}>
          <div style={{ flex: 1 }}>
            <div style={{ display: 'flex', gap: '10px', alignItems: 'center', marginBottom: '6px' }}>
              <span style={{ ...vdSt.catBadge, background: color }}>{CATEGORIES.find(c => c.id === cmp.category)?.label}</span>
              <span style={vdSt.docLabel}>เทียบราคา 3 เจ้า</span>
              <span style={{ ...vdSt.statusBadge2, background: (V_STATUSES[cmp.status]?.bg || '#F1F5F9'), color: V_STATUSES[cmp.status]?.color || '#64748B' }}>{V_STATUSES[cmp.status]?.label}</span>
            </div>
            <h2 style={vdSt.title}>{cmp.name}</h2>
            <div style={vdSt.meta}>
              {spec && <span style={vdSt.specRef}>สเปคอ้างอิง: {spec.name}</span>}
              <span style={vdSt.metaTxt}>ปี {cmp.year}{cmp.month ? ' / ' + THAI_MONTHS[parseInt(cmp.month,10)-1] : ''}</span>
              <span style={vdSt.metaTxt}>สร้างโดย {cmp.createdBy} · {cmp.createdDate}</span>
            </div>
          </div>
          <div style={{ display: 'flex', gap: '7px', flexShrink: 0 }}>
            {user?.role === 'admin' && (
              <HdrBtn color="#D97706" bg="#FFFBEB" border="#FDE68A" onClick={() => onEdit(cmp)}>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                แก้ไข
              </HdrBtn>
            )}
            <button style={{ ...vdSt.excelBtn, background: '#059669' }} onClick={() => onExcel(cmp)}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Export Excel
            </button>
            <button style={vdSt.closeBtn} onClick={onClose}>
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
        </div>

        {/* Comparison table */}
        <div style={vdSt.body}>
          <div style={vdSt.tableWrap}>
            <table style={vdSt.table}>
              <colgroup>
                <col style={{ width: '180px' }}/>
                {spec && <col style={{ minWidth: '200px' }}/>}
                {v.map(vd => <col key={vd.id} style={{ minWidth: '200px' }}/>)}
              </colgroup>
              <thead>
                <tr>
                  <th style={vdSt.cornerTh}>รายการ</th>
                  {spec && <th style={{ ...vdSt.specTh, borderTop: `4px solid #7C3AED` }}>
                    <div style={vdSt.thCard}>
                      <span style={{ ...vdSt.thTag, background: '#7C3AED' }}>📋 สเปคอ้างอิง</span>
                      <div style={vdSt.thName}>{spec.name}</div>
                      {spec.budget > 0 && <div style={vdSt.thPrice}>{spec.budget.toLocaleString('th-TH')} ฿</div>}
                      <div style={{ fontSize: '11px', color: '#94A3B8' }}>วงเงินงบประมาณ</div>
                    </div>
                  </th>}
                  {v.map((vd, i) => (
                    <th key={vd.id} style={{ ...vdSt.vendorTh, borderTop: `4px solid ${['#2563EB','#059669','#DC2626'][i] || color}` }}>
                      <div style={vdSt.thCard}>
                        <span style={{ ...vdSt.thTag, background: ['#2563EB','#059669','#DC2626'][i] || color }}>เจ้าที่ {i+1}</span>
                        <div style={vdSt.thVendor}>{vd.name}</div>
                        <div style={vdSt.thBrand}>{vd.brand} — {vd.model}</div>
                        <div style={{ ...vdSt.thPrice, color: vd.price === minP ? '#059669' : '#1E293B' }}>
                          {fmt(vd.price)}
                          {vd.price === minP && <span style={vdSt.lowestBadge}>ต่ำสุด</span>}
                        </div>
                      </div>
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {tableRows.map((row, i) => {
                  if (row.type === 'group') return (
                    <tr key={i} style={{ background: '#F1F5F9' }}>
                      <td colSpan={(spec ? 1 : 0) + v.length + 1} style={vdSt.groupCell}>{row.label}</td>
                    </tr>
                  );
                  return (
                    <tr key={i} style={{ borderBottom: '1px solid #F8FAFC' }}>
                      <td style={vdSt.fieldCell}>{row.field}</td>
                      {spec && (
                        <td style={{ ...vdSt.valCell, background: '#FAF5FF', borderLeft: '2px solid #DDD6FE' }}>
                          {spec.specs?.[row.field]
                            ? <><span style={vdSt.reqLabel}>ข้อกำหนด</span>{String(spec.specs[row.field]).split('\n').map((ln,li) => <div key={li} style={{ color: '#4C1D95', fontSize: '12px', lineHeight: '1.5' }}>{ln}</div>)}</>
                            : <span style={{ color: '#C4B5FD' }}>—</span>}
                        </td>
                      )}
                      {v.map((vd, vi) => (
                        <td key={vd.id} style={vdSt.valCell}>
                          {vd.specs?.[row.field]
                            ? String(vd.specs[row.field]).split('\n').map((ln,li) => <div key={li} style={{ lineHeight: '1.5' }}>{ln}</div>)
                            : <span style={{ color: '#CBD5E1' }}>—</span>}
                        </td>
                      ))}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          {cmp.notes && (
            <div style={vdSt.notes}><strong>หมายเหตุ:</strong> {cmp.notes}</div>
          )}
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Form Modal
// ─────────────────────────────────────────────────────────────
function VendorCompareForm({ cmp, specs, onSave, onClose, user }) {
  const isEdit = !!cmp;

  const blankVendor = id => ({ id, name: '', brand: '', model: '', price: '', specs: {} });
  const blank = {
    id: 'cmp-' + Date.now(), name: '', category: 'Notebook',
    year: '2569', month: '05', specId: '', notes: '', status: 'draft',
    createdDate: '2569-05-21', createdBy: user.name,
    vendors: [blankVendor('v1'), blankVendor('v2'), blankVendor('v3')],
    editHistory: [],
  };

  const [form, setForm] = React.useState(() => isEdit
    ? { ...cmp, vendors: (cmp.vendors || []).map(v => ({ ...v, specs: { ...v.specs } })) }
    : blank);
  const [tab, setTab] = React.useState('info');
  const [errors, setErrors] = React.useState({});

  const set       = (k, v) => setForm(f => ({ ...f, [k]: v }));
  const setVendor = (vid, k, v) => setForm(f => ({
    ...f, vendors: f.vendors.map(vd => vd.id === vid ? { ...vd, [k]: v } : vd)
  }));
  const setVSpec  = (vid, field, v) => setForm(f => ({
    ...f, vendors: f.vendors.map(vd => vd.id === vid ? { ...vd, specs: { ...vd.specs, [field]: v } } : vd)
  }));

  const validate = () => {
    const e = {};
    if (!form.name.trim()) e.name = 'กรุณากรอกชื่อ';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSave = () => {
    if (!validate()) { setTab('info'); return; }
    const entry = { date: form.createdDate, user: user.name, action: isEdit ? 'แก้ไข' : 'สร้างใหม่', detail: form.name };
    onSave({ ...form, vendors: form.vendors.map(v => ({ ...v, price: parseFloat(v.price) || 0 })), editHistory: [...(form.editHistory || []), entry] });
  };

  const tabs = [
    { id: 'info', label: 'ข้อมูลทั่วไป' },
    { id: 'v1',   label: `เจ้าที่ 1` },
    { id: 'v2',   label: `เจ้าที่ 2` },
    { id: 'v3',   label: `เจ้าที่ 3` },
  ];

  const curVendor = form.vendors.find(v => v.id === tab);

  return (
    <div style={vfSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={vfSt.modal}>
        <div style={vfSt.header}>
          <div>
            <h2 style={vfSt.title}>{isEdit ? 'แก้ไขการเปรียบเทียบ' : 'สร้างการเปรียบเทียบใหม่'}</h2>
            <p style={vfSt.subtitle}>บันทึกข้อมูลและราคาจาก 3 เจ้า</p>
          </div>
          <button style={vfSt.closeBtn} onClick={onClose}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        {/* Tab nav */}
        <div style={vfSt.tabs}>
          {tabs.map((t, i) => {
            const vd  = form.vendors.find(v => v.id === t.id);
            const color = ['#2563EB','#059669','#DC2626'][i-1] || '#1B3A6B';
            return (
              <button key={t.id}
                style={{ ...vfSt.tab, ...(tab === t.id ? { ...vfSt.tabOn, borderBottomColor: color, color } : {}) }}
                onClick={() => setTab(t.id)}>
                {t.label}
                {vd?.name && <span style={{ fontSize: '11px', color: '#94A3B8', marginLeft: '4px' }}>({vd.name.substring(0,8)}...)</span>}
              </button>
            );
          })}
        </div>

        <div style={vfSt.body}>
          {/* Info tab */}
          {tab === 'info' && (
            <div>
              <VF label="ชื่อการเปรียบเทียบ *" error={errors.name}>
                <input value={form.name} onChange={e => set('name', e.target.value)}
                  placeholder="เช่น เปรียบเทียบ Notebook สำนักงาน ปี 2569" style={vfSt.input}/>
              </VF>
              <div style={vfSt.row2}>
                <VF label="ประเภทสินค้า">
                  <select value={form.category} onChange={e => set('category', e.target.value)} style={vfSt.select}>
                    {CATEGORIES.filter(c => c.id !== 'all').map(c => <option key={c.id} value={c.id}>{c.label}</option>)}
                  </select>
                </VF>
                <VF label="สถานะ">
                  <select value={form.status} onChange={e => set('status', e.target.value)} style={vfSt.select}>
                    <option value="draft">ร่าง</option>
                    <option value="final">สรุปแล้ว</option>
                  </select>
                </VF>
              </div>
              <div style={vfSt.row2}>
                <VF label="ปี พ.ศ.">
                  <select value={form.year} onChange={e => set('year', e.target.value)} style={vfSt.select}>
                    {YEAR_OPTIONS.map(y => <option key={y} value={y}>ปี {y}</option>)}
                  </select>
                </VF>
                <VF label="เดือน">
                  <select value={form.month || ''} onChange={e => set('month', e.target.value)} style={vfSt.select}>
                    <option value="">— ไม่ระบุ —</option>
                    {THAI_MONTHS.map((mn,i) => {
                      const val = String(i+1).padStart(2,'0');
                      return <option key={val} value={val}>{mn}</option>;
                    })}
                  </select>
                </VF>
              </div>
              <VF label="คุณลักษณะเฉพาะอ้างอิง (ถ้ามี)">
                <select value={form.specId} onChange={e => set('specId', e.target.value)} style={vfSt.select}>
                  <option value="">— ไม่ผูกกับสเปค —</option>
                  {specs.filter(s => s.category === form.category || !form.category).map(s => (
                    <option key={s.id} value={s.id}>{s.name}</option>
                  ))}
                </select>
              </VF>
              <VF label="หมายเหตุ">
                <textarea value={form.notes} onChange={e => set('notes', e.target.value)}
                  rows={3} placeholder="หมายเหตุเพิ่มเติม..." style={vfSt.textarea}/>
              </VF>
            </div>
          )}

          {/* Vendor tabs */}
          {curVendor && (
            <div>
              <div style={vfSt.vendorHead}>ข้อมูลบริษัท / ผู้ขาย</div>
              <VF label="ชื่อบริษัท / ร้านค้า *">
                <input value={curVendor.name} onChange={e => setVendor(curVendor.id,'name',e.target.value)}
                  placeholder="เช่น บริษัท เอบีซี จำกัด" style={vfSt.input}/>
              </VF>
              <div style={vfSt.row2}>
                <VF label="แบรนด์">
                  <input value={curVendor.brand} onChange={e => setVendor(curVendor.id,'brand',e.target.value)}
                    placeholder="เช่น ASUS" style={vfSt.input}/>
                </VF>
                <VF label="ราคาเสนอ (บาท)">
                  <input type="number" value={curVendor.price} onChange={e => setVendor(curVendor.id,'price',e.target.value)}
                    placeholder="0" style={vfSt.input}/>
                </VF>
              </div>
              <VF label="รุ่น / โมเดล">
                <input value={curVendor.model} onChange={e => setVendor(curVendor.id,'model',e.target.value)}
                  placeholder="เช่น Vivobook 16 (X1607CA)" style={vfSt.input}/>
              </VF>

              <div style={{ ...vfSt.vendorHead, marginTop: '20px' }}>ข้อมูลจำเพาะที่เสนอ</div>
              <div style={vfSt.groupHint}>กรอกสเปคจริงที่เจ้านี้เสนอในแต่ละรายการ</div>
              {SPEC_GROUPS.map(group => (
                <div key={group.id} style={{ marginBottom: '20px' }}>
                  <div style={vfSt.groupLabel}>{group.label}</div>
                  {group.fields.map(field => (
                    <VF key={field} label={field}>
                      <textarea
                        value={curVendor.specs?.[field] || ''}
                        onChange={e => setVSpec(curVendor.id, field, e.target.value)}
                        rows={2} placeholder={`สเปคที่เสนอสำหรับ ${field}`}
                        style={vfSt.textarea}/>
                    </VF>
                  ))}
                </div>
              ))}
            </div>
          )}
        </div>

        <div style={vfSt.footer}>
          <div>
            {Object.keys(errors).length > 0 && <span style={{ color:'#DC2626', fontSize:'13px', fontWeight:'600' }}>กรุณากรอกข้อมูลที่จำเป็น</span>}
          </div>
          <div style={{ display:'flex', gap:'10px' }}>
            <button style={vfSt.cancelBtn} onClick={onClose}>ยกเลิก</button>
            <button style={vfSt.saveBtn} onClick={handleSave}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              {isEdit ? 'บันทึกการแก้ไข' : 'สร้างการเปรียบเทียบ'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function VF({ label, error, children }) {
  return (
    <div style={{ marginBottom: '14px' }}>
      <label style={{ display:'block', fontSize:'12px', fontWeight:'700', color:'#374151', marginBottom:'5px' }}>{label}</label>
      {children}
      {error && <div style={{ fontSize:'12px', color:'#DC2626', marginTop:'3px' }}>{error}</div>}
    </div>
  );
}

// ─── Styles ──────────────────────────────────────────────────
const vcSt = {
  page:       { padding:'28px' },
  toolbar:    { display:'flex', justifyContent:'space-between', alignItems:'flex-end', marginBottom:'16px' },
  pageTitle:  { fontSize:'22px', fontWeight:'800', color:'#1E293B', margin:'0 0 4px' },
  pageCount:  { color:'#64748B', fontSize:'13px', margin:0 },
  addBtn:     { display:'flex', alignItems:'center', gap:'6px', padding:'9px 18px', background:'#1B3A6B', color:'white', border:'none', borderRadius:'9px', cursor:'pointer', fontSize:'14px', fontWeight:'700', fontFamily:'Sarabun, sans-serif' },
  filterRow:  { display:'flex', alignItems:'center', gap:'6px', marginBottom:'8px', flexWrap:'wrap' },
  filterLabel:{ fontSize:'12px', fontWeight:'700', color:'#64748B', marginRight:'2px', whiteSpace:'nowrap' },
  pill:       { display:'flex', alignItems:'center', gap:'5px', padding:'5px 12px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'20px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', color:'#475569', fontWeight:'600' },
  pillOn:     { background:'#1B3A6B', borderColor:'#1B3A6B', color:'white' },
  pillCnt:    { fontSize:'11px', fontWeight:'700', background:'rgba(255,255,255,0.25)', padding:'1px 5px', borderRadius:'8px' },
  empty:      { textAlign:'center', padding:'80px 20px' },
  grid:       { display:'grid', gridTemplateColumns:'repeat(auto-fill,minmax(340px,1fr))', gap:'16px', marginTop:'16px' },
  card:       { background:'white', borderRadius:'14px', border:'1px solid #E2E8F0', overflow:'hidden', display:'flex', flexDirection:'column', transition:'box-shadow 0.2s, transform 0.15s' },
  cardHov:    { boxShadow:'0 6px 20px rgba(0,0,0,0.1)', transform:'translateY(-2px)' },
  cardTop:    { padding:'12px 16px', display:'flex', justifyContent:'space-between', alignItems:'center' },
  cardCat:    { color:'white', fontSize:'12px', fontWeight:'800' },
  statusBadge:{ fontSize:'11px', fontWeight:'700', padding:'2px 8px', borderRadius:'10px' },
  cardBody:   { padding:'16px', flex:1 },
  cardName:   { fontSize:'15px', fontWeight:'800', color:'#1E293B', marginBottom:'4px', lineHeight:'1.3' },
  cardSpec:   { fontSize:'11px', color:'#7C3AED', background:'#F5F3FF', padding:'2px 8px', borderRadius:'4px', display:'inline-block', marginBottom:'10px' },
  vendorList: { display:'flex', flexDirection:'column', gap:'5px', marginBottom:'10px' },
  vendorRow:  { display:'flex', alignItems:'center', gap:'8px' },
  vendorNum:  { width:'18px', height:'18px', borderRadius:'50%', background:'#F1F5F9', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'11px', fontWeight:'800', color:'#64748B', flexShrink:0 },
  vendorName: { fontSize:'12px', color:'#374151', fontWeight:'600', flex:1, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' },
  vendorPrice:{ fontSize:'13px', fontWeight:'800', color:'#1E293B', flexShrink:0 },
  lowestTag:  { fontSize:'10px', fontWeight:'800', color:'#059669', background:'#F0FFF4', padding:'1px 6px', borderRadius:'4px', flexShrink:0 },
  cardMeta:   { display:'flex', gap:'12px', flexWrap:'wrap' },
  metaTxt:    { fontSize:'11px', color:'#94A3B8' },
  cardFooter: { padding:'12px 16px', borderTop:'1px solid #F1F5F9', display:'flex', gap:'7px', alignItems:'center' },
  btnView:    { padding:'6px 12px', border:'1.5px solid #E2E8F0', background:'white', color:'#374151', borderRadius:'7px', cursor:'pointer', fontSize:'12px', fontFamily:'Sarabun, sans-serif', fontWeight:'600' },
  btnExcel:   { display:'flex', alignItems:'center', gap:'5px', padding:'6px 12px', border:'none', color:'white', borderRadius:'7px', cursor:'pointer', fontSize:'12px', fontFamily:'Sarabun, sans-serif', fontWeight:'700' },
};

const vdSt = {
  overlay:     { position:'fixed', inset:0, background:'rgba(15,23,42,0.55)', zIndex:200, display:'flex', alignItems:'center', justifyContent:'center', padding:'20px' },
  modal:       { background:'white', borderRadius:'16px', width:'900px', maxWidth:'100%', maxHeight:'92vh', display:'flex', flexDirection:'column', boxShadow:'0 24px 80px rgba(0,0,0,0.25)' },
  header:      { padding:'20px 26px 16px', display:'flex', justifyContent:'space-between', alignItems:'flex-start', borderBottom:'1px solid #F1F5F9', gap:'16px' },
  catBadge:    { color:'white', fontSize:'11px', fontWeight:'800', padding:'3px 8px', borderRadius:'5px' },
  docLabel:    { fontSize:'12px', fontWeight:'700', color:'#94A3B8', background:'#F1F5F9', padding:'3px 8px', borderRadius:'4px' },
  statusBadge2:{ fontSize:'11px', fontWeight:'700', padding:'3px 8px', borderRadius:'4px' },
  title:       { fontSize:'18px', fontWeight:'800', color:'#1E293B', margin:'0 0 8px' },
  meta:        { display:'flex', gap:'10px', flexWrap:'wrap', alignItems:'center' },
  specRef:     { fontSize:'12px', color:'#7C3AED', background:'#F5F3FF', padding:'2px 8px', borderRadius:'4px', fontWeight:'600' },
  metaTxt:     { fontSize:'12px', color:'#94A3B8' },
  excelBtn:    { display:'flex', alignItems:'center', gap:'6px', padding:'8px 14px', border:'none', color:'white', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', fontWeight:'700' },
  closeBtn:    { padding:'8px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'8px', cursor:'pointer', color:'#64748B', display:'flex' },
  body:        { overflowY:'auto', padding:'20px 26px', flex:1 },
  tableWrap:   { background:'white', border:'1px solid #E2E8F0', borderRadius:'12px', overflow:'auto', marginBottom:'16px' },
  table:       { width:'100%', borderCollapse:'collapse' },
  cornerTh:    { padding:'12px 14px', background:'#F8FAFC', fontSize:'12px', fontWeight:'700', color:'#64748B', borderBottom:'1px solid #E2E8F0', position:'sticky', left:0, zIndex:10 },
  specTh:      { padding:0, borderLeft:'1px solid #E2E8F0', verticalAlign:'top', background:'white' },
  vendorTh:    { padding:0, borderLeft:'1px solid #E2E8F0', verticalAlign:'top', background:'white' },
  thCard:      { padding:'14px 16px', display:'flex', flexDirection:'column', gap:'4px' },
  thTag:       { alignSelf:'flex-start', color:'white', fontSize:'10px', fontWeight:'800', padding:'2px 7px', borderRadius:'4px' },
  thName:      { fontSize:'13px', fontWeight:'800', color:'#4C1D95', lineHeight:'1.3' },
  thVendor:    { fontSize:'13px', fontWeight:'800', color:'#1E293B', lineHeight:'1.3' },
  thBrand:     { fontSize:'11px', color:'#94A3B8' },
  thPrice:     { fontSize:'18px', fontWeight:'900', display:'flex', alignItems:'center', gap:'8px' },
  lowestBadge: { fontSize:'10px', fontWeight:'800', color:'#059669', background:'#F0FFF4', padding:'2px 7px', borderRadius:'4px' },
  groupCell:   { padding:'9px 16px', fontSize:'11px', fontWeight:'800', color:'#1B3A6B', textTransform:'uppercase', letterSpacing:'0.07em', borderBottom:'1px solid #E2E8F0', position:'sticky', left:0 },
  fieldCell:   { padding:'9px 14px', fontSize:'12px', color:'#64748B', fontWeight:'600', verticalAlign:'top', background:'#FAFAFA', position:'sticky', left:0, borderRight:'1px solid #E2E8F0', whiteSpace:'nowrap' },
  valCell:     { padding:'9px 14px', fontSize:'12px', color:'#1E293B', verticalAlign:'top', borderLeft:'1px solid #F1F5F9', lineHeight:'1.5' },
  reqLabel:    { display:'block', fontSize:'10px', fontWeight:'700', color:'#7C3AED', background:'#F5F3FF', padding:'1px 5px', borderRadius:'3px', marginBottom:'3px', width:'fit-content' },
  notes:       { padding:'12px 16px', background:'#FFFBEB', borderRadius:'8px', fontSize:'13px', color:'#92400E', border:'1px solid #FDE68A' },
};

const vfSt = {
  overlay:    { position:'fixed', inset:0, background:'rgba(15,23,42,0.55)', zIndex:300, display:'flex', alignItems:'center', justifyContent:'center', padding:'20px' },
  modal:      { background:'white', borderRadius:'16px', width:'780px', maxWidth:'100%', maxHeight:'92vh', display:'flex', flexDirection:'column', boxShadow:'0 24px 80px rgba(0,0,0,0.25)' },
  header:     { padding:'20px 26px', borderBottom:'1px solid #E2E8F0', display:'flex', justifyContent:'space-between', alignItems:'flex-start' },
  title:      { fontSize:'18px', fontWeight:'800', color:'#1E293B', margin:'0 0 3px' },
  subtitle:   { fontSize:'13px', color:'#94A3B8', margin:0 },
  closeBtn:   { padding:'8px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'8px', cursor:'pointer', color:'#64748B', display:'flex' },
  tabs:       { display:'flex', borderBottom:'1px solid #E2E8F0', padding:'0 26px', overflowX:'auto' },
  tab:        { padding:'11px 18px', border:'none', background:'transparent', fontSize:'14px', fontWeight:'600', color:'#94A3B8', cursor:'pointer', borderBottom:'2px solid transparent', fontFamily:'Sarabun, sans-serif', whiteSpace:'nowrap' },
  tabOn:      { color:'#1B3A6B', borderBottomColor:'#1B3A6B' },
  body:       { overflowY:'auto', padding:'20px 26px', flex:1 },
  vendorHead: { fontSize:'14px', fontWeight:'800', color:'#1B3A6B', margin:'0 0 12px', paddingBottom:'8px', borderBottom:'1.5px solid #EFF6FF' },
  groupLabel: { fontSize:'12px', fontWeight:'800', color:'#64748B', margin:'0 0 8px', textTransform:'uppercase', letterSpacing:'0.06em' },
  groupHint:  { fontSize:'12px', color:'#94A3B8', background:'#F8FAFC', padding:'8px 12px', borderRadius:'8px', marginBottom:'14px' },
  row2:       { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'12px' },
  input:      { width:'100%', padding:'9px 12px', border:'1.5px solid #E2E8F0', borderRadius:'8px', fontSize:'14px', fontFamily:'Sarabun, sans-serif', outline:'none', boxSizing:'border-box', color:'#1E293B' },
  select:     { width:'100%', padding:'9px 12px', border:'1.5px solid #E2E8F0', borderRadius:'8px', fontSize:'14px', fontFamily:'Sarabun, sans-serif', outline:'none', background:'white' },
  textarea:   { width:'100%', padding:'9px 12px', border:'1.5px solid #E2E8F0', borderRadius:'8px', fontSize:'13px', fontFamily:'Sarabun, sans-serif', outline:'none', resize:'vertical', boxSizing:'border-box', lineHeight:'1.5' },
  footer:     { padding:'14px 26px', borderTop:'1px solid #E2E8F0', display:'flex', justifyContent:'space-between', alignItems:'center' },
  cancelBtn:  { padding:'9px 22px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'8px', cursor:'pointer', fontSize:'14px', fontFamily:'Sarabun, sans-serif', color:'#374151', fontWeight:'600' },
  saveBtn:    { display:'flex', alignItems:'center', gap:'6px', padding:'9px 22px', border:'none', background:'#1B3A6B', color:'white', borderRadius:'8px', cursor:'pointer', fontSize:'14px', fontFamily:'Sarabun, sans-serif', fontWeight:'700' },
};

Object.assign(window, { VendorCompareList, VendorCompareDetail, VendorCompareForm, exportComparisonExcel });
