// ============================================================
// src/components/CategoryManager.jsx
// ============================================================

const PRESET_COLORS = [
  '#2563EB','#7C3AED','#DB2777','#059669',
  '#DC2626','#EA580C','#0369A1','#D97706',
  '#0D9488','#475569','#65A30D','#9333EA',
];

function CategoryManager({ categories, products, onSave, onClose }) {
  const editable = categories.filter(c => c.id !== 'all');
  const [list,     setList]     = React.useState(editable.map(c => ({ ...c })));
  const [showAdd,  setShowAdd]  = React.useState(false);
  const [newName,  setNewName]  = React.useState('');
  const [newShort, setNewShort] = React.useState('');
  const [newColor, setNewColor] = React.useState(PRESET_COLORS[0]);
  const [error,    setError]    = React.useState('');
  const [editId,   setEditId]   = React.useState(null); // for inline edit

  const countIn = catId => products.filter(p => p.category === catId).length;

  // ── Add ──────────────────────────────────────────────────
  const handleAdd = () => {
    const name = newName.trim();
    if (!name) { setError('กรุณากรอกชื่อหมวดหมู่'); return; }
    const id = name.replace(/\s+/g, '-');
    if (list.some(c => c.id.toLowerCase() === id.toLowerCase())) {
      setError('ชื่อหมวดหมู่นี้มีอยู่แล้ว'); return;
    }
    const short = newShort.trim() || name.substring(0, 4).toUpperCase();
    setList(prev => [...prev, { id, label: name, short, color: newColor }]);
    setNewName(''); setNewShort(''); setNewColor(PRESET_COLORS[0]);
    setShowAdd(false); setError('');
  };

  // ── Delete ───────────────────────────────────────────────
  const handleDelete = catId => {
    const cnt = countIn(catId);
    const msg = cnt > 0
      ? `หมวดหมู่นี้มีสินค้า ${cnt} รายการ\nสินค้าเหล่านั้นจะยังคงอยู่แต่ไม่ถูกจัดหมวด\nต้องการลบใช่ไหม?`
      : 'ต้องการลบหมวดหมู่นี้ใช่ไหม?';
    if (!window.confirm(msg)) return;
    setList(prev => prev.filter(c => c.id !== catId));
  };

  // ── Inline color edit ─────────────────────────────────────
  const updateColor = (catId, color) => {
    setList(prev => prev.map(c => c.id === catId ? { ...c, color } : c));
  };

  // ── Save ─────────────────────────────────────────────────
  const handleSave = () => {
    const full = [{ id: 'all', label: 'ทั้งหมด', short: 'ALL', color: '' }, ...list];
    onSave(full);
    onClose();
  };

  return (
    <div style={cmSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={cmSt.modal}>
        {/* Header */}
        <div style={cmSt.header}>
          <div>
            <h2 style={cmSt.title}>จัดการหมวดหมู่สินค้า</h2>
            <p style={cmSt.subtitle}>{list.length} หมวดหมู่</p>
          </div>
          <button style={cmSt.closeBtn} onClick={onClose}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div style={cmSt.body}>
          {/* Category list */}
          <div style={cmSt.list}>
            {list.map((cat, idx) => (
              <div key={cat.id} style={cmSt.row}>
                {/* Color swatch + picker */}
                <div style={cmSt.colorWrap}>
                  <div style={{ ...cmSt.swatch, background: cat.color }}
                    onClick={() => setEditId(editId === cat.id ? null : cat.id)}
                    title="คลิกเพื่อเปลี่ยนสี"></div>
                  {editId === cat.id && (
                    <div style={cmSt.colorPicker}>
                      {PRESET_COLORS.map(c => (
                        <div key={c}
                          style={{ ...cmSt.colorDot, background: c, ...(cat.color === c ? cmSt.colorDotOn : {}) }}
                          onClick={() => { updateColor(cat.id, c); setEditId(null); }}>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                {/* Info */}
                <div style={cmSt.catInfo}>
                  <div style={cmSt.catName}>{cat.label}</div>
                  <div style={cmSt.catMeta}>
                    <span style={{ ...cmSt.shortTag, background: cat.color }}>{cat.short}</span>
                    <span style={cmSt.catCount}>{countIn(cat.id)} รายการ</span>
                  </div>
                </div>

                {/* Delete */}
                <button style={cmSt.deleteBtn} onClick={() => handleDelete(cat.id)} title="ลบหมวดหมู่">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                  </svg>
                </button>
              </div>
            ))}
          </div>

          {/* Add form */}
          {showAdd ? (
            <div style={cmSt.addBox}>
              <div style={cmSt.addTitle}>เพิ่มหมวดหมู่ใหม่</div>

              <div style={cmSt.addRow}>
                <div style={cmSt.addField}>
                  <label style={cmSt.addLabel}>ชื่อหมวดหมู่ *</label>
                  <input value={newName} onChange={e => setNewName(e.target.value)}
                    placeholder="เช่น Tablet" style={cmSt.addInput} autoFocus
                    onKeyDown={e => e.key === 'Enter' && handleAdd()}/>
                </div>
                <div style={cmSt.addField}>
                  <label style={cmSt.addLabel}>ชื่อย่อ</label>
                  <input value={newShort} onChange={e => setNewShort(e.target.value)}
                    placeholder="เช่น TAB (ไม่เกิน 5 ตัว)" maxLength={5} style={cmSt.addInput}/>
                </div>
              </div>

              <div style={{ marginBottom: '14px' }}>
                <label style={cmSt.addLabel}>สีประจำหมวด</label>
                <div style={cmSt.colorGrid}>
                  {PRESET_COLORS.map(c => (
                    <div key={c}
                      style={{ ...cmSt.colorDotLg, background: c, ...(newColor === c ? cmSt.colorDotLgOn : {}) }}
                      onClick={() => setNewColor(c)}>
                    </div>
                  ))}
                </div>
              </div>

              {error && <div style={cmSt.errorMsg}>{error}</div>}

              <div style={{ display: 'flex', gap: '8px' }}>
                <button style={cmSt.cancelAddBtn} onClick={() => { setShowAdd(false); setError(''); }}>ยกเลิก</button>
                <button style={{ ...cmSt.confirmAddBtn, background: newColor }} onClick={handleAdd}>
                  เพิ่มหมวดหมู่
                </button>
              </div>
            </div>
          ) : (
            <button style={cmSt.addBtn} onClick={() => setShowAdd(true)}>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              เพิ่มหมวดหมู่ใหม่
            </button>
          )}
        </div>

        {/* Footer */}
        <div style={cmSt.footer}>
          <span style={cmSt.footerNote}>การเปลี่ยนแปลงจะมีผลทันที</span>
          <div style={{ display: 'flex', gap: '10px' }}>
            <button style={cmSt.cancelBtn} onClick={onClose}>ยกเลิก</button>
            <button style={cmSt.saveBtn} onClick={handleSave}>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              บันทึก
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

const cmSt = {
  overlay:       { position:'fixed', inset:0, background:'rgba(15,23,42,0.55)', zIndex:400, display:'flex', alignItems:'center', justifyContent:'center', padding:'20px' },
  modal:         { background:'white', borderRadius:'16px', width:'480px', maxWidth:'100%', maxHeight:'88vh', display:'flex', flexDirection:'column', boxShadow:'0 24px 80px rgba(0,0,0,0.25)' },
  header:        { padding:'20px 24px', borderBottom:'1px solid #E2E8F0', display:'flex', justifyContent:'space-between', alignItems:'flex-start' },
  title:         { fontSize:'18px', fontWeight:'800', color:'#1E293B', margin:'0 0 3px' },
  subtitle:      { fontSize:'13px', color:'#94A3B8', margin:0 },
  closeBtn:      { padding:'7px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'8px', cursor:'pointer', color:'#64748B', display:'flex' },
  body:          { overflowY:'auto', padding:'16px 24px', flex:1 },
  list:          { display:'flex', flexDirection:'column', gap:'6px', marginBottom:'12px' },
  row:           { display:'flex', alignItems:'center', gap:'12px', padding:'10px 12px', background:'#F8FAFC', borderRadius:'10px', border:'1px solid #E2E8F0', position:'relative' },
  colorWrap:     { position:'relative', flexShrink:0 },
  swatch:        { width:'32px', height:'32px', borderRadius:'8px', cursor:'pointer', border:'2px solid rgba(0,0,0,0.08)', flexShrink:0 },
  colorPicker:   { position:'absolute', top:'38px', left:0, background:'white', border:'1.5px solid #E2E8F0', borderRadius:'10px', padding:'10px', display:'grid', gridTemplateColumns:'repeat(4,1fr)', gap:'6px', boxShadow:'0 8px 24px rgba(0,0,0,0.12)', zIndex:10, width:'130px' },
  colorDot:      { width:'22px', height:'22px', borderRadius:'50%', cursor:'pointer', border:'2px solid transparent' },
  colorDotOn:    { border:'2px solid white', outline:'2px solid #1B3A6B' },
  catInfo:       { flex:1 },
  catName:       { fontSize:'14px', fontWeight:'700', color:'#1E293B', marginBottom:'3px' },
  catMeta:       { display:'flex', alignItems:'center', gap:'8px' },
  shortTag:      { color:'white', fontSize:'10px', fontWeight:'800', padding:'1px 6px', borderRadius:'4px' },
  catCount:      { fontSize:'11px', color:'#94A3B8' },
  deleteBtn:     { padding:'6px', border:'1.5px solid #FEE2E2', background:'white', borderRadius:'7px', cursor:'pointer', color:'#DC2626', display:'flex', flexShrink:0 },
  addBox:        { background:'#F8FAFC', border:'1.5px dashed #CBD5E1', borderRadius:'12px', padding:'16px', marginTop:'8px' },
  addTitle:      { fontSize:'13px', fontWeight:'800', color:'#1B3A6B', marginBottom:'14px' },
  addRow:        { display:'grid', gridTemplateColumns:'1fr 1fr', gap:'12px', marginBottom:'12px' },
  addField:      {},
  addLabel:      { display:'block', fontSize:'12px', fontWeight:'700', color:'#374151', marginBottom:'5px' },
  addInput:      { width:'100%', padding:'8px 12px', border:'1.5px solid #E2E8F0', borderRadius:'8px', fontSize:'13px', fontFamily:'Sarabun, sans-serif', outline:'none', boxSizing:'border-box' },
  colorGrid:     { display:'flex', gap:'8px', flexWrap:'wrap', marginTop:'6px' },
  colorDotLg:    { width:'28px', height:'28px', borderRadius:'7px', cursor:'pointer', border:'2px solid transparent' },
  colorDotLgOn:  { border:'2px solid white', outline:'2.5px solid #1B3A6B', borderRadius:'7px' },
  errorMsg:      { color:'#DC2626', fontSize:'12px', fontWeight:'600', marginBottom:'10px' },
  cancelAddBtn:  { padding:'7px 16px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'7px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', color:'#374151', fontWeight:'600' },
  confirmAddBtn: { padding:'7px 16px', border:'none', color:'white', borderRadius:'7px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', fontWeight:'700' },
  addBtn:        { width:'100%', display:'flex', alignItems:'center', justifyContent:'center', gap:'7px', padding:'11px', border:'1.5px dashed #CBD5E1', background:'white', borderRadius:'10px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', color:'#64748B', fontWeight:'600', marginTop:'8px' },
  footer:        { padding:'14px 24px', borderTop:'1px solid #E2E8F0', display:'flex', justifyContent:'space-between', alignItems:'center' },
  footerNote:    { fontSize:'12px', color:'#94A3B8' },
  cancelBtn:     { padding:'8px 20px', border:'1.5px solid #E2E8F0', background:'white', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', color:'#374151', fontWeight:'600' },
  saveBtn:       { display:'flex', alignItems:'center', gap:'6px', padding:'8px 20px', border:'none', background:'#1B3A6B', color:'white', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', fontWeight:'700' },
};

Object.assign(window, { CategoryManager });
