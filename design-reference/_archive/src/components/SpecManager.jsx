// ============================================================
// src/components/SpecManager.jsx
// SpecListView  +  SpecFormModal  +  SpecDetailModal
// ============================================================

const THAI_MONTHS = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
const YEAR_OPTIONS = ['2566','2567','2568','2569','2570','2571'];

const getSpecYear  = s => s.year  || (s.createdDate || '').substring(0,4) || '2569';
const getSpecMonth = s => s.month || (s.createdDate || '').substring(5,7) || '';

// ─────────────────────────────────────────────────────────────
// Spec List View
// ─────────────────────────────────────────────────────────────
function SpecListView({ specs, onView, onEdit, onDelete, onUseCompare, user }) {
  const [hovered,  setHovered]  = React.useState(null);
  const [selYear,  setSelYear]  = React.useState('all');
  const [selMonth, setSelMonth] = React.useState('all');

  // Available years (from data)
  const availYears = [...new Set(specs.map(getSpecYear))].sort().reverse();

  // Available months for selected year
  const availMonths = React.useMemo(() => {
    const src = selYear === 'all' ? specs : specs.filter(s => getSpecYear(s) === selYear);
    return [...new Set(src.map(getSpecMonth).filter(Boolean))].sort();
  }, [specs, selYear]);

  // Filtered specs
  const filtered = React.useMemo(() => specs.filter(s => {
    if (selYear  !== 'all' && getSpecYear(s)  !== selYear)  return false;
    if (selMonth !== 'all' && getSpecMonth(s) !== selMonth) return false;
    return true;
  }), [specs, selYear, selMonth]);

  // Period label for heading
  const periodLabel = React.useMemo(() => {
    if (selYear === 'all') return 'ทุกช่วงเวลา';
    if (selMonth === 'all') return `ปี ${selYear}`;
    const mIdx = parseInt(selMonth, 10) - 1;
    return `${THAI_MONTHS[mIdx]} ${selYear}`;
  }, [selYear, selMonth]);

  const fmt = n => n ? n.toLocaleString('th-TH') + ' ฿' : '—';
  const fieldCount = s => Object.values(s.specs || {}).filter(v => v).length;

  return (
    <div style={specSt.page}>
      <div style={specSt.toolbar}>
        <div>
          <h2 style={specSt.pageTitle}>คุณลักษณะเฉพาะ</h2>
          <p style={specSt.pageCount}>{filtered.length} รายการ · {periodLabel}</p>
        </div>
        {user.role === 'admin' && (
          <button style={specSt.addBtn} onClick={() => onEdit(null)}>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            สร้างสเปคใหม่
          </button>
        )}
      </div>

      {/* ── Year filter ── */}
      <div style={specSt.filterRow}>
        <span style={specSt.filterLabel}>ปี พ.ศ.:</span>
        {['all', ...availYears].map(y => (
          <button key={y}
            style={{ ...specSt.filterPill, ...(selYear === y ? specSt.filterPillOn : {}) }}
            onClick={() => { setSelYear(y); setSelMonth('all'); }}>
            {y === 'all' ? 'ทั้งหมด' : `ปี ${y}`}
            {y !== 'all' && (
              <span style={specSt.filterCount}>
                {specs.filter(s => getSpecYear(s) === y).length}
              </span>
            )}
          </button>
        ))}
      </div>

      {/* ── Month filter (only when a year is selected) ── */}
      {selYear !== 'all' && availMonths.length > 0 && (
        <div style={specSt.filterRow}>
          <span style={specSt.filterLabel}>เดือน:</span>
          <button style={{ ...specSt.filterPill, ...(selMonth === 'all' ? specSt.filterPillOn : {}) }}
            onClick={() => setSelMonth('all')}>ทุกเดือน</button>
          {availMonths.map(m => {
            const mIdx = parseInt(m, 10) - 1;
            const cnt  = specs.filter(s => getSpecYear(s) === selYear && getSpecMonth(s) === m).length;
            return (
              <button key={m}
                style={{ ...specSt.filterPill, ...(selMonth === m ? specSt.filterPillOn : {}) }}
                onClick={() => setSelMonth(m)}>
                {THAI_MONTHS[mIdx]}
                <span style={specSt.filterCount}>{cnt}</span>
              </button>
            );
          })}
        </div>
      )}

      {/* ── Summary bar ── */}
      {specs.length > 0 && (
        <div style={specSt.summaryBar}>
          {availYears.map(y => {
            const ySpecs = specs.filter(s => getSpecYear(s) === y);
            return (
              <div key={y} style={specSt.summaryItem} onClick={() => { setSelYear(y); setSelMonth('all'); }}>
                <div style={specSt.summaryYear}>ปี {y}</div>
                <div style={specSt.summaryNum}>{ySpecs.length}</div>
                <div style={specSt.summaryLbl}>รายการ</div>
              </div>
            );
          })}
          <div style={specSt.summaryItem}>
            <div style={specSt.summaryYear}>รวมทั้งหมด</div>
            <div style={{ ...specSt.summaryNum, color: '#1B3A6B' }}>{specs.length}</div>
            <div style={specSt.summaryLbl}>รายการ</div>
          </div>
        </div>
      )}

      {filtered.length === 0 && (
        <div style={specSt.empty}>
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" strokeWidth="1.5" strokeLinecap="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <p style={{ color: '#94A3B8', marginTop: '12px' }}>ยังไม่มีคุณลักษณะเฉพาะ</p>
        </div>
      )}

      <div style={specSt.grid}>
        {filtered.map(s => {
          const color = CAT_COLORS[s.category] || '#64748B';
          const isHov = hovered === s.id;
          return (
            <div key={s.id}
              style={{ ...specSt.card, ...(isHov ? specSt.cardHov : {}) }}
              onMouseEnter={() => setHovered(s.id)}
              onMouseLeave={() => setHovered(null)}>

              {/* Card header */}
              <div style={{ ...specSt.cardTop, background: color }}>
                <span style={specSt.cardCat}>{CATEGORIES.find(c => c.id === s.category)?.label || s.category}</span>
                <span style={specSt.cardFields}>{fieldCount(s)} ข้อกำหนด</span>
              </div>

              {/* Card body */}
              <div style={specSt.cardBody}>
                <div style={specSt.cardName}>{s.name}</div>
                {s.purpose && <div style={specSt.cardPurpose}>{s.purpose}</div>}

                <div style={specSt.cardMeta}>
                  <span style={specSt.metaItem}>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    {(() => {
                      const y = getSpecYear(s);
                      const m = getSpecMonth(s);
                      const mLabel = m ? THAI_MONTHS[parseInt(m,10)-1] + ' ' : '';
                      return mLabel + 'ปี ' + y;
                    })()}
                  </span>
                  {s.budget > 0 && (
                    <span style={specSt.metaItem}>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                      </svg>
                      วงเงิน {fmt(s.budget)}
                    </span>
                  )}
                </div>

                {/* Spec preview */}
                <div style={specSt.specPreview}>
                  {Object.entries(s.specs || {}).slice(0, 3).map(([k, v]) => (
                    <div key={k} style={specSt.specPreviewRow}>
                      <span style={specSt.specPreviewKey}>{k}</span>
                      <span style={specSt.specPreviewVal}>{String(v).substring(0, 50)}{String(v).length > 50 ? '…' : ''}</span>
                    </div>
                  ))}
                  {fieldCount(s) > 3 && (
                    <div style={specSt.specMore}>+ อีก {fieldCount(s) - 3} ข้อกำหนด</div>
                  )}
                </div>
              </div>

              {/* Card footer */}
              <div style={specSt.cardFooter}>
                <button style={specSt.btnView} onClick={() => onView(s)}>ดูรายละเอียด</button>
                <button style={{ ...specSt.btnUse, background: color }} onClick={() => onUseCompare(s)}>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                    <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                  </svg>
                  ใช้เปรียบเทียบ
                </button>
                {user.role === 'admin' && (
                  <div style={specSt.cardActions}>
                    <ActionBtn title="แก้ไข" color="#D97706" onClick={() => onEdit(s)}>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                      </svg>
                    </ActionBtn>
                    <ActionBtn title="ลบ" color="#DC2626" onClick={() => onDelete(s.id)}>
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                      </svg>
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
// Spec Detail Modal
// ─────────────────────────────────────────────────────────────
function SpecDetailModal({ spec, onClose, onEdit, onUseCompare, user }) {
  if (!spec) return null;
  const color   = CAT_COLORS[spec.category] || '#64748B';
  const catLabel = CATEGORIES.find(c => c.id === spec.category)?.label || spec.category;
  const fmt      = n => n ? n.toLocaleString('th-TH') + ' บาท' : '—';
  const [tab, setTab] = React.useState('specs');

  return (
    <div style={specDetSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={specDetSt.modal}>
        {/* Header */}
        <div style={{ ...specDetSt.header, borderTop: `4px solid ${color}` }}>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={specDetSt.topRow}>
              <span style={{ ...specDetSt.catBadge, background: color }}>{catLabel}</span>
              <span style={specDetSt.docLabel}>คุณลักษณะเฉพาะ</span>
            </div>
            <h2 style={specDetSt.title}>{spec.name}</h2>
            {spec.purpose && <p style={specDetSt.purpose}>{spec.purpose}</p>}
            <div style={specDetSt.metaRow}>
              {spec.budget > 0 && <span style={specDetSt.budget}>วงเงิน {fmt(spec.budget)}</span>}
              <span style={specDetSt.metaText}>สร้างโดย {spec.createdBy} · {spec.createdDate}</span>
            </div>
          </div>
          <div style={specDetSt.headerBtns}>
            {user?.role === 'admin' && (
              <HdrBtn color="#D97706" bg="#FFFBEB" border="#FDE68A" onClick={() => onEdit(spec)}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                แก้ไข
              </HdrBtn>
            )}
            <button style={{ ...specDetSt.useBtn, background: color }}
              onClick={() => { onUseCompare(spec); onClose(); }}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
              ใช้เปรียบเทียบ
            </button>
            <button style={specDetSt.closeBtn} onClick={onClose}>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        {/* Tabs */}
        <div style={specDetSt.tabs}>
          {[['specs', 'ข้อกำหนดสเปค'], ['history', 'ประวัติ']].map(([id, lbl]) => (
            <button key={id} style={{ ...specDetSt.tab, ...(tab === id ? specDetSt.tabOn : {}) }}
              onClick={() => setTab(id)}>{lbl}</button>
          ))}
        </div>

        {/* Content */}
        <div style={specDetSt.body}>
          {tab === 'specs' && (
            <div>
              {SPEC_GROUPS.map(group => {
                const rows = group.fields.filter(f => spec.specs?.[f]);
                if (rows.length === 0) return null;
                return (
                  <div key={group.id} style={specDetSt.section}>
                    <div style={{ ...specDetSt.sectionTitle, borderLeft: `3px solid ${color}` }}>
                      {group.label}
                    </div>
                    <table style={specDetSt.tbl}>
                      <tbody>
                        {rows.map(field => (
                          <tr key={field} style={specDetSt.tblRow}>
                            <td style={specDetSt.tblKey}>{field}</td>
                            <td style={specDetSt.tblVal}>
                              <div style={specDetSt.reqBadge}>ข้อกำหนด</div>
                              {String(spec.specs[field]).split('\n').map((ln, i) => (
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
              {(spec.editHistory || []).length === 0 && (
                <div style={{ textAlign: 'center', color: '#94A3B8', padding: '48px' }}>ไม่มีประวัติ</div>
              )}
              {[...(spec.editHistory || [])].reverse().map((h, i) => (
                <div key={i} style={{ display: 'flex', gap: '12px', padding: '12px 0', borderBottom: '1px solid #F1F5F9' }}>
                  <div style={{ width: '10px', height: '10px', borderRadius: '50%', background: color, marginTop: '5px', flexShrink: 0 }}></div>
                  <div>
                    <div style={{ display: 'flex', gap: '10px', alignItems: 'center', marginBottom: '3px' }}>
                      <span style={{ fontWeight: '700', color: '#1E293B', fontSize: '14px' }}>{h.action}</span>
                      <span style={{ color: '#2563EB', fontSize: '13px' }}>โดย {h.user}</span>
                      <span style={{ color: '#94A3B8', fontSize: '12px', marginLeft: 'auto' }}>{h.date}</span>
                    </div>
                    {h.detail && <div style={{ fontSize: '13px', color: '#64748B' }}>{h.detail}</div>}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────
// Spec Form Modal
// ─────────────────────────────────────────────────────────────
function SpecFormModal({ spec, onSave, onClose, user }) {
  const isEdit = !!spec;

  const blank = {
    id: 'sp-' + Date.now(),
    name: '', category: 'Notebook', purpose: '', budget: '',
    createdDate: '2569-05-21', createdBy: user.name,
    specs: {}, editHistory: [],
  };

  const [form, setForm]       = React.useState(() => isEdit ? { ...spec, specs: { ...spec.specs } } : blank);
  const [section, setSection] = React.useState('basic');
  const [errors, setErrors]   = React.useState({});

  const set      = (k, v) => setForm(f => ({ ...f, [k]: v }));
  const setSpec  = (k, v) => setForm(f => ({ ...f, specs: { ...f.specs, [k]: v } }));

  const validate = () => {
    const e = {};
    if (!form.name.trim()) e.name = 'กรุณากรอกชื่อสเปค';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSave = () => {
    if (!validate()) return;
    const entry = {
      date: form.createdDate, user: user.name,
      action: isEdit ? 'แก้ไขสเปค' : 'สร้างสเปคใหม่',
      detail: form.name,
    };
    onSave({ ...form, budget: parseFloat(form.budget) || 0, editHistory: [...(form.editHistory || []), entry] });
  };

  const color = CAT_COLORS[form.category] || '#64748B';
  const navSections = [
    { id: 'basic',    label: 'ข้อมูลพื้นฐาน' },
    { id: 'allspecs', label: 'ข้อมูลจำเพาะ' },
  ];
  const totalSpecCount = SPEC_GROUPS.reduce((n, g) => n + g.fields.filter(f => form.specs[f]).length, 0);

  return (
    <div style={specFrmSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={specFrmSt.modal}>
        {/* Header */}
        <div style={{ ...specFrmSt.header, borderLeft: `4px solid ${color}` }}>
          <div>
            <h2 style={specFrmSt.title}>{isEdit ? 'แก้ไขคุณลักษณะเฉพาะ' : 'สร้างคุณลักษณะเฉพาะใหม่'}</h2>
            <p style={specFrmSt.subtitle}>
              {isEdit ? `แก้ไข: ${spec.name}` : 'กำหนดข้อกำหนดสเปคสำหรับการจัดซื้อ'}
              {totalSpecCount > 0 && <span style={specFrmSt.fieldCount}>{totalSpecCount} ข้อกำหนด</span>}
            </p>
          </div>
          <button style={specFrmSt.closeBtn} onClick={onClose}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div style={specFrmSt.body}>
          {/* Left nav */}
          <div style={specFrmSt.nav}>
            {navSections.map(s => {
              const cnt = s.id === 'allspecs' ? totalSpecCount : 0;
              return (
                <button key={s.id}
                  style={{ ...specFrmSt.navBtn, ...(section === s.id ? specFrmSt.navBtnOn : {}) }}
                  onClick={() => setSection(s.id)}>
                  <span style={{ flex: 1 }}>{s.label}</span>
                  {cnt > 0 && <span style={{ ...specFrmSt.navCount, background: color + '20', color }}>{cnt}</span>}
                </button>
              );
            })}
          </div>

          {/* Content */}
          <div style={specFrmSt.content}>
            {section === 'basic' && (
              <div>
                <div style={specFrmSt.sectionHead}>ข้อมูลพื้นฐาน</div>

                <SpecField label="ชื่อคุณลักษณะเฉพาะ *" error={errors.name}>
                  <input value={form.name} onChange={e => set('name', e.target.value)}
                    placeholder="เช่น Notebook สำหรับงานสำนักงาน ปี 2569"
                    style={specFrmSt.input}/>
                </SpecField>

                <div style={specFrmSt.row2}>
                  <SpecField label="ประเภทสินค้า">
                    <select value={form.category} onChange={e => set('category', e.target.value)} style={specFrmSt.select}>
                      {CATEGORIES.filter(c => c.id !== 'all').map(c => (
                        <option key={c.id} value={c.id}>{c.label}</option>
                      ))}
                    </select>
                  </SpecField>
                  <SpecField label="วงเงินงบประมาณ (บาท/เครื่อง)">
                    <input type="number" value={form.budget} onChange={e => set('budget', e.target.value)}
                      placeholder="0" style={specFrmSt.input}/>
                  </SpecField>
                </div>

                <div style={specFrmSt.row2}>
                  <SpecField label="ปี พ.ศ. (ปีงบประมาณ)">
                    <select value={form.year || '2569'} onChange={e => set('year', e.target.value)} style={specFrmSt.select}>
                      {YEAR_OPTIONS.map(y => <option key={y} value={y}>ปี {y}</option>)}
                    </select>
                  </SpecField>
                  <SpecField label="เดือน">
                    <select value={form.month || ''} onChange={e => set('month', e.target.value)} style={specFrmSt.select}>
                      <option value="">— ไม่ระบุเดือน —</option>
                      {THAI_MONTHS.map((mn, i) => {
                        const val = String(i + 1).padStart(2, '0');
                        return <option key={val} value={val}>{mn} (เดือน {val})</option>;
                      })}
                    </select>
                  </SpecField>
                </div>

                <SpecField label="วัตถุประสงค์ / หมายเหตุ">
                  <textarea value={form.purpose} onChange={e => set('purpose', e.target.value)}
                    rows={3} placeholder="ระบุวัตถุประสงค์การใช้งาน หรือหมายเหตุเพิ่มเติม"
                    style={specFrmSt.textarea}/>
                </SpecField>

                {/* Preview */}
                <div style={{ ...specFrmSt.preview, borderColor: color + '40' }}>
                  <div style={specFrmSt.previewLabel}>ตัวอย่างแสดงผล</div>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '6px' }}>
                    <span style={{ ...specFrmSt.prevCat, background: color }}>{CATEGORIES.find(c => c.id === form.category)?.label}</span>
                    <span style={{ fontSize: '11px', color: '#94A3B8' }}>คุณลักษณะเฉพาะ</span>
                  </div>
                  <div style={{ fontSize: '16px', fontWeight: '800', color: '#1E293B' }}>{form.name || 'ชื่อสเปค'}</div>
                  {form.budget > 0 && <div style={{ fontSize: '14px', color: '#059669', fontWeight: '700', marginTop: '4px' }}>
                    วงเงิน {parseFloat(form.budget).toLocaleString('th-TH')} บาท
                  </div>}
                  <div style={{ fontSize: '13px', color: '#64748B', marginTop: '4px' }}>{totalSpecCount} ข้อกำหนด</div>
                </div>
              </div>
            )}

            {section === 'allspecs' && (
              <div>
                <div style={specFrmSt.groupHint}>
                  กรอกข้อกำหนดขั้นต่ำสำหรับแต่ละรายการ เช่น "ไม่น้อยกว่า 16GB" หรือ "รองรับ Wi-Fi 6"
                </div>
                {SPEC_GROUPS.map(group => (
                  <div key={group.id} style={specFrmSt.groupBlock}>
                    <div style={{ ...specFrmSt.sectionHead, borderLeft: `3px solid ${color}`, paddingLeft: '10px' }}>
                      {group.label}
                    </div>
                    {group.fields.map(field => (
                      <SpecField key={field} label={field}>
                        <textarea
                          value={form.specs[field] || ''}
                          onChange={e => setSpec(field, e.target.value)}
                          rows={2}
                          placeholder={`ระบุข้อกำหนดสำหรับ ${field}`}
                          style={specFrmSt.textarea}
                        />
                      </SpecField>
                    ))}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Footer */}
        <div style={specFrmSt.footer}>
          <div>
            {Object.keys(errors).length > 0 && (
              <span style={specFrmSt.errorMsg}>กรุณากรอกข้อมูลที่จำเป็น</span>
            )}
          </div>
          <div style={{ display: 'flex', gap: '10px' }}>
            <button style={specFrmSt.cancelBtn} onClick={onClose}>ยกเลิก</button>
            <button style={{ ...specFrmSt.saveBtn, background: color }} onClick={handleSave}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
              </svg>
              {isEdit ? 'บันทึกการแก้ไข' : 'สร้างสเปค'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function SpecField({ label, error, children }) {
  return (
    <div style={{ marginBottom: '14px' }}>
      <label style={{ display: 'block', fontSize: '12px', fontWeight: '700', color: '#374151', marginBottom: '5px' }}>{label}</label>
      {children}
      {error && <div style={{ fontSize: '12px', color: '#DC2626', marginTop: '4px' }}>{error}</div>}
    </div>
  );
}

// ─── Styles ──────────────────────────────────────────────────

const specSt = {
  page:          { padding: '28px' },
  toolbar:       { display: 'flex', justifyContent: 'space-between', alignItems: 'flex-end', marginBottom: '16px' },
  pageTitle:     { fontSize: '22px', fontWeight: '800', color: '#1E293B', margin: '0 0 4px' },
  pageCount:     { color: '#64748B', fontSize: '13px', margin: 0 },
  addBtn:        { display: 'flex', alignItems: 'center', gap: '6px', padding: '9px 18px', background: '#1B3A6B', color: 'white', border: 'none', borderRadius: '9px', cursor: 'pointer', fontSize: '14px', fontWeight: '700', fontFamily: 'Sarabun, sans-serif' },
  filterRow:     { display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '8px', flexWrap: 'wrap' },
  filterLabel:   { fontSize: '12px', fontWeight: '700', color: '#64748B', marginRight: '2px', whiteSpace: 'nowrap' },
  filterPill:    { display: 'flex', alignItems: 'center', gap: '5px', padding: '5px 12px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '20px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', color: '#475569', fontWeight: '600', transition: 'all 0.15s' },
  filterPillOn:  { background: '#1B3A6B', borderColor: '#1B3A6B', color: 'white' },
  filterCount:   { fontSize: '11px', fontWeight: '700', background: 'rgba(255,255,255,0.25)', padding: '1px 5px', borderRadius: '8px' },
  summaryBar:    { display: 'flex', gap: '12px', margin: '12px 0 20px', padding: '14px 18px', background: 'white', borderRadius: '12px', border: '1px solid #E2E8F0', flexWrap: 'wrap' },
  summaryItem:   { display: 'flex', flexDirection: 'column', alignItems: 'center', minWidth: '70px', cursor: 'pointer', padding: '4px 10px', borderRadius: '8px' },
  summaryYear:   { fontSize: '12px', color: '#94A3B8', fontWeight: '600', marginBottom: '2px' },
  summaryNum:    { fontSize: '22px', fontWeight: '900', color: '#059669', lineHeight: '1' },
  summaryLbl:    { fontSize: '11px', color: '#94A3B8', marginTop: '2px' },
  empty:         { textAlign: 'center', padding: '80px 20px' },
  grid:          { display: 'grid', gridTemplateColumns: 'repeat(auto-fill,minmax(320px,1fr))', gap: '16px' },
  card:          { background: 'white', borderRadius: '14px', border: '1px solid #E2E8F0', overflow: 'hidden', display: 'flex', flexDirection: 'column', transition: 'box-shadow 0.2s, transform 0.15s' },
  cardHov:       { boxShadow: '0 6px 20px rgba(0,0,0,0.1)', transform: 'translateY(-2px)' },
  cardTop:       { padding: '12px 16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  cardCat:       { color: 'white', fontSize: '12px', fontWeight: '800' },
  cardFields:    { color: 'rgba(255,255,255,0.85)', fontSize: '11px', fontWeight: '600', background: 'rgba(255,255,255,0.18)', padding: '2px 8px', borderRadius: '10px' },
  cardBody:      { padding: '16px', flex: 1 },
  cardName:      { fontSize: '16px', fontWeight: '800', color: '#1E293B', marginBottom: '6px', lineHeight: '1.3' },
  cardPurpose:   { fontSize: '12px', color: '#64748B', lineHeight: '1.5', marginBottom: '10px', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' },
  cardMeta:      { display: 'flex', gap: '12px', marginBottom: '12px', flexWrap: 'wrap' },
  metaItem:      { display: 'flex', alignItems: 'center', gap: '4px', fontSize: '12px', color: '#94A3B8' },
  specPreview:   { background: '#F8FAFC', borderRadius: '8px', padding: '10px 12px', display: 'flex', flexDirection: 'column', gap: '5px' },
  specPreviewRow:{ display: 'flex', gap: '8px', alignItems: 'flex-start' },
  specPreviewKey:{ fontSize: '11px', fontWeight: '700', color: '#64748B', minWidth: '80px', flexShrink: 0 },
  specPreviewVal:{ fontSize: '11px', color: '#374151', lineHeight: '1.4' },
  specMore:      { fontSize: '11px', color: '#94A3B8', marginTop: '2px' },
  cardFooter:    { padding: '12px 16px', borderTop: '1px solid #F1F5F9', display: 'flex', gap: '8px', alignItems: 'center' },
  btnView:       { padding: '6px 12px', border: '1.5px solid #E2E8F0', background: 'white', color: '#374151', borderRadius: '7px', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', fontWeight: '600' },
  btnUse:        { display: 'flex', alignItems: 'center', gap: '5px', padding: '6px 12px', border: 'none', color: 'white', borderRadius: '7px', cursor: 'pointer', fontSize: '12px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700' },
  cardActions:   { display: 'flex', gap: '5px', marginLeft: 'auto' },
};

const specDetSt = {
  overlay:      { position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', zIndex: 200, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' },
  modal:        { background: 'white', borderRadius: '16px', width: '760px', maxWidth: '100%', maxHeight: '92vh', display: 'flex', flexDirection: 'column', boxShadow: '0 24px 80px rgba(0,0,0,0.25)' },
  header:       { padding: '22px 26px 18px', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', borderBottom: '1px solid #F1F5F9', gap: '16px' },
  topRow:       { display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '6px' },
  catBadge:     { color: 'white', fontSize: '11px', fontWeight: '800', padding: '3px 8px', borderRadius: '5px' },
  docLabel:     { fontSize: '12px', fontWeight: '700', color: '#94A3B8', background: '#F1F5F9', padding: '3px 8px', borderRadius: '4px' },
  title:        { fontSize: '19px', fontWeight: '800', color: '#1E293B', margin: '0 0 6px', lineHeight: '1.3' },
  purpose:      { fontSize: '13px', color: '#64748B', margin: '0 0 8px', lineHeight: '1.5' },
  metaRow:      { display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' },
  budget:       { fontSize: '18px', fontWeight: '900', color: '#059669' },
  metaText:     { fontSize: '12px', color: '#94A3B8' },
  headerBtns:   { display: 'flex', gap: '7px', flexShrink: 0 },
  useBtn:       { display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 14px', border: 'none', color: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700' },
  closeBtn:     { padding: '8px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', color: '#64748B', display: 'flex' },
  tabs:         { display: 'flex', borderBottom: '1px solid #E2E8F0', padding: '0 26px' },
  tab:          { padding: '12px 18px', border: 'none', background: 'transparent', fontSize: '14px', fontWeight: '600', color: '#94A3B8', cursor: 'pointer', borderBottom: '2px solid transparent', fontFamily: 'Sarabun, sans-serif' },
  tabOn:        { color: '#1B3A6B', borderBottomColor: '#1B3A6B' },
  body:         { overflowY: 'auto', padding: '22px 26px', flex: 1 },
  section:      { marginBottom: '22px' },
  sectionTitle: { fontSize: '13px', fontWeight: '800', color: '#1B3A6B', paddingLeft: '10px', marginBottom: '10px' },
  tbl:          { width: '100%', borderCollapse: 'collapse' },
  tblRow:       { borderBottom: '1px solid #F8FAFC' },
  tblKey:       { width: '170px', padding: '8px 12px 8px 0', fontSize: '12px', color: '#64748B', fontWeight: '600', verticalAlign: 'top' },
  tblVal:       { padding: '8px 0', fontSize: '13px', color: '#1E293B', lineHeight: '1.6', verticalAlign: 'top' },
  reqBadge:     { display: 'inline-block', fontSize: '10px', fontWeight: '700', color: '#7C3AED', background: '#F5F3FF', padding: '1px 6px', borderRadius: '4px', marginBottom: '4px' },
};

const specFrmSt = {
  overlay:      { position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', zIndex: 300, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' },
  modal:        { background: 'white', borderRadius: '16px', width: '860px', maxWidth: '100%', maxHeight: '92vh', display: 'flex', flexDirection: 'column', boxShadow: '0 24px 80px rgba(0,0,0,0.25)' },
  header:       { padding: '20px 26px', borderBottom: '1px solid #E2E8F0', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' },
  title:        { fontSize: '19px', fontWeight: '800', color: '#1E293B', margin: '0 0 4px' },
  subtitle:     { fontSize: '13px', color: '#94A3B8', margin: 0, display: 'flex', alignItems: 'center', gap: '10px' },
  fieldCount:   { fontSize: '12px', background: '#EFF6FF', color: '#2563EB', padding: '2px 8px', borderRadius: '4px', fontWeight: '700' },
  closeBtn:     { padding: '8px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', color: '#64748B', display: 'flex' },
  body:         { display: 'flex', flex: 1, overflow: 'hidden' },
  nav:          { width: '168px', borderRight: '1px solid #F1F5F9', padding: '12px 10px', display: 'flex', flexDirection: 'column', gap: '2px', overflowY: 'auto', flexShrink: 0 },
  navBtn:       { width: '100%', display: 'flex', alignItems: 'center', padding: '8px 10px', border: 'none', background: 'transparent', borderRadius: '7px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', textAlign: 'left', color: '#64748B', gap: '6px' },
  navBtnOn:     { background: '#EFF6FF', color: '#1B3A6B', fontWeight: '700' },
  navCount:     { fontSize: '11px', fontWeight: '700', padding: '1px 6px', borderRadius: '8px', flexShrink: 0 },
  content:      { flex: 1, overflowY: 'auto', padding: '20px 26px' },
  sectionHead:  { fontSize: '14px', fontWeight: '800', color: '#1B3A6B', margin: '0 0 12px', paddingBottom: '8px', borderBottom: '1.5px solid #EFF6FF' },
  groupBlock:   { marginBottom: '28px', paddingBottom: '20px', borderBottom: '1px solid #F1F5F9' },
  groupHint:    { fontSize: '12px', color: '#94A3B8', background: '#F8FAFC', padding: '8px 12px', borderRadius: '8px', marginBottom: '16px', lineHeight: '1.5' },
  row2:         { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' },
  input:        { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', outline: 'none', boxSizing: 'border-box', color: '#1E293B' },
  select:       { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', outline: 'none', background: 'white' },
  textarea:     { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', outline: 'none', resize: 'vertical', boxSizing: 'border-box', lineHeight: '1.5', color: '#1E293B' },
  preview:      { marginTop: '20px', padding: '16px', background: '#F8FAFC', borderRadius: '10px', border: '1.5px dashed #CBD5E1' },
  previewLabel: { fontSize: '11px', fontWeight: '700', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: '10px' },
  prevCat:      { color: 'white', fontSize: '11px', fontWeight: '700', padding: '3px 8px', borderRadius: '4px' },
  footer:       { padding: '14px 26px', borderTop: '1px solid #E2E8F0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  errorMsg:     { color: '#DC2626', fontSize: '13px', fontWeight: '600' },
  cancelBtn:    { padding: '9px 22px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', color: '#374151', fontWeight: '600' },
  saveBtn:      { display: 'flex', alignItems: 'center', gap: '6px', padding: '9px 22px', border: 'none', color: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700' },
};

Object.assign(window, { SpecListView, SpecDetailModal, SpecFormModal });
