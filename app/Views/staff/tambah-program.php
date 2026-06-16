<?php
// Baca flag deadline error dari session key terpisah (tidak ditimpa oleh show_flash())
$showDeadlinePopup = !empty($_SESSION['sipedo_deadline_error']);
unset($_SESSION['sipedo_deadline_error']);
$staffCanAddProgram = StaffModel::isActiveUser((int)(current_user()['db_id'] ?? 0));
?>

<div class="section-head">
    <h3 class="section-title">Tambah Program Donasi Baru</h3>
</div>

<?php if (!$staffCanAddProgram): ?>
    <div class="flash flash-error">
        Status staff kamu sedang nonaktif. Kamu tetap bisa melihat halaman ini, tetapi tidak bisa menambahkan program baru sampai admin mengaktifkan kembali akun staff kamu.
    </div>
<?php endif; ?>

<div class="panel" style="padding:24px;">
    <form action="index.php?route=program/add" method="post" enctype="multipart/form-data"
          id="formTambahProgram">
        <input type="hidden" name="action" value="add">

        <div class="field">
            <label>Judul Program <span style="color:#dc2626;">*</span></label>
            <input type="text" name="name" placeholder="cth: Beasiswa Anak Yatim 2026" maxlength="150" required>
            <small style="color:#6b7280;">Maks. 150 karakter</small>
        </div>

        <div class="field">
            <label>Deskripsi Program</label>
            <textarea name="description" rows="3" placeholder="Jelaskan tujuan, manfaat, dan sasaran penerima program..."></textarea>
        </div>

        <div class="field">
            <label>Target Dana (Rp) <span style="color:#dc2626;">*</span></label>
            <input type="number" name="target" placeholder="cth: 50000000" min="100000" step="50000" required>
            <small style="color:#6b7280;">Minimal Rp 100.000</small>
        </div>

        <div class="field">
            <label>Tanggal Selesai <span style="color:#dc2626;">*</span></label>
            <input type="date" name="deadline" id="inputDeadline"
                   min="<?= date('Y-m-d') ?>" required>
            <small style="color:#6b7280;">Tanggal selesai tidak boleh hari ini atau sebelumnya.</small>
        </div>

        <div class="field">
            <label>Kategori <span style="color:#dc2626;">*</span></label>
            <select name="category" required>
                <option value="">Pilih Kategori</option>
                <option value="Pendidikan">Pendidikan</option>
                <option value="Kesehatan">Kesehatan</option>
                <option value="Keagamaan">Keagamaan</option>
                <option value="Pangan">Pangan &amp; Gizi</option>
                <option value="Infrastruktur">Infrastruktur</option>
                <option value="Lingkungan">Lingkungan</option>
                <option value="Sosial">Sosial</option>
                <option value="Kedaruratan">Kedaruratan</option>
            </select>
        </div>

        <div class="field">
            <label>Status Awal</label>
            <select name="status">
                <option value="active">Aktif (langsung publish)</option>
                <option value="draft">Draft (simpan dulu)</option>
            </select>
        </div>

        <div class="field">
            <label>Gambar Banner Program</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
            <small style="color:#6b7280;">JPG / PNG / WEBP — maks. 2 MB. Rasio ideal 16:9 (mis. 800×450 px). Kosongkan jika tidak ingin upload gambar.</small>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
            <a class="btn light" href="index.php?route=app&page=program-staff">Batal</a>
            <button class="btn green" type="submit" id="btnSimpan">Simpan Program</button>
        </div>
    </form>
</div>


<div id="modalDeadlinePast" style="
    display:none;
    position:fixed;inset:0;
    background:rgba(0,0,0,0.45);
    z-index:9999;
    align-items:center;
    justify-content:center;">
    <div style="
        background:#fff;
        border-radius:16px;
        padding:36px 32px;
        max-width:420px;
        width:90%;
        text-align:center;
        box-shadow:0 20px 60px rgba(0,0,0,0.25);
        animation:popIn .2s ease;">
        <div style="font-size:2.8rem;margin-bottom:12px;">📅</div>
        <h3 style="font-family:'Playfair Display',serif;color:#0f1f3d;margin-bottom:10px;font-size:1.25rem;">
            Program Tidak Bisa Didaftarkan
        </h3>
        <p style="color:#4b5563;font-size:0.9rem;line-height:1.6;margin-bottom:24px;">
            Tanggal yang kamu pilih sudah lewat.<br>
            <strong>Program tidak bisa didaftarkan di hari yang sudah lewat.</strong><br>
            Silakan pilih tanggal selesai mulai dari hari ini ke depan.
        </p>
        <button onclick="document.getElementById('modalDeadlinePast').style.display='none';
                         document.getElementById('inputDeadline').focus();"
                style="
                    background:#0f1f3d;color:#fff;
                    border:none;border-radius:10px;
                    padding:11px 28px;font-size:0.9rem;
                    font-family:'Poppins',sans-serif;
                    font-weight:600;cursor:pointer;">
            Oke, Pilih Tanggal Lain
        </button>
    </div>
</div>

<style>
@keyframes popIn {
    from { transform:scale(0.85); opacity:0; }
    to   { transform:scale(1);    opacity:1; }
}
</style>

<script>
(function () {
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var staffCanAddProgram = <?= $staffCanAddProgram ? 'true' : 'false' ?>;

    function showModal() {
        var m = document.getElementById('modalDeadlinePast');
        m.style.display = 'flex';
    }

    function showInactiveStaffWarning() {
        alert('Status staff kamu sedang nonaktif. Kamu hanya dapat melihat data dan tidak bisa menambahkan program baru sampai admin mengaktifkan kembali akun staff kamu.');
    }


    document.getElementById('inputDeadline').addEventListener('change', function () {
        var chosen = new Date(this.value);
        chosen.setHours(0, 0, 0, 0);
        if (chosen <= today) {
            this.value = '';
            showModal();
        }
    });


    document.getElementById('formTambahProgram').addEventListener('submit', function (e) {
        if (!staffCanAddProgram) {
            e.preventDefault();
            showInactiveStaffWarning();
            return;
        }
        var val = document.getElementById('inputDeadline').value;
        if (!val) return;
        var chosen = new Date(val);
        chosen.setHours(0, 0, 0, 0);
        if (chosen <= today) {
            e.preventDefault();
            document.getElementById('inputDeadline').value = '';
            showModal();
        }
    });


    <?php if ($showDeadlinePopup): ?>
    window.addEventListener('DOMContentLoaded', function () { showModal(); });
    <?php endif; ?>
})();
</script>
