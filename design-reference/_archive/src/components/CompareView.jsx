// ============================================================
// src/components/CompareView.jsx  (updated: spec baseline)
// ============================================================

function CompareView({ compareIds, products, baseSpec, onSetBaseSpec, onClearBaseSpec, onClear, onRemove, onViewProduct, specs, onGoSpecs }) {
  const [showSpecPicker, setShowSpecPicker] = React.useState(false);
  const items = compareIds.map(id => products.find(p => p.id === id)).filter(Boolean);

  if (items.length === 0 && !baseSpec) {
    return (
      <div style={cmpSt.emptyWrap}>
        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" strokeWidth="1.5" strokeLinecap="round">
          <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        <h3 style={cmpSt.emptyTitle}>ยังไม่มีสินค้าในรายการเปรียบเทียบ</h3>
        <p style={cmpSt.emptyDesc}>กลับไปที่รายการสินค้าแล้วติ๊กเลือกสินค้าที่ต้องการ (สูงสุด 3 รายการ)</p>
      </div>
    );
  }

  // All fields that have data in spec OR products
  const tableRows = [];
  SPEC_GROUPS.forEach(group => {
    const fieldsWithData = group.fields.filter(f =>
      (baseSpec && baseSpec.specs?.[f]) || items.some(p => p.specs?.[f])
    );
    if (fieldsWithData.length > 0) {
      tableRows.push({ type: 'group', label: group.label });
      fieldsWithData.forEach(f => tableRows.push({ type: 'field', field: f }));
    }
  });

  const fmt = n => n ? n.toLocaleString('th-TH') + ' ฿' : '—';
  const specColor = '#7C3AED';

  return (
    <div style={cmpSt.page}>
      {/* Toolbar */}
      <div style={cmpSt.toolbar}>
        <div>
          <h2 style={cmpSt.pageTitle}>เปรียบเทียบสินค้า</h2>
          <p style={cmpSt.pageCount}>
            {baseSpec && <span style={cmpSt.specChip}>📋 สเปคอ้างอิง: {baseSpec.name}</span>}
            {items.length > 0 && <span>{items.length} สินค้าที่เลือก</span>}
          </p>
        </div>
        <div style={cmpSt.toolbarRight}>
          {/* Spec picker */}
          <div style={{ position: 'relative' }}>
            <button style={{ ...cmpSt.specBtn, ...(baseSpec ? cmpSt.specBtnOn : {}) }}
              onClick={() => setShowSpecPicker(v => !v)}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              {baseSpec ? 'เปลี่ยนสเปคอ้างอิง' : 'เลือกสเปคอ้างอิง'}
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>
            {showSpecPicker && (
              <div style={cmpSt.specDropdown}>
                <div style={cmpSt.specDropTitle}>เลือกคุณลักษณะเฉพาะ</div>
                {specs.length === 0 && (
                  <div style={cmpSt.specDropEmpty}>
                    ยังไม่มีสเปค
                    <button style={cmpSt.goSpecsBtn} onClick={() => { setShowSpecPicker(false); onGoSpecs(); }}>
                      สร้างสเปคใหม่ →
                    </button>
                  </div>
                )}
                {specs.map(s => (
                  <button key={s.id} style={cmpSt.specOption}
                    onClick={() => { onSetBaseSpec(s); setShowSpecPicker(false); }}>
                    <span style={{ ...cmpSt.specOptDot, background: CAT_COLORS[s.category] || '#64748B' }}></span>
                    <div>
                      <div style={cmpSt.specOptName}>{s.name}</div>
                      <div style={cmpSt.specOptCat}>{CATEGORIES.find(c => c.id === s.category)?.label}</div>
                    </div>
                    {baseSpec?.id === s.id && (
                      <svg style={{ marginLeft: 'auto', color: specColor }} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    )}
                  </button>
                ))}
                {baseSpec && (
                  <button style={cmpSt.clearSpecBtn} onClick={() => { onClearBaseSpec(); setShowSpecPicker(false); }}>
                    ล้างสเปคอ้างอิง
                  </button>
                )}
              </div>
            )}
          </div>

          <button style={cmpSt.printBtn} onClick={() => window.print()}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
              <polyline points="6 9 6 2 18 2 18 9"/>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
              <rect x="6" y="14" width="12" height="8"/>
            </svg>
            พิมพ์
          </button>
          {items.length > 0 && (
            <button style={cmpSt.clearBtn} onClick={onClear}>ล้างสินค้า</button>
          )}
        </div>
      </div>

      {(items.length > 0 || baseSpec) && (
        <div style={cmpSt.tableWrap}>
          <table style={cmpSt.table}>
            <colgroup>
              <col style={{ width: '190px' }}/>
              {baseSpec && <col style={{ minWidth: '230px' }}/>}
              {items.map(p => <col key={p.id} style={{ minWidth: '220px' }}/>)}
            </colgroup>

            <thead>
              <tr>
                <th style={cmpSt.cornerCell}>รายละเอียด</th>

                {/* Spec baseline column */}
                {baseSpec && (
                  <th style={{ ...cmpSt.productHead, borderTop: `4px solid ${specColor}` }}>
                    <div style={cmpSt.productCard}>
                      <span style={{ ...cmpSt.catTag, background: specColor }}>
                        📋 สเปคอ้างอิง
                      </span>
                      <div style={cmpSt.pBrand}>{CATEGORIES.find(c => c.id === baseSpec.category)?.label}</div>
                      <div style={{ ...cmpSt.pModel, color: specColor }}>{baseSpec.name}</div>
                      {baseSpec.budget > 0 && (
                        <div style={cmpSt.pPrice}>{fmt(baseSpec.budget)}</div>
                      )}
                      <div style={cmpSt.pRef}>วงเงินงบประมาณ · {baseSpec.createdDate}</div>
                      <div style={cmpSt.specFieldCount}>
                        {Object.values(baseSpec.specs || {}).filter(v => v).length} ข้อกำหนด
                      </div>
                    </div>
                  </th>
                )}

                {/* Product columns */}
                {items.map(p => {
                  const color = CAT_COLORS[p.category] || '#64748B';
                  return (
                    <th key={p.id} style={{ ...cmpSt.productHead, borderTop: `4px solid ${color}` }}>
                      <div style={cmpSt.productCard}>
                        <button style={cmpSt.removeBtn} onClick={() => onRemove(p.id)} title="นำออก">
                          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                          </svg>
                        </button>
                        <span style={{ ...cmpSt.catTag, background: color }}>
                          {CATEGORIES.find(c => c.id === p.category)?.label}
                        </span>
                        <div style={cmpSt.pBrand}>{p.brand}</div>
                        <div style={cmpSt.pModel}>{p.model}</div>
                        <div style={cmpSt.pPrice}>{fmt(p.price)}</div>
                        <div style={cmpSt.pRef}>{p.priceRef} · {p.priceDate}</div>
                        <button style={cmpSt.viewBtn} onClick={() => onViewProduct(p)}>ดูรายละเอียดเต็ม →</button>
                      </div>
                    </th>
                  );
                })}
              </tr>
            </thead>

            <tbody>
              {tableRows.map((row, i) => {
                if (row.type === 'group') {
                  return (
                    <tr key={i} style={cmpSt.groupRow}>
                      <td colSpan={(baseSpec ? 1 : 0) + items.length + 1} style={cmpSt.groupCell}>
                        {row.label}
                      </td>
                    </tr>
                  );
                }

                // Check if product values differ
                const productVals = items.map(p => (p.specs?.[row.field] || '').trim());
                const allSame = productVals.length > 1 && productVals.every(v => v === productVals[0]);

                return (
                  <tr key={i} style={cmpSt.dataRow}>
                    <td style={cmpSt.fieldCell}>{row.field}</td>

                    {/* Spec requirement cell */}
                    {baseSpec && (
                      <td style={{ ...cmpSt.valCell, background: '#FAF5FF', borderLeft: `2px solid ${specColor}20` }}>
                        {baseSpec.specs?.[row.field]
                          ? <>
                              <div style={cmpSt.reqLabel}>ข้อกำหนด</div>
                              {String(baseSpec.specs[row.field]).split('\n').map((ln, li) => (
                                <div key={li} style={{ lineHeight: '1.55', fontSize: '13px', color: '#4C1D95' }}>{ln}</div>
                              ))}
                            </>
                          : <span style={cmpSt.noReq}>ไม่ระบุ</span>
                        }
                      </td>
                    )}

                    {/* Product cells */}
                    {items.map(p => {
                      const val = p.specs?.[row.field];
                      return (
                        <td key={p.id} style={{ ...cmpSt.valCell, background: (!allSame && productVals.length > 1) ? '#FFFBEB' : 'white' }}>
                          {val
                            ? String(val).split('\n').map((ln, li) => <div key={li} style={{ lineHeight: '1.55' }}>{ln}</div>)
                            : <span style={cmpSt.noVal}>—</span>
                          }
                        </td>
                      );
                    })}
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {(items.length > 0 || baseSpec) && (
        <div style={cmpSt.legend}>
          {baseSpec && <><span style={cmpSt.legendSpecDot}></span><span style={cmpSt.legendText}>คอลัมน์สีม่วง = ข้อกำหนดสเปคอ้างอิง</span></>}
          <span style={cmpSt.legendDot}></span>
          <span style={cmpSt.legendText}>พื้นหลังสีเหลืองอ่อน = ค่าที่แตกต่างกันระหว่างสินค้า</span>
        </div>
      )}

      {/* Spec picker backdrop */}
      {showSpecPicker && (
        <div style={{ position: 'fixed', inset: 0, zIndex: 90 }} onClick={() => setShowSpecPicker(false)}></div>
      )}
    </div>
  );
}

const cmpSt = {
  page:         { padding: '28px' },
  emptyWrap:    { display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '100px 40px', gap: '12px' },
  emptyTitle:   { fontSize: '18px', fontWeight: '700', color: '#94A3B8', margin: 0 },
  emptyDesc:    { fontSize: '14px', color: '#CBD5E1', margin: 0, textAlign: 'center' },
  toolbar:      { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: '18px' },
  pageTitle:    { fontSize: '22px', fontWeight: '800', color: '#1E293B', margin: '0 0 6px' },
  pageCount:    { color: '#64748B', fontSize: '13px', margin: 0, display: 'flex', alignItems: 'center', gap: '10px' },
  specChip:     { background: '#F5F3FF', color: '#7C3AED', padding: '3px 10px', borderRadius: '6px', fontSize: '12px', fontWeight: '700' },
  toolbarRight: { display: 'flex', gap: '8px', alignItems: 'center' },
  specBtn:      { display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 14px', border: '1.5px solid #E2E8F0', background: 'white', color: '#374151', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', fontWeight: '600' },
  specBtnOn:    { borderColor: '#7C3AED', background: '#F5F3FF', color: '#7C3AED' },
  specDropdown: { position: 'absolute', top: 'calc(100% + 6px)', right: 0, background: 'white', border: '1.5px solid #E2E8F0', borderRadius: '12px', padding: '8px', width: '260px', boxShadow: '0 8px 24px rgba(0,0,0,0.12)', zIndex: 100 },
  specDropTitle:{ fontSize: '11px', fontWeight: '700', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: '0.08em', padding: '4px 8px 8px' },
  specDropEmpty:{ padding: '12px 8px', display: 'flex', flexDirection: 'column', gap: '8px', fontSize: '13px', color: '#94A3B8' },
  goSpecsBtn:   { background: '#1B3A6B', color: 'white', border: 'none', borderRadius: '6px', padding: '6px 12px', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700', alignSelf: 'flex-start' },
  specOption:   { width: '100%', display: 'flex', alignItems: 'center', gap: '10px', padding: '9px 10px', border: 'none', background: 'transparent', borderRadius: '8px', cursor: 'pointer', fontFamily: 'Sarabun, sans-serif', textAlign: 'left' },
  specOptDot:   { width: '10px', height: '10px', borderRadius: '50%', flexShrink: 0 },
  specOptName:  { fontSize: '13px', fontWeight: '700', color: '#1E293B' },
  specOptCat:   { fontSize: '11px', color: '#94A3B8' },
  clearSpecBtn: { width: '100%', padding: '8px', border: 'none', background: 'transparent', color: '#DC2626', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700', borderTop: '1px solid #F1F5F9', marginTop: '4px' },
  printBtn:     { display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 14px', border: '1.5px solid #E2E8F0', background: 'white', color: '#374151', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', fontWeight: '600' },
  clearBtn:     { padding: '8px 14px', border: '1.5px solid #FEE2E2', background: '#FFF5F5', color: '#DC2626', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700' },
  tableWrap:    { background: 'white', borderRadius: '14px', border: '1px solid #E2E8F0', overflow: 'auto', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' },
  table:        { width: '100%', borderCollapse: 'collapse' },
  cornerCell:   { padding: '14px 16px', background: '#F8FAFC', fontSize: '12px', fontWeight: '700', color: '#64748B', borderBottom: '1px solid #E2E8F0', position: 'sticky', left: 0, zIndex: 10 },
  productHead:  { padding: 0, borderLeft: '1px solid #E2E8F0', verticalAlign: 'top', background: 'white' },
  productCard:  { padding: '16px 18px 14px', position: 'relative', display: 'flex', flexDirection: 'column', gap: '5px' },
  removeBtn:    { position: 'absolute', top: '10px', right: '10px', width: '22px', height: '22px', borderRadius: '50%', background: '#FEE2E2', border: 'none', color: '#DC2626', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' },
  catTag:       { alignSelf: 'flex-start', color: 'white', fontSize: '10px', fontWeight: '800', padding: '2px 7px', borderRadius: '4px' },
  pBrand:       { fontSize: '12px', color: '#94A3B8', fontWeight: '600' },
  pModel:       { fontSize: '14px', fontWeight: '800', color: '#1E293B', lineHeight: '1.3', paddingRight: '24px' },
  pPrice:       { fontSize: '20px', fontWeight: '900', color: '#059669' },
  pRef:         { fontSize: '11px', color: '#94A3B8' },
  specFieldCount:{ fontSize: '12px', fontWeight: '700', color: '#7C3AED', background: '#F5F3FF', padding: '3px 8px', borderRadius: '4px', alignSelf: 'flex-start' },
  viewBtn:      { alignSelf: 'flex-start', padding: '5px 10px', background: '#EFF6FF', color: '#1D4ED8', border: 'none', borderRadius: '6px', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700', marginTop: '2px' },
  groupRow:     { background: '#F1F5F9' },
  groupCell:    { padding: '9px 16px', fontSize: '11px', fontWeight: '800', color: '#1B3A6B', textTransform: 'uppercase', letterSpacing: '0.07em', borderBottom: '1px solid #E2E8F0', position: 'sticky', left: 0 },
  dataRow:      { borderBottom: '1px solid #F8FAFC' },
  fieldCell:    { padding: '10px 16px', fontSize: '12px', color: '#64748B', fontWeight: '600', verticalAlign: 'top', background: '#FAFAFA', position: 'sticky', left: 0, borderRight: '1px solid #E2E8F0', whiteSpace: 'nowrap' },
  valCell:      { padding: '10px 16px', fontSize: '13px', color: '#1E293B', verticalAlign: 'top', borderLeft: '1px solid #F1F5F9' },
  reqLabel:     { fontSize: '10px', fontWeight: '700', color: '#7C3AED', background: '#F5F3FF', display: 'inline-block', padding: '1px 6px', borderRadius: '3px', marginBottom: '4px' },
  noReq:        { color: '#C4B5FD', fontSize: '13px' },
  noVal:        { color: '#CBD5E1', fontSize: '16px' },
  legend:       { display: 'flex', alignItems: 'center', gap: '8px', marginTop: '12px', paddingLeft: '4px', flexWrap: 'wrap' },
  legendSpecDot:{ display: 'inline-block', width: '14px', height: '14px', background: '#FAF5FF', border: '1px solid #DDD6FE', borderRadius: '3px' },
  legendDot:    { display: 'inline-block', width: '14px', height: '14px', background: '#FFFBEB', border: '1px solid #FDE68A', borderRadius: '3px' },
  legendText:   { fontSize: '12px', color: '#94A3B8', marginRight: '12px' },
};

Object.assign(window, { CompareView });
