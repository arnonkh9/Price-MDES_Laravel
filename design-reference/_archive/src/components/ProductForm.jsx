// ============================================================
// src/components/ProductForm.jsx
// ============================================================

function ProductFormModal({ product, onSave, onClose, user }) {
  const isEdit = !!product;

  const blankForm = {
    id: 'p-' + Date.now(),
    category: 'Notebook', brand: '', model: '',
    price: '', priceUnit: 'บาท/เครื่อง',
    priceDate: '2569-05-21', priceRef: 'ราคากลาง สพร. ปี 2569',
    specs: {}, editHistory: [],
  };

  const [form, setForm]     = React.useState(() => isEdit ? { ...product, specs: { ...product.specs } } : blankForm);
  const [section, setSection] = React.useState('basic');
  const [errors, setErrors]   = React.useState({});

  const set    = (key, val) => setForm(f => ({ ...f, [key]: val }));
  const setSpec = (key, val) => setForm(f => ({ ...f, specs: { ...f.specs, [key]: val } }));

  const validate = () => {
    const e = {};
    if (!form.brand.trim())  e.brand = 'กรุณากรอกแบรนด์';
    if (!form.model.trim())  e.model = 'กรุณากรอกรุ่น/โมเดล';
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const handleSave = () => {
    if (!validate()) return;
    const entry = {
      date:   form.priceDate || '2569-05-21',
      user:   user.name,
      action: isEdit ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูลใหม่',
      detail: isEdit ? `แก้ไข ${form.model}` : `เพิ่ม ${form.model}`,
    };
    onSave({ ...form, price: parseFloat(form.price) || 0, editHistory: [...(form.editHistory || []), entry] });
  };

  const navSections = [
    { id: 'basic', label: 'ข้อมูลพื้นฐาน' },
    ...SPEC_GROUPS.map(g => ({ id: g.id, label: g.label })),
  ];

  const activeGroup = SPEC_GROUPS.find(g => g.id === section);

  return (
    <div style={formSt.overlay} onClick={e => e.target === e.currentTarget && onClose()}>
      <div style={formSt.modal}>

        {/* Header */}
        <div style={formSt.header}>
          <div>
            <h2 style={formSt.title}>{isEdit ? 'แก้ไขข้อมูลสินค้า' : 'เพิ่มสินค้าใหม่'}</h2>
            <p style={formSt.subtitle}>{isEdit ? `กำลังแก้ไข: ${product.model}` : 'กรอกข้อมูลสินค้าและสเปค'}</p>
          </div>
          <button style={formSt.closeBtn} onClick={onClose}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        {/* Body: left nav + right form */}
        <div style={formSt.body}>
          {/* Left nav */}
          <div style={formSt.nav}>
            {navSections.map(s => (
              <button key={s.id}
                style={{ ...formSt.navBtn, ...(section === s.id ? formSt.navBtnOn : {}) }}
                onClick={() => setSection(s.id)}>
                {s.label}
              </button>
            ))}
          </div>

          {/* Right content */}
          <div style={formSt.content}>

            {section === 'basic' && (
              <div>
                <div style={formSt.sectionHead}>ข้อมูลพื้นฐาน</div>

                <div style={formSt.row2}>
                  <Field label="ประเภทสินค้า *" error={null}>
                    <select value={form.category} onChange={e => set('category', e.target.value)} style={formSt.select}>
                      {CATEGORIES.filter(c => c.id !== 'all').map(c => (
                        <option key={c.id} value={c.id}>{c.label}</option>
                      ))}
                    </select>
                  </Field>
                  <Field label="แบรนด์ *" error={errors.brand}>
                    <input value={form.brand} onChange={e => set('brand', e.target.value)}
                      placeholder="เช่น ASUS, HP, Dell" style={formSt.input}/>
                  </Field>
                </div>

                <Field label="รุ่น / โมเดล *" error={errors.model}>
                  <input value={form.model} onChange={e => set('model', e.target.value)}
                    placeholder="เช่น Vivobook 16 (X1607CA-MB535WA)" style={formSt.input}/>
                </Field>

                <div style={{ ...formSt.sectionHead, marginTop: '24px' }}>ราคากลาง</div>

                <div style={formSt.row2}>
                  <Field label="ราคา (บาท)">
                    <input type="number" value={form.price} onChange={e => set('price', e.target.value)}
                      placeholder="0" style={formSt.input}/>
                  </Field>
                  <Field label="วันที่อ้างอิง">
                    <input value={form.priceDate} onChange={e => set('priceDate', e.target.value)}
                      placeholder="2569-05-21" style={formSt.input}/>
                  </Field>
                </div>

                <Field label="แหล่งอ้างอิง">
                  <input value={form.priceRef} onChange={e => set('priceRef', e.target.value)}
                    placeholder="ราคากลาง สพร. ปี 2569" style={formSt.input}/>
                </Field>

                <div style={formSt.previewBox}>
                  <div style={formSt.previewLabel}>ตัวอย่างแสดงผล</div>
                  <div style={formSt.previewRow}>
                    <span style={{ ...formSt.previewCat, background: CAT_COLORS[form.category] || '#64748B' }}>
                      {CATEGORIES.find(c => c.id === form.category)?.label}
                    </span>
                    <span style={formSt.previewBrand}>{form.brand || 'แบรนด์'}</span>
                  </div>
                  <div style={formSt.previewModel}>{form.model || 'รุ่น / โมเดล'}</div>
                  <div style={formSt.previewPrice}>
                    {form.price ? parseFloat(form.price).toLocaleString('th-TH') + ' บาท' : '—'}
                  </div>
                </div>
              </div>
            )}

            {activeGroup && section !== 'basic' && (
              <div>
                <div style={formSt.sectionHead}>{activeGroup.label}</div>
                {activeGroup.fields.map(field => (
                  <Field key={field} label={field}>
                    <textarea
                      value={form.specs[field] || ''}
                      onChange={e => setSpec(field, e.target.value)}
                      placeholder={`กรอก ${field}`}
                      rows={2}
                      style={formSt.textarea}
                    />
                  </Field>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Footer */}
        <div style={formSt.footer}>
          <div style={formSt.footerLeft}>
            {Object.keys(errors).length > 0 && (
              <span style={formSt.errorMsg}>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                กรุณากรอกข้อมูลที่จำเป็น
              </span>
            )}
          </div>
          <div style={formSt.footerRight}>
            <button style={formSt.cancelBtn} onClick={onClose}>ยกเลิก</button>
            <button style={formSt.saveBtn} onClick={handleSave}>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
              </svg>
              {isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มสินค้า'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function Field({ label, error, children }) {
  return (
    <div style={formSt.field}>
      <label style={formSt.fieldLabel}>{label}</label>
      {children}
      {error && <div style={formSt.fieldError}>{error}</div>}
    </div>
  );
}

const formSt = {
  overlay:     { position: 'fixed', inset: 0, background: 'rgba(15,23,42,0.55)', zIndex: 300, display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '20px' },
  modal:       { background: 'white', borderRadius: '16px', width: '880px', maxWidth: '100%', maxHeight: '92vh', display: 'flex', flexDirection: 'column', boxShadow: '0 24px 80px rgba(0,0,0,0.25)' },
  header:      { padding: '20px 26px', borderBottom: '1px solid #E2E8F0', display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' },
  title:       { fontSize: '19px', fontWeight: '800', color: '#1E293B', margin: '0 0 3px' },
  subtitle:    { fontSize: '13px', color: '#94A3B8', margin: 0 },
  closeBtn:    { padding: '8px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', color: '#64748B', display: 'flex' },
  body:        { display: 'flex', flex: 1, overflow: 'hidden' },
  nav:         { width: '168px', borderRight: '1px solid #F1F5F9', padding: '12px 10px', display: 'flex', flexDirection: 'column', gap: '2px', overflowY: 'auto', flexShrink: 0 },
  navBtn:      { width: '100%', padding: '8px 10px', border: 'none', background: 'transparent', borderRadius: '7px', cursor: 'pointer', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', textAlign: 'left', color: '#64748B' },
  navBtnOn:    { background: '#EFF6FF', color: '#1B3A6B', fontWeight: '700' },
  content:     { flex: 1, overflowY: 'auto', padding: '20px 26px' },
  sectionHead: { fontSize: '14px', fontWeight: '800', color: '#1B3A6B', margin: '0 0 16px', paddingBottom: '8px', borderBottom: '1.5px solid #EFF6FF' },
  row2:        { display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' },
  field:       { marginBottom: '14px' },
  fieldLabel:  { display: 'block', fontSize: '12px', fontWeight: '700', color: '#374151', marginBottom: '5px' },
  fieldError:  { fontSize: '12px', color: '#DC2626', marginTop: '4px' },
  input:       { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', outline: 'none', boxSizing: 'border-box', color: '#1E293B' },
  select:      { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', outline: 'none', background: 'white' },
  textarea:    { width: '100%', padding: '9px 12px', border: '1.5px solid #E2E8F0', borderRadius: '8px', fontSize: '13px', fontFamily: 'Sarabun, sans-serif', outline: 'none', resize: 'vertical', boxSizing: 'border-box', lineHeight: '1.5', color: '#1E293B' },
  previewBox:  { marginTop: '24px', padding: '16px', background: '#F8FAFC', borderRadius: '10px', border: '1.5px dashed #CBD5E1' },
  previewLabel:{ fontSize: '11px', fontWeight: '700', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: '10px' },
  previewRow:  { display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '6px' },
  previewCat:  { color: 'white', fontSize: '11px', fontWeight: '700', padding: '3px 8px', borderRadius: '4px' },
  previewBrand:{ fontSize: '13px', color: '#64748B', fontWeight: '600' },
  previewModel:{ fontSize: '16px', fontWeight: '800', color: '#1E293B', marginBottom: '6px' },
  previewPrice:{ fontSize: '20px', fontWeight: '900', color: '#059669' },
  footer:      { padding: '14px 26px', borderTop: '1px solid #E2E8F0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' },
  footerLeft:  {},
  footerRight: { display: 'flex', gap: '10px' },
  errorMsg:    { display: 'flex', alignItems: 'center', gap: '6px', color: '#DC2626', fontSize: '13px', fontWeight: '600' },
  cancelBtn:   { padding: '9px 22px', border: '1.5px solid #E2E8F0', background: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', color: '#374151', fontWeight: '600' },
  saveBtn:     { display: 'flex', alignItems: 'center', gap: '6px', padding: '9px 22px', border: 'none', background: '#1B3A6B', color: 'white', borderRadius: '8px', cursor: 'pointer', fontSize: '14px', fontFamily: 'Sarabun, sans-serif', fontWeight: '700' },
};

Object.assign(window, { ProductFormModal });
