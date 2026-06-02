// ============================================================
// src/components/ProductList.jsx
// ============================================================

function ProductList({ products, category, search, onView, onEdit, onDelete, compareList, onToggleCompare, user }) {
  const [sortBy,   setSortBy]   = React.useState('brand');
  const [sortDir,  setSortDir]  = React.useState('asc');
  const [viewMode, setViewMode] = React.useState('table');
  const [hovered,  setHovered]  = React.useState(null);

  const filtered = React.useMemo(() => {
    let list = category === 'all' ? products : products.filter(p => p.category === category);
    if (search) {
      const q = search.toLowerCase();
      list = list.filter(p =>
        p.brand.toLowerCase().includes(q) ||
        p.model.toLowerCase().includes(q) ||
        p.category.toLowerCase().includes(q) ||
        Object.values(p.specs || {}).some(v => v && String(v).toLowerCase().includes(q))
      );
    }
    return [...list].sort((a, b) => {
      const av = a[sortBy] ?? '', bv = b[sortBy] ?? '';
      if (typeof av === 'number') return sortDir === 'asc' ? av - bv : bv - av;
      return sortDir === 'asc'
        ? String(av).localeCompare(String(bv), 'th')
        : String(bv).localeCompare(String(av), 'th');
    });
  }, [products, category, search, sortBy, sortDir]);

  const handleSort = (field) => {
    if (sortBy === field) setSortDir(d => d === 'asc' ? 'desc' : 'asc');
    else { setSortBy(field); setSortDir('asc'); }
  };

  const fmt = p => p ? p.toLocaleString('th-TH') + ' ฿' : '—';
  const catLabel = CATEGORIES.find(c => c.id === category)?.label || 'ทั้งหมด';

  function SortIco({ field }) {
    if (sortBy !== field) return <span style={{ opacity: 0.25, marginLeft: '4px' }}>↕</span>;
    return <span style={{ marginLeft: '4px', color: '#1B3A6B' }}>{sortDir === 'asc' ? '↑' : '↓'}</span>;
  }

  return (
    <div style={listSt.page}>
      {/* Toolbar */}
      <div style={listSt.toolbar}>
        <div>
          <h2 style={listSt.pageTitle}>{catLabel}</h2>
          <p style={listSt.pageCount}>
            {filtered.length} รายการ
            {search && <span style={listSt.searchTag}>ค้นหา: "{search}"</span>}
          </p>
        </div>
        <div style={listSt.toolbarRight}>
          {compareList.length > 0 && (
            <span style={listSt.compareChip}>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              เลือกเปรียบเทียบ {compareList.length}/3 รายการ
            </span>
          )}
          <div style={listSt.viewToggle}>
            <button style={{ ...listSt.viewBtn, ...(viewMode === 'table' ? listSt.viewBtnOn : {}) }}
              onClick={() => setViewMode('table')} title="มุมมองตาราง">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
              </svg>
            </button>
            <button style={{ ...listSt.viewBtn, ...(viewMode === 'card' ? listSt.viewBtnOn : {}) }}
              onClick={() => setViewMode('card')} title="มุมมองการ์ด">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* Empty */}
      {filtered.length === 0 && (
        <div style={listSt.empty}>
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" strokeWidth="1.5">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p style={{ color: '#94A3B8', margin: '12px 0 0', fontSize: '15px' }}>ไม่พบรายการที่ค้นหา</p>
        </div>
      )}

      {/* Table */}
      {filtered.length > 0 && viewMode === 'table' && (
        <div style={listSt.tableWrap}>
          <table style={listSt.table}>
            <thead>
              <tr style={listSt.thead}>
                <th style={{ ...listSt.th, width: '36px', textAlign: 'center' }}>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" strokeWidth="2" strokeLinecap="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                  </svg>
                </th>
                <th style={{ ...listSt.th, cursor: 'pointer' }} onClick={() => handleSort('brand')}>แบรนด์ <SortIco field="brand"/></th>
                <th style={{ ...listSt.th, cursor: 'pointer', minWidth: '240px' }} onClick={() => handleSort('model')}>รุ่น / โมเดล <SortIco field="model"/></th>
                <th style={{ ...listSt.th, cursor: 'pointer' }} onClick={() => handleSort('category')}>ประเภท <SortIco field="category"/></th>
                <th style={{ ...listSt.th, minWidth: '200px' }}>สเปคหลัก</th>
                <th style={{ ...listSt.th, cursor: 'pointer', textAlign: 'right' }} onClick={() => handleSort('price')}>ราคากลาง <SortIco field="price"/></th>
                <th style={{ ...listSt.th, textAlign: 'center', width: '100px' }}>จัดการ</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map(p => {
                const color = CAT_COLORS[p.category] || '#64748B';
                const isHovered = hovered === p.id;
                return (
                  <tr key={p.id}
                    style={{ ...listSt.tr, ...(isHovered ? listSt.trHover : {}) }}
                    onMouseEnter={() => setHovered(p.id)}
                    onMouseLeave={() => setHovered(null)}>
                    <td style={{ ...listSt.td, textAlign: 'center' }}>
                      <input type="checkbox"
                        checked={compareList.includes(p.id)}
                        onChange={() => onToggleCompare(p.id)}
                        disabled={!compareList.includes(p.id) && compareList.length >= 3}
                        style={{ cursor: 'pointer', accentColor: '#1B3A6B' }}
                      />
                    </td>
                    <td style={listSt.td}>
                      <span style={{ ...listSt.brandBadge, background: color + '18', color }}>
                        {p.brand}
                      </span>
                    </td>
                    <td style={listSt.td}>
                      <div style={listSt.modelLink} onClick={() => onView(p)} title={p.model}>
                        {p.model}
                      </div>
                    </td>
                    <td style={listSt.td}>
                      <span style={{ ...listSt.catTag, background: color, color: 'white' }}>
                        {CATEGORIES.find(c => c.id === p.category)?.label || p.category}
                      </span>
                    </td>
                    <td style={listSt.td}>
                      <div style={listSt.specLine}>{p.specs['Processor']?.split('(')[0]?.trim()?.substring(0, 38) || '—'}</div>
                      <div style={listSt.specLine2}>{p.specs['Main Memory'] || p.specs['Storage'] || ''}</div>
                    </td>
                    <td style={{ ...listSt.td, textAlign: 'right' }}>
                      <div style={listSt.priceVal}>{fmt(p.price)}</div>
                      <div style={listSt.priceDate}>{p.priceDate}</div>
                    </td>
                    <td style={{ ...listSt.td, textAlign: 'center' }}>
                      <div style={listSt.actionsRow}>
                        <ActionBtn title="ดูรายละเอียด" color="#2563EB" onClick={() => onView(p)}>
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                          </svg>
                        </ActionBtn>
                        {user.role === 'admin' && (
                          <ActionBtn title="แก้ไข" color="#D97706" onClick={() => onEdit(p)}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                          </ActionBtn>
                        )}
                        {user.role === 'admin' && (
                          <ActionBtn title="ลบ" color="#DC2626" onClick={() => onDelete(p.id)}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                              <polyline points="3 6 5 6 21 6"/>
                              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                            </svg>
                          </ActionBtn>
                        )}
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      {/* Cards */}
      {filtered.length > 0 && viewMode === 'card' && (
        <div style={listSt.cardGrid}>
          {filtered.map(p => {
            const color = CAT_COLORS[p.category] || '#64748B';
            return (
              <div key={p.id} style={listSt.card} onClick={() => onView(p)}>
                <div style={{ ...listSt.cardTop, background: color }}>
                  <span style={listSt.cardCatLabel}>{CATEGORIES.find(c => c.id === p.category)?.label}</span>
                  <div onClick={e => e.stopPropagation()} style={{ display: 'flex', alignItems: 'center' }}>
                    <input type="checkbox"
                      checked={compareList.includes(p.id)}
                      onChange={() => onToggleCompare(p.id)}
                      disabled={!compareList.includes(p.id) && compareList.length >= 3}
                      style={{ cursor: 'pointer', accentColor: 'white' }}
                    />
                  </div>
                </div>
                <div style={listSt.cardBody}>
                  <div style={listSt.cardBrand}>{p.brand}</div>
                  <div style={listSt.cardModel}>{p.model}</div>
                  <div style={listSt.cardSpecs}>
                    <div style={listSt.cardSpecLine}>{p.specs['Processor']?.split('(')[0]?.trim()?.substring(0, 44) || '—'}</div>
                    <div style={listSt.cardSpecLine}>{p.specs['Main Memory'] || '—'}</div>
                    <div style={listSt.cardSpecLine}>{p.specs['Storage'] || '—'}</div>
                  </div>
                  <div style={listSt.cardFooter}>
                    <div>
                      <div style={listSt.cardPriceLabel}>ราคากลาง</div>
                      <div style={listSt.cardPriceVal}>{fmt(p.price)}</div>
                    </div>
                    {user.role === 'admin' && (
                      <div style={listSt.cardActions} onClick={e => e.stopPropagation()}>
                        <ActionBtn title="แก้ไข" color="#D97706" onClick={() => onEdit(p)}>
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </ActionBtn>
                        <ActionBtn title="ลบ" color="#DC2626" onClick={() => onDelete(p.id)}>
                          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </ActionBtn>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

function ActionBtn({ title, color, onClick, children }) {
  const [hov, setHov] = React.useState(false);
  return (
    <button title={title} onClick={onClick}
      onMouseEnter={() => setHov(true)} onMouseLeave={() => setHov(false)}
      style={{ padding: '6px', border: `1.5px solid ${hov ? color : '#E2E8F0'}`, borderRadius: '7px', background: hov ? color + '12' : 'white', cursor: 'pointer', color, display: 'flex', transition: 'all 0.15s' }}>
      {children}
    </button>
  );
}

const listSt = {
  page:        { padding: '28px' },
  toolbar:     { display: 'flex', alignItems: 'flex-end', justifyContent: 'space-between', marginBottom: '18px' },
  pageTitle:   { fontSize: '22px', fontWeight: '800', color: '#1E293B', margin: '0 0 4px' },
  pageCount:   { color: '#64748B', fontSize: '13px', margin: 0, display: 'flex', alignItems: 'center', gap: '8px' },
  searchTag:   { background: '#EFF6FF', color: '#2563EB', padding: '2px 8px', borderRadius: '4px', fontSize: '12px', fontWeight: '600' },
  toolbarRight:{ display: 'flex', alignItems: 'center', gap: '10px' },
  compareChip: { display: 'flex', alignItems: 'center', gap: '6px', background: '#EFF6FF', color: '#2563EB', fontSize: '12px', fontWeight: '700', padding: '6px 12px', borderRadius: '8px' },
  viewToggle:  { display: 'flex', gap: '4px' },
  viewBtn:     { padding: '7px', border: '1.5px solid #E2E8F0', borderRadius: '7px', background: 'white', cursor: 'pointer', color: '#94A3B8', display: 'flex' },
  viewBtnOn:   { background: '#1B3A6B', borderColor: '#1B3A6B', color: 'white' },
  empty:       { textAlign: 'center', padding: '80px 20px' },
  tableWrap:   { background: 'white', borderRadius: '14px', border: '1px solid #E2E8F0', overflow: 'auto', boxShadow: '0 2px 8px rgba(0,0,0,0.04)' },
  table:       { width: '100%', borderCollapse: 'collapse' },
  thead:       { background: '#F8FAFC' },
  th:          { padding: '11px 14px', textAlign: 'left', fontSize: '12px', fontWeight: '700', color: '#64748B', borderBottom: '1px solid #E2E8F0', userSelect: 'none', whiteSpace: 'nowrap' },
  tr:          { borderBottom: '1px solid #F1F5F9', transition: 'background 0.1s' },
  trHover:     { background: '#F8FAFC' },
  td:          { padding: '11px 14px', fontSize: '13px', color: '#374151', verticalAlign: 'middle' },
  brandBadge:  { padding: '3px 9px', borderRadius: '6px', fontWeight: '700', fontSize: '12px', whiteSpace: 'nowrap' },
  modelLink:   { fontWeight: '700', cursor: 'pointer', color: '#1B3A6B', maxWidth: '260px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' },
  catTag:      { padding: '3px 8px', borderRadius: '5px', fontSize: '11px', fontWeight: '700', whiteSpace: 'nowrap' },
  specLine:    { fontSize: '12px', color: '#475569', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: '220px' },
  specLine2:   { fontSize: '11px', color: '#94A3B8', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', maxWidth: '220px', marginTop: '2px' },
  priceVal:    { fontWeight: '800', color: '#059669', fontSize: '14px', textAlign: 'right' },
  priceDate:   { fontSize: '11px', color: '#94A3B8', textAlign: 'right', marginTop: '2px' },
  actionsRow:  { display: 'flex', gap: '5px', justifyContent: 'center' },
  cardGrid:    { display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(270px,1fr))', gap: '16px' },
  card:        { background: 'white', borderRadius: '14px', border: '1px solid #E2E8F0', overflow: 'hidden', cursor: 'pointer', boxShadow: '0 2px 6px rgba(0,0,0,0.04)', transition: 'box-shadow 0.2s, transform 0.15s' },
  cardTop:     { padding: '12px 14px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  cardCatLabel:{ color: 'white', fontSize: '12px', fontWeight: '700' },
  cardBody:    { padding: '14px 16px' },
  cardBrand:   { fontSize: '12px', color: '#94A3B8', marginBottom: '3px' },
  cardModel:   { fontSize: '14px', fontWeight: '800', color: '#1E293B', marginBottom: '10px', lineHeight: '1.3' },
  cardSpecs:   { display: 'flex', flexDirection: 'column', gap: '3px', marginBottom: '12px' },
  cardSpecLine:{ fontSize: '12px', color: '#64748B', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' },
  cardFooter:  { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', borderTop: '1px solid #F1F5F9', paddingTop: '10px' },
  cardPriceLabel:{ fontSize: '11px', color: '#94A3B8', marginBottom: '2px' },
  cardPriceVal:{ fontSize: '16px', fontWeight: '800', color: '#059669' },
  cardActions: { display: 'flex', gap: '5px' },
};

Object.assign(window, { ProductList, ActionBtn });
