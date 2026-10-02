const { chromium, request } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const B = 'http://127.0.0.1:8000';
const OUT = '/home/claude/work/shots';
fs.mkdirSync(OUT, { recursive: true });
const sql = q => execSync(`mysql -uroot inventaris_db -N -e "${q.replace(/"/g, '\\"')}"`).toString().trim();
const reset = () => execSync('mysql -uroot < /home/claude/work/inventaris/database.sql');

const R = { functional: [], responsive: [], e2e: [] };
const F = (id, area, skenario, harapan, cond, aktual) =>
  { R.functional.push({ id, area, skenario, harapan, aktual: String(aktual), lulus: !!cond }); console.log((cond ? 'PASS ' : 'FAIL ') + id + ' ' + skenario + ' -> ' + aktual); };

(async () => {
  reset();
  const api = await request.newContext({ baseURL: B, maxRedirects: 0 });
  const flashOf = async () => { const h = await (await api.get('/index.php')).text(); const m = h.match(/class="alert (\w+)">([^<]*)/); return m ? { type: m[1], msg: m[2] } : null; };
  let r, h;

  // ---------- FUNGSIONAL ----------
  r = await api.get('/index.php'); h = await r.text();
  F('F01', 'Read', 'Buka daftar produk (JOIN kategori+supplier)', 'HTTP 200, 5 baris, total 10', r.status() === 200 && (h.match(/<code>/g) || []).length === 5 && /Total 10 produk/.test(h), `HTTP ${r.status()}, ${(h.match(/<code>/g)||[]).length} baris`);

  for (const [id, q, exp] of [['F02', 'mouse', 1], ['F03', 'medan', 2], ['F04', 'ELK-001', 1], ['F05', 'zzzz', 0], ['F06', "' OR 1=1 --", 0]]) {
    r = await api.get('/index.php?q=' + encodeURIComponent(q)); h = await r.text();
    const n = (h.match(/<code>/g) || []).length;
    F(id, 'Search', `Cari "${q}"`, `HTTP 200, ${exp} hasil`, r.status() === 200 && n === exp, `HTTP ${r.status()}, ${n} hasil`);
  }

  for (const [id, p, exp] of [['F07', '2', 2], ['F08', '999', 2], ['F09', '0', 1], ['F10', 'abc', 1]]) {
    r = await api.get('/index.php?page=' + p); h = await r.text();
    const m = h.match(/halaman (\d+) \/ (\d+)/);
    F(id, 'Pagination', `page=${p}`, `HTTP 200, jatuh ke halaman ${exp}`, r.status() === 200 && m && +m[1] === exp, `HTTP ${r.status()}, ${m ? m[0] : '-'}`);
  }

  r = await api.get('/create.php'); h = await r.text();
  F('F11', 'Create', 'Dropdown kategori & supplier terisi dari DB', '12 <option> (2 placeholder + 5 + 5)', (h.match(/<option/g) || []).length === 12, `${(h.match(/<option/g)||[]).length} option`);

  r = await api.post('/create.php', { form: { kode: 'TST-001', nama: 'Produk <b>Uji</b>', kategori_id: '1', supplier_id: '2', harga: '12345.50', stok: '7' } });
  const fl = await flashOf(); const fl2 = await flashOf();
  F('F12', 'Create', 'Tambah produk valid', 'HTTP 302 ke index, flash sukses, data tersimpan', r.status() === 302 && fl && fl.type === 'success' && sql("SELECT COUNT(*) FROM produk WHERE kode='TST-001'") === '1', `HTTP ${r.status()}, flash=${fl && fl.type}`);
  F('F13', 'Flash', 'Flash tampil sekali (refresh tidak muncul lagi)', 'Flash kedua = kosong', fl2 === null, fl2 ? 'masih muncul' : 'hilang');
  h = await (await api.get('/index.php')).text();
  F('F14', 'Keamanan', 'Nama berisi <b> di-escape (XSS)', 'Tampil sebagai &lt;b&gt;', h.includes('Produk &lt;b&gt;Uji&lt;/b&gt;') && !h.includes('<b>Uji</b>'), 'ter-escape');

  r = await api.post('/create.php', { form: { kode: 'TST-001', nama: 'Dup', kategori_id: '1', supplier_id: '1', harga: '1', stok: '1' } }); h = await r.text();
  F('F15', 'Validasi', 'Kode duplikat', 'Pesan error, tidak tersimpan ganda', r.status() === 200 && /sudah dipakai/.test(h) && sql("SELECT COUNT(*) FROM produk WHERE kode='TST-001'") === '1', `HTTP ${r.status()}`);
  r = await api.post('/create.php', { form: { kode: '', nama: '', kategori_id: '0', supplier_id: '0', harga: 'abc', stok: '-3' } }); h = await r.text();
  F('F16', 'Validasi', 'Semua field kosong/tidak valid', '6 pesan error', (h.match(/<li>/g) || []).length === 6, `${(h.match(/<li>/g)||[]).length} pesan`);
  r = await api.post('/create.php', { form: { kode: 'T2', nama: 'N', kategori_id: '1', supplier_id: '1', harga: '-5', stok: '1.5' } }); h = await r.text();
  F('F17', 'Validasi', 'Harga negatif & stok desimal', '2 pesan error', (h.match(/<li>/g) || []).length === 2, `${(h.match(/<li>/g)||[]).length} pesan`);
  r = await api.post('/create.php', { form: { kode: 'FK-1', nama: 'N', kategori_id: '999', supplier_id: '1', harga: '1', stok: '1' } }); h = await r.text();
  F('F18', 'Validasi', 'FK tidak valid (kategori_id=999)', 'Ditolak DB, pesan error, tidak crash', r.status() === 200 && /tidak valid/.test(h), `HTTP ${r.status()}`);

  const id = sql("SELECT id FROM produk WHERE kode='TST-001'");
  r = await api.get('/edit.php?id=' + id); h = await r.text();
  F('F19', 'Update', 'Form edit ter-isi data lama', 'value kode & harga terisi', /name="kode"[^>]*value="TST-001"/.test(h) && /name="harga"[^>]*value="12345.50"/.test(h), 'pre-filled');
  r = await api.post('/edit.php?id=' + id, { form: { id, kode: 'TST-001', nama: 'Produk Diedit', kategori_id: '3', supplier_id: '3', harga: '99000', stok: '0' } });
  const fe = await flashOf();
  F('F20', 'Update', 'Simpan perubahan', 'HTTP 302, flash sukses, DB berubah', r.status() === 302 && fe && fe.type === 'success' && sql(`SELECT CONCAT(nama,'|',kategori_id,'|',harga,'|',stok) FROM produk WHERE id=${id}`) === 'Produk Diedit|3|99000.00|0', `HTTP ${r.status()}`);
  r = await api.post('/edit.php?id=' + id, { form: { kode: 'ELK-001', nama: 'X', kategori_id: '1', supplier_id: '1', harga: '1', stok: '1' } }); h = await r.text();
  F('F21', 'Update', 'Ubah kode jadi milik produk lain', 'Ditolak, data lama utuh', /sudah dipakai/.test(h) && sql(`SELECT kode FROM produk WHERE id=${id}`) === 'TST-001', 'ditolak');
  r = await api.get('/edit.php?id=9999'); const f9 = await flashOf();
  F('F22', 'Update', 'Edit id tidak ada', 'Redirect + flash error', r.status() === 302 && f9 && f9.type === 'error', `HTTP ${r.status()}`);

  r = await api.get('/delete.php?id=' + id);
  F('F23', 'Delete', 'Delete lewat GET', 'Ditolak, data tetap ada', sql(`SELECT COUNT(*) FROM produk WHERE id=${id}`) === '1', 'data tetap');
  r = await api.post('/delete.php', { form: { id } }); const fd = await flashOf();
  F('F24', 'Delete', 'Delete lewat POST', 'Terhapus + 1 baris log DELETE', sql(`SELECT COUNT(*) FROM produk WHERE id=${id}`) === '0' && sql(`SELECT COUNT(*) FROM log_aktivitas WHERE aksi='DELETE' AND produk_id=${id}`) === '1' && fd && fd.type === 'success', 'terhapus + log');
  const logBefore = sql('SELECT COUNT(*) FROM log_aktivitas');
  r = await api.post('/delete.php', { form: { id: '9999' } }); const fn = await flashOf();
  F('F25', 'Delete', 'Delete id tidak ada', 'Flash error, tidak ada log palsu', fn && fn.type === 'error' && sql('SELECT COUNT(*) FROM log_aktivitas') === logBefore, 'tidak ada log palsu');

  sql("INSERT INTO produk (kode,nama,kategori_id,supplier_id,harga,stok) VALUES ('RB-001','Uji Rollback',1,1,1000,1)");
  const rid = sql("SELECT id FROM produk WHERE kode='RB-001'");
  sql('RENAME TABLE log_aktivitas TO log_aktivitas_x');
  r = await api.post('/delete.php', { form: { id: rid } }); const fr = await flashOf();
  const still = sql(`SELECT COUNT(*) FROM produk WHERE id=${rid}`);
  sql('RENAME TABLE log_aktivitas_x TO log_aktivitas'); sql(`DELETE FROM produk WHERE id=${rid}`);
  F('F26', 'Transaction', 'Delete saat INSERT log gagal (tabel log dirusak sementara)', 'ROLLBACK: produk tetap ada, flash error', still === '1' && fr && fr.type === 'error', `produk ada=${still}`);

  r = await api.get('/export.php'); const buf = await r.body(); const csv = buf.toString('utf8');
  F('F27', 'Export', 'Export CSV', 'Content-Type csv, BOM UTF-8, 11 baris', /text\/csv/.test(r.headers()['content-type']) && buf[0] === 0xEF && buf[1] === 0xBB && buf[2] === 0xBF && csv.trim().split('\n').length === 11, `${csv.trim().split('\n').length} baris, BOM=${buf[0] === 0xEF}`);
  await api.dispose();

  // ---------- E2E BROWSER (confirm dialog) ----------
  reset();
  execSync('mysql -uroot inventaris_db', { input: "INSERT INTO produk (kode,nama,kategori_id,supplier_id,harga,stok) VALUES ('QT-001','Pensil \"2B\" & Penghapus O''Brien',2,2,5000,10);" });
  const browser = await chromium.launch();
  const pg = await browser.newPage({ viewport: { width: 1280, height: 800 } });
  const E = (id, skenario, cond, aktual = '') => { R.e2e.push({ id, skenario, lulus: !!cond, aktual }); console.log((cond ? 'PASS ' : 'FAIL ') + id + ' ' + skenario); };
  await pg.goto(B + '/index.php?q=Pensil');
  const nm = await pg.locator('tbody tr td').nth(2).innerText();
  E('E01', 'Nama berisi tanda kutip, & dan apostrof tampil utuh', nm === 'Pensil "2B" & Penghapus O\'Brien', nm);
  let msg = ''; pg.once('dialog', async d => { msg = d.message(); await d.dismiss(); });
  await pg.click('tbody tr button:has-text("Hapus")'); await pg.waitForTimeout(400);
  E('E02', 'Dialog confirm() muncul memuat nama produk lengkap', msg.includes('Pensil "2B" & Penghapus O\'Brien'), msg);
  E('E03', 'Klik Batal pada confirm: produk tidak terhapus', (await pg.locator('tbody tr code').count()) === 1);
  pg.once('dialog', d => d.accept());
  await pg.click('tbody tr button:has-text("Hapus")'); await pg.waitForLoadState();
  E('E04', 'Klik OK pada confirm: produk terhapus + flash sukses', sql("SELECT COUNT(*) FROM produk WHERE kode='QT-001'") === '0' && (await pg.locator('.alert.success').count()) === 1);

  // ---------- RESPONSIF 3 BREAKPOINT ----------
  const bps = [['Mobile', 375, 700], ['Tablet', 768, 900], ['Desktop', 1280, 800]];
  const pages = [['index', '/index.php'], ['create', '/create.php'], ['edit', '/edit.php?id=1']];
  for (const [bn, w, hgt] of bps) {
    await pg.setViewportSize({ width: w, height: hgt });
    for (const [pn, url] of pages) {
      await pg.goto(B + url);
      const m = await pg.evaluate(() => {
        const g = document.querySelector('.grid'), tw = document.querySelector('.table-wrap');
        return {
          pageOverflow: document.documentElement.scrollWidth > window.innerWidth,
          cols: g ? getComputedStyle(g).gridTemplateColumns.split(' ').length : null,
          tableScroll: tw ? tw.scrollWidth > tw.clientWidth : null,
        };
      });
      await pg.screenshot({ path: `${OUT}/${pn}_${w}.png`, fullPage: true });
      R.responsive.push({ bp: bn, w, page: pn, ...m });
      console.log(`${bn} ${w} ${pn}`, JSON.stringify(m));
    }
  }
  await browser.close();
  fs.writeFileSync('/home/claude/work/results.json', JSON.stringify(R, null, 1));
  const all = [...R.functional, ...R.e2e], bad = all.filter(x => !x.lulus);
  console.log(`\nFUNGSIONAL+E2E: ${all.length - bad.length}/${all.length} lulus`);
  process.exit(bad.length ? 1 : 0);
})().catch(e => { console.error(e); process.exit(2); });
