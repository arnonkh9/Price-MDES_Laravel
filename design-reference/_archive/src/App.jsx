// ============================================================
// src/App.jsx  –  Root application state + routing
// ============================================================

function App() {
  // ── Auth ──────────────────────────────────────────────────
  const [user, setUser] = React.useState(() => {
    try { return JSON.parse(localStorage.getItem('priceref_user')); } catch { return null; }
  });

  // ── Categories (dynamic) ─────────────────────────────────
  const [categories, setCategories] = React.useState(() => {
    try {
      const s = localStorage.getItem('priceref_cats');
      const base = s ? JSON.parse(s) : CATEGORIES;
      // Always merge CAT_COLORS so color field is populated
      return base.map(c => ({ ...c, color: c.color || CAT_COLORS[c.id] || '' }));
    } catch {
      return CATEGORIES.map(c => ({ ...c, color: CAT_COLORS[c.id] || '' }));
    }
  });

  // Keep globals in sync so all components see updated categories
  React.useEffect(() => {
    // Ensure every category has a color (migration for existing localStorage data)
    const withColors = categories.map(c => ({ ...c, color: c.color || CAT_COLORS[c.id] || '' }));
    window.CATEGORIES = withColors;
    window.CAT_COLORS = Object.fromEntries(
      withColors.filter(c => c.id !== 'all' && c.color).map(c => [c.id, c.color])
    );
    localStorage.setItem('priceref_cats', JSON.stringify(withColors));
  }, [categories]);

  const [showCatMgr, setShowCatMgr] = React.useState(false);

  const handleSaveCategories = newCats => {
    window.CATEGORIES = newCats;
    window.CAT_COLORS = Object.fromEntries(
      newCats.filter(c => c.id !== 'all' && c.color).map(c => [c.id, c.color])
    );
    setCategories(newCats);
    notify('อัปเดตหมวดหมู่สำเร็จ');
  };

  // ── Products ──────────────────────────────────────────────
  const [products, setProducts] = React.useState(() => {
    try { const s = localStorage.getItem('priceref_products'); return s ? JSON.parse(s) : INITIAL_PRODUCTS; }
    catch { return INITIAL_PRODUCTS; }
  });

  React.useEffect(() => {
    localStorage.setItem('priceref_products', JSON.stringify(products));
  }, [products]);

  // ── Specs (คุณลักษณะเฉพาะ) ──────────────────────────────
  const [specs, setSpecs] = React.useState(() => {
    try { const s = localStorage.getItem('priceref_specs'); return s ? JSON.parse(s) : INITIAL_SPECS; }
    catch { return INITIAL_SPECS; }
  });

  React.useEffect(() => {
    localStorage.setItem('priceref_specs', JSON.stringify(specs));
  }, [specs]);

  // ── Navigation ────────────────────────────────────────────
  const [view,     setView]     = React.useState('dashboard');
  const [category, setCategory] = React.useState('all');
  const [search,   setSearch]   = React.useState('');

  // ── Product Modals ────────────────────────────────────────
  const [selectedProduct, setSelectedProduct] = React.useState(null);
  const [editingProduct,  setEditingProduct]  = React.useState(null);
  const [showProductForm, setShowProductForm] = React.useState(false);

  // ── Spec Modals ───────────────────────────────────────────
  const [selectedSpec, setSelectedSpec]   = React.useState(null);
  const [editingSpec,  setEditingSpec]    = React.useState(null);
  const [showSpecForm, setShowSpecForm]   = React.useState(false);

  // ── Compare ───────────────────────────────────────────────
  const [compareList, setCompareList] = React.useState([]);
  const [baseSpec,    setBaseSpec]    = React.useState(null);

  // ── Toast ─────────────────────────────────────────────────
  const [toast, setToast] = React.useState(null);
  const notify = (msg, type = 'success') => {
    setToast({ msg, type });
    setTimeout(() => setToast(null), 3000);
  };

  // ── Auth handlers ─────────────────────────────────────────
  const handleLogin  = u => { setUser(u); localStorage.setItem('priceref_user', JSON.stringify(u)); };
  const handleLogout = () => { setUser(null); localStorage.removeItem('priceref_user'); };

  // ── Product handlers ──────────────────────────────────────
  const handleSaveProduct = p => {
    setProducts(prev => {
      const idx = prev.findIndex(x => x.id === p.id);
      if (idx >= 0) { const n = [...prev]; n[idx] = p; return n; }
      return [...prev, p];
    });
    setShowProductForm(false);
    setEditingProduct(null);
    notify((p.editHistory || []).length > 1 ? 'บันทึกการแก้ไขสำเร็จ' : 'เพิ่มสินค้าใหม่สำเร็จ');
  };

  const handleDeleteProduct = id => {
    if (!window.confirm('ต้องการลบสินค้านี้ใช่ไหม?')) return;
    setProducts(prev => prev.filter(p => p.id !== id));
    setCompareList(prev => prev.filter(c => c !== id));
    if (selectedProduct?.id === id) setSelectedProduct(null);
    notify('ลบสินค้าสำเร็จ');
  };

  const handleToggleCompare = id => {
    setCompareList(prev => {
      if (prev.includes(id)) return prev.filter(c => c !== id);
      if (prev.length >= 3) { notify('เปรียบเทียบได้สูงสุด 3 รายการ', 'warn'); return prev; }
      const next = [...prev, id];
      return next;
    });
  };

  const openEditProduct = p => { setEditingProduct(p); setShowProductForm(true); setSelectedProduct(null); };
  const openAddProduct  = () => { setEditingProduct(null); setShowProductForm(true); };

  // ── Spec handlers ─────────────────────────────────────────
  const handleSaveSpec = s => {
    setSpecs(prev => {
      const idx = prev.findIndex(x => x.id === s.id);
      if (idx >= 0) { const n = [...prev]; n[idx] = s; return n; }
      return [...prev, s];
    });
    setShowSpecForm(false);
    setEditingSpec(null);
    notify((s.editHistory || []).length > 1 ? 'บันทึกการแก้ไขสเปคสำเร็จ' : 'สร้างสเปคใหม่สำเร็จ');
  };

  const handleDeleteSpec = id => {
    if (!window.confirm('ต้องการลบสเปคนี้ใช่ไหม?')) return;
    setSpecs(prev => prev.filter(s => s.id !== id));
    if (baseSpec?.id === id) setBaseSpec(null);
    if (selectedSpec?.id === id) setSelectedSpec(null);
    notify('ลบสเปคสำเร็จ');
  };

  const openEditSpec   = s => { setEditingSpec(s); setShowSpecForm(true); setSelectedSpec(null); };
  const openAddSpec    = () => { setEditingSpec(null); setShowSpecForm(true); };

  const handleUseSpecCompare = s => {
    setBaseSpec(s);
    setView('compare');
    notify(`ใช้ "${s.name}" เป็นสเปคอ้างอิง`);
  };

  // ── Export ────────────────────────────────────────────────
  const handleImport = () => notify('ฟีเจอร์นำเข้า Excel จะพร้อมใช้งานเร็ว ๆ นี้', 'info');

  const handleExport = () => {
    const headers = ['id', 'category', 'brand', 'model', 'price', 'priceDate', 'priceRef'];
    const rows    = products.map(p => headers.map(h => JSON.stringify(p[h] || '')).join(','));
    const csv     = [headers.join(','), ...rows].join('\n');
    const blob    = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
    const url     = URL.createObjectURL(blob);
    const a       = document.createElement('a');
    a.href = url; a.download = 'ราคากลาง.csv'; a.click();
    URL.revokeObjectURL(url);
    notify('ส่งออกไฟล์ CSV สำเร็จ');
  };

  // ── Vendor 3-way Comparisons ──────────────────────────────
  const [comparisons, setComparisons] = React.useState(() => {
    try { const s = localStorage.getItem('priceref_cmp3'); return s ? JSON.parse(s) : INITIAL_COMPARISONS; }
    catch { return INITIAL_COMPARISONS; }
  });
  React.useEffect(() => {
    localStorage.setItem('priceref_cmp3', JSON.stringify(comparisons));
  }, [comparisons]);

  const [selectedCmp, setSelectedCmp] = React.useState(null);
  const [editingCmp,  setEditingCmp]  = React.useState(null);
  const [showCmpForm, setShowCmpForm] = React.useState(false);

  const handleSaveCmp = cmp => {
    setComparisons(prev => {
      const idx = prev.findIndex(x => x.id === cmp.id);
      if (idx >= 0) { const n = [...prev]; n[idx] = cmp; return n; }
      return [...prev, cmp];
    });
    setShowCmpForm(false); setEditingCmp(null);
    notify('บันทึกการเปรียบเทียบสำเร็จ');
  };
  const handleDeleteCmp = id => {
    if (!window.confirm('ต้องการลบการเปรียบเทียบนี้?')) return;
    setComparisons(prev => prev.filter(c => c.id !== id));
    if (selectedCmp?.id === id) setSelectedCmp(null);
    notify('ลบสำเร็จ');
  };
  const openEditCmp = cmp => { setEditingCmp(cmp); setShowCmpForm(true); setSelectedCmp(null); };
  const handleExcelCmp = cmp => exportComparisonExcel(cmp, specs);

  const handleAddHistory = (productId, entry) => {
    setProducts(prev => prev.map(p =>
      p.id === productId ? { ...p, editHistory: [...(p.editHistory || []), entry] } : p
    ));
    setSelectedProduct(prev => prev && prev.id === productId
      ? { ...prev, editHistory: [...(prev.editHistory || []), entry] }
      : prev
    );
    notify('บันทึกประวัติสำเร็จ');
  };

  const productCounts = React.useMemo(() => {
    const m = {};
    products.forEach(p => { m[p.category] = (m[p.category] || 0) + 1; });
    return m;
  }, [products]);

  // Show compare in sidebar if there are products OR a base spec
  const showCompare = compareList.length > 0 || !!baseSpec;

  // ── Login guard ───────────────────────────────────────────
  if (!user) return <LoginPage onLogin={handleLogin} />;

  return (
    <div style={appSt.root}>
      <Sidebar
        category={category}
        onCategory={setCategory}
        view={view}
        onView={setView}
        compareCount={compareList.length}
        productCounts={productCounts}
        specCount={specs.length}
        baseSpec={baseSpec}
        compareCount3={comparisons.length}
        onManageCategories={user.role === 'admin' ? () => setShowCatMgr(true) : null}
      />

      <div style={appSt.main}>
        <AppHeader
          user={user}
          onLogout={handleLogout}
          search={search}
          onSearch={q => { setSearch(q); if (q) { setView('list'); setCategory('all'); } }}
          onAdd={openAddProduct}
          onImport={handleImport}
          onExport={handleExport}
        />

        <main style={appSt.content}>
          {view === 'dashboard' && (
            <Dashboard
              products={products}
              onViewProduct={setSelectedProduct}
              onGoCategory={cat => { setCategory(cat); setView('list'); }}
            />
          )}

          {view === 'list' && (
            <ProductList
              products={products}
              category={category}
              search={search}
              onView={setSelectedProduct}
              onEdit={openEditProduct}
              onDelete={handleDeleteProduct}
              compareList={compareList}
              onToggleCompare={handleToggleCompare}
              user={user}
            />
          )}

          {view === 'specs' && (
            <SpecListView
              specs={specs}
              onView={setSelectedSpec}
              onEdit={openEditSpec}
              onDelete={handleDeleteSpec}
              onUseCompare={handleUseSpecCompare}
              user={user}
            />
          )}

          {view === 'vendor3' && (
            <VendorCompareList
              comparisons={comparisons}
              specs={specs}
              onView={setSelectedCmp}
              onEdit={openEditCmp}
              onDelete={handleDeleteCmp}
              onExcel={handleExcelCmp}
              user={user}
            />
          )}

          {view === 'compare' && (
            <CompareView
              compareIds={compareList}
              products={products}
              baseSpec={baseSpec}
              onSetBaseSpec={setBaseSpec}
              onClearBaseSpec={() => setBaseSpec(null)}
              onClear={() => setCompareList([])}
              onRemove={id => setCompareList(prev => prev.filter(c => c !== id))}
              onViewProduct={setSelectedProduct}
              specs={specs}
              onGoSpecs={() => { setView('specs'); }}
            />
          )}
        </main>
      </div>

      {/* ── Category Manager ── */}
      {showCatMgr && (
        <CategoryManager
          categories={categories}
          products={products}
          onSave={handleSaveCategories}
          onClose={() => setShowCatMgr(false)}
        />
      )}

      {/* ── Product modals ── */}
      {selectedProduct && (
        <ProductDetailModal
          product={selectedProduct}
          onClose={() => setSelectedProduct(null)}
          onEdit={openEditProduct}
          compareList={compareList}
          onToggleCompare={handleToggleCompare}
          onAddHistory={handleAddHistory}
          user={user}
        />
      )}
      {showProductForm && (
        <ProductFormModal
          product={editingProduct}
          onSave={handleSaveProduct}
          onClose={() => { setShowProductForm(false); setEditingProduct(null); }}
          user={user}
        />
      )}

      {/* ── Spec modals ── */}
      {selectedSpec && (
        <SpecDetailModal
          spec={selectedSpec}
          onClose={() => setSelectedSpec(null)}
          onEdit={openEditSpec}
          onUseCompare={handleUseSpecCompare}
          user={user}
        />
      )}
      {showSpecForm && (
        <SpecFormModal
          spec={editingSpec}
          onSave={handleSaveSpec}
          onClose={() => { setShowSpecForm(false); setEditingSpec(null); }}
          user={user}
        />
      )}

      {/* ── Vendor 3-way modals ── */}
      {selectedCmp && (
        <VendorCompareDetail
          cmp={selectedCmp}
          specs={specs}
          onClose={() => setSelectedCmp(null)}
          onEdit={openEditCmp}
          onExcel={handleExcelCmp}
          user={user}
        />
      )}
      {showCmpForm && (
        <VendorCompareForm
          cmp={editingCmp}
          specs={specs}
          onSave={handleSaveCmp}
          onClose={() => { setShowCmpForm(false); setEditingCmp(null); }}
          user={user}
        />
      )}

      {/* ── Toast ── */}
      {toast && (
        <div style={{
          ...appSt.toast,
          background: toast.type === 'warn' ? '#D97706'
            : toast.type === 'info' ? '#0369A1'
            : '#1B3A6B',
        }}>
          {toast.type === 'success' && (
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="2.5" strokeLinecap="round">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          )}
          {toast.msg}
        </div>
      )}
    </div>
  );
}

const appSt = {
  root:  { display: 'flex', minHeight: '100vh', background: '#F0F4F8', fontFamily: 'Sarabun, sans-serif' },
  main:  { marginLeft: '240px', flex: 1, display: 'flex', flexDirection: 'column', minHeight: '100vh' },
  content: { marginTop: '60px', flex: 1, overflowY: 'auto' },
  toast: {
    position: 'fixed', bottom: '24px', right: '24px',
    color: 'white', padding: '12px 20px', borderRadius: '10px',
    fontSize: '14px', fontWeight: '700', fontFamily: 'Sarabun, sans-serif',
    boxShadow: '0 8px 24px rgba(0,0,0,0.18)',
    display: 'flex', alignItems: 'center', gap: '8px',
    zIndex: 500, animation: 'slideUp 0.25s ease',
  },
};

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
