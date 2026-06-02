// ============================================================
// src/components/Login.jsx
// ============================================================

function LoginPage({ onLogin }) {
  const [username, setUsername] = React.useState('');
  const [password, setPassword] = React.useState('');
  const [error, setError]       = React.useState('');
  const [loading, setLoading]   = React.useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setTimeout(() => {
      const user = USERS.find(u => u.username === username && u.password === password);
      if (user) { onLogin(user); }
      else { setError('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'); setLoading(false); }
    }, 400);
  };

  return (
    <div style={loginSt.wrap}>
      {/* Left panel */}
      <div style={loginSt.left}>
        <div style={loginSt.leftInner}>
          <div style={loginSt.logoRow}>
            <svg width="44" height="44" viewBox="0 0 48 48" fill="none">
              <rect width="48" height="48" rx="12" fill="white" fillOpacity="0.15"/>
              <path d="M10 30 L16 18 L22 26 L28 14 L34 22 L38 18" stroke="white" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/>
              <circle cx="38" cy="18" r="3" fill="#60A5FA"/>
            </svg>
            <div>
              <div style={loginSt.logoTitle}>ระบบราคากลาง</div>
              <div style={loginSt.logoSub}>Price Reference Management</div>
            </div>
          </div>

          <h1 style={loginSt.headline}>จัดการข้อมูล<br/>ราคากลางอุปกรณ์ IT</h1>
          <p style={loginSt.desc}>ระบบเก็บข้อมูล เปรียบเทียบ และพิมพ์ราคากลาง<br/>สำหรับการจัดซื้อจัดจ้างภาครัฐ</p>

          <div style={loginSt.features}>
            {['ค้นหาและเปรียบเทียบสินค้า', 'บันทึกราคากลางพร้อมวันที่อ้างอิง', 'พิมพ์ใบสรุปสเปค / Export Excel', 'ประวัติการแก้ไขข้อมูล'].map((f, i) => (
              <div key={i} style={loginSt.featureItem}>
                <div style={loginSt.featureDot}></div>
                <span>{f}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Right panel */}
      <div style={loginSt.right}>
        <div style={loginSt.card}>
          <h2 style={loginSt.cardTitle}>เข้าสู่ระบบ</h2>
          <p style={loginSt.cardSub}>กรุณาใส่ชื่อผู้ใช้และรหัสผ่าน</p>

          <form onSubmit={handleSubmit}>
            <div style={loginSt.field}>
              <label style={loginSt.label}>ชื่อผู้ใช้งาน</label>
              <div style={loginSt.inputWrap}>
                <svg style={loginSt.inputIcon} width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input type="text" value={username} onChange={e => setUsername(e.target.value)}
                  placeholder="กรอกชื่อผู้ใช้" style={loginSt.input} required autoFocus/>
              </div>
            </div>

            <div style={loginSt.field}>
              <label style={loginSt.label}>รหัสผ่าน</label>
              <div style={loginSt.inputWrap}>
                <svg style={loginSt.inputIcon} width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" value={password} onChange={e => setPassword(e.target.value)}
                  placeholder="กรอกรหัสผ่าน" style={loginSt.input} required/>
              </div>
            </div>

            {error && (
              <div style={loginSt.errorBox}>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                {error}
              </div>
            )}

            <button type="submit" style={loginSt.btn} disabled={loading}>
              {loading ? 'กำลังเข้าสู่ระบบ…' : 'เข้าสู่ระบบ'}
            </button>
          </form>

          <div style={loginSt.hint}>
            <div style={loginSt.hintTitle}>บัญชีทดสอบ</div>
            <div style={loginSt.hintRow}><code>admin</code> / <code>admin123</code><span style={loginSt.hintBadge}>ผู้ดูแล</span></div>
            <div style={loginSt.hintRow}><code>user01</code> / <code>user123</code><span style={{...loginSt.hintBadge, background:'#F1F5F9', color:'#475569'}}>ผู้ใช้</span></div>
          </div>
        </div>
      </div>
    </div>
  );
}

const loginSt = {
  wrap:        { display:'flex', minHeight:'100vh', fontFamily:'Sarabun, sans-serif' },
  left:        { flex:'1', background:'linear-gradient(145deg,#1B3A6B 0%,#1e4d99 100%)', display:'flex', alignItems:'center', padding:'60px' },
  leftInner:   { maxWidth:'440px' },
  logoRow:     { display:'flex', alignItems:'center', gap:'14px', marginBottom:'48px' },
  logoTitle:   { color:'white', fontWeight:'800', fontSize:'20px' },
  logoSub:     { color:'rgba(255,255,255,0.55)', fontSize:'12px', marginTop:'2px' },
  headline:    { color:'white', fontSize:'40px', fontWeight:'800', lineHeight:'1.2', margin:'0 0 16px' },
  desc:        { color:'rgba(255,255,255,0.65)', fontSize:'16px', lineHeight:'1.6', margin:'0 0 36px' },
  features:    { display:'flex', flexDirection:'column', gap:'12px' },
  featureItem: { display:'flex', alignItems:'center', gap:'10px', color:'rgba(255,255,255,0.85)', fontSize:'15px' },
  featureDot:  { width:'8px', height:'8px', borderRadius:'50%', background:'#60A5FA', flexShrink:0 },
  right:       { width:'480px', background:'#F8FAFC', display:'flex', alignItems:'center', justifyContent:'center', padding:'40px' },
  card:        { background:'white', borderRadius:'20px', padding:'44px', width:'100%', boxShadow:'0 4px 32px rgba(0,0,0,0.08)' },
  cardTitle:   { fontSize:'26px', fontWeight:'800', color:'#1E293B', margin:'0 0 6px' },
  cardSub:     { color:'#94A3B8', fontSize:'14px', margin:'0 0 32px' },
  field:       { marginBottom:'20px' },
  label:       { display:'block', fontSize:'13px', fontWeight:'700', color:'#374151', marginBottom:'7px' },
  inputWrap:   { position:'relative' },
  inputIcon:   { position:'absolute', left:'13px', top:'50%', transform:'translateY(-50%)', color:'#94A3B8', pointerEvents:'none' },
  input:       { width:'100%', padding:'11px 13px 11px 40px', border:'1.5px solid #E2E8F0', borderRadius:'10px', fontSize:'15px', fontFamily:'Sarabun, sans-serif', outline:'none', boxSizing:'border-box', color:'#1E293B' },
  errorBox:    { display:'flex', alignItems:'center', gap:'8px', color:'#DC2626', fontSize:'14px', background:'#FEF2F2', border:'1px solid #FECACA', borderRadius:'8px', padding:'10px 14px', marginBottom:'16px' },
  btn:         { width:'100%', padding:'13px', background:'#1B3A6B', color:'white', border:'none', borderRadius:'10px', fontSize:'16px', fontWeight:'700', cursor:'pointer', fontFamily:'Sarabun, sans-serif', marginTop:'4px', transition:'opacity 0.2s' },
  hint:        { marginTop:'28px', padding:'16px', background:'#F8FAFC', borderRadius:'10px', border:'1px solid #E2E8F0' },
  hintTitle:   { fontSize:'12px', fontWeight:'700', color:'#64748B', marginBottom:'10px', textTransform:'uppercase', letterSpacing:'0.06em' },
  hintRow:     { display:'flex', alignItems:'center', gap:'8px', fontSize:'13px', color:'#374151', marginBottom:'6px' },
  hintBadge:   { marginLeft:'auto', fontSize:'11px', background:'#DBEAFE', color:'#1D4ED8', padding:'2px 8px', borderRadius:'4px', fontWeight:'600' },
};

Object.assign(window, { LoginPage });
