// ============================================================
// src/components/Layout.jsx  –  Sidebar + Header
// ============================================================

function Sidebar({ category, onCategory, view, onView, compareCount, productCounts, specCount, baseSpec, compareCount3, onManageCategories }) {
  return (
    <div style={sidebarSt.sidebar}>
      {/* Brand */}
      <div style={sidebarSt.brand}>
        <svg width="34" height="34" viewBox="0 0 48 48" fill="none">
          <rect width="48" height="48" rx="11" fill="white" fillOpacity="0.12"/>
          <path d="M10 30 L16 18 L22 26 L28 14 L34 22 L38 18" stroke="white" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
          <circle cx="38" cy="18" r="3" fill="#60A5FA"/>
        </svg>
        <div>
          <div style={sidebarSt.brandName}>ระบบราคากลาง</div>
          <div style={sidebarSt.brandSub}>Price Reference System</div>
        </div>
      </div>

      <nav style={sidebarSt.nav}>
        {/* Dashboard */}
        <div style={sidebarSt.sectionLabel}>เมนูหลัก</div>
        <SidebarBtn
          active={view === 'dashboard'}
          onClick={() => onView('dashboard')}
          icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>}
          label="แดชบอร์ด"
        />

        {/* Categories */}
        <div style={sidebarSt.sectionLabel}>หมวดสินค้า</div>
        {CATEGORIES.map(cat => (
          <SidebarBtn
            key={cat.id}
            active={view === 'list' && category === cat.id}
            onClick={() => { onCategory(cat.id); onView('list'); }}
            icon={
              <span style={{
                fontSize:'10px', fontWeight:'700',
                background: cat.id === 'all'
                  ? 'rgba(255,255,255,0.15)'
                  : (CAT_COLORS[cat.id] || '#64748B') + '30',
                color: cat.id === 'all' ? 'rgba(255,255,255,0.7)' : (CAT_COLORS[cat.id] || '#94A3B8'),
                padding:'2px 5px', borderRadius:'4px', minWidth:'34px',
                textAlign:'center', display:'inline-block',
              }}>{cat.short}</span>
            }
            label={cat.label}
            badge={cat.id !== 'all' && productCounts[cat.id] ? productCounts[cat.id] : null}
          />
        ))}

        {/* Spec / TOR */}
        <div style={sidebarSt.sectionLabel}>คุณลักษณะเฉพาะ</div>
        <SidebarBtn
          active={view === 'specs'}
          onClick={() => onView('specs')}
          icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>}
          label="สร้าง / จัดการสเปค"
          badge={specCount > 0 ? specCount : null}
        />
        <SidebarBtn
          active={view === 'vendor3'}
          onClick={() => onView('vendor3')}
          icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="3" y1="20" x2="21" y2="20"/></svg>}
          label="เทียบราคา 3 เจ้า"
          badge={compareCount3 > 0 ? compareCount3 : null}
        />

        {/* Compare */}
        {(compareCount > 0 || baseSpec) && (
          <>
            <div style={sidebarSt.sectionLabel}>เปรียบเทียบ</div>
            <SidebarBtn
              active={view === 'compare'}
              onClick={() => onView('compare')}
              icon={<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>}
              label="เปรียบเทียบสินค้า"
              badge={compareCount || null}
              badgeColor="#EF4444"
            />
          </>
        )}
      </nav>

      {/* Footer */}
      {/* Manage categories (admin) */}
      {onManageCategories && (
        <button style={sidebarSt.manageBtn} onClick={onManageCategories} title="จัดการหมวดหมู่">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/>
          </svg>
          จัดการหมวดหมู่
        </button>
      )}

      <div style={sidebarSt.footer}>
        <div style={sidebarSt.footerText}>v1.0.0 · ปี 2569</div>
      </div>
    </div>
  );
}

function SidebarBtn({ active, onClick, icon, label, badge, badgeColor }) {
  return (
    <button
      style={{ ...sidebarSt.navBtn, ...(active ? sidebarSt.navBtnActive : {}) }}
      onClick={onClick}
    >
      <span style={sidebarSt.navBtnIcon}>{icon}</span>
      <span style={sidebarSt.navBtnLabel}>{label}</span>
      {badge != null && (
        <span style={{ ...sidebarSt.badge, background: badgeColor || 'rgba(255,255,255,0.15)' }}>
          {badge}
        </span>
      )}
    </button>
  );
}

const sidebarSt = {
  sidebar:      { width:'240px', height:'100vh', background:'#1B3A6B', display:'flex', flexDirection:'column', position:'fixed', left:0, top:0, overflowY:'auto', zIndex:100 },
  brand:        { padding:'20px 18px', display:'flex', alignItems:'center', gap:'12px', borderBottom:'1px solid rgba(255,255,255,0.08)', flexShrink:0 },
  brandName:    { color:'white', fontWeight:'800', fontSize:'14px', lineHeight:'1.2' },
  brandSub:     { color:'rgba(255,255,255,0.4)', fontSize:'10px', marginTop:'2px' },
  nav:          { padding:'10px 10px', flex:1 },
  sectionLabel: { color:'rgba(255,255,255,0.35)', fontSize:'10px', fontWeight:'700', letterSpacing:'0.1em', textTransform:'uppercase', padding:'14px 8px 5px' },
  navBtn:       { width:'100%', display:'flex', alignItems:'center', gap:'9px', padding:'8px 10px', border:'none', background:'transparent', color:'rgba(255,255,255,0.65)', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', textAlign:'left', transition:'all 0.15s' },
  navBtnActive: { background:'rgba(255,255,255,0.14)', color:'white' },
  navBtnIcon:   { flexShrink:0, display:'flex', alignItems:'center' },
  navBtnLabel:  { flex:1, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' },
  badge:        { fontSize:'11px', color:'white', padding:'1px 7px', borderRadius:'10px', fontWeight:'700', flexShrink:0 },
  footer:       { padding:'14px 18px', borderTop:'1px solid rgba(255,255,255,0.08)' },
  footerText:   { color:'rgba(255,255,255,0.25)', fontSize:'11px' },
  manageBtn:    { width:'calc(100% - 20px)', margin:'0 10px 8px', display:'flex', alignItems:'center', gap:'7px', padding:'7px 10px', border:'1px solid rgba(255,255,255,0.12)', background:'transparent', color:'rgba(255,255,255,0.45)', borderRadius:'8px', cursor:'pointer', fontSize:'12px', fontFamily:'Sarabun, sans-serif' },
};


// ─────────────────────────────────────────────────────────────
// Header
// ─────────────────────────────────────────────────────────────
function AppHeader({ user, onLogout, search, onSearch, onAdd, onImport, onExport }) {
  const [showUserMenu, setShowUserMenu] = React.useState(false);

  return (
    <div style={headerSt.header}>
      {/* Search */}
      <div style={headerSt.searchBox}>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" strokeWidth="2" strokeLinecap="round" style={{flexShrink:0}}>
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          type="text" value={search} onChange={e => onSearch(e.target.value)}
          placeholder="ค้นหา Brand, Model, สเปค..."
          style={headerSt.searchInput}
        />
        {search && (
          <button style={headerSt.clearBtn} onClick={() => onSearch('')}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        )}
      </div>

      <div style={headerSt.actions}>
        {/* Import */}
        <button style={headerSt.btnSecondary} onClick={onImport} title="นำเข้าจาก Excel">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          <span>นำเข้า</span>
        </button>

        {/* Export */}
        <button style={headerSt.btnSecondary} onClick={onExport} title="ส่งออก Excel">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          <span>ส่งออก</span>
        </button>

        {/* Add */}
        {user.role === 'admin' && (
          <button style={headerSt.btnPrimary} onClick={onAdd}>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            เพิ่มสินค้า
          </button>
        )}

        {/* User */}
        <div style={headerSt.userWrap}>
          <button style={headerSt.userBtn} onClick={() => setShowUserMenu(v => !v)}>
            <div style={headerSt.avatar}>{user.name.charAt(0)}</div>
            <div style={headerSt.userInfo}>
              <div style={headerSt.userName}>{user.name}</div>
              <div style={headerSt.userDept}>{user.department}</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" strokeWidth="2" strokeLinecap="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>

          {showUserMenu && (
            <div style={headerSt.dropdown}>
              <div style={headerSt.dropUser}>
                <div style={headerSt.dropName}>{user.name}</div>
                <div style={headerSt.dropRole}>{user.role === 'admin' ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งาน'}</div>
              </div>
              <div style={headerSt.dropDivider}></div>
              <button style={headerSt.dropItem} onClick={() => { setShowUserMenu(false); onLogout(); }}>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                ออกจากระบบ
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

const headerSt = {
  header:      { height:'60px', background:'white', borderBottom:'1px solid #E2E8F0', display:'flex', alignItems:'center', padding:'0 20px', gap:'12px', position:'fixed', top:0, left:'240px', right:0, zIndex:50, boxShadow:'0 1px 4px rgba(0,0,0,0.04)' },
  searchBox:   { display:'flex', alignItems:'center', gap:'9px', background:'#F8FAFC', border:'1.5px solid #E2E8F0', borderRadius:'10px', padding:'0 12px', maxWidth:'380px', flex:1 },
  searchInput: { border:'none', background:'transparent', outline:'none', fontSize:'14px', fontFamily:'Sarabun, sans-serif', padding:'10px 0', flex:1, color:'#1E293B', minWidth:0 },
  clearBtn:    { background:'none', border:'none', cursor:'pointer', color:'#94A3B8', display:'flex', padding:'2px' },
  actions:     { display:'flex', alignItems:'center', gap:'8px', marginLeft:'auto' },
  btnSecondary:{ display:'flex', alignItems:'center', gap:'6px', padding:'7px 13px', border:'1.5px solid #E2E8F0', background:'white', color:'#475569', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontWeight:'600', fontFamily:'Sarabun, sans-serif' },
  btnPrimary:  { display:'flex', alignItems:'center', gap:'6px', padding:'8px 16px', border:'none', background:'#1B3A6B', color:'white', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontWeight:'700', fontFamily:'Sarabun, sans-serif' },
  userWrap:    { position:'relative' },
  userBtn:     { display:'flex', alignItems:'center', gap:'9px', padding:'5px 10px', border:'1.5px solid #E2E8F0', borderRadius:'10px', background:'white', cursor:'pointer', fontFamily:'Sarabun, sans-serif' },
  avatar:      { width:'32px', height:'32px', borderRadius:'50%', background:'#1B3A6B', color:'white', display:'flex', alignItems:'center', justifyContent:'center', fontWeight:'800', fontSize:'14px', flexShrink:0 },
  userInfo:    { textAlign:'left' },
  userName:    { fontSize:'13px', fontWeight:'700', color:'#1E293B', lineHeight:'1.2' },
  userDept:    { fontSize:'11px', color:'#94A3B8' },
  dropdown:    { position:'absolute', top:'calc(100% + 8px)', right:0, background:'white', border:'1.5px solid #E2E8F0', borderRadius:'12px', padding:'8px', width:'200px', boxShadow:'0 8px 24px rgba(0,0,0,0.1)', zIndex:200 },
  dropUser:    { padding:'8px 10px 10px' },
  dropName:    { fontSize:'14px', fontWeight:'700', color:'#1E293B' },
  dropRole:    { fontSize:'12px', color:'#64748B' },
  dropDivider: { height:'1px', background:'#F1F5F9', margin:'4px 0' },
  dropItem:    { display:'flex', alignItems:'center', gap:'8px', width:'100%', padding:'9px 10px', border:'none', background:'transparent', color:'#DC2626', cursor:'pointer', fontSize:'13px', fontFamily:'Sarabun, sans-serif', borderRadius:'8px', fontWeight:'600' },
};

Object.assign(window, { Sidebar, AppHeader });
