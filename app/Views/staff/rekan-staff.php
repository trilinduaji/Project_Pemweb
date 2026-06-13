<?php
$myUserId  = (int)(current_user()['db_id'] ?? 0);
$rekanList = StaffModel::getRekanByStaff($myUserId);
$myPrograms= StaffModel::getProgramsForStaff($myUserId);
$allStaff  = array_filter($_SESSION['staffList'] ?? [], fn($s) => (int)($s['user_id'] ?? 0) !== $myUserId && $s['status'] === 'active');


$byProgram = [];
foreach ($rekanList as $r) {
    $byProgram[$r['program_id']][] = $r;
}
?>

<link rel="stylesheet" href="public/assets/css/rekan-staff.css">


<div class="rk-header" data-aos="fade-down">
    <div>
        <h3 class="rk-title">Rekan Se-Program</h3>
        <p class="rk-sub">Kelola rekan staff yang bekerja bersama di program yang kamu koordinasi.</p>
    </div>
    <button class="btn green rk-btn-add" id="btnTambahRekan">
        <span class="btn-icon">+</span> Tambah Rekan
    </button>
</div>


<?php if (empty($myPrograms)): ?>
<div class="rk-empty-notice" data-aos="fade-up">
    <div class="rk-empty-icon">📋</div>
    <p>Kamu belum memiliki program yang aktif.<br>Buat program terlebih dahulu untuk mulai menambahkan rekan.</p>
    <a class="btn green" href="index.php?route=app&page=tambah-program">+ Buat Program Baru</a>
</div>
<?php else: ?>


<?php if (empty($rekanList)): ?>
<div class="rk-empty-notice" data-aos="fade-up">
    <div class="rk-empty-icon">🤝</div>
    <p>Belum ada rekan yang ditambahkan ke programmu.<br>Klik tombol <strong>Tambah Rekan</strong> untuk mulai berkolaborasi.</p>
</div>
<?php else: ?>

<div class="rk-programs" id="rekanProgramList">
    <?php foreach ($myPrograms as $prog):
        $members = $byProgram[$prog['id']] ?? [];
        if (empty($members)) continue;
    ?>
    <div class="rk-program-block" data-aos="fade-up">
        <div class="rk-program-head">
            <div class="rk-program-badge"><?= e($prog['kode']) ?></div>
            <h4 class="rk-program-name"><?= e($prog['name']) ?></h4>
            <span class="rk-member-count"><?= count($members) ?> rekan</span>
        </div>

        <div class="rk-member-grid">
            <?php foreach ($members as $m):
                $isMe = (int)$m['staff_uid'] === $myUserId;
                $canRemove = (int)$m['created_by'] === $myUserId && !$isMe;
            ?>
            <div class="rk-member-card <?= $isMe ? 'rk-card-self' : '' ?>" data-aos="zoom-in">
                <div class="rk-avatar" style="background:<?= e($m['color'] ?? '#d97706') ?>">
                    <?= e($m['initials'] ?? strtoupper(substr($m['staff_name'], 0, 2))) ?>
                </div>
                <div class="rk-member-info">
                    <strong class="rk-member-name">
                        <?= e($m['staff_name']) ?>
                        <?php if ($isMe): ?><span class="rk-badge-self">Kamu</span><?php endif; ?>
                    </strong>
                    <small class="rk-member-email"><?= e($m['staff_email']) ?></small>
                    <span class="rk-role-pill"><?= e($m['role_in_program']) ?></span>
                </div>
                <div class="rk-member-meta">
                    <span class="rk-added-by">Ditambahkan oleh <em><?= e($m['added_by_name']) ?></em></span>
                    <span class="rk-joined"><?= date('d M Y', strtotime($m['joined_at'])) ?></span>
                </div>
                <?php if ($canRemove): ?>
                <form action="index.php?route=staff/remove-rekan" method="post" class="rk-remove-form">
                    <input type="hidden" name="ps_id" value="<?= (int)$m['ps_id'] ?>">
                    <button class="rk-btn-remove" type="submit"
                        onclick="return confirm('Keluarkan <?= e($m['staff_name']) ?> dari program ini?')">
                        ✕
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>


<div class="rk-modal-overlay" id="modalRekan">
    <div class="rk-modal" data-aos="zoom-in">
        <div class="rk-modal-head">
            <h4>Tambah Rekan ke Program</h4>
            <button class="rk-modal-close" id="btnCloseModal">&times;</button>
        </div>

        <form action="index.php?route=staff/add-rekan" method="post" class="rk-form" id="formTambahRekan">
            <div class="rk-field">
                <label>Pilih Program</label>
                <select name="program_id" required id="selectProgram">
                    <option value="">— Pilih program —</option>
                    <?php foreach ($myPrograms as $prog): ?>
                    <option value="<?= (int)$prog['id'] ?>"><?= e($prog['kode']) ?> — <?= e($prog['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="rk-field">
                <label>Pilih Rekan Staff</label>
                <select name="rekan_user_id" required id="selectRekan">
                    <option value="">— Pilih rekan —</option>
                    <?php foreach ($allStaff as $s): ?>
                    <option value="<?= (int)$s['user_id'] ?>"><?= e($s['name']) ?> (<?= e($s['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="rk-field">
                <label>Peran dalam Program</label>
                <select name="role_in_program">
                    <option value="Anggota">Anggota</option>
                    <option value="Koordinator Lapangan">Koordinator Lapangan</option>
                    <option value="Verifikator">Verifikator</option>
                    <option value="Dokumentasi">Dokumentasi</option>
                    <option value="Bendahara Program">Bendahara Program</option>
                </select>
            </div>

            <div class="rk-modal-actions">
                <button type="button" class="btn light" id="btnCancelModal">Batal</button>
                <button type="submit" class="btn green">
                    <span class="btn-icon">✓</span> Tambahkan Rekan
                </button>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<script>

const overlay   = document.getElementById('modalRekan');
const btnOpen   = document.getElementById('btnTambahRekan');
const btnClose  = document.getElementById('btnCloseModal');
const btnCancel = document.getElementById('btnCancelModal');

function openModal() {
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => overlay.querySelector('.rk-modal').classList.add('entered'), 10);
}
function closeModal() {
    overlay.querySelector('.rk-modal').classList.remove('entered');
    setTimeout(() => { overlay.classList.remove('active'); document.body.style.overflow = ''; }, 250);
}

if (btnOpen)   btnOpen.addEventListener('click', openModal);
if (btnClose)  btnClose.addEventListener('click', closeModal);
if (btnCancel) btnCancel.addEventListener('click', closeModal);
overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });


const aosEls = document.querySelectorAll('[data-aos]');
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('aos-visible'); observer.unobserve(e.target); }
    });
}, { threshold: 0.08 });
aosEls.forEach(el => observer.observe(el));


document.querySelectorAll('.rk-member-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px)';
        this.style.boxShadow = '0 8px 24px rgba(0,0,0,0.09)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = '';
        this.style.boxShadow = '';
    });
});


document.querySelectorAll('.rk-member-count').forEach(el => {
    const target = parseInt(el.textContent);
    let count = 0;
    const step = Math.ceil(target / 20);
    const timer = setInterval(() => {
        count = Math.min(count + step, target);
        el.textContent = count + ' rekan';
        if (count >= target) clearInterval(timer);
    }, 40);
});
</script>
