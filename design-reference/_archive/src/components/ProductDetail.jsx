// ============================================================
// src/components/ProductDetail.jsx
// ============================================================

function ProductDetailModal({ product, onClose, onEdit, compareList, onToggleCompare, user, onAddHistory }) {
  const [tab, setTab] = React.useState('specs');
  const [hSource,  setHSource]  = React.useState('Excel');
  const [hUrl,     setHUrl]     = React.useState('');
  const [hNote,    setHNote]    = React.useState('');
  const [hSaving,  setHSaving]  = React.useState(false);

  if (!product) return null;

  const color     = CAT_COLORS[product.category] || '#64748B';
  const catLabel  = CATEGORIES.find(c => c.id === product.category)?.label || product.category;
  const fmt       = n => n ? n.toLocaleString('th-TH') + ' บาท' : '—';
  const inCompare = compareList.includes(product.id);

  const handlePrint = () => window.print();

  const SOURCE_OPTS = ['Excel', 'กรอกด้วยมือ', 'ดาวน์โหลดจากเว็บ', 'API / ระบบอัตโนมัติ', 'อื่นๆ'];

  const handleAddHistory = () => {
    if (!hNote.trim() && !hUrl.trim()) return;
    setHSaving(true);
    const entry = {
      date:   new Date().toLocaleDateString('th-TH-u-ca-buddhist', { year:'numeric', month:'2-digit', day:'2-digit' }).replace(/\//g,'-'),
      user:   user.name,
      action: 'บันทึกข้อมูลเข้าระบบ',
      detail: hNote.trim() || `นำเข้าจาก ${hSource}`,
      source: hSource,
      url:    hUrl.trim(),
    };
    onAddHistory && onAddHistory(product.id, entry);
    setHNote(''); setHUrl(''); setHSource('Excel');
    setTimeout(() => setHSaving(false), 300);
  };

  return (
    <div style={detailSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={detailSt.modal}>

        {/* ── Header ── */}
        <div style={{ ...detailSt.header, borderTop: `4px solid ${color}` }}>
          <div style={detailSt.headerLeft}>
            <div style={detailSt.topRow}>
              <span style={{ ...detailSt.catBadge, background: color }}>{catLabel}</span>
              <span style={detailSt.brandTxt}>{product.brand}</span>
            </div>
            <h2 style={detailSt.title}>{product.model}</h2>
            <div style={detailSt.priceRow}>
              <span style={detailSt.price}>{fmt(product.price)}</span>
              <span style={detailSt.priceRef}>{product.priceRef}</span>
              <span style={detailSt.priceDate}>วันที่ {product.priceDate}</span>
            </div>
          </div>

          <div style={detailSt.headerRight}>
            {user?.role === 'admin' && (
              <HdrBtn color="#D97706" bg="#FFFBEB" border="#FDE68A" onClick={() => onEdit(product)}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                แก้ไข
              </HdrBtn>
            )}
            <HdrBtn color="#374151" bg="white" border="#E2E8F0" onClick={handlePrint}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
              </svg>
              พิมพ์
            </HdrBtn>
            <HdrBtn
              color={inCompare ? 'white' : '#1D4ED8'}
              bg={inCompare ? '#1D4ED8' : '#EFF6FF'}
              border={inCompare ? '#1D4ED8' : '#BFDBFE'}
              onClick={() => onToggleCompare(product.id)}
              disabled={!inCompare && compareList.length >= 3}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              {inCompare ? 'ยกเลิกเปรียบเทียบ' : 'เปรียบเทียบ'}
            </HdrBtn>
            <button style={detailSt.closeBtn} onClick={onClose}>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        {/* ── Tabs ── */}
        <div style={detailSt.tabs}>
          {[['specs', 'ข้อมูลสเปค'], ['history', 'ประวัติการแก้ไข']].map(([id, lbl]) => (
            <button key={id} style={{ ...detailSt.tab, ...(tab === id ? detailSt.tabOn : {}) }}
              onClick={() => setTab(id)}>{lbl}</button>
          ))}
        </div>

        {/* ── Content ── */}
        <div style={detailSt.body}>
          {tab === 'specs' && (
            <div>
              {SPEC_GROUPS.map(group => {
                const rows = group.fields.filter(f => product.specs?.[f]);
                if (rows.length === 0) return null;
                return (
                  <div key={group.id} style={detailSt.section}>
                    <div style={{ ...detailSt.sectionTitle, borderLeft: `3px solid ${color}` }}>
                      {group.label}
                    </div>
                    <table style={detailSt.specTbl}>
                      <tbody>
                        {rows.map(field => (
                          <tr key={field} style={detailSt.specRow}>
                            <td style={detailSt.specKey}>{field}</td>
                            <td style={detailSt.specVal}>
                              {String(product.specs[field]).split('\n').map((ln, i) => (
                                <div key={i} style={{ lineHeight: '1.6' }}>{ln}</div>
                              ))}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                );
              })}
            </div>
          )}

          {tab === 'history' && (
            <div>
              {/* ── Import log form (admin only) ── */}
              {user?.role === 'admin' && (
                <div style={detailSt.importBox}>
                  <div style={detailSt.importTitle}>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                      <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    บันทึกช่องทางนำข้อมูลเข้า
                  </div>

                  {/* Source type */}
                  <div style={detailSt.importRow}>
                    <label style={detailSt.importLabel}>ช่องทางนำข้อมูลเข้า</label>
                    <div style={detailSt.sourceOpts}>
                      {SOURCE_OPTS.map(s => (
                        <button key={s}
                          style={{ ...detailSt.sourcePill, ...(hSource === s ? { ...detailSt.sourcePillOn, background: color, borderColor: color } : {}) }}
                          onClick={() => setHSource(s)}>
                          {s}
                        </button>
                      ))}
                    </div>
                  </div>

                  {/* URL */}
                  <div style={detailSt.importRow}>
                    <label style={detailSt.importLabel}>URL อ้างอิง / แหล่งที่มา</label>
                    <div style={detailSt.urlWrap}>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" strokeWidth="2" strokeLinecap="round" style={{ flexShrink:0 }}>
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                      </svg>
                      <input value={hUrl} onChange={e => setHUrl(e.target.value)}
                        placeholder="https://example.com/spec.xlsx หรือ URL ที่เกี่ยวข้อง"
                        style={detailSt.urlInput}/>
                    </div>
                  </div>

                  {/* Note */}
                  <div style={detailSt.importRow}>
                    <label style={detailSt.importLabel}>หมายเหตุ</label>
                    <input value={hNote} onChange={e => setHNote(e.target.value)}
                      placeholder="เช่น อัปเดตราคาจาก Spec Sheet เดือน พ.ค. 2569"
                      style={detailSt.noteInput}/>
                  </div>

                  <button style={{ ...detailSt.saveHistBtn, background: color }} onClick={handleAddHistory} disabled={hSaving}>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                      <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                      <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    {hSaving ? 'กำลังบันทึก...' : 'บันทึกประวัติ'}
                  </button>
                </div>
              )}

              {/* ── History list ── */}
              <div style={detailSt.histList}>
                {(product.editHistory || []).length === 0 && (
                  <div style={{ textAlign: 'center', color: '#94A3B8', padding: '48px' }}>ไม่มีประวัติการแก้ไข</div>
                )}
                {[...(product.editHistory || [])].reverse().map((h, i) => (
                  <div key={i} style={detailSt.histItem}>
                    <div style={{ ...detailSt.histDot, background: color }}></div>
                    <div style={detailSt.histBody}>
                      <div style={detailSt.histTop}>
                        <span style={detailSt.histAction}>{h.action}</span>
                        {h.source && (
                          <span style={detailSt.sourceBadge}>
                            {h.source === 'Excel' && '📊'}
                            {h.source === 'กรอกด้วยมือ' && '✍️'}
                            {h.source === 'ดาวน์โหลดจากเว็บ' && '🌐'}
                            {h.source === 'API / ระบบอัตโนมัติ' && '⚙️'}
                            {' '}{h.source}
                          </span>
                        )}
                        <span style={detailSt.histUser}>โดย {h.user}</span>
                        <span style={detailSt.histDate}>{h.date}</span>
                      </div>
                      {h.detail && <div style={detailSt.histDetail}>{h.detail}</div>}
                      {h.url && (
                        <a href={h.url} target="_blank" rel="noopener noreferrer" style={detailSt.histUrl}>
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                          </svg>
                          {h.url.length > 60 ? h.url.substring(0,60) + '...' : h.url}
                        </a>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function HdrBtn({ color, bg, border, onClick, disabled, children }) {
  return (
    <button disabled={disabled}
      style={{ display: 'flex', alignItems: 'center', gap: '5px', padding: '7px 13px', border: `1.5px solid ${border}`, background: disabled ? '#F1F5F9' : bg, color: disabled ? '#94A3B8' : color, borderRadius: '8px', cursor: disabled ? 'not-allowed' : 'pointer', fontSize: '13px', fontWeight: '600', fontFamily: 'Sarabun, sans-serif', whiteSpace: 'nowrap' }}>
      {children}
    </button>
  );
}

const detailSt = {
  overlay:      { position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', zIndex: 200, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' },
  modal:        { background: 'white', borderRadius: '16px', width: '780px', maxWidth: '100%', maxHeight: '92vh', display: 'flex', flexDirection: 'column', boxShadow: '0 24px 80px rgba(0,0,0,0.25)' },
  header:       { padding: '22px 26px 18px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '16px', borderBottom: '1px solid #F1F5F9' },
  headerLeft:   { flex: 1, minWidth: 0 },
  topRow:       { display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '6px' },
  catBadge:     { color: 'white', fontSize: '11px', fontWeight: '800', padding: '3px 8px', borderRadius: '5px' },
  brandTxt:     { fontSize: '14px', fontWeight: '700', color: '#64748B' },
  title:        { fontSize: '19px', fontWeight: '800', color: '#1E293B', margin: '0 0 10px', lineHeight: '1.3' },
  priceRow:     { display: 'flex', alignItems: 'center', gap: '10px', flexWrap: 'wrap' },
  price:        { fontSize: '22px', fontWeight: '900', color: '#059669' },
  priceRef:     { fontSize: '12px', color: '#64748B', background: '#F1F5F9', padding: '3px 8px', borderRadius: '4px' },
  priceDate:    { fontSize: '12px', color: '#94A3B8' },
  headerRight:  { display: 'flex', gap: '7px', alignItems: 'flex-start', flexShrink: 0 },
  closeBtn:     { padding: '8px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', color: '#64748B', display: 'flex' },
  tabs:         { display: 'flex', borderBottom: '1px solid #E2E8F0', padding: '0 26px' },
  tab:          { padding: '12px 18px', border: 'none', background: 'transparent', fontSize: '14px', fontWeight: '600', color: '#94A3B8', cursor: 'pointer', borderBottom: '2px solid transparent', fontFamily: 'Sarabun, sans-serif' },
  tabOn:        { color: '#1B3A6B', borderBottomColor: '#1B3A6B' },
  body:         { overflowY: 'auto', padding: '22px 26px', flex: 1 },
  section:      { marginBottom: '22px' },
  sectionTitle: { fontSize: '13px', fontWeight: '800', color: '#1B3A6B', paddingLeft: '10px', marginBottom: '10px', letterSpacing: '0.03em' },
  specTbl:      { width: '100%', borderCollapse: 'collapse' },
  specRow:      { borderBottom: '1px solid #F8FAFC' },
  specKey:      { width: '190px', padding: '8px 12px 8px 0', fontSize: '12px', color: '#64748B', fontWeight: '600', verticalAlign: 'top' },
  specVal:      { padding: '8px 0', fontSize: '13px', color: '#1E293B', lineHeight: '1.6', verticalAlign: 'top' },
  histList:     { display: 'flex', flexDirection: 'column' },
  histItem:     { display: 'flex', gap: '12px', padding: '14px 0', borderBottom: '1px solid #F1F5F9' },
  histDot:      { width: '10px', height: '10px', borderRadius: '50%', marginTop: '5px', flexShrink: 0 },
  histBody:     { flex: 1 },
  histTop:      { display: 'flex', gap: '10px', alignItems: 'center', flexWrap: 'wrap', marginBottom: '4px' },
  histAction:   { fontWeight: '700', color: '#1E293B', fontSize: '14px' },
  sourceBadge:  { fontSize: '11px', fontWeight: '700', color: '#7C3AED', background: '#F5F3FF', padding: '2px 8px', borderRadius: '4px' },
  histUser:     { color: '#2563EB', fontSize: '13px', fontWeight: '600' },
  histDate:     { color: '#94A3B8', fontSize: '12px', marginLeft: 'auto' },
  histDetail:   { fontSize: '13px', color: '#64748B' },
  histUrl:      { display: 'inline-flex', alignItems: 'center', gap: '5px', fontSize: '12px', color: '#2563EB', marginTop: '4px', textDecoration: 'none', wordBreak: 'break-all' },
  importBox:    { background: '#F8FAFC', border: '1.5px solid #E2E8F0', borderRadius: '12px', padding: '16px', marginBottom: '20px' },
  importTitle:  { display: 'flex', alignItems: 'center', gap: '7px', fontSize: '13px', fontWeight: '800', color: '#1E293B', marginBottom: '14px' },
  importRow:    { marginBottom: '12px' },
  importLabel:  { display: 'block', fontSize: '12px', fontWeight: '700', color: '#374151', marginBottom: '6px' },
  sourceOpts:   { display: 'flex', gap: '6px', flexWrap: 'wrap' },
  sourcePill:   { padding: '5px 12px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '20px', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', color: '#475569', fontWeight: '600' },
  sourcePillOn: { color: 'white', fontWeight: '700' },
  urlWrap:      { display: 'flex', alignItems: 'center', gap: '8px', background: 'white', border: '1.5px solid #E2E8F0', borderRadius: '8px', padding: '0 12px' },
  urlInput:     { flex: 1, border: 'none', outline: 'none', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', padding: '9px 0', color: '#1E293B', background: 'transparent' },
  noteInput:    { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', outline: 'none', boxSizing: 'border-box' },
  saveHistBtn:  { display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 18px', border: 'none', color: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700', marginTop: '4px' },
};

Object.assign(window, { ProductDetailModal });
